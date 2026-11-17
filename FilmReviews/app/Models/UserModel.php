<?php
declare(strict_types=1);

namespace App\Models;

/**
 * User domain model managing account queries, suspension state, and Boss CLI recovery.
 */
final class UserModel extends BaseModel
{
    public function __construct(\PDO $pdo)
    {
        parent::__construct($pdo, 'users', 'id');
    }

    public function findByEmail(string $email): array|false
    {
        return $this->findOne('email', $email);
    }

    public function findByResetToken(string $token): array|false
    {
        return $this->findOne('reset_token', $token);
    }

    public function isBlocked(array $user): bool
    {
        if (empty($user['is_blocked']) || (int) $user['is_blocked'] !== 1) {
            return false;
        }
        if (!empty($user['blocked_until']) && strtotime((string) $user['blocked_until']) <= time()) {
            return false;
        }

        return true;
    }

    public function syncExpiredBlocks(): void
    {
        try {
            $this->gateway->customExecute(
                'UPDATE users SET is_blocked = 0, blocked_until = NULL, block_reason = NULL WHERE is_blocked = 1 AND blocked_until IS NOT NULL AND blocked_until <= NOW()'
            );
        } catch (\Throwable $e) {
            error_log('Failed to synchronize expired user blocks: ' . $e->getMessage());
        }
    }

    public function hasAnyAdministrator(): bool
    {
        $row = $this->gateway->customQuerySingle(
            'SELECT COUNT(*) AS cnt FROM users WHERE role = 1 OR role >= 2'
        );

        return ((int) ($row['cnt'] ?? 0)) > 0;
    }

    public function findAllOrderedForAdmin(): array
    {
        return $this->findAll('(role = 0) ASC, role ASC, id ASC');
    }

    /**
     * @return array<int, string>
     */
    public function getActiveAvatarFilenames(): array
    {
        $rows = $this->gateway->customQuery("SELECT avatar FROM users WHERE avatar IS NOT NULL AND avatar != ''");
        return array_values(array_filter(array_map(static fn(array $r): string => (string) ($r['avatar'] ?? ''), $rows)));
    }

    public function findBossForEmergencyReset(?string $email): ?array
    {
        if ($email !== null) {
            $row = $this->gateway->customQuerySingle(
                'SELECT id, name, email, role, status, is_blocked, is_deleted
                 FROM users
                 WHERE email = :email AND (role = 1 OR id = 1)
                 LIMIT 1',
                ['email' => $email]
            );
            return $row !== false ? $row : null;
        }

        $row = $this->gateway->customQuerySingle(
            'SELECT id, name, email, role, status, is_blocked, is_deleted
             FROM users
             WHERE role = 1 OR id = 1
             ORDER BY (id = 1) DESC, id ASC
             LIMIT 1'
        );

        return $row !== false ? $row : null;
    }

    public function resetBossCredentials(int $bossId, string $passwordHash): int
    {
        return $this->gateway->customExecute(
            "UPDATE users
             SET password = :password,
                 role = 1,
                 badge = 'System Boss',
                 reset_token = NULL,
                 reset_expires = NULL,
                 status = 'active',
                 is_deleted = 0,
                 is_blocked = 0,
                 blocked_until = NULL,
                 block_reason = NULL,
                 updated_at = NOW()
             WHERE id = :id AND (role = 1 OR id = 1)
             LIMIT 1",
            [
                'password' => $passwordHash,
                'id'       => $bossId,
            ]
        );
    }

    /**
     * Fetch all Sub-Admin accounts (role >= 2 and excluding Root Boss ID 1).
     *
     * @return array<int, array<string, mixed>>
     */
    public function findSubAdmins(): array
    {
        return $this->gateway->customQuery(
            'SELECT id, name, email, status, is_deleted, is_blocked
             FROM users
             WHERE role >= 2 AND id != 1
             ORDER BY id ASC'
        );
    }

    public function resetSubAdminPassword(int $userId, string $passwordHash): int
    {
        return $this->gateway->customExecute(
            'UPDATE users
             SET password = :password,
                 reset_token = NULL,
                 reset_expires = NULL,
                 updated_at = NOW()
             WHERE id = :id AND role >= 2 AND id != 1
             LIMIT 1',
            [
                'password' => $passwordHash,
                'id'       => $userId,
            ]
        );
    }
}
