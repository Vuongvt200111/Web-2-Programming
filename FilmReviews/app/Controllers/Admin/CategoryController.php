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
use App\Services\AuditLogger;
use App\Services\SecurityService;

/**
 * Administrative Film Category CRUD Controller (Create: All Admins, Edit/Delete: System Boss Only).
 */
final class CategoryController extends BaseController
{
    private CategoryModel $categories;
    private FilmModel $films;

    public function __construct(
        Config $config,
        Response $response,
        Flash $flash,
        SecurityService $security,
        AuthMiddleware $auth,
        AuditLogger $auditLogger,
        CategoryModel $categories,
        FilmModel $films
    ) {
        parent::__construct($config, $response, $flash, $security, $auth, $auditLogger);
        $this->categories = $categories;
        $this->films = $films;
    }

    public function index(): array
    {
        $auth = $this->auth->requireAdmin();
        $message = '';
        $error = '';

        if ($this->isPost()) {
            $this->security->validateCsrfRequest($this->baseUrl() . 'index.php?route=admin/manage-categories');
            [$message, $error] = $this->handlePost($auth);
        }

        if (($_GET['status'] ?? '') === 'deleted') {
            $message = 'Category deleted successfully!';
        } elseif (($_GET['error'] ?? '') === 'boss_only') {
            $error = 'Access denied: Only the System Boss has permission to edit or delete categories!';
        }

        return $this->view('admin/manage_categories', 'layouts/admin', 'Manage Categories - Admin', [
            'categories' => $this->categories->findAllWithFilmCount(),
            'message'    => $message,
            'error'      => $error,
            'isBoss'     => $auth->isBoss(),
        ]);
    }

    public function delete(): never
    {
        $this->auth->requireBoss('admin/delete-category');

        if ($this->isPost() && isset($_POST['id'])) {
            $this->security->validateCsrfRequest($this->baseUrl() . 'index.php?route=admin/manage-categories');
            $id = (int) $_POST['id'];
            $targetCat = $this->categories->findById($id);
            if ($targetCat) {
                $catName = (string) ($targetCat['category_name'] ?? "ID #{$id}");
                $filmCount = $this->films->total('category_id', $id);
                $this->categories->delete($id);
                $this->auditLogger->log(
                    'DELETE_CATEGORY',
                    "The System Boss deleted film category '{$catName}' (ID: {$id}, Associated films: {$filmCount})"
                );
                $this->redirect('index.php?route=admin/manage-categories&status=deleted');
            }
        }

        $this->redirect('index.php?route=admin/manage-categories');
    }

    /**
     * @return array{0: string, 1: string}
     */
    private function handlePost(AuthContext $auth): array
    {
        if (isset($_POST['add_category'])) {
            $categoryName = trim((string) ($_POST['category_name'] ?? ''));
            $description = trim((string) ($_POST['description'] ?? ''));

            if ($categoryName === '') {
                return ['', 'Category name cannot be empty!'];
            }
            if ($this->categories->findOne('category_name', $categoryName)) {
                return ['', 'Category "' . $categoryName . '" already exists!'];
            }

            $newCatId = $this->categories->insert([
                'category_name' => $categoryName,
                'description'   => $description,
            ]);
            $this->auditLogger->log('ADD_CATEGORY', "Created film category '{$categoryName}' (ID: {$newCatId})");
            return ['Category "' . $categoryName . '" created successfully!', ''];
        }

        if (isset($_POST['edit_category'])) {
            $categoryId = (int) ($_POST['id'] ?? 0);
            if (!$auth->isBoss()) {
                $this->auditLogger->log('SECURITY_VIOLATION', "Sub-Admin attempted unauthorized edit of film category ID: {$categoryId}");
                return ['', 'Access denied: Only the System Boss has permission to edit film categories!'];
            }

            $categoryName = trim((string) ($_POST['category_name'] ?? ''));
            $description = trim((string) ($_POST['description'] ?? ''));
            if ($categoryId <= 0 || $categoryName === '') {
                return ['', 'Invalid category ID or empty category name!'];
            }

            $this->categories->update([
                'id'            => $categoryId,
                'category_name' => $categoryName,
                'description'   => $description,
            ]);
            $this->auditLogger->log('EDIT_CATEGORY', "The System Boss updated film category '{$categoryName}' (ID: {$categoryId})");
            return ['Category updated successfully!', ''];
        }

        if (isset($_POST['delete_id'])) {
            $deleteId = (int) $_POST['delete_id'];
            if (!$auth->isBoss()) {
                $this->auditLogger->log('SECURITY_VIOLATION', "Sub-Admin attempted unauthorized deletion of film category ID: {$deleteId}");
                return ['', 'Access denied: Only the System Boss has permission to delete film categories!'];
            }

            $targetCat = $this->categories->findById($deleteId);
            if (!$targetCat) {
                return ['', 'Target category does not exist!'];
            }

            $catName = (string) ($targetCat['category_name'] ?? "ID #{$deleteId}");
            $filmCount = $this->films->total('category_id', $deleteId);
            $this->categories->delete($deleteId);
            $this->auditLogger->log(
                'DELETE_CATEGORY',
                "The System Boss deleted film category '{$catName}' (ID: {$deleteId}, Associated films: {$filmCount})"
            );
            return ['Category "' . $catName . '" deleted successfully!', ''];
        }

        return ['', ''];
    }
}
