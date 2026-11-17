<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Core\AuthContext;
use App\Core\AuthMiddleware;
use App\Core\Config;
use App\Core\Flash;
use App\Core\Response;
use App\Models\UserModel;
use App\Services\AuditLogger;
use App\Services\MediaService;
use App\Services\SecurityService;

/**
 * Hierarchical RBAC User Management, Suspension (Blocking), Restoration, and Sub-Admin Provisioning.
 */
final class AdminUserController extends BaseController
{
    private UserModel $users;
    private MediaService $media;

    public function __construct(
        Config $config,
        Response $response,
        Flash $flash,
        SecurityService $security,
        AuthMiddleware $auth,
        AuditLogger $auditLogger,
        UserModel $users,
        MediaService $media
    ) {
        parent::__construct($config, $response, $flash, $security, $auth, $auditLogger);
        $this->users = $users;
        $this->media = $media;
    }

    public function manageUsers(): array
    {
        $auth = $this->auth->requireAdmin();
        $this->users->syncExpiredBlocks();

        $message = (string) $this->flash->pull('error_message', '');
        if ($message === '' && isset($_GET['status'])) {
            $status = (string) $_GET['status'];
            if ($status === 'restored') {
                $message = 'User account access has been restored successfully!';
            } elseif ($status === 'notfound') {
                $message = 'Target user does not exist.';
            }
        }

        if ($this->isPost()) {
            $this->security->validateCsrfRequest($this->baseUrl() . 'index.php?route=admin/manage-users');
            $message = $this->dispatchUserAction($auth);
        }

        $rawUsers = $this->users->findAllOrderedForAdmin();
        $enrichedUsers = [];
        foreach ($rawUsers as $u) {
            $uid = (int) ($u['id'] ?? 0);
            $u['role_int'] = (int) ($u['role'] ?? 0);
            $u['is_target_boss'] = AuthContext::isBossRecord($u);
            $u['is_self'] = ($uid === (int) $auth->userId());
            $u['is_target_blocked'] = $this->users->isBlocked($u);
            $u['is_deleted_flag'] = ((int) ($u['is_deleted'] ?? 0) === 1);
            $u['blocked_until_formatted'] = !empty($u['blocked_until'])
                ? date('d/m/Y H:i', strtotime((string) $u['blocked_until']))
                : null;
            $u['created_at_formatted'] = !empty($u['created_at'])
                ? date('d/m/Y H:i', strtotime((string) $u['created_at']))
                : 'N/A';
            $u['avatar_meta'] = $this->media->resolveAvatar(
                $u['avatar'] ?? null,
                (string) ($u['name'] ?? 'U'),
                $this->baseUrl()
            );
            $enrichedUsers[] = $u;
        }

        return $this->view('admin/manage_users', 'layouts/admin', 'Manage Users', [
            'users'   => $enrichedUsers,
            'message' => $message,
            'isBoss'  => $auth->isBoss(),
        ]);
    }

    public function restoreUser(): never
    {
        $auth = $this->auth->requireAdmin();

        if (!$this->isPost() || !isset($_POST['id'])) {
            $this->redirect('index.php?route=admin/manage-users');
        }

        $this->security->validateCsrfRequest($this->baseUrl() . 'index.php?route=admin/manage-users');
        $targetUserId = (int) $_POST['id'];
        $target = $this->users->findById($targetUserId);

        if (!$target) {
            $this->redirect('index.php?route=admin/manage-users&status=notfound');
        }

        if (AuthContext::isAdminRecord($target) && !$auth->isBoss()) {
            $this->auditLogger->log(
                'SECURITY_VIOLATION',
                "Sub-Admin attempted unauthorized restoration of Admin ID: {$targetUserId}"
            );
            $this->flash->set('error_message', 'Access denied: Only the System Boss can restore administrative accounts!');
            $this->redirect('index.php?route=admin/manage-users&status=denied');
        }

        $this->users->update([
            'id'         => $targetUserId,
            'is_deleted' => 0,
            'status'     => 'active',
        ]);
        $this->auditLogger->log(
            'RESTORE_USER',
            "Restored deactivated user account '" . ($target['name'] ?? 'User') . "' (ID: {$targetUserId})"
        );

        $this->redirect('index.php?route=admin/manage-users&status=restored');
    }

    public function addNewAdmin(): array
    {
        $this->auth->requireBoss('admin/add-new-admin');

        $error = '';
        $success = '';
        $restoreError = '';
        $restoreSuccess = '';
        $restoreEmail = '';
        $activePanel = 'create';

        if ($this->isPost()) {
            $this->security->validateCsrfRequest($this->baseUrl() . 'index.php?route=admin/add-new-admin');
            $formAction = trim((string) ($_POST['action'] ?? 'create_admin'));

            if ($formAction === 'restore_sub_admin') {
                $activePanel = 'restore';
                $restoreEmail = trim((string) ($_POST['sub_admin_email'] ?? ''));
                $newPassword = (string) ($_POST['new_password'] ?? '');
                $confirmPassword = (string) ($_POST['confirm_password'] ?? '');

                if ($restoreEmail === '' || $newPassword === '' || $confirmPassword === '') {
                    $restoreError = 'Please fill in all required fields!';
                } elseif (!filter_var($restoreEmail, FILTER_VALIDATE_EMAIL)) {
                    $restoreError = 'Invalid Sub-Admin email address format!';
                } elseif (strlen($newPassword) < 6) {
                    $restoreError = 'New password must be at least 6 characters long!';
                } elseif ($newPassword !== $confirmPassword) {
                    $restoreError = 'New password and confirmation do not match!';
                } else {
                    $target = $this->users->findByEmail($restoreEmail);
                    if (!$target) {
                        $restoreError = 'No account found with this email address!';
                    } elseif (AuthContext::isBossRecord($target) || (int) ($target['role'] ?? 0) < 2) {
                        $restoreError = 'Access Denied: Only Sub-Admin accounts can be restored from this panel!';
                    } else {
                        $hashed = password_hash($newPassword, PASSWORD_DEFAULT);
                        $this->users->resetSubAdminPassword((int) $target['id'], $hashed);
                        $this->auditLogger->log(
                            'RESET_SUBADMIN_PASSWORD',
                            "The System Boss restored password for Sub-Admin '{$target['name']}' ({$restoreEmail}, ID: {$target['id']})"
                        );
                        $restoreSuccess = "Password restored successfully for Sub-Admin '{$target['name']}' ({$restoreEmail})!";
                        $restoreEmail = '';
                    }
                }
            } else {
                $name = trim((string) ($_POST['name'] ?? ''));
                $email = trim((string) ($_POST['email'] ?? ''));
                $password = (string) ($_POST['password'] ?? '');

                if ($name === '' || $email === '' || $password === '') {
                    $error = 'Please fill in all the required information!';
                } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $error = 'Invalid email address format!';
                } elseif ($this->users->findByEmail($email)) {
                    $error = 'This email already exists in the system!';
                } else {
                    $newAdminId = $this->users->insert([
                        'name'       => $name,
                        'email'      => $email,
                        'password'   => password_hash($password, PASSWORD_DEFAULT),
                        'role'       => 2,
                        'badge'      => 'Sub-Admin',
                        'status'     => 'active',
                        'is_deleted' => 0,
                        'created_at' => date('Y-m-d H:i:s'),
                    ]);
                    $this->auditLogger->log(
                        'CREATE_ADMIN',
                        "The System Boss created new Sub-Admin account '{$name}' ({$email}, ID: {$newAdminId})"
                    );
                    $success = 'New Sub-Admin account created successfully with badge Sub-Admin!';
                }
            }
        }

        return $this->view('admin/add_new_admin', 'layouts/admin', 'Add New Sub-Admin', [
            'error'          => $error,
            'success'        => $success,
            'restoreError'   => $restoreError,
            'restoreSuccess' => $restoreSuccess,
            'restoreEmail'   => $restoreEmail,
            'activePanel'    => $activePanel,
            'subAdmins'      => $this->users->findSubAdmins(),
        ]);
    }

    private function dispatchUserAction(AuthContext $auth): string
    {
        if (isset($_POST['promote_id'])) {
            return $this->handlePromote($auth, (int) $_POST['promote_id']);
        }
        if (isset($_POST['demote_id'])) {
            return $this->handleDemote($auth, (int) $_POST['demote_id']);
        }
        if (isset($_POST['delete_id'])) {
            return $this->handleDeleteUser($auth, (int) $_POST['delete_id']);
        }
        if (isset($_POST['block_id'])) {
            return $this->handleBlockUser($auth, (int) $_POST['block_id']);
        }
        if (isset($_POST['unblock_id'])) {
            return $this->handleUnblockUser($auth, (int) $_POST['unblock_id']);
        }

        return '';
    }

    private function handlePromote(AuthContext $auth, int $promoteId): string
    {
        if (!$auth->isBoss()) {
            $this->auditLogger->log('SECURITY_VIOLATION', "Sub-Admin attempted unauthorized promotion of user ID: {$promoteId}");
            return 'Access Denied: Only the System Boss can promote users to Sub-Admin!';
        }

        $target = $this->users->findById($promoteId);
        if (!$target || (int) ($target['role'] ?? 0) !== 0) {
            return 'Invalid target or user is already an administrator.';
        }

        $this->users->update([
            'id'    => $promoteId,
            'role'  => 2,
            'badge' => 'Sub-Admin',
        ]);
        $this->auditLogger->log('PROMOTE_USER', "Promoted user '{$target['name']}' (ID: {$promoteId}) to Sub-Admin");

        return "Successfully promoted user '{$target['name']}' to Sub-Admin!";
    }

    private function handleDemote(AuthContext $auth, int $demoteId): string
    {
        if (!$auth->isBoss()) {
            $this->auditLogger->log('SECURITY_VIOLATION', "Sub-Admin attempted unauthorized demotion of Admin ID: {$demoteId}");
            return 'Access Denied: Only the System Boss can demote Sub-Admins!';
        }

        $target = $this->users->findById($demoteId);
        if (!$target) {
            return 'Target user does not exist.';
        }
        if (AuthContext::isBossRecord($target)) {
            return 'Cannot demote the System Boss!';
        }
        if ((int) ($target['role'] ?? 0) >= 2) {
            $this->users->update([
                'id'    => $demoteId,
                'role'  => 0,
                'badge' => 'Member',
            ]);
            $this->auditLogger->log('DEMOTE_USER', "Demoted Sub-Admin '{$target['name']}' (ID: {$demoteId}) to regular Member");
            return "Successfully demoted Sub-Admin '{$target['name']}' to regular Member!";
        }

        return 'Target user is not a Sub-Admin.';
    }

    private function handleDeleteUser(AuthContext $auth, int $deleteId): string
    {
        if ($deleteId === 1) {
            $this->auditLogger->log('SECURITY_VIOLATION', 'Attempted unauthorized deletion of root admin account (ID: 1)');
            return 'Security Violation: Cannot delete root admin account (System Boss)!';
        }
        if ($deleteId === (int) $auth->userId()) {
            return 'Administrative Safety: Administrators cannot delete their own active account!';
        }

        $target = $this->users->findById($deleteId);
        if (!$target) {
            return 'Target user does not exist.';
        }

        if (AuthContext::isBossRecord($target)) {
            $this->auditLogger->log('SECURITY_VIOLATION', "Attempted unauthorized deletion of System Boss account (ID: {$deleteId})");
            return 'Security Violation: Cannot delete System Boss account!';
        }

        $targetRole = (int) ($target['role'] ?? 0);
        if (!$auth->isBoss() && AuthContext::isAdminRecord($target)) {
            $this->auditLogger->log('SECURITY_VIOLATION', "Sub-Admin attempted unauthorized deletion of peer Admin account (ID: {$deleteId})");
            return 'Access Denied: Sub-Admins are strictly forbidden from deleting peer Admin accounts!';
        }

        $this->media->deleteSafely($target['avatar'] ?? null);
        $this->users->delete($deleteId);

        if ($auth->isBoss()) {
            $this->auditLogger->log('DELETE_USER', "The System Boss deleted account '{$target['name']}' (ID: {$deleteId}, Role: {$targetRole})");
            return "The System Boss has successfully deleted account '{$target['name']}'!";
        }

        $this->auditLogger->log('DELETE_USER', "Sub-Admin deleted regular member '{$target['name']}' (ID: {$deleteId})");
        return "Member account '{$target['name']}' has been deleted!";
    }

    private function handleBlockUser(AuthContext $auth, int $blockId): string
    {
        $target = $this->users->findById($blockId);
        if (!$target) {
            return 'Target user does not exist.';
        }
        if (AuthContext::isBossRecord($target)) {
            $this->auditLogger->log('SECURITY_VIOLATION', "Attempted unauthorized blocking of System Boss (ID: {$blockId})");
            return 'Security Violation: Cannot block the System Boss!';
        }
        if ($blockId === (int) $auth->userId()) {
            return 'Administrative Safety: Administrators cannot block their own active account!';
        }
        if (!$auth->isBoss() && AuthContext::isAdminRecord($target)) {
            $this->auditLogger->log('SECURITY_VIOLATION', "Sub-Admin attempted unauthorized blocking of peer Admin account (ID: {$blockId})");
            return 'Access Denied: Sub-Admins are strictly forbidden from blocking peer Admin accounts!';
        }

        $durationKey = trim((string) ($_POST['duration'] ?? '24_hours'));
        $durationMap = $this->config->get('app', 'block_durations', []);
        $durationCfg = is_array($durationMap) ? ($durationMap[$durationKey] ?? null) : null;
        $blockedUntil = null;
        $untilText = 'indefinitely';

        if (is_array($durationCfg) && !empty($durationCfg['spec'])) {
            $blockedUntil = date('Y-m-d H:i:s', strtotime((string) $durationCfg['spec']));
            $untilText = (string) ($durationCfg['label'] ?? 'temporarily')
                . ' (until ' . date('d/m/Y H:i', strtotime($blockedUntil)) . ')';
        }

        $reason = trim((string) ($_POST['block_reason'] ?? ''));
        $this->users->update([
            'id'            => $blockId,
            'is_blocked'    => 1,
            'blocked_until' => $blockedUntil,
            'block_reason'  => $reason !== '' ? $reason : null,
        ]);

        $operatorTitle = $auth->isBoss() ? 'The System Boss' : 'Sub-Admin';
        $reasonNote = $reason !== '' ? " | Reason: {$reason}" : '';
        $targetRole = (int) ($target['role'] ?? 0);
        $this->auditLogger->log(
            'BLOCK_USER',
            "{$operatorTitle} blocked user '{$target['name']}' (ID: {$blockId}, Role: {$targetRole}) {$untilText}{$reasonNote}"
        );

        return "User '{$target['name']}' has been temporarily blocked {$untilText}!";
    }

    private function handleUnblockUser(AuthContext $auth, int $unblockId): string
    {
        $target = $this->users->findById($unblockId);
        if (!$target) {
            return 'Target user does not exist.';
        }
        if (!$auth->isBoss() && AuthContext::isAdminRecord($target)) {
            $this->auditLogger->log('SECURITY_VIOLATION', "Sub-Admin attempted unauthorized unblocking of Admin account (ID: {$unblockId})");
            return 'Access Denied: Only the System Boss can manage Admin account status!';
        }

        $this->users->update([
            'id'            => $unblockId,
            'is_blocked'    => 0,
            'blocked_until' => null,
            'block_reason'  => null,
        ]);

        $operatorTitle = $auth->isBoss() ? 'The System Boss' : 'Sub-Admin';
        $this->auditLogger->log('UNBLOCK_USER', "{$operatorTitle} unblocked user account '{$target['name']}' (ID: {$unblockId})");

        return "User account '{$target['name']}' has been unblocked successfully!";
    }
}
