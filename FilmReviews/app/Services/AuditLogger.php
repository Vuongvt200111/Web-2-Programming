<?php
declare(strict_types=1);

namespace App\Services;

use App\Models\AuditLogModel;

/**
 * Pure OOP Audit Trail recording service (zero global PDO dependencies).
 */
final class AuditLogger
{
    private AuditLogModel $auditLogs;

    public function __construct(AuditLogModel $auditLogs)
    {
        $this->auditLogs = $auditLogs;
    }

    public function log(
        string $action,
        string $details,
        ?int $userId = null,
        ?int $userRole = null,
        ?string $userName = null
    ): void {
        try {
            $uid = $userId ?? (isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : null);
            $uname = $userName ?? (string) ($_SESSION['name'] ?? ($_SESSION['email'] ?? 'System/Guest'));
            $urole = $userRole ?? (int) ($_SESSION['role'] ?? 0);
            $ip = (string) ($_SERVER['REMOTE_ADDR'] ?? '127.0.0.1');

            $this->auditLogs->record([
                'user_id'    => $uid,
                'user_name'  => $uname,
                'user_role'  => $urole,
                'action'     => strtoupper($action),
                'details'    => $details,
                'ip_address' => $ip,
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        } catch (\Throwable $e) {
            error_log('AuditLogger Failure: ' . $e->getMessage());
        }
    }
}
