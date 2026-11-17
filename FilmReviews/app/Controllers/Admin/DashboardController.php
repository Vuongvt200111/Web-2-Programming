<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Core\AuthMiddleware;
use App\Core\Config;
use App\Core\Flash;
use App\Core\Response;
use App\Models\CategoryModel;
use App\Models\FilmModel;
use App\Models\MessageModel;
use App\Models\ReviewModel;
use App\Models\UserModel;
use App\Services\AuditLogger;
use App\Services\SecurityService;

/**
 * Administrative Control Panel Overview Dashboard Controller.
 */
final class DashboardController extends BaseController
{
    private UserModel $users;
    private FilmModel $films;
    private ReviewModel $reviews;
    private CategoryModel $categories;
    private MessageModel $messages;

    public function __construct(
        Config $config,
        Response $response,
        Flash $flash,
        SecurityService $security,
        AuthMiddleware $auth,
        AuditLogger $auditLogger,
        UserModel $users,
        FilmModel $films,
        ReviewModel $reviews,
        CategoryModel $categories,
        MessageModel $messages
    ) {
        parent::__construct($config, $response, $flash, $security, $auth, $auditLogger);
        $this->users = $users;
        $this->films = $films;
        $this->reviews = $reviews;
        $this->categories = $categories;
        $this->messages = $messages;
    }

    public function index(): array
    {
        $this->auth->requireAdmin();

        return $this->view('admin/dashboard', 'layouts/admin', 'Admin Dashboard', [
            'totalFilms'      => $this->films->total(),
            'totalReviews'    => $this->reviews->total(),
            'totalUsers'      => $this->users->total(),
            'totalCategories' => $this->categories->total(),
            'totalMessages'   => $this->messages->countUnreplied(),
            'pendingCount'    => $this->films->total('status', 'pending'),
            'error'           => (string) $this->flash->pull('error_message', ''),
            'success'         => (string) $this->flash->pull('success_message', ''),
        ]);
    }
}
