<?php
declare(strict_types=1);

namespace App\Services;

use App\Core\Config;

/**
 * Centralized media upload validation, safe deletion, orphan garbage collection, and path resolution.
 */
final class MediaService
{
    private Config $config;
    /** @var array<string, mixed> */
    private array $mediaConfig;

    public function __construct(Config $config)
    {
        $this->config = $config;
        $this->mediaConfig = $config->section('media');
    }

    public function uploadDir(): string
    {
        return rtrim((string) ($this->mediaConfig['upload_dir'] ?? $this->config->rootPath('uploads')), '/\\');
    }

    /**
     * @return array<int, string>
     */
    public function getProtectedAssets(): array
    {
        $assets = $this->mediaConfig['protected_assets'] ?? [];
        return is_array($assets) ? $assets : [];
    }

    /**
     * @return array{success: bool, filename?: string, error?: string}
     */
    public function uploadSecureImage(array $file, ?string $targetDir = null): array
    {
        if (!isset($file['error']) || is_array($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
            return ['success' => false, 'error' => 'File upload error or no file selected!'];
        }

        $maxBytes = (int) ($this->mediaConfig['max_bytes'] ?? 5242880);
        if ((int) ($file['size'] ?? 0) > $maxBytes) {
            return [
                'success' => false,
                'error'   => 'Image file size exceeds allowable limit (maximum ' . round($maxBytes / 1048576) . 'MB)!',
            ];
        }

        $allowedExtensions = $this->mediaConfig['allowed_extensions'] ?? ['jpg', 'jpeg', 'png', 'webp'];
        $originalName = (string) ($file['name'] ?? '');
        $clientExt = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        if (!in_array($clientExt, $allowedExtensions, true)) {
            return [
                'success' => false,
                'error'   => 'The system only accepts valid image formats (' . implode(', ', $allowedExtensions) . ').',
            ];
        }

        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $realMime = (string) $finfo->file((string) $file['tmp_name']);
        $mimeToCanonical = $this->mediaConfig['mime_to_canonical'] ?? [
            'image/jpeg'  => 'jpg',
            'image/pjpeg' => 'jpg',
            'image/png'   => 'png',
            'image/x-png' => 'png',
            'image/webp'  => 'webp',
        ];

        if (!isset($mimeToCanonical[$realMime])) {
            return ['success' => false, 'error' => 'File content is not a valid image format or is corrupted!'];
        }

        if (@getimagesize((string) $file['tmp_name']) === false) {
            return ['success' => false, 'error' => 'File is corrupted or not a real image!'];
        }

        $destDir = $targetDir !== null ? rtrim($targetDir, '/\\') : $this->uploadDir();
        if (!is_dir($destDir)) {
            @mkdir($destDir, 0775, true);
        }

        $canonicalExt = $mimeToCanonical[$realMime];
        $newFileName = time() . '_' . bin2hex(random_bytes(8)) . '.' . $canonicalExt;
        $targetPath = $destDir . DIRECTORY_SEPARATOR . $newFileName;

        $saveSuccess = is_uploaded_file((string) $file['tmp_name'])
            ? move_uploaded_file((string) $file['tmp_name'], $targetPath)
            : copy((string) $file['tmp_name'], $targetPath);

        if (!$saveSuccess) {
            return ['success' => false, 'error' => 'Failed to save image file to server!'];
        }

        return [
            'success'  => true,
            'filename' => $newFileName,
        ];
    }

    public function deleteSafely(?string $fileName, ?string $dir = null): bool
    {
        if (empty($fileName)) {
            return false;
        }

        $cleanName = basename($fileName);
        $protectedLower = array_map('strtolower', $this->getProtectedAssets());
        if (in_array(strtolower($cleanName), $protectedLower, true)) {
            return false;
        }

        $targetDir = $dir !== null ? rtrim($dir, '/\\') : $this->uploadDir();
        $targetPath = $targetDir . DIRECTORY_SEPARATOR . $cleanName;

        if (is_file($targetPath)) {
            return @unlink($targetPath);
        }

        return false;
    }

    /**
     * Garbage collect unreferenced files in uploads/ while preserving active DB refs,
     * source references, and protected system assets.
     *
     * @param array<int, string> $activeDbFilenames
     * @return array{deletedCount: int, freedBytes: int, freedFormatted: string}
     */
    public function pruneOrphans(array $activeDbFilenames): array
    {
        $activeImages = array_map(static fn(string $f): string => strtolower(basename($f)), $activeDbFilenames);
        $activeSet = array_flip(array_filter($activeImages));
        $protectedSet = array_flip(array_map('strtolower', $this->getProtectedAssets()));

        $codeContent = '';
        $scanDirs = $this->mediaConfig['source_scan_dirs'] ?? ['public', 'views', 'app', 'config', 'bin', 'database'];
        foreach ($scanDirs as $dirName) {
            $dirPath = $this->config->rootPath((string) $dirName);
            if (!is_dir($dirPath)) {
                continue;
            }
            $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dirPath));
            foreach ($iterator as $fileInfo) {
                if ($fileInfo instanceof \SplFileInfo && $fileInfo->isFile()) {
                    if (in_array(strtolower($fileInfo->getExtension()), ['php', 'css', 'js', 'sql', 'html'], true)) {
                        $content = @file_get_contents($fileInfo->getRealPath() ?: $fileInfo->getPathname());
                        if (is_string($content)) {
                            $codeContent .= ' ' . $content;
                        }
                    }
                }
            }
        }

        $uploadDir = $this->uploadDir();
        $scannedFiles = is_dir($uploadDir) ? scandir($uploadDir) : false;

        $deletedCount = 0;
        $freedBytes = 0;

        if ($scannedFiles !== false) {
            foreach ($scannedFiles as $file) {
                if ($file === '.' || $file === '..') {
                    continue;
                }

                $filePath = $uploadDir . DIRECTORY_SEPARATOR . $file;
                if (!is_file($filePath)) {
                    continue;
                }

                $lowerName = strtolower($file);
                if (isset($protectedSet[$lowerName]) || isset($activeSet[$lowerName]) || stripos($codeContent, $file) !== false) {
                    continue;
                }

                $fileSize = @filesize($filePath);
                if (@unlink($filePath)) {
                    $deletedCount++;
                    $freedBytes += ($fileSize !== false ? (int) $fileSize : 0);
                }
            }
        }

        $freedFormatted = $freedBytes >= 1048576
            ? round($freedBytes / 1048576, 2) . ' MB'
            : round($freedBytes / 1024, 2) . ' KB';

        return [
            'deletedCount'   => $deletedCount,
            'freedBytes'     => $freedBytes,
            'freedFormatted' => $freedFormatted,
        ];
    }

    public function fileExists(?string $fileName): bool
    {
        if (empty($fileName)) {
            return false;
        }

        $cleanName = basename($fileName);
        if ($cleanName === '' || $cleanName === '.' || $cleanName === '..') {
            return false;
        }

        return is_file($this->uploadDir() . DIRECTORY_SEPARATOR . $cleanName);
    }

    /**
     * Uniform avatar resolution for all templates (navbar, profile, reviews feed).
     *
     * @return array{hasImage: bool, filename: ?string, url: ?string, initial: string}
     */
    public function resolveAvatar(?string $fileName, string $fallbackName = 'U', ?string $baseUrl = null): array
    {
        $resolvedBaseUrl = $baseUrl ?? $this->config->baseUrl();
        $cleanName = !empty($fileName) ? basename($fileName) : null;
        $hasImage = $cleanName !== null && $this->fileExists($cleanName);
        $initial = strtoupper(substr(trim($fallbackName) !== '' ? trim($fallbackName) : 'U', 0, 1));

        return [
            'hasImage' => $hasImage,
            'filename' => $hasImage ? $cleanName : null,
            'url'      => $hasImage ? ($resolvedBaseUrl . 'uploads/' . $cleanName) : null,
            'initial'  => $initial,
        ];
    }

    public function resolvePoster(?string $fileName, ?string $baseUrl = null): string
    {
        $resolvedBaseUrl = $baseUrl ?? $this->config->baseUrl();
        $defaultPoster = (string) ($this->mediaConfig['default_poster'] ?? 'default.png');
        $chosen = !empty($fileName) ? basename($fileName) : $defaultPoster;

        return $resolvedBaseUrl . 'uploads/' . $chosen;
    }
}
