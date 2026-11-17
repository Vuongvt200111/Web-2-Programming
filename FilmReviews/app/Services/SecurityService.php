<?php
declare(strict_types=1);

namespace App\Services;

use App\Core\Config;
use App\Core\Flash;
use App\Core\Response;

/**
 * Handles CSRF tokens and security validation checks.
 */
final class SecurityService
{
    private Config $config;
    private Response $response;
    private Flash $flash;

    public function __construct(Config $config, Response $response, Flash $flash)
    {
        $this->config = $config;
        $this->response = $response;
        $this->flash = $flash;
    }

    public function ensureSession(): void
    {
        $sessionName = (string) $this->config->get('app', 'session_name', 'CINEMA_SESSION');
        if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
            session_name($sessionName);
            @session_start();
        }
        if (!isset($_SESSION) || !is_array($_SESSION)) {
            $_SESSION = [];
        }
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
    }

    public function getCsrfToken(): string
    {
        $this->ensureSession();
        return (string) $_SESSION['csrf_token'];
    }

    public function verifyCsrfToken(?string $token): bool
    {
        $this->ensureSession();
        if (empty($token) || empty($_SESSION['csrf_token'])) {
            return false;
        }

        return hash_equals((string) $_SESSION['csrf_token'], $token);
    }

    public function validateCsrfRequest(?string $redirectUrl = null): void
    {
        $token = isset($_POST['csrf_token']) && is_string($_POST['csrf_token'])
            ? $_POST['csrf_token']
            : null;

        if ($this->verifyCsrfToken($token)) {
            return;
        }

        if ($redirectUrl !== null && $redirectUrl !== '') {
            $this->flash->set(
                'system_error',
                'Session expired or invalid security token (CSRF check failed). Please try again!'
            );
            $this->response->redirect($redirectUrl);
        }

        $baseUrl = $this->config->baseUrl();
        $html = "<div style='font-family: Arial, sans-serif; padding: 25px; background: #222; color: #ff4d4d; border: 1px solid #ff4d4d; border-radius: 8px; max-width: 550px; margin: 60px auto; text-align: center; box-shadow: 0 4px 15px rgba(0,0,0,0.5);'>"
            . "<h3 style='margin-top:0;'>[403 FORBIDDEN] SESSION SECURITY ERROR</h3>"
            . "<p style='color:#ccc; font-size:14px;'>Request rejected due to missing CSRF token or expired session.</p>"
            . "<a href='" . htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') . "index.php?route=film/list' style='display:inline-block; margin-top:15px; padding:8px 16px; background:#dc3545; color:#fff; text-decoration:none; border-radius:4px; font-weight:bold;'>&larr; Return to film catalog</a>"
            . "</div>";

        $this->response->abort(403, $html);
    }
}
