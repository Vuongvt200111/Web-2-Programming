<?php
declare(strict_types=1);

namespace App\Core;

use App\Presenters\ViewHelper;
use App\Services\MediaService;
use App\Services\SecurityService;
use App\Services\TabSession;

/**
 * Pure MVC View Renderer injecting sanitized view variables, CSRF, AuthContext, and URL helpers.
 */
final class View
{
    private Config $config;
    private SecurityService $security;
    private MediaService $media;

    public function __construct(Config $config, SecurityService $security, MediaService $media)
    {
        $this->config = $config;
        $this->security = $security;
        $this->media = $media;
    }

    /**
     * Render view template and wrap in optional layout.
     *
     * @param array<string, mixed> $pageData
     */
    public function render(array $pageData): string
    {
        $baseUrl = $this->config->baseUrl();
        $authContext = AuthContext::fromSession();
        $viewHelper = new ViewHelper(
            $baseUrl,
            $this->security->getCsrfToken(),
            TabSession::getId()
        );

        $shared = [
            'title'      => $pageData['title'] ?? 'Tokusatsu Universe',
            'baseUrl'    => $baseUrl,
            'csrfToken'  => $this->security->getCsrfToken(),
            'tabId'      => TabSession::getId(),
            'auth'       => $authContext->toViewArray($this->media, $baseUrl),
            'viewHelper' => $viewHelper,
            'currentYear' => date('Y'),
        ];

        $variables = array_merge($shared, $pageData['variables'] ?? []);

        $templatePath = $this->resolveViewPath((string) $pageData['template']);
        $content = $this->renderFile($templatePath, $variables);

        if (!empty($pageData['layout'])) {
            $layoutPath = $this->resolveViewPath((string) $pageData['layout']);
            $layoutVars = array_merge($variables, [
                'content' => $content,
                'output'  => $content,
            ]);
            return $this->renderFile($layoutPath, $layoutVars);
        }

        return $content;
    }

    private function resolveViewPath(string $viewName): string
    {
        if (is_file($viewName)) {
            return $viewName;
        }

        $relative = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, ltrim($viewName, '/\\'));
        if (!str_ends_with($relative, '.php')) {
            $relative .= '.php';
        }

        $path = $this->config->rootPath('views/' . $relative);
        if (!is_file($path)) {
            throw new \RuntimeException("View file not found: {$path}");
        }

        return $path;
    }

    /**
     * @param array<string, mixed> $variables
     */
    private function renderFile(string $filePath, array $variables): string
    {
        extract($variables, EXTR_SKIP);
        ob_start();
        include $filePath;
        $output = ob_get_clean();
        return $output === false ? '' : $output;
    }
}
