<?php
declare(strict_types=1);

namespace App\Core;

/**
 * PSR-4 Autoloader for the App namespace.
 */
final class Autoloader
{
    private static bool $registered = false;

    public static function register(string $appBaseDir): void
    {
        if (self::$registered) {
            return;
        }

        $normalizedBase = rtrim($appBaseDir, '/\\') . DIRECTORY_SEPARATOR;

        spl_autoload_register(static function (string $className) use ($normalizedBase): void {
            $prefix = 'App\\';
            if (!str_starts_with($className, $prefix)) {
                return;
            }

            $relativeClass = substr($className, strlen($prefix));
            $file = $normalizedBase . str_replace('\\', DIRECTORY_SEPARATOR, $relativeClass) . '.php';
            if (is_file($file)) {
                require_once $file;
            }
        });

        self::$registered = true;
    }
}
