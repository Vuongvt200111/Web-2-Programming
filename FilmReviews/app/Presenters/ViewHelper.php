<?php
declare(strict_types=1);

namespace App\Presenters;

use App\Services\TabSession;

/**
 * Pure OOP template helper injected into all Views (replaces procedural tabUrl/getCsrfToken).
 */
final class ViewHelper
{
    private string $baseUrl;
    private string $csrfToken;
    private string $tabId;

    public function __construct(string $baseUrl, string $csrfToken, string $tabId)
    {
        $this->baseUrl = $baseUrl;
        $this->csrfToken = $csrfToken;
        $this->tabId = $tabId;
    }

    public function e(mixed $value): string
    {
        return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES, 'UTF-8');
    }

    public function tabUrl(string $url): string
    {
        return TabSession::decorateUrl($url);
    }

    public function routeUrl(string $route, array $query = [], bool $withTab = true): string
    {
        $params = array_merge(['route' => $route], $query);
        $url = $this->baseUrl . 'index.php?' . http_build_query($params);
        return $withTab ? TabSession::decorateUrl($url) : $url;
    }

    public function asset(string $relativePath): string
    {
        return $this->baseUrl . ltrim($relativePath, '/');
    }

    public function csrfInput(): string
    {
        return '<input type="hidden" name="csrf_token" value="' . $this->e($this->csrfToken) . '">';
    }

    public function tabInput(): string
    {
        $val = $this->tabId !== 'default' ? $this->tabId : '';
        return '<input type="hidden" name="tab_id" value="' . $this->e($val) . '" class="tab-session-input">';
    }

    public function tabId(): string
    {
        return $this->tabId;
    }
}
