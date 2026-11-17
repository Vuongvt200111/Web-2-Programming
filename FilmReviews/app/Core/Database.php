<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Encapsulates PDO connection lifecycle without polluting the global scope.
 */
final class Database
{
    private \PDO $pdo;

    public function __construct(Config $config)
    {
        $timezone = (string) $config->get('app', 'timezone', 'Asia/Ho_Chi_Minh');
        date_default_timezone_set($timezone);

        $db = $config->section('database');
        $host = (string) ($db['host'] ?? 'localhost');
        $dbname = (string) ($db['dbname'] ?? 'filmreviews_tokusatsu');
        $charset = (string) ($db['charset'] ?? 'utf8mb4');
        $username = (string) ($db['username'] ?? 'root');
        $password = (string) ($db['password'] ?? '');
        $options = is_array($db['options'] ?? null) ? $db['options'] : [
            \PDO::ATTR_ERRMODE            => \PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
        ];

        $dsn = "mysql:host={$host};dbname={$dbname};charset={$charset}";
        $this->pdo = new \PDO($dsn, $username, $password, $options);
    }

    public function getPdo(): \PDO
    {
        return $this->pdo;
    }
}
