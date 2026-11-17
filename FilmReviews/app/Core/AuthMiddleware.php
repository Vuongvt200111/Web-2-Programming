<?php
declare(strict_types=1);

namespace App\Core;

use App\Services\AuditLogger;

/**
 * Role-Based Access Control (RBAC) and HTTP Method enforcement middleware.
 */
final class AuthMiddleware
{
    private Config $config;
    private Response $response;
    private Flash $flash;
    private AuditLogger $auditLogger;

    public function __construct(
        Config $config,
        Response $response,
        Flash $flash,
        AuditLogger $auditLogger
    ) {
        $this->config = $config;
        $this->response = $response;
        $this->flash = $flash;
        $this->auditLogger = $auditLogger;
    }

    public function current(): AuthContext
    {
        return AuthContext::fromSession();
    }

    public function requireAuthenticated(string $redirectRoute = 'login'): AuthContext
    {
        $auth = $this->current();
        if (!$auth->isAuthenticated()) {
            $this->response->redirect($this->config->baseUrl() . 'index.php?route=' . $redirectRoute);
        }

        return $auth;
    }

    public function requireReviewerOnly(string $redirectRoute = 'film/list'): AuthContext
    {
        $auth = $this->current();
        if (!$auth->isReviewer()) {
            $this->response->redirect($this->config->baseUrl() . 'index.php?route=' . $redirectRoute);
        }

        return $auth;
    }

    public function requireAdmin(string $redirectRoute = 'film/list'): AuthContext
    {
        $auth = $this->current();
        if (!$auth->isAdmin()) {
            $this->response->redirect($this->config->baseUrl() . 'index.php?route=' . $redirectRoute);
        }

        return $auth;
    }

    public function requireBoss(?string $routeLabel = null): AuthContext
    {
        $auth = $this->requireAdmin();
        if (!$auth->isBoss()) {
            $routeInfo = $routeLabel ?? (string) ($_GET['route'] ?? 'unknown');
            $this->auditLogger->log(
                'SECURITY_VIOLATION',
                "Sub-Admin attempted unauthorized access to Boss-only route: {$routeInfo}"
            );
            $this->flash->set('error_message', 'Access denied: This feature is strictly reserved for the System Boss!');
            $this->response->redirect($this->config->baseUrl() . 'index.php?route=admin/dashboard');
        }

        return $auth;
    }

    public function requirePost(string $errorMessage = 'Method Not Allowed. Action requires POST request.'): void
    {
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            $this->response->abort(405, $errorMessage);
        }
    }
}
