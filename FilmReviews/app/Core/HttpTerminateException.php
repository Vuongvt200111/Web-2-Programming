<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Signal exception thrown under TEST_RUNNER mode to unwind cleanly without terminating PHP CLI.
 */
final class HttpTerminateException extends \RuntimeException
{
    private int $statusCode;
    private ?string $location;
    private string $responseBody;

    public function __construct(int $statusCode = 200, ?string $location = null, string $responseBody = '')
    {
        parent::__construct($location !== null ? "HTTP Redirect to {$location}" : "HTTP {$statusCode}");
        $this->statusCode = $statusCode;
        $this->location = $location;
        $this->responseBody = $responseBody;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function getLocation(): ?string
    {
        return $this->location;
    }

    public function getResponseBody(): string
    {
        return $this->responseBody;
    }
}
