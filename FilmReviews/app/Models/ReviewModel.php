<?php
declare(strict_types=1);

namespace App\Models;

/**
 * Review domain model managing film review queries and aggregated cinema metrics.
 */
final class ReviewModel extends BaseModel
{
    public function __construct(\PDO $pdo)
    {
        parent::__construct($pdo, 'reviews', 'id');
    }

    /**
     * @return array<string, mixed>
     */
    public function getMetricsByFilm(int $filmId): array
    {
        $row = $this->gateway->customQuerySingle(
            'SELECT
                COUNT(*) AS total_reviews,
                COALESCE(ROUND(AVG(rating), 1), 0.0) AS avg_rating,
                COALESCE(ROUND((SUM(CASE WHEN recommend = 1 THEN 1 ELSE 0 END) / NULLIF(COUNT(*), 0)) * 100), 0) AS recommend_rate,
                SUM(CASE WHEN rating = 5 THEN 1 ELSE 0 END) AS stars_5,
                SUM(CASE WHEN rating = 4 THEN 1 ELSE 0 END) AS stars_4,
                SUM(CASE WHEN rating = 3 THEN 1 ELSE 0 END) AS stars_3,
                SUM(CASE WHEN rating = 2 THEN 1 ELSE 0 END) AS stars_2,
                SUM(CASE WHEN rating = 1 THEN 1 ELSE 0 END) AS stars_1,
                SUM(CASE WHEN is_verified_watcher = 1 THEN 1 ELSE 0 END) AS verified_count
             FROM reviews
             WHERE film_id = :fid',
            ['fid' => $filmId]
        );

        return $row ?: [
            'total_reviews'  => 0,
            'avg_rating'     => 0.0,
            'recommend_rate' => 0,
            'stars_5'        => 0,
            'stars_4'        => 0,
            'stars_3'        => 0,
            'stars_2'        => 0,
            'stars_1'        => 0,
            'verified_count' => 0,
        ];
    }

    public function findByFilmFiltered(int $filmId, string $filter, string $sort): array
    {
        $whereClause = 'WHERE reviews.film_id = :film_id';
        $params = ['film_id' => $filmId];

        if ($filter === 'verified') {
            $whereClause .= ' AND reviews.is_verified_watcher = 1';
        } elseif ($filter === 'recommended') {
            $whereClause .= ' AND reviews.recommend = 1';
        } elseif ($filter === 'not_recommended') {
            $whereClause .= ' AND reviews.recommend = 0';
        }

        $orderBy = match ($sort) {
            'newest'  => 'ORDER BY reviews.created_at DESC',
            'highest' => 'ORDER BY reviews.rating DESC, reviews.created_at DESC',
            'lowest'  => 'ORDER BY reviews.rating ASC, reviews.created_at DESC',
            default   => 'ORDER BY reviews.helpful_count DESC, reviews.created_at DESC',
        };

        $sql = "SELECT reviews.*,
                       COALESCE(users.name, 'Anonymous Reviewer') AS reviewer_name,
                       users.avatar AS reviewer_avatar,
                       users.role AS reviewer_role
                FROM reviews
                LEFT JOIN users ON reviews.user_id = users.id
                {$whereClause}
                {$orderBy}";

        return $this->gateway->customQuery($sql, $params);
    }

    public function findAllWithDetailsForAdmin(): array
    {
        return $this->gateway->customQuery(
            "SELECT r.id, r.user_id, r.rating, r.content, r.created_at,
                    COALESCE(u.name, 'Deleted Member') AS user_name,
                    COALESCE(u.role, 0) AS user_role,
                    COALESCE(f.title, 'Deleted Film') AS film_title
             FROM reviews r
             LEFT JOIN users u ON r.user_id = u.id
             LEFT JOIN films f ON r.film_id = f.id
             ORDER BY r.id DESC"
        );
    }

    /**
     * @return array<int, string>
     */
    public function getActiveTicketProofs(): array
    {
        try {
            $rows = $this->gateway->customQuery("SELECT ticket_proof FROM reviews WHERE ticket_proof IS NOT NULL AND ticket_proof != ''");
            return array_values(array_filter(array_map(static fn(array $r): string => (string) ($r['ticket_proof'] ?? ''), $rows)));
        } catch (\Throwable $e) {
            return [];
        }
    }
}
