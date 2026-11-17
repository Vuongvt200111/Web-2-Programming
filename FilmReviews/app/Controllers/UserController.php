<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\AuthContext;
use App\Core\AuthMiddleware;
use App\Core\Config;
use App\Core\Flash;
use App\Core\Response;
use App\Models\CategoryModel;
use App\Models\FilmModel;
use App\Models\MessageModel;
use App\Models\UserModel;
use App\Services\AuditLogger;
use App\Services\MediaService;
use App\Services\SecurityService;
use App\Services\TabSession;

/**
 * Handles user profile management, support inquiries, and reviewer account self-deactivation.
 */
final class UserController extends BaseController
{
    private UserModel $users;
    private CategoryModel $categories;
    private MessageModel $messages;
    private FilmModel $films;
    private MediaService $media;

    public function __construct(
        Config $config,
        Response $response,
        Flash $flash,
        SecurityService $security,
        AuthMiddleware $auth,
        AuditLogger $auditLogger,
        UserModel $users,
        CategoryModel $categories,
        MessageModel $messages,
        FilmModel $films,
        MediaService $media
    ) {
        parent::__construct($config, $response, $flash, $security, $auth, $auditLogger);
        $this->users = $users;
        $this->categories = $categories;
        $this->messages = $messages;
        $this->films = $films;
        $this->media = $media;
    }

    public function profile(): array
    {
        $auth = $this->auth->requireAuthenticated('login');
        $userId = (int) $auth->userId();

        $error = (string) $this->flash->pull('error_message', '');
        $success = '';

        $userInfo = $this->users->findById($userId);
        if (!$userInfo || (!empty($userInfo['is_deleted']) && (int) $userInfo['is_deleted'] === 1)) {
            TabSession::logout();
            $this->redirect('index.php?route=login');
        }

        $categories = $this->categories->findAllAlphabetical();
        $isBoss = AuthContext::isBossRecord($userInfo);
        $userRole = (int) ($userInfo['role'] ?? 0);
        $isReviewerOnly = !$isBoss && $userRole === 0;
        $canChangeOwnPassword = ($isReviewerOnly || $isBoss);

        $requestedSection = (string) ($_GET['section'] ?? 'profile');
        $allowedSections = $isReviewerOnly
            ? ['profile', 'password', 'films']
            : ($isBoss ? ['profile', 'password'] : ['profile']);
        $activeSection = in_array($requestedSection, $allowedSections, true)
            ? $requestedSection
            : 'profile';

        if ($isReviewerOnly && ($_GET['status'] ?? '') === 'film_deleted') {
            $success = 'Your film has been successfully deleted.';
        }

        if ($this->isPost() && isset($_POST['change_password'])) {
            $activeSection = $canChangeOwnPassword ? 'password' : 'profile';
            $this->security->validateCsrfRequest($this->baseUrl() . 'index.php?route=user/profile');

            if (!$canChangeOwnPassword) {
                $error = 'Access Denied: Sub-Admin accounts cannot change their own password. Please request a password reset from the System Boss!';
            } else {
                $currentPassword = (string) ($_POST['current_password'] ?? '');
                $newPassword = (string) ($_POST['new_password'] ?? '');
                $confirmPassword = (string) ($_POST['confirm_password'] ?? '');

                if ($currentPassword === '' || $newPassword === '' || $confirmPassword === '') {
                    $error = 'Please fill in all password fields!';
                } elseif (!password_verify($currentPassword, (string) ($userInfo['password'] ?? ''))) {
                    $error = 'Current password is incorrect!';
                } elseif (strlen($newPassword) < 6) {
                    $error = 'New password must be at least 6 characters long!';
                } elseif ($newPassword !== $confirmPassword) {
                    $error = 'New password and confirmation do not match!';
                } elseif ($currentPassword === $newPassword) {
                    $error = 'New password must be different from your current password!';
                } else {
                    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                    $this->users->update([
                        'id'       => $userId,
                        'password' => $hashedPassword,
                    ]);
                    $userInfo['password'] = $hashedPassword;
                    if ($isBoss) {
                        $this->auditLogger->log(
                            'CHANGE_BOSS_PASSWORD',
                            "The System Boss changed own account password via Profile ({$userInfo['email']})"
                        );
                    }
                    $success = 'Your password has been changed successfully!';
                }
            }
        }

        if ($this->isPost() && isset($_POST['update_profile'])) {
            $this->security->validateCsrfRequest($this->baseUrl() . 'index.php?route=user/profile');

            $name = trim((string) ($_POST['name'] ?? ''));
            $bio = trim((string) ($_POST['bio'] ?? ''));
            $favoriteGenre = trim((string) ($_POST['favorite_genre'] ?? ''));

            $oldAvatar = $userInfo['avatar'] ?? null;
            $avatar = $oldAvatar;
            $uploadOk = true;
            $isNewAvatarUploaded = false;

            if (isset($_FILES['avatar']) && ($_FILES['avatar']['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK) {
                $uploadResult = $this->media->uploadSecureImage($_FILES['avatar']);
                if (!$uploadResult['success']) {
                    $error = (string) ($uploadResult['error'] ?? 'Upload failed!');
                    $uploadOk = false;
                } else {
                    $avatar = $uploadResult['filename'];
                    $isNewAvatarUploaded = true;
                }
            }

            if ($name === '') {
                $error = 'The display name cannot be left blank!';
            } elseif ($uploadOk) {
                $this->users->update([
                    'id'             => $userId,
                    'name'           => $name,
                    'bio'            => $bio,
                    'favorite_genre' => $favoriteGenre !== '' ? $favoriteGenre : null,
                    'avatar'         => $avatar,
                ]);

                if ($isNewAvatarUploaded && !empty($oldAvatar) && $oldAvatar !== $avatar) {
                    $this->media->deleteSafely((string) $oldAvatar);
                }

                TabSession::set('name', $name);
                TabSession::set('avatar', $avatar);

                $success = 'Profile updated successfully!';
                $userInfo['name'] = $name;
                $userInfo['bio'] = $bio;
                $userInfo['favorite_genre'] = $favoriteGenre;
                $userInfo['avatar'] = $avatar;
            }
        }

        $myFilms = [];
        $pendingCount = 0;
        $approvedCount = 0;

        if ($isReviewerOnly) {
            $myFilms = $this->films->findByUserWithStats($userId);
            foreach ($myFilms as &$mf) {
                $mf['poster_url'] = $this->media->resolvePoster($mf['images'] ?? null, $this->baseUrl());
                $mf['normalized_status'] = (($mf['status'] ?? 'pending') === 'approved') ? 'approved' : 'pending';
                $mf['created_at_formatted'] = !empty($mf['created_at'])
                    ? date('d M Y', strtotime((string) $mf['created_at']))
                    : '';
                $mf['avg_score_formatted'] = number_format((float) ($mf['avg_score'] ?? 0), 1);
                $mf['search_haystack'] = mb_strtolower(
                    (string) ($mf['title'] ?? 'Untitled Film') . ' ' . (string) ($mf['category_name'] ?? 'General'),
                    'UTF-8'
                );

                if ($mf['normalized_status'] === 'approved') {
                    $approvedCount++;
                } else {
                    $pendingCount++;
                }
            }
            unset($mf);
        }

        $profileAvatar = $this->media->resolveAvatar(
            $userInfo['avatar'] ?? null,
            (string) ($userInfo['name'] ?? 'U'),
            $this->baseUrl()
        );

        return $this->view('user/profile', 'layouts/catalog', 'Profile', [
            'userInfo'       => $userInfo,
            'profileAvatar'  => $profileAvatar,
            'categories'     => $categories,
            'error'          => $error,
            'success'        => $success,
            'activeSection'        => $activeSection,
            'isReviewerOnly'       => $isReviewerOnly,
            'canChangeOwnPassword' => $canChangeOwnPassword,
            'isTargetBoss'         => $isBoss,
            'isTargetAdmin'  => AuthContext::isAdminRecord($userInfo),
            'myFilms'        => $myFilms,
            'pendingCount'   => $pendingCount,
            'approvedCount'  => $approvedCount,
        ]);
    }

    public function contact(): array
    {
        $auth = $this->auth->requireAuthenticated('login');
        $userId = (int) $auth->userId();

        $currentUser = $this->users->findById($userId);
        if (!$currentUser || (!empty($currentUser['is_deleted']) && (int) $currentUser['is_deleted'] === 1)) {
            TabSession::logout();
            $this->redirect('index.php?route=login');
        }

        $successMsg = (string) $this->flash->pull('contact_success', '');
        $errorMsg = (string) $this->flash->pull('contact_error', '');

        if ($this->isPost()) {
            $this->security->validateCsrfRequest($this->baseUrl() . 'index.php?route=user/contact');

            $userMessage = trim((string) ($_POST['user_message'] ?? ''));
            if ($userMessage === '') {
                $this->flash->set('contact_error', 'Please enter the content of the message!');
            } else {
                if (!$this->messages->isRecentDuplicate($userId, $userMessage, 60)) {
                    $this->messages->insert([
                        'user_id'      => $userId,
                        'user_message' => $userMessage,
                        'status'       => 'pending',
                        'created_at'   => date('Y-m-d H:i:s'),
                    ]);
                }
                $this->flash->set('contact_success', 'Your letter has been successfully sent! The admin will respond as soon as possible. Thank you!');
            }

            $this->redirect('index.php?route=user/contact');
        }

        $userMessages = $this->messages->findByUserOrdered($userId);
        foreach ($userMessages as &$msg) {
            $msg['created_at_formatted'] = !empty($msg['created_at'])
                ? date('M d, Y - H:i', strtotime((string) $msg['created_at']))
                : '';
            $msg['updated_at_formatted'] = !empty($msg['updated_at'])
                ? date('M d, Y - H:i', strtotime((string) $msg['updated_at']))
                : '';
        }
        unset($msg);

        return $this->view('user/contact', 'layouts/catalog', 'Contact Admin & Support History', [
            'currentUser'  => $currentUser,
            'error_msg'    => $errorMsg,
            'success_msg'  => $successMsg,
            'userMessages' => $userMessages,
        ]);
    }

    public function deleteAccount(): never
    {
        $auth = $this->auth->requireAuthenticated('login');
        $this->auth->requirePost('Method Not Allowed. Account deletion requires POST request.');
        $this->security->validateCsrfRequest();

        if ($auth->isAdmin()) {
            $this->flash->set(
                'error_message',
                'Administrative Safety: Administrative accounts (Boss and Sub-Admins) cannot self-delete. Privileges must be revoked by the System Boss.'
            );
            $this->redirect('index.php?route=user/profile');
        }

        $this->users->update([
            'id'         => $auth->userId(),
            'is_deleted' => 1,
        ]);

        TabSession::logout();
        $this->redirect('index.php?route=film/list&status=account_deleted');
    }
}
