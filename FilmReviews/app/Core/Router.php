<?php
declare(strict_types=1);

namespace App\Core;

use App\Controllers\Admin\AdminFilmController;
use App\Controllers\Admin\AdminReviewController;
use App\Controllers\Admin\AdminUserController;
use App\Controllers\Admin\AuditLogController;
use App\Controllers\Admin\CategoryController;
use App\Controllers\Admin\DashboardController;
use App\Controllers\Admin\MessageController;
use App\Controllers\AuthController;
use App\Controllers\ErrorController;
use App\Controllers\FilmController;
use App\Controllers\ReviewController;
use App\Controllers\SetupController;
use App\Controllers\UserController;

/**
 * Pure OOP Front-Controller Route Dispatcher supporting all canonical and legacy aliases.
 */
final class Router
{
    /** @var array<string, array{0: class-string, 1: string}> */
    private const ROUTE_TABLE = [
        ''                            => [FilmController::class, 'home'],
        'home'                        => [FilmController::class, 'home'],
        'film/home'                   => [FilmController::class, 'home'],
        'film/list'                   => [FilmController::class, 'list'],
        'list_films'                  => [FilmController::class, 'list'],
        'list'                        => [FilmController::class, 'list'],
        'film/add'                    => [FilmController::class, 'userAdd'],
        'film/user-add'               => [FilmController::class, 'userAdd'],
        'user/add_film'               => [FilmController::class, 'userAdd'],
        'film/edit'                   => [FilmController::class, 'userEdit'],
        'film/user-edit'              => [FilmController::class, 'userEdit'],
        'user/edit_film'              => [FilmController::class, 'userEdit'],
        'film/delete'                 => [FilmController::class, 'userDelete'],
        'film/user-delete'            => [FilmController::class, 'userDelete'],
        'user/delete_film'            => [FilmController::class, 'userDelete'],

        'admin/add-film'              => [AdminFilmController::class, 'add'],
        'admin/film-add'              => [AdminFilmController::class, 'add'],
        'admin/add_film'              => [AdminFilmController::class, 'add'],
        'admin/edit-film'             => [AdminFilmController::class, 'edit'],
        'admin/film-edit'             => [AdminFilmController::class, 'edit'],
        'admin/edit_film'             => [AdminFilmController::class, 'edit'],
        'admin/delete-film'           => [AdminFilmController::class, 'delete'],
        'admin/film-delete'           => [AdminFilmController::class, 'delete'],
        'admin/delete_film'           => [AdminFilmController::class, 'delete'],
        'admin/approve-films'         => [AdminFilmController::class, 'approve'],
        'admin/approve_films'         => [AdminFilmController::class, 'approve'],
        'film/approve'                => [AdminFilmController::class, 'approve'],

        'review/view'                 => [ReviewController::class, 'show'],
        'review'                      => [ReviewController::class, 'show'],
        'review/add'                  => [ReviewController::class, 'add'],
        'user/add_review'             => [ReviewController::class, 'add'],
        'review/vote'                 => [ReviewController::class, 'voteHelpful'],
        'review/vote-helpful'         => [ReviewController::class, 'voteHelpful'],
        'review/vote_helpful'         => [ReviewController::class, 'voteHelpful'],
        'review/edit'                 => [ReviewController::class, 'edit'],
        'user/edit_review'            => [ReviewController::class, 'edit'],
        'review/delete'               => [ReviewController::class, 'delete'],
        'user/delete_review'          => [ReviewController::class, 'delete'],

        'admin/delete-review'         => [AdminReviewController::class, 'deleteFromFilm'],
        'admin/delete_review'         => [AdminReviewController::class, 'deleteFromFilm'],
        'review/admin-delete'         => [AdminReviewController::class, 'deleteFromFilm'],
        'admin/manage-reviews'        => [AdminReviewController::class, 'manage'],
        'admin/manage_reviews'        => [AdminReviewController::class, 'manage'],
        'review/admin-manage'         => [AdminReviewController::class, 'manage'],

        'login'                       => [AuthController::class, 'login'],
        'user/login'                  => [AuthController::class, 'login'],
        'register'                    => [AuthController::class, 'register'],
        'user/register'               => [AuthController::class, 'register'],
        'logout'                      => [AuthController::class, 'logout'],
        'user/logout'                 => [AuthController::class, 'logout'],
        'setup-admin'                 => [SetupController::class, 'setupAdmin'],
        'setup_admin'                 => [SetupController::class, 'setupAdmin'],
        'user/setup-admin'            => [SetupController::class, 'setupAdmin'],
        'forgot-password'             => [AuthController::class, 'forgotPassword'],
        'user/forgot-password'        => [AuthController::class, 'forgotPassword'],
        'user/forgot_password'        => [AuthController::class, 'forgotPassword'],
        'reset-password'              => [AuthController::class, 'resetPassword'],
        'user/reset-password'         => [AuthController::class, 'resetPassword'],
        'user/reset_password'         => [AuthController::class, 'resetPassword'],

        'profile'                     => [UserController::class, 'profile'],
        'user/profile'                => [UserController::class, 'profile'],
        'contact'                     => [UserController::class, 'contact'],
        'user/contact'                => [UserController::class, 'contact'],
        'user/contact_us'             => [UserController::class, 'contact'],
        'delete-account'              => [UserController::class, 'deleteAccount'],
        'user/delete-account'         => [UserController::class, 'deleteAccount'],
        'user/delete_account'         => [UserController::class, 'deleteAccount'],

        'admin/dashboard'             => [DashboardController::class, 'index'],
        'admin'                       => [DashboardController::class, 'index'],
        'dashboard'                   => [DashboardController::class, 'index'],
        'admin/manage-users'          => [AdminUserController::class, 'manageUsers'],
        'admin/manage_users'          => [AdminUserController::class, 'manageUsers'],
        'admin/restore-user'          => [AdminUserController::class, 'restoreUser'],
        'admin/restore_user'          => [AdminUserController::class, 'restoreUser'],
        'admin/messages'              => [MessageController::class, 'index'],
        'admin/add-new-admin'         => [AdminUserController::class, 'addNewAdmin'],
        'admin/add_new_admin'         => [AdminUserController::class, 'addNewAdmin'],
        'admin/add-admin'             => [AdminUserController::class, 'addNewAdmin'],
        'admin/manage-categories'     => [CategoryController::class, 'index'],
        'admin/manage_categories'     => [CategoryController::class, 'index'],
        'admin/categories'            => [CategoryController::class, 'index'],
        'admin/delete-category'       => [CategoryController::class, 'delete'],
        'admin/delete_category'       => [CategoryController::class, 'delete'],
        'admin/audit-logs'            => [AuditLogController::class, 'index'],
        'admin/audit_logs'            => [AuditLogController::class, 'index'],
        'admin/audit'                 => [AuditLogController::class, 'index'],
        'admin/export-audit-logs'     => [AuditLogController::class, 'export'],
        'admin/export_audit_logs'     => [AuditLogController::class, 'export'],
        'admin/delete-audit-log'      => [AuditLogController::class, 'delete'],
        'admin/delete_audit_log'      => [AuditLogController::class, 'delete'],
        'admin/prune-orphan-images'   => [AuditLogController::class, 'pruneOrphanImages'],
        'admin/prune_orphan_images'   => [AuditLogController::class, 'pruneOrphanImages'],
    ];

    /**
     * @return array{0: class-string, 1: string, 2: array<int, mixed>}
     */
    public function resolve(string $rawRoute): array
    {
        $normalized = strtolower(trim($rawRoute, '/'));
        if (isset(self::ROUTE_TABLE[$normalized])) {
            [$controller, $action] = self::ROUTE_TABLE[$normalized];
            return [$controller, $action, []];
        }

        return [ErrorController::class, 'notFound', [$rawRoute]];
    }
}
