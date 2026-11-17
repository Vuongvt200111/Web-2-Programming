<?php
declare(strict_types=1);

namespace App\Presenters;

/**
 * Formats Audit Log records for UI table rendering and plain-text Notepad export.
 */
final class AuditLogPresenter
{
    public static function getBadgeClass(string $action): string
    {
        if (str_contains($action, 'SECURITY') || str_contains($action, 'BLOCK')) {
            return 'bg-danger text-white';
        }
        if (str_contains($action, 'APPROVE') || str_contains($action, 'SUCCESS') || $action === 'UNBLOCK_USER' || str_contains($action, 'ADD_') || str_contains($action, 'SUBMIT_')) {
            return 'bg-success text-white';
        }
        if ($action === 'USER_LOGIN' || $action === 'USER_LOGOUT') {
            return 'bg-info text-dark';
        }
        if ($action === 'USER_REGISTER' || $action === 'CREATE_ADMIN' || $action === 'SETUP_ADMIN') {
            return 'bg-primary text-white';
        }
        if (str_contains($action, 'DELETE') || str_contains($action, 'REJECT') || str_contains($action, 'FAILED')) {
            return 'bg-warning text-dark';
        }
        if (str_contains($action, 'PROMOTE') || str_contains($action, 'DEMOTE') || str_contains($action, 'EDIT_')) {
            return 'bg-info text-dark';
        }

        return 'bg-secondary text-white';
    }

    public static function getRoleLabel(int $role): string
    {
        if ($role === 1) {
            return 'Boss';
        }
        if ($role >= 2) {
            return 'Sub-Admin';
        }

        return 'Reviewer / Member';
    }

    /**
     * @param array<int, array<string, mixed>> $logs
     */
    public static function formatExportText(
        string $scope,
        string $filterAction,
        array $logs,
        string $bossName
    ): string {
        $scopeTitle = ($scope === 'admin') ? 'Administrator Activities' : 'Reviewers Activities';
        $filterTitle = ($filterAction === 'all' || $filterAction === '') ? 'All Actions' : $filterAction;
        $exportedAt = date('d/m/Y H:i:s');

        $lines = [
            '                 TOKUSATSU UNIVERSE - SYSTEM AUDIT TRAIL REPORT',
            'Report Category : ' . $scopeTitle,
            'Action Filter   : ' . $filterTitle,
            'Exported By     : ' . $bossName . ' (System Boss)',
            'Exported At     : ' . $exportedAt,
            'Total Records   : ' . count($logs),
            '',
        ];

        if ($logs === []) {
            $lines[] = 'No audit log entries recorded under "' . $scopeTitle . '" for filter "' . $filterTitle . '".';
            $lines[] = '';
        } else {
            foreach ($logs as $idx => $log) {
                $num = $idx + 1;
                $id = (int) ($log['id'] ?? 0);
                $time = !empty($log['created_at']) ? date('d/m/Y H:i:s', strtotime((string) $log['created_at'])) : 'N/A';
                $userName = (string) ($log['user_name'] ?? 'Unknown');
                $roleLabel = self::getRoleLabel((int) ($log['user_role'] ?? 0));
                $action = (string) ($log['action'] ?? 'UNKNOWN');
                $details = (string) ($log['details'] ?? '');
                $ip = (string) ($log['ip_address'] ?? 'N/A');

                $lines[] = sprintf('[%d] Log ID: #%d | Timestamp: %s | IP: %s', $num, $id, $time, $ip);
                $lines[] = sprintf('    Operator : %s (%s)', $userName, $roleLabel);
                $lines[] = sprintf('    Action   : %s', $action);
                $lines[] = sprintf('    Details  : %s', $details);
                $lines[] = str_repeat('-', 88);
            }
        }

        $lines[] = '                           END OF AUDIT TRAIL REPORT';

        return implode("\r\n", $lines) . "\r\n";
    }
}
