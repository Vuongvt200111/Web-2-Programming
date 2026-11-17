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
use App\Models\AuditLogModel;
use App\Models\CategoryModel;
use App\Models\FilmModel;
use App\Models\MessageModel;
use App\Models\ReviewModel;
use App\Models\ReviewVoteModel;
use App\Models\UserModel;
use App\Services\AuditLogger;
use App\Services\MailerService;
use App\Services\MediaService;
use App\Services\ReviewService;
use App\Services\SearchService;
use App\Services\SecurityService;
use App\Services\TabSession;

/**
 * Dependency Injection Container & MVC Kernel orchestrating request lifecycle.
 */
final class Application
{
    private Config $config;
    private Database $database;
    private Response $response;
    private Flash $flash;
    private SecurityService $security;
    private MediaService $media;
    private MailerService $mailer;
    private SearchService $searchService;

    private UserModel $users;
    private FilmModel $films;
    private ReviewModel $reviews;
    private ReviewVoteModel $reviewVotes;
    private CategoryModel $categories;
    private MessageModel $messages;
    private AuditLogModel $auditLogs;

    private AuditLogger $auditLogger;
    private AuthMiddleware $auth;
    private ReviewService $reviewService;
    private View $view;
    private Router $router;

    public function __construct(string $rootPath)
    {
        $this->config = new Config($rootPath);
        $this->database = new Database($this->config);
        $pdo = $this->database->getPdo();

        $this->response = new Response();
        $this->flash = new Flash();
        $this->security = new SecurityService($this->config, $this->response, $this->flash);
        $this->media = new MediaService($this->config);
        $this->mailer = new MailerService($this->config);
        $this->searchService = new SearchService($this->config);

        $this->users = new UserModel($pdo);
        $this->films = new FilmModel($pdo);
        $this->reviews = new ReviewModel($pdo);
        $this->reviewVotes = new ReviewVoteModel($pdo);
        $this->categories = new CategoryModel($pdo);
        $this->messages = new MessageModel($pdo);
        $this->auditLogs = new AuditLogModel($pdo);

        $this->auditLogger = new AuditLogger($this->auditLogs);
        $this->auth = new AuthMiddleware($this->config, $this->response, $this->flash, $this->auditLogger);
        $this->reviewService = new ReviewService($this->reviews, $this->reviewVotes, $this->media);
        $this->view = new View($this->config, $this->security, $this->media);
        $this->router = new Router();
    }

    public function handleHttpRequest(?string $overrideRoute = null): void
    {
        $sessionName = (string) $this->config->get('app', 'session_name', 'CINEMA_SESSION');
        $tabTimeout = (int) $this->config->get('app', 'tab_timeout', 28800);

        TabSession::init(null, $tabTimeout, $sessionName);
        $this->security->ensureSession();
        $this->enforceSessionIntegrity();

        $rawRoute = $overrideRoute ?? (isset($_GET['route']) && is_string($_GET['route']) ? $_GET['route'] : '');
        [$controllerClass, $method, $args] = $this->router->resolve($rawRoute);

        $controller = $this->instantiateController($controllerClass);
        $result = $controller->$method(...$args);

        if (is_array($result)) {
            echo $this->view->render($result);
        }
    }

    public function getConfig(): Config
    {
        return $this->config;
    }

    public function getUsers(): UserModel
    {
        return $this->users;
    }

    public function getAuditLogger(): AuditLogger
    {
        return $this->auditLogger;
    }

    private function enforceSessionIntegrity(): void
    {
        $this->users->syncExpiredBlocks();

        if (!isset($_SESSION['user_id'])) {
            return;
        }

        $activeUser = $this->users->findById((int) $_SESSION['user_id']);
        if (!$activeUser) {
            TabSession::logout();
            $this->flash->set('system_error', 'Your active session ended because this account no longer exists.');
            $this->response->redirect($this->config->baseUrl() . 'index.php?route=login');
        }

        if (!empty($activeUser['is_deleted']) && (int) $activeUser['is_deleted'] === 1) {
            TabSession::logout();
            $this->flash->set('system_error', 'Your active session ended because this account has been deactivated.');
            $this->response->redirect($this->config->baseUrl() . 'index.php?route=login');
        }

        if ($this->users->isBlocked($activeUser)) {
            $untilMsg = !empty($activeUser['blocked_until'])
                ? 'until ' . date('d/m/Y H:i', strtotime((string) $activeUser['blocked_until']))
                : 'indefinitely';
            $reasonMsg = !empty($activeUser['block_reason'])
                ? ' (Reason: ' . $activeUser['block_reason'] . ')'
                : '';

            TabSession::logout();
            $this->flash->set(
                'system_error',
                "Your active session was terminated: This account is currently blocked {$untilMsg}{$reasonMsg}."
            );
            $this->response->redirect($this->config->baseUrl() . 'index.php?route=login');
        }

        $sessionRole = (int) TabSession::get('role', $_SESSION['role'] ?? 0);
        $sessionIsBoss = (bool) TabSession::get('is_boss', $_SESSION['is_boss'] ?? false);
        $dbRole = (int) ($activeUser['role'] ?? 0);
        $dbIsBoss = AuthContext::isBossRecord($activeUser);

        if ($sessionRole !== $dbRole || $sessionIsBoss !== $dbIsBoss) {
            $wasPromotedToAdmin = ($sessionRole === 0 && ($dbRole >= 1 || $dbIsBoss));
            TabSession::logout();

            if ($wasPromotedToAdmin) {
                $this->flash->set(
                    'system_notice',
                    'Your account has been promoted to Sub-Admin by the System Boss. Please sign in again to activate administrative access.'
                );
            } else {
                $this->flash->set(
                    'system_error',
                    'Your active session was terminated: Your account role permissions were updated by the System Boss. Please sign in again.'
                );
            }

            $this->response->redirect($this->config->baseUrl() . 'index.php?route=login');
        }

        TabSession::refreshFromRecord($activeUser);
    }

    private function instantiateController(string $controllerClass): object
    {
        return match ($controllerClass) {
            FilmController::class => new FilmController(
                $this->config,
                $this->response,
                $this->flash,
                $this->security,
                $this->auth,
                $this->auditLogger,
                $this->films,
                $this->categories,
                $this->media,
                $this->searchService
            ),
            ReviewController::class => new ReviewController(
                $this->config,
                $this->response,
                $this->flash,
                $this->security,
                $this->auth,
                $this->auditLogger,
                $this->reviews,
                $this->films,
                $this->reviewVotes,
                $this->media,
                $this->reviewService
            ),
            AuthController::class => new AuthController(
                $this->config,
                $this->response,
                $this->flash,
                $this->security,
                $this->auth,
                $this->auditLogger,
                $this->users,
                $this->mailer
            ),
            SetupController::class => new SetupController(
                $this->config,
                $this->response,
                $this->flash,
                $this->security,
                $this->auth,
                $this->auditLogger,
                $this->users
            ),
            UserController::class => new UserController(
                $this->config,
                $this->response,
                $this->flash,
                $this->security,
                $this->auth,
                $this->auditLogger,
                $this->users,
                $this->categories,
                $this->messages,
                $this->films,
                $this->media
            ),
            DashboardController::class => new DashboardController(
                $this->config,
                $this->response,
                $this->flash,
                $this->security,
                $this->auth,
                $this->auditLogger,
                $this->users,
                $this->films,
                $this->reviews,
                $this->categories,
                $this->messages
            ),
            AdminUserController::class => new AdminUserController(
                $this->config,
                $this->response,
                $this->flash,
                $this->security,
                $this->auth,
                $this->auditLogger,
                $this->users,
                $this->media
            ),
            AdminFilmController::class => new AdminFilmController(
                $this->config,
                $this->response,
                $this->flash,
                $this->security,
                $this->auth,
                $this->auditLogger,
                $this->films,
                $this->categories,
                $this->users,
                $this->media
            ),
            AdminReviewController::class => new AdminReviewController(
                $this->config,
                $this->response,
                $this->flash,
                $this->security,
                $this->auth,
                $this->auditLogger,
                $this->reviews,
                $this->users,
                $this->reviewService
            ),
            CategoryController::class => new CategoryController(
                $this->config,
                $this->response,
                $this->flash,
                $this->security,
                $this->auth,
                $this->auditLogger,
                $this->categories,
                $this->films
            ),
            MessageController::class => new MessageController(
                $this->config,
                $this->response,
                $this->flash,
                $this->security,
                $this->auth,
                $this->auditLogger,
                $this->messages
            ),
            AuditLogController::class => new AuditLogController(
                $this->config,
                $this->response,
                $this->flash,
                $this->security,
                $this->auth,
                $this->auditLogger,
                $this->auditLogs,
                $this->users,
                $this->films,
                $this->reviews,
                $this->media
            ),
            ErrorController::class => new ErrorController(
                $this->config,
                $this->response,
                $this->flash,
                $this->security,
                $this->auth,
                $this->auditLogger
            ),
            default => throw new \RuntimeException("Unknown controller class: {$controllerClass}"),
        };
    }
}
