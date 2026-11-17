<?php
declare(strict_types=1);

namespace App\Models;

/**
 * Message domain model for user support inquiries and admin responses.
 */
final class MessageModel extends BaseModel
{
    public function __construct(\PDO $pdo)
    {
        parent::__construct($pdo, 'messages', 'id');
    }

    public function isRecentDuplicate(int $userId, string $message, int $windowSeconds = 60): bool
    {
        $lastMsg = $this->gateway->customQuerySingle(
            'SELECT user_message, created_at FROM messages WHERE user_id = :uid ORDER BY id DESC LIMIT 1',
            ['uid' => $userId]
        );

        if (!$lastMsg) {
            return false;
        }

        return trim((string) ($lastMsg['user_message'] ?? '')) === $message
            && !empty($lastMsg['created_at'])
            && (time() - strtotime((string) $lastMsg['created_at'])) < $windowSeconds;
    }

    public function findByUserOrdered(int $userId): array
    {
        return $this->gateway->customQuery(
            'SELECT * FROM messages WHERE user_id = :uid ORDER BY id DESC',
            ['uid' => $userId]
        );
    }

    public function countUnreplied(): int
    {
        $row = $this->gateway->customQuerySingle(
            "SELECT COUNT(*) AS cnt FROM messages WHERE status IS NULL OR status != 'replied'"
        );

        return (int) ($row['cnt'] ?? 0);
    }

    public function findAllWithUsers(): array
    {
        return $this->gateway->customQuery(
            'SELECT m.*, u.name, u.email
             FROM messages m
             LEFT JOIN users u ON m.user_id = u.id
             ORDER BY m.id DESC'
        );
    }
}
