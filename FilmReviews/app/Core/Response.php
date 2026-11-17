<?php
declare(strict_types=1);

namespace App\Core;

use App\Services\TabSession;

/**
 * Unified HTTP Response handler for redirects, error aborts, and file downloads.
 */
final class Response
{
    public function redirect(string $url): never
    {
        $decoratedUrl = TabSession::decorateUrl($url);

        if (PHP_SAPI === 'cli' && defined('TEST_RUNNER')) {
            throw new HttpTerminateException(302, $decoratedUrl);
        }

        if (!headers_sent()) {
            header('Location: ' . $decoratedUrl);
        } else {
            echo '<script>window.location.href=' . json_encode($decoratedUrl) . ';</script>';
            echo '<noscript><meta http-equiv="refresh" content="0;url=' . htmlspecialchars($decoratedUrl, ENT_QUOTES, 'UTF-8') . '"></noscript>';
        }

        exit;
    }

    public function abort(int $statusCode, string $htmlBody): never
    {
        if (PHP_SAPI === 'cli' && defined('TEST_RUNNER')) {
            throw new HttpTerminateException($statusCode, null, $htmlBody);
        }

        if (!headers_sent()) {
            http_response_code($statusCode);
        }

        echo $htmlBody;
        exit;
    }

    public function downloadText(string $filename, string $content): never
    {
        if (PHP_SAPI === 'cli' && defined('TEST_RUNNER')) {
            throw new HttpTerminateException(200, null, $content);
        }

        if (!headers_sent()) {
            header('Content-Type: text/plain; charset=utf-8');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Content-Length: ' . strlen($content));
            header('Cache-Control: no-store, no-cache, must-revalidate');
            header('Pragma: no-cache');
        }

        echo $content;
        exit;
    }
}
