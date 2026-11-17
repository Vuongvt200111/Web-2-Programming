<?php
declare(strict_types=1);

/*
TOKUSATSU UNIVERSE - SYSTEM BOSS EMERGENCY CREDENTIAL RESET UTILITY (CLI-ONLY)
 */

use App\Core\Application;
use App\Core\Autoloader;

if (PHP_SAPI !== 'cli') {
    if (!headers_sent()) {
        header('HTTP/1.1 403 Forbidden');
        header('Content-Type: text/plain; charset=utf-8');
    }
    http_response_code(403);
    exit("403 FORBIDDEN: Direct web access to CLI administrative utilities is strictly prohibited.\n");
}

$args = $_SERVER['argv'] ?? [];
array_shift($args);

if (in_array('--help', $args, true) || in_array('-h', $args, true)) {
    echo "Tokusatsu Universe - System Boss Emergency Password Reset (CLI Only)\n";
    echo "Usage:\n";
    echo "  php bin/emergency_reset_boss.php [new_password] [--email=boss@tokusatsu.com]\n\n";
    echo "Options:\n";
    echo "  [new_password]   Optional new plain-text password (min 6 chars).\n";
    echo "                   If omitted, a secure random password is generated.\n";
    echo "  --email=ADDRESS  Target specific Boss email if multiple Boss records exist.\n";
    echo "  -h, --help       Show this help message.\n";
    exit(0);
}

$customPassword = null;
$targetEmail = null;

foreach ($args as $arg) {
    if (str_starts_with($arg, '--email=')) {
        $targetEmail = trim(substr($arg, 8));
    } elseif (!str_starts_with($arg, '-')) {
        $customPassword = $arg;
    }
}

$generatedRandomly = false;
if ($customPassword === null || $customPassword === '') {
    $newPassword = 'Boss_' . strtoupper(bin2hex(random_bytes(4))) . '!';
    $generatedRandomly = true;
} else {
    if (strlen($customPassword) < 6) {
        fwrite(STDERR, "[ERROR] Custom password must be at least 6 characters long.\n");
        exit(1);
    }
    $newPassword = $customPassword;
}

$rootDir = dirname(__DIR__);
require_once $rootDir . '/app/Core/Autoloader.php';
Autoloader::register($rootDir . '/app');

try {
    $app = new Application($rootDir);
    $users = $app->getUsers();
    $auditLogger = $app->getAuditLogger();

    $boss = $users->findBossForEmergencyReset($targetEmail);
    if (!$boss) {
        fwrite(STDERR, "[ERROR] No System Boss account found in database matching criteria.\n");
        exit(1);
    }

    $bossId = (int) $boss['id'];
    $bossEmail = (string) $boss['email'];
    $bossName = (string) $boss['name'];
    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

    $users->resetBossCredentials($bossId, $hashedPassword);

    $_SERVER['REMOTE_ADDR'] = '127.0.0.1 (CLI-LOCAL)';
    $auditLogger->log(
        'EMERGENCY_BOSS_RESET',
        "System Boss password was reset via CLI emergency script (bin/emergency_reset_boss.php) for {$bossEmail} (ID: {$bossId}).",
        $bossId,
        1,
        $bossName . ' [CLI]'
    );

    echo "\n==================================================================\n";
    echo " [SUCCESS] SYSTEM BOSS CREDENTIALS RESET SUCCESSFULLY\n";
    echo "==================================================================\n";
    echo " Boss ID      : #{$bossId}\n";
    echo " Boss Name    : {$bossName}\n";
    echo " Boss Email   : {$bossEmail}\n";
    echo " New Password : {$newPassword}" . ($generatedRandomly ? '  (auto-generated)' : '') . "\n";
    echo " Security     : Lock/suspension cleared & reset tokens invalidated.\n";
    echo " Audit Trail  : Logged action EMERGENCY_BOSS_RESET.\n";
    echo "==================================================================\n\n";
} catch (\Throwable $e) {
    fwrite(STDERR, '[ERROR] Database operation failed: ' . $e->getMessage() . "\n");
    exit(1);
}
