<?php
declare(strict_types=1);

namespace App\Services;

/**
 * Per-Tab Session Isolation & Partition Manager (RFC 4122 UUID v4).
 */
final class TabSession
{
    private static ?string $currentTabId = null;
    private static bool $initialized = false;
    private const UUID_PATTERN = '/^[a-f0-9]{8}-[a-f0-9]{4}-[1-8][a-f0-9]{3}-[89ab][a-f0-9]{3}-[a-f0-9]{12}$/i';
    private const DEFAULT_TIMEOUT = 28800;

    public static function generateUuidV4(): string
    {
        $data = random_bytes(16);
        $data[6] = chr((ord($data[6]) & 0x0f) | 0x40);
        $data[8] = chr((ord($data[8]) & 0x3f) | 0x80);
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }

    public static function init(
        ?string $customTabId = null,
        int $tabTimeout = self::DEFAULT_TIMEOUT,
        string $sessionName = 'CINEMA_SESSION'
    ): string {
        if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
            session_name($sessionName);
            @session_start();
        }
        if (!isset($_SESSION) || !is_array($_SESSION)) {
            $_SESSION = [];
        }

        $rawTabId = $customTabId
            ?? $_GET['tab_id']
            ?? $_POST['tab_id']
            ?? $_SERVER['HTTP_X_TAB_SESSION']
            ?? null;

        if (!empty($rawTabId) && preg_match(self::UUID_PATTERN, (string) $rawTabId)) {
            self::$currentTabId = (string) $rawTabId;
        } else {
            self::$currentTabId = 'default';
        }

        $now = time();
        if (isset($_SESSION['tabs']) && is_array($_SESSION['tabs'])) {
            foreach ($_SESSION['tabs'] as $id => $tab) {
                if (isset($tab['last_seen']) && ($now - (int) $tab['last_seen']) > $tabTimeout) {
                    unset($_SESSION['tabs'][$id]);
                }
            }
        } else {
            $_SESSION['tabs'] = [];
        }

        if (!isset($_SESSION['tabs'][self::$currentTabId])) {
            if (
                self::$currentTabId !== 'default'
                && isset($_SESSION['tabs']['default'])
                && !empty($_SESSION['tabs']['default']['user_id'])
                && ($now - (int) ($_SESSION['tabs']['default']['last_seen'] ?? 0)) < 60
            ) {
                $_SESSION['tabs'][self::$currentTabId] = $_SESSION['tabs']['default'];
                $_SESSION['tabs'][self::$currentTabId]['last_seen'] = $now;
                unset($_SESSION['tabs']['default']);
            } else {
                $_SESSION['tabs'][self::$currentTabId] = [
                    'user_id'    => null,
                    'role'       => 0,
                    'name'       => null,
                    'email'      => null,
                    'avatar'     => null,
                    'is_boss'    => false,
                    'created_at' => $now,
                    'last_seen'  => $now,
                ];
            }
        } else {
            $_SESSION['tabs'][self::$currentTabId]['last_seen'] = $now;
        }

        self::syncToGlobals();
        self::$initialized = true;

        return self::$currentTabId;
    }

    public static function getId(): string
    {
        if (!self::$initialized) {
            self::init();
        }

        return self::$currentTabId ?? 'default';
    }

    public static function decorateUrl(string $url): string
    {
        $tabId = self::getId();
        if ($tabId === '' || $tabId === 'default') {
            return $url;
        }

        $hashPos = strpos($url, '#');
        $baseUrl = ($hashPos !== false) ? substr($url, 0, $hashPos) : $url;
        $fragment = ($hashPos !== false) ? substr($url, $hashPos) : '';

        if (preg_match('/(?:^|[?&])tab_id=/', $baseUrl) === 1) {
            return $url;
        }

        $separator = (strpos($baseUrl, '?') !== false) ? '&' : '?';
        return $baseUrl . $separator . 'tab_id=' . urlencode($tabId) . $fragment;
    }

    public static function set(string $key, mixed $value): void
    {
        $tabId = self::getId();
        $_SESSION['tabs'][$tabId][$key] = $value;
        $_SESSION['tabs'][$tabId]['last_seen'] = time();
        self::syncToGlobals();
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        $tabId = self::getId();
        return $_SESSION['tabs'][$tabId][$key] ?? $default;
    }

    public static function has(string $key): bool
    {
        $tabId = self::getId();
        return isset($_SESSION['tabs'][$tabId][$key]);
    }

    public static function isUserLoggedInElsewhere(int $userId, int $tabTimeout = self::DEFAULT_TIMEOUT): bool
    {
        $currentTabId = self::getId();
        $now = time();

        if (!isset($_SESSION['tabs']) || !is_array($_SESSION['tabs'])) {
            return false;
        }

        if (isset($_POST['alive_tab_ids']) && is_string($_POST['alive_tab_ids'])) {
            $rawAlive = array_filter(array_map('trim', explode(',', $_POST['alive_tab_ids'])));
            $aliveTabIds = [];
            foreach ($rawAlive as $candidate) {
                if (preg_match(self::UUID_PATTERN, $candidate)) {
                    $aliveTabIds[] = $candidate;
                }
            }
            foreach ($_SESSION['tabs'] as $tid => $tdata) {
                if ($tid !== $currentTabId && $tid !== 'default' && !in_array($tid, $aliveTabIds, true)) {
                    unset($_SESSION['tabs'][$tid]);
                }
            }
        }

        foreach ($_SESSION['tabs'] as $tid => $tdata) {
            if ($tid === $currentTabId || $tid === 'default') {
                continue;
            }
            if (!empty($tdata['user_id']) && (int) $tdata['user_id'] === $userId) {
                $lastSeen = (int) ($tdata['last_seen'] ?? 0);
                if (($now - $lastSeen) <= $tabTimeout) {
                    return true;
                }
            }
        }

        return false;
    }

    public static function login(array $userData): void
    {
        $tabId = self::getId();
        $now = time();

        $_SESSION['tabs'][$tabId] = [
            'user_id'    => (int) $userData['id'],
            'role'       => (int) ($userData['role'] ?? 0),
            'name'       => $userData['name'] ?? 'User',
            'email'      => $userData['email'] ?? '',
            'avatar'     => $userData['avatar'] ?? null,
            'is_boss'    => !empty($userData['is_boss']) || ((int) ($userData['role'] ?? 0) === 1) || ((int) ($userData['id'] ?? 0) === 1),
            'created_at' => $now,
            'last_seen'  => $now,
        ];

        self::syncToGlobals();
    }

    public static function logout(): void
    {
        $tabId = self::getId();
        unset($_SESSION['tabs'][$tabId]);

        unset(
            $_SESSION['user_id'],
            $_SESSION['role'],
            $_SESSION['name'],
            $_SESSION['email'],
            $_SESSION['avatar'],
            $_SESSION['is_boss']
        );
    }

    public static function syncToGlobals(): void
    {
        $tabId = self::$currentTabId ?? 'default';
        $tabData = $_SESSION['tabs'][$tabId] ?? [];

        if (!empty($tabData['user_id'])) {
            $_SESSION['user_id'] = $tabData['user_id'];
            $_SESSION['role']    = $tabData['role'] ?? 0;
            $_SESSION['name']    = $tabData['name'] ?? 'User';
            $_SESSION['email']   = $tabData['email'] ?? '';
            $_SESSION['avatar']  = $tabData['avatar'] ?? null;
            $_SESSION['is_boss'] = !empty($tabData['is_boss']);
        } else {
            unset(
                $_SESSION['user_id'],
                $_SESSION['role'],
                $_SESSION['name'],
                $_SESSION['email'],
                $_SESSION['avatar'],
                $_SESSION['is_boss']
            );
        }
    }

    public static function refreshFromRecord(array $userData): void
    {
        $tabId = self::getId();
        if (empty($_SESSION['tabs'][$tabId]['user_id'])) {
            return;
        }

        $role = (int) ($userData['role'] ?? 0);
        $isBoss = ((int) ($userData['id'] ?? 0) === 1) || ($role === 1);

        $_SESSION['tabs'][$tabId]['role']    = $role;
        $_SESSION['tabs'][$tabId]['name']    = (string) ($userData['name'] ?? 'User');
        $_SESSION['tabs'][$tabId]['email']   = (string) ($userData['email'] ?? '');
        $_SESSION['tabs'][$tabId]['avatar']  = $userData['avatar'] ?? null;
        $_SESSION['tabs'][$tabId]['is_boss'] = $isBoss;

        self::syncToGlobals();
    }

    public static function resetForTest(): void
    {
        self::$currentTabId = null;
        self::$initialized = false;
    }
}
