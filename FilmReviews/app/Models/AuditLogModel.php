<?php
declare(strict_types=1);

namespace App\Models;

/**
 * Audit Log domain model encapsulating security trail recording and scoped filtering.
 */
final class AuditLogModel extends BaseModel
{
    public const REVIEWER_ACTIONS = [
        'BLOCKED_LOGIN_ATTEMPT',
        'CONCURRENT_LOGIN_BLOCKED',
        'DELETE_FILM',
        'DELETE_REVIEW',
        'EDIT_FILM',
        'EDIT_REVIEW',
        'LOGIN_FAILED',
        'SUBMIT_FILM',
        'SUBMIT_REVIEW',
        'USER_LOGIN',
        'USER_LOGOUT',
        'USER_REGISTER',
    ];

    public const ADMIN_ACTIONS = [
        'ADD_CATEGORY',
        'APPROVE_FILM',
        'BLOCK_USER',
        'CREATE_ADMIN',
        'DELETE_CATEGORY',
        'DELETE_FILM',
        'DELETE_REVIEW',
        'DELETE_USER',
        'DEMOTE_USER',
        'EDIT_CATEGORY',
        'EMERGENCY_BOSS_RESET',
        'PROMOTE_USER',
        'PRUNE_ORPHAN_FILES',
        'REJECT_FILM',
        'RESTORE_USER',
        'SECURITY_VIOLATION',
        'SETUP_ADMIN',
        'UNBLOCK_USER',
    ];

    public function __construct(\PDO $pdo)
    {
        parent::__construct($pdo, 'audit_logs', 'id');
    }

    public function record(array $data): void
    {
        $this->insert($data);
    }

    /**
     * Resolve audit log scope, action dropdowns, and filtered records cleanly.
     *
     * @return array<string, mixed>
     */
    public function getFilteredData(?string $rawScopeInput, string $rawFilterAction): array
    {
        $reviewerActions = self::REVIEWER_ACTIONS;
        $adminActions = self::ADMIN_ACTIONS;
        sort($reviewerActions);
        sort($adminActions);

        $rawScope = strtolower(trim((string) $rawScopeInput));
        $filterAction = trim($rawFilterAction);

        if ($filterAction === '' || $filterAction === 'role_reviewer' || $filterAction === 'role_admin') {
            if ($filterAction === 'role_reviewer') {
                $rawScope = 'reviewer';
            } elseif ($filterAction === 'role_admin') {
                $rawScope = 'admin';
            }
            $filterAction = 'all';
        }

        if (in_array($rawScope, ['reviewer', 'admin'], true)) {
            $scope = $rawScope;
        } elseif ($filterAction !== 'all' && in_array($filterAction, $adminActions, true) && !in_array($filterAction, $reviewerActions, true)) {
            $scope = 'admin';
        } else {
            $scope = 'reviewer';
        }

        $activeActions = ($scope === 'admin') ? $adminActions : $reviewerActions;
        if ($filterAction !== 'all' && !in_array($filterAction, $activeActions, true)) {
            $filterAction = 'all';
        }

        $params = [];
        if ($scope === 'admin') {
            $sql = "SELECT * FROM `audit_logs` WHERE `user_role` >= 1 AND `action` NOT IN ('USER_LOGIN', 'USER_LOGOUT', 'DELETE_AUDIT_LOG')";
            if ($filterAction !== 'all') {
                $sql .= ' AND `action` = :act';
                $params['act'] = $filterAction;
            }
        } else {
            $sql = "SELECT * FROM `audit_logs` WHERE `user_role` = 0 AND `action` != 'DELETE_AUDIT_LOG'";
            if ($filterAction !== 'all') {
                $sql .= ' AND `action` = :act';
                $params['act'] = $filterAction;
            }
        }

        $sql .= ' ORDER BY `id` DESC LIMIT 100';
        $logs = $this->gateway->customQuery($sql, $params);

        $revCountRow = $this->gateway->customQuerySingle(
            "SELECT COUNT(*) AS cnt FROM `audit_logs` WHERE `user_role` = 0 AND `action` != 'DELETE_AUDIT_LOG'"
        );
        $admCountRow = $this->gateway->customQuerySingle(
            "SELECT COUNT(*) AS cnt FROM `audit_logs` WHERE `user_role` >= 1 AND `action` NOT IN ('USER_LOGIN', 'USER_LOGOUT', 'DELETE_AUDIT_LOG')"
        );

        $distinctActions = array_values(array_unique(array_merge($reviewerActions, $adminActions)));
        sort($distinctActions);

        return [
            'logs'               => $logs,
            'scope'              => $scope,
            'filterAction'       => $filterAction,
            'activeActions'      => $activeActions,
            'reviewerActions'    => $reviewerActions,
            'adminActions'       => $adminActions,
            'distinctActions'    => $distinctActions,
            'reviewerTotalCount' => (int) ($revCountRow['cnt'] ?? 0),
            'adminTotalCount'    => (int) ($admCountRow['cnt'] ?? 0),
        ];
    }
}
