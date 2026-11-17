<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Core\AuthContext;
use App\Core\AuthMiddleware;
use App\Core\Config;
use App\Core\Flash;
use App\Core\Response;
use App\Models\CategoryModel;
use App\Models\FilmModel;
use App\Models\UserModel;
use App\Services\AuditLogger;
use App\Services\MediaService;
use App\Services\SecurityService;

/**
 * Administrative Film Publishing, Editing, Deletion, and Pending Moderation Controller.
 */
final class AdminFilmController extends BaseController
{
    private FilmModel $films;
    private CategoryModel $categories;
    private UserModel $users;
    private MediaService $media;

    public function __construct(
        Config $config,
        Response $response,
        Flash $flash,
        SecurityService $security,
        AuthMiddleware $auth,
        AuditLogger $auditLogger,
        FilmModel $films,
        CategoryModel $categories,
        UserModel $users,
        MediaService $media
    ) {
        parent::__construct($config, $response, $flash, $security, $auth, $auditLogger);
        $this->films = $films;
        $this->categories = $categories;
        $this->users = $users;
        $this->media = $media;
    }

    public function add(): array
    {
        $auth = $this->auth->requireAdmin();
        $categories = $this->categories->findAllAlphabetical();
        $error = '';

        if ($this->isPost()) {
            $this->security->validateCsrfRequest($this->baseUrl() . 'index.php?route=admin/add-film');

            $title = trim((string) ($_POST['title'] ?? ''));
            $description = trim((string) ($_POST['description'] ?? ''));
            $categoryId = (string) ($_POST['category_id'] ?? '');
            $isAnniversary = isset($_POST['is_anniversary']) ? 1 : 0;

            if ($title === '' || $description === '' || $categoryId === '') {
                $error = 'Please enter all the required information!';
            } elseif (!isset($_FILES['image']) || ($_FILES['image']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
                $error = 'Please select a valid poster image!';
            } else {
                $uploadResult = $this->media->uploadSecureImage($_FILES['image']);
                if (!$uploadResult['success']) {
                    $error = (string) ($uploadResult['error'] ?? 'Upload failed.');
                } else {
                    $this->films->insert([
                        'title'          => $title,
                        'description'    => $description,
                        'category_id'    => $categoryId,
                        'is_anniversary' => $isAnniversary,
                        'images'         => $uploadResult['filename'],
                        'user_id'        => (int) $auth->userId(),
                        'status'         => 'approved',
                        'created_at'     => date('Y-m-d H:i:s'),
                    ]);

                    $this->redirect('index.php?route=film/list&status=added');
                }
            }
        }

        return $this->view('admin/add_film', 'layouts/catalog', 'Add Film', [
            'categories' => $categories,
            'error'      => $error,
            'page_title' => 'Admin - Add New Film',
            'action_url' => $this->baseUrl() . 'index.php?route=admin/add-film',
        ]);
    }

    public function edit(): array
    {
        $auth = $this->auth->requireAdmin();
        $filmId = (int) ($_GET['id'] ?? ($_POST['film_id'] ?? 0));
        $film = $this->films->findById($filmId);

        if (!$film) {
            $this->redirect('index.php?route=film/list');
        }

        if ($this->isFilmOwnedByBoss($film) && !$auth->isBoss()) {
            $this->flash->set('error_message', 'Access denied: Sub-Admins cannot modify films published by the System Boss!');
            $this->redirect('index.php?route=film/list&status=denied');
        }

        $categories = $this->categories->findAllAlphabetical();
        $error = '';

        if ($this->isPost()) {
            $this->security->validateCsrfRequest($this->baseUrl() . 'index.php?route=admin/edit-film&id=' . $filmId);

            $title = trim((string) ($_POST['title'] ?? ''));
            $description = trim((string) ($_POST['description'] ?? ''));
            $categoryId = (string) ($_POST['category_id'] ?? '');
            $isAnniversary = isset($_POST['is_anniversary']) ? 1 : 0;
            $oldImage = (string) ($film['images'] ?? '');
            $image = $oldImage;
            $uploadOk = true;
            $uploadedNewFile = null;

            if (isset($_FILES['image']) && ($_FILES['image']['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK) {
                $uploadResult = $this->media->uploadSecureImage($_FILES['image']);
                if (!$uploadResult['success']) {
                    $error = (string) ($uploadResult['error'] ?? 'Upload failed.');
                    $uploadOk = false;
                } else {
                    $image = (string) $uploadResult['filename'];
                    $uploadedNewFile = $image;
                }
            }

            if ($uploadOk) {
                if ($title === '' || $description === '' || $categoryId === '') {
                    if ($uploadedNewFile !== null) {
                        $this->media->deleteSafely($uploadedNewFile);
                    }
                    $error = 'Please enter all the required information!';
                } else {
                    $this->films->update([
                        'id'             => $filmId,
                        'title'          => $title,
                        'description'    => $description,
                        'category_id'    => $categoryId,
                        'is_anniversary' => $isAnniversary,
                        'images'         => $image,
                    ]);

                    if ($uploadedNewFile !== null && $oldImage !== '' && $oldImage !== $image) {
                        $this->media->deleteSafely($oldImage);
                    }

                    $this->redirect('index.php?route=film/list&status=edited');
                }
            }
        }

        $film['poster_url'] = $this->media->resolvePoster($film['images'] ?? null, $this->baseUrl());

        return $this->view('admin/edit_film', 'layouts/catalog', 'Update Film', [
            'film'       => $film,
            'categories' => $categories,
            'error'      => $error,
            'page_title' => 'ADMIN - EDIT FILM',
            'action_url' => $this->baseUrl() . 'index.php?route=admin/edit-film',
        ]);
    }

    public function delete(): never
    {
        $auth = $this->auth->requireAdmin();
        $this->auth->requirePost('Method Not Allowed. Deletion requires POST request.');
        $this->security->validateCsrfRequest($this->baseUrl() . 'index.php?route=film/list');

        $filmId = (int) ($_POST['id'] ?? 0);
        if ($filmId > 0) {
            $film = $this->films->findById($filmId);
            if ($film) {
                if ($this->isFilmOwnedByBoss($film) && !$auth->isBoss()) {
                    $this->auditLogger->log(
                        'SECURITY_VIOLATION',
                        "Sub-Admin attempted unauthorized deletion of Boss film ID: {$filmId}"
                    );
                    $this->flash->set('error_message', 'Access denied: Sub-Admins cannot delete films published by the System Boss!');
                    $this->redirect('index.php?route=film/list&status=denied');
                }

                $this->media->deleteSafely($film['images'] ?? null);
                $this->films->delete($filmId);
                $this->auditLogger->log('DELETE_FILM', "Deleted film '{$film['title']}' (ID: {$filmId})");
            }
        }

        $this->redirect('index.php?route=film/list&status=deleted');
    }

    public function approve(): array
    {
        $this->auth->requireAdmin();
        $alert = null;

        if ($this->isPost()) {
            $this->security->validateCsrfRequest($this->baseUrl() . 'index.php?route=admin/approve-films');

            $action = (string) ($_POST['action'] ?? '');
            $filmId = (int) ($_POST['film_id'] ?? 0);

            if ($filmId > 0 && $action === 'approve') {
                $this->films->update([
                    'id'     => $filmId,
                    'status' => 'approved',
                ]);
                $this->auditLogger->log('APPROVE_FILM', "Approved and published film ID: {$filmId}");
                $alert = [
                    'type'    => 'success',
                    'message' => 'Film approved and published!',
                ];
            } elseif ($filmId > 0 && $action === 'reject') {
                $film = $this->films->findById($filmId);
                if ($film) {
                    $this->media->deleteSafely($film['images'] ?? null);
                    $this->films->delete($filmId);
                    $this->auditLogger->log('REJECT_FILM', "Rejected and deleted pending film ID: {$filmId}");
                }
                $alert = [
                    'type'    => 'danger',
                    'message' => 'Rejected and deleted the film!',
                ];
            }
        }

        $pendingFilms = $this->films->findPendingWithUploader();
        foreach ($pendingFilms as &$film) {
            $film['poster_url'] = $this->media->resolvePoster($film['images'] ?? null, $this->baseUrl());
            $film['created_at_formatted'] = !empty($film['created_at'])
                ? date('d/m/Y H:i', strtotime((string) $film['created_at']))
                : 'N/A';
        }
        unset($film);

        return $this->view('admin/approve_films', 'layouts/admin', 'Approve Films', [
            'pending_films' => $pendingFilms,
            'alert'         => $alert,
        ]);
    }

    private function isFilmOwnedByBoss(array $film): bool
    {
        if ((int) ($film['user_id'] ?? 0) === 1) {
            return true;
        }

        $uploader = $this->users->findById((int) ($film['user_id'] ?? 0));
        return $uploader ? AuthContext::isBossRecord($uploader) : false;
    }
}
