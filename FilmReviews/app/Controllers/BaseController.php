<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\AuthMiddleware;
use App\Core\Config;
use App\Core\Flash;
use App\Core\Response;
use App\Services\AuditLogger;
use App\Services\SecurityService;

/**
 * Shared foundation for all HTTP Controllers.
 */
abstract class BaseController
{
    protected Config $config;
    protected Response $response;
    protected Flash $flash;
    protected SecurityService $security;
    protected AuthMiddleware $auth;
    protected AuditLogger $auditLogger;

    public function __construct(
        Config $config,
        Response $response,
        Flash $flash,
        SecurityService $security,
        AuthMiddleware $auth,
        AuditLogger $auditLogger
    ) {
        $this->config = $config;
        $this->response = $response;
        $this->flash = $flash;
        $this->security = $security;
        $this->auth = $auth;
        $this->auditLogger = $auditLogger;
    }

    protected function isPost(): bool
    {
        return ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST';
    }

    protected function baseUrl(): string
    {
        return $this->config->baseUrl();
    }

    protected function redirect(string $relativePath): never
    {
        $this->response->redirect($this->baseUrl() . ltrim($relativePath, '/'));
    }

    /**
     * @param array<string, mixed> $variables
     * @return array{title: string, template: string, layout: ?string, variables: array<string, mixed>}
     */
    protected function view(
        string $template,
        ?string $layout,
        string $title,
        array $variables = []
    ): array {
        return [
            'title'     => $title,
            'template'  => $template,
            'layout'    => $layout,
            'variables' => $variables,
        ];
    }

    /**
     * Resolve URL status query values into safe presentation alerts inside controllers.
     *
     * @param array<string, array{type: string, message: string}> $map
     * @return array{type: string, message: string}|null
     */
    protected function mapStatusAlert(string $status, array $map): ?array
    {
        return $map[$status] ?? null;
    }
}
