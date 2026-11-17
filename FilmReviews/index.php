<?php
declare(strict_types=1);

/**
 * Pure MVC Front Controller Entry Point - Tokusatsu Universe Cinema System.
 */

use App\Core\Application;
use App\Core\Autoloader;
use App\Core\HttpTerminateException;

require_once __DIR__ . '/app/Core/Autoloader.php';
Autoloader::register(__DIR__ . '/app');

try {
    $app = new Application(__DIR__);
    $app->handleHttpRequest(); // All web requests must pass through here.
} catch (HttpTerminateException $e) {
    throw $e;
} catch (\Throwable $e) {
    error_log('Tokusatsu Universe Kernel Error: ' . $e->getMessage());
    if (!headers_sent()) {
        http_response_code(500);
    }
    echo '<div style="font-family:sans-serif;padding:40px;text-align:center;background:#0d1117;color:#f85149;">';
    echo '<h2>System Error</h2><p>An unexpected server error occurred. Please try again later.</p>';
    echo '</div>';
}
