<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Centralized configuration loader and RFC 3986 BASE_URL resolver.
 */
final class Config
{
    private string $rootPath;
    /** @var array<string, array<string, mixed>> */
    private array $sections = [];

    public function __construct(string $rootPath)
    {
        $this->rootPath = rtrim($rootPath, '/\\');
        $this->ensureBaseUrlDefined();
    }

    public function rootPath(string $relativePath = ''): string
    {
        if ($relativePath === '') {
            return $this->rootPath;
        }

        return $this->rootPath . DIRECTORY_SEPARATOR . ltrim(str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $relativePath), DIRECTORY_SEPARATOR);
    }

    public function baseUrl(): string
    {
        return defined('BASE_URL') ? (string) BASE_URL : '/';
    }

    /**
     * @return array<string, mixed>
     */
    public function section(string $name): array
    {
        if (!isset($this->sections[$name])) {
            $path = $this->rootPath('config/' . $name . '.php');
            $data = is_file($path) ? require $path : [];
            $this->sections[$name] = is_array($data) ? $data : [];
        }

        return $this->sections[$name];
    }

    /**
     * @param mixed $default
     * @return mixed
     */
    public function get(string $section, string $key, mixed $default = null): mixed
    {
        $data = $this->section($section);
        return $data[$key] ?? $default;
    }

    private function ensureBaseUrlDefined(): void
    {
        if (defined('BASE_URL')) {
            return;
        }

        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';

        $rawDocRoot = $_SERVER['DOCUMENT_ROOT'] ?? 'C:/xampp/htdocs';
        $resolvedDocRoot = realpath($rawDocRoot);
        $docRoot = str_replace('\\', '/', $resolvedDocRoot !== false ? $resolvedDocRoot : $rawDocRoot);

        $resolvedAppDir = realpath($this->rootPath);
        $appDir = str_replace('\\', '/', $resolvedAppDir !== false ? $resolvedAppDir : $this->rootPath);
        $subPath = str_replace($docRoot, '', $appDir);

        $parts = array_filter(explode('/', trim($subPath, '/')), static fn(string $part): bool => $part !== '');
        $encodedParts = array_map('rawurlencode', $parts);
        $encodedSubPath = $encodedParts === [] ? '/' : '/' . implode('/', $encodedParts) . '/';

        define('BASE_URL', $protocol . $host . $encodedSubPath);
    }
}
