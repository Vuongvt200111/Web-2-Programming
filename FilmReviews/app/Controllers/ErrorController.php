<?php
declare(strict_types=1);

namespace App\Controllers;

/**
 * Handles fallback HTTP 404 responses cleanly through the View layer.
 */
final class ErrorController extends BaseController
{
    public function notFound(string $rawRoute = ''): array
    {
        if (!headers_sent()) {
            http_response_code(404);
        }

        return $this->view('errors/404', 'layouts/catalog', '404 Not Found - Tokusatsu Universe', [
            'route' => $rawRoute,
        ]);
    }
}
