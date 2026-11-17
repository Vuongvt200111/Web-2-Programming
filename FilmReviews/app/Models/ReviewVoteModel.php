<?php
declare(strict_types=1);

namespace App\Models;

/**
 * Review Vote domain model encapsulating helpful vote toggles and counts.
 */
final class ReviewVoteModel extends BaseModel
{
    public function __construct(\PDO $pdo)
    {
        parent::__construct($pdo, 'review_votes', 'id');
    }

    /**
     * @return array<int, int>
     */
    public function findUserVotedReviewIds(int $userId): array
    {
        $rows = $this->gateway->customQuery(
            'SELECT review_id FROM review_votes WHERE user_id = :uid AND is_helpful = 1',
            ['uid' => $userId]
        );

        return array_map(static fn(array $row): int => (int) $row['review_id'], $rows);
    }

    /**
     * Toggle helpful vote for user on review. Returns true if voted, false if removed.
     */
    public function toggleHelpful(int $userId, int $reviewId): bool
    {
        $existing = $this->gateway->customQuerySingle(
            'SELECT id, is_helpful FROM review_votes WHERE user_id = :uid AND review_id = :rid',
            ['uid' => $userId, 'rid' => $reviewId]
        );

        if ($existing) {
            $this->delete((int) $existing['id']);
            return false;
        }

        $this->insert([
            'user_id'    => $userId,
            'review_id'  => $reviewId,
            'is_helpful' => 1,
        ]);

        return true;
    }

    public function countHelpful(int $reviewId): int
    {
        $row = $this->gateway->customQuerySingle(
            'SELECT COUNT(*) AS cnt FROM review_votes WHERE review_id = :rid AND is_helpful = 1',
            ['rid' => $reviewId]
        );

        return (int) ($row['cnt'] ?? 0);
    }

    public function deleteByReviewId(int $reviewId): void
    {
        $this->deleteWhere('review_id', $reviewId);
    }
}
