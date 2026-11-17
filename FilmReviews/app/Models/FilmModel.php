<?php
declare(strict_types=1);

namespace App\Models;

use App\Services\SearchService;

/**
 * Film domain model encapsulating catalog faceted search, stats aggregation, and uploader queries.
 */
final class FilmModel extends BaseModel
{
    public const SORT_WHITELIST = [
        'newest'           => 'films.created_at DESC',
        'oldest'           => 'films.created_at ASC',
        'rating_desc'      => 'avg_score DESC, total_reviews DESC, films.id DESC',
        'recommended_desc' => 'recommend_rate DESC, total_reviews DESC, films.id DESC',
        'title_asc'        => 'films.title ASC',
        'title_desc'       => 'films.title DESC',
    ];

    public function __construct(\PDO $pdo)
    {
        parent::__construct($pdo, 'films', 'id');
    }

    /**
     * Execute two-tier Unicode word-boundary faceted catalog search.
     *
     * @param array<string, mixed> $filters
     * @param array<string, mixed>|null $searchResolution
     * @return array<int, array<string, mixed>>
     */
    public function searchCatalog(
        array $filters,
        ?array $searchResolution,
        SearchService $searchService
    ): array {
        $result = $this->searchCatalogPaginated($filters, $searchResolution, $searchService, 1, 10000);
        return $result['films'];
    }

    /**
     * Execute paginated two-tier faceted catalog search using SQL LIMIT ... OFFSET ...
     *
     * @param array<string, mixed> $filters
     * @param array<string, mixed>|null $searchResolution
     * @return array{films: array<int, array<string, mixed>>, total: int, page: int, perPage: int, totalPages: int}
     */
    public function searchCatalogPaginated(
        array $filters,
        ?array $searchResolution,
        SearchService $searchService,
        int $page = 1,
        int $perPage = 12
    ): array {
        $ctx = $this->buildCatalogQueryContext($filters, $searchResolution, $searchService);
        $activeWhere = implode(' AND ', $ctx['conditions']);
        $activeParams = $ctx['params'];

        $total = $this->countCatalogMatches($activeWhere, $activeParams);

        if ($total === 0 && $ctx['search'] !== '' && $ctx['wordBoundaryRegex'] !== null) {
            $fallbackConditions = $ctx['baseConditions'];
            $fallbackParams = $ctx['baseParams'];

            if (!empty($searchResolution['tokens']) && count($searchResolution['tokens']) > 1) {
                $descTokenClauses = [];
                foreach ($searchResolution['tokens'] as $idx => $tok) {
                    $descTokenClauses[] = "LOWER(films.description) REGEXP :desc_tok_rx_{$idx}";
                    $fallbackParams["desc_tok_rx_{$idx}"] = $searchService->buildUnicodeWordBoundaryRegex((string) $tok);
                }
                $fallbackConditions[] = '(' . implode(' AND ', $descTokenClauses) . ')';
            } else {
                $fallbackConditions[] = 'LOWER(films.description) REGEXP :search_desc_rx';
                $fallbackParams['search_desc_rx'] = $ctx['wordBoundaryRegex'];
            }

            $activeWhere = implode(' AND ', $fallbackConditions);
            $activeParams = $fallbackParams;
            $total = $this->countCatalogMatches($activeWhere, $activeParams);
        }

        $safePerPage = max(1, $perPage);
        $totalPages = max(1, (int) ceil($total / $safePerPage));
        $currentPage = max(1, min($page, $totalPages));
        $offset = ($currentPage - 1) * $safePerPage;

        if ($total === 0) {
            return [
                'films'      => [],
                'total'      => 0,
                'page'       => 1,
                'perPage'    => $safePerPage,
                'totalPages' => 1,
            ];
        }

        $selectSql = "SELECT films.*,
                       COALESCE(users.name, 'Tokusatsu Universe') AS uploader_name,
                       COALESCE(users.role, 0) AS uploader_role,
                       COALESCE(category.category_name, 'General') AS category_name,
                       COALESCE(stats.avg_score, 0.0) AS avg_score,
                       COALESCE(stats.total_reviews, 0) AS total_reviews,
                       COALESCE(stats.recommend_rate, 0) AS recommend_rate,
                       COALESCE(stats.verified_count, 0) AS verified_count
                FROM films
                LEFT JOIN users ON films.user_id = users.id
                LEFT JOIN category ON films.category_id = category.id
                LEFT JOIN (
                    SELECT film_id,
                           ROUND(AVG(rating), 1) AS avg_score,
                           COUNT(*) AS total_reviews,
                           ROUND((SUM(CASE WHEN recommend = 1 THEN 1 ELSE 0 END) / COUNT(*)) * 100) AS recommend_rate,
                           SUM(CASE WHEN is_verified_watcher = 1 THEN 1 ELSE 0 END) AS verified_count
                    FROM reviews
                    GROUP BY film_id
                ) stats ON films.id = stats.film_id
                WHERE {$activeWhere}
                ORDER BY {$ctx['orderByClause']}
                LIMIT :limit OFFSET :offset";

        $stmt = $this->gateway->getPdo()->prepare($selectSql);
        foreach ($activeParams as $paramKey => $paramVal) {
            $stmt->bindValue(':' . ltrim((string) $paramKey, ':'), $paramVal, is_int($paramVal) ? \PDO::PARAM_INT : \PDO::PARAM_STR);
        }
        $stmt->bindValue(':limit', $safePerPage, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
        $stmt->execute();

        return [
            'films'      => $stmt->fetchAll(),
            'total'      => $total,
            'page'       => $currentPage,
            'perPage'    => $safePerPage,
            'totalPages' => $totalPages,
        ];
    }

    /**
     * @param array<string, mixed> $filters
     * @param array<string, mixed>|null $searchResolution
     * @return array<string, mixed>
     */
    private function buildCatalogQueryContext(
        array $filters,
        ?array $searchResolution,
        SearchService $searchService
    ): array {
        $category = (string) ($filters['category'] ?? 'all');
        $search = (string) ($filters['search'] ?? '');
        $anniversary = (int) ($filters['anniversary'] ?? 0);
        $mineUserId = isset($filters['mine_user_id']) ? (int) $filters['mine_user_id'] : 0;
        $sortKey = (string) ($filters['sort'] ?? 'newest');
        $orderByClause = self::SORT_WHITELIST[$sortKey] ?? self::SORT_WHITELIST['newest'];

        $conditions = ["films.status = 'approved'"];
        $params = [];

        if ($mineUserId > 0) {
            $conditions[] = 'films.user_id = :mine_uid';
            $params['mine_uid'] = $mineUserId;
        }

        if ($category !== '' && $category !== 'all') {
            $conditions[] = 'category.category_name = :category';
            $params['category'] = $category;
        }

        if ($anniversary === 1) {
            $conditions[] = 'films.is_anniversary = 1';
        }

        $baseConditions = $conditions;
        $baseParams = $params;
        $wordBoundaryRegex = null;

        if ($search !== '' && $searchResolution !== null) {
            $wordBoundaryRegex = $searchService->buildUnicodeWordBoundaryRegex((string) $searchResolution['resolved']);
            $subConditions = [
                'LOWER(films.title) REGEXP :search_title_rx',
                'LOWER(category.category_name) REGEXP :search_cat_rx',
            ];
            $params['search_title_rx'] = $wordBoundaryRegex;
            $params['search_cat_rx'] = $wordBoundaryRegex;

            if (!empty($searchResolution['unaccented']) && $searchResolution['unaccented'] !== $searchResolution['resolved']) {
                $subConditions[] = 'LOWER(films.title) REGEXP :unacc_title_rx';
                $params['unacc_title_rx'] = $searchService->buildUnicodeWordBoundaryRegex((string) $searchResolution['unaccented']);
            }

            if (!empty($searchResolution['corrected_from']) && $searchResolution['corrected_from'] !== $searchResolution['resolved']) {
                $subConditions[] = 'LOWER(films.title) REGEXP :orig_title_rx';
                $params['orig_title_rx'] = $searchService->buildUnicodeWordBoundaryRegex((string) $searchResolution['corrected_from']);
            }

            if (!empty($searchResolution['studio_categories'])) {
                $studioPlaceholders = [];
                foreach ($searchResolution['studio_categories'] as $idx => $sCat) {
                    $ph = ":studio_cat_{$idx}";
                    $studioPlaceholders[] = $ph;
                    $params["studio_cat_{$idx}"] = $sCat;
                }
                $subConditions[] = 'category.category_name IN (' . implode(',', $studioPlaceholders) . ')';
            }

            if (!empty($searchResolution['semantic_categories'])) {
                $semPlaceholders = [];
                foreach ($searchResolution['semantic_categories'] as $idx => $semCat) {
                    $ph = ":sem_cat_{$idx}";
                    $semPlaceholders[] = $ph;
                    $params["sem_cat_{$idx}"] = $semCat;
                }
                $subConditions[] = 'category.category_name IN (' . implode(',', $semPlaceholders) . ')';
            }

            if (!empty($searchResolution['semantic_title_keywords'])) {
                foreach ($searchResolution['semantic_title_keywords'] as $idx => $tkw) {
                    $subConditions[] = "LOWER(films.title) REGEXP :sem_title_rx_{$idx}";
                    $params["sem_title_rx_{$idx}"] = $searchService->buildUnicodeWordBoundaryRegex((string) $tkw);
                }
            }

            if (count($searchResolution['tokens']) > 1) {
                $tokenAndClauses = [];
                foreach ($searchResolution['tokens'] as $idx => $tok) {
                    $tokenAndClauses[] = "LOWER(films.title) REGEXP :tok_rx_{$idx}";
                    $params["tok_rx_{$idx}"] = $searchService->buildUnicodeWordBoundaryRegex((string) $tok);
                }
                $subConditions[] = '(' . implode(' AND ', $tokenAndClauses) . ')';
            }

            $conditions[] = '(' . implode(' OR ', $subConditions) . ')';
        }

        return [
            'conditions'        => $conditions,
            'params'            => $params,
            'baseConditions'    => $baseConditions,
            'baseParams'        => $baseParams,
            'wordBoundaryRegex' => $wordBoundaryRegex,
            'search'            => $search,
            'orderByClause'     => $orderByClause,
        ];
    }

    /**
     * @param array<string, mixed> $params
     */
    private function countCatalogMatches(string $whereClause, array $params): int
    {
        $row = $this->gateway->customQuerySingle(
            "SELECT COUNT(*) AS cnt
             FROM films
             LEFT JOIN users ON films.user_id = users.id
             LEFT JOIN category ON films.category_id = category.id
             WHERE {$whereClause}",
            $params
        );

        return (int) ($row['cnt'] ?? 0);
    }

    public function findByUserWithStats(int $userId): array
    {
        return $this->gateway->customQuery(
            "SELECT f.*,
                    COALESCE(c.category_name, 'General') AS category_name,
                    COALESCE(stats.avg_score, 0.0) AS avg_score,
                    COALESCE(stats.total_reviews, 0) AS total_reviews
             FROM films f
             LEFT JOIN category c ON f.category_id = c.id
             LEFT JOIN (
                 SELECT film_id,
                        ROUND(AVG(rating), 1) AS avg_score,
                        COUNT(*) AS total_reviews
                 FROM reviews
                 GROUP BY film_id
             ) stats ON f.id = stats.film_id
             WHERE f.user_id = :uid
             ORDER BY (f.status = 'pending') DESC, f.created_at DESC, f.id DESC",
            ['uid' => $userId]
        );
    }

    public function findWithUploaderAndCategory(int $filmId): array|false
    {
        return $this->gateway->customQuerySingle(
            "SELECT films.*,
                    COALESCE(users.name, 'Tokusatsu Universe') AS uploader_name,
                    COALESCE(category.category_name, 'General') AS category_name
             FROM films
             LEFT JOIN users ON films.user_id = users.id
             LEFT JOIN category ON films.category_id = category.id
             WHERE films.id = :id",
            ['id' => $filmId]
        );
    }

    public function findPendingWithUploader(): array
    {
        return $this->gateway->customQuery(
            "SELECT films.*, users.name AS uploader_name
             FROM films
             JOIN users ON films.user_id = users.id
             WHERE films.status = 'pending'
             ORDER BY films.created_at ASC"
        );
    }

    public function isRecentDuplicateSubmission(
        int $userId,
        string $title,
        string $description,
        int $windowSeconds = 60
    ): bool {
        $lastFilm = $this->gateway->customQuerySingle(
            'SELECT title, description, created_at FROM films WHERE user_id = :uid ORDER BY id DESC LIMIT 1',
            ['uid' => $userId]
        );

        if (!$lastFilm) {
            return false;
        }

        return trim((string) ($lastFilm['title'] ?? '')) === $title
            && trim((string) ($lastFilm['description'] ?? '')) === $description
            && !empty($lastFilm['created_at'])
            && (time() - strtotime((string) $lastFilm['created_at'])) < $windowSeconds;
    }

    /**
     * @return array<int, string>
     */
    public function getActiveMediaFilenames(): array
    {
        $files = [];
        $posters = $this->gateway->customQuery("SELECT images FROM films WHERE images IS NOT NULL AND images != ''");
        foreach ($posters as $row) {
            if (!empty($row['images'])) {
                $files[] = (string) $row['images'];
            }
        }

        try {
            $backdrops = $this->gateway->customQuery("SELECT backdrop_image FROM films WHERE backdrop_image IS NOT NULL AND backdrop_image != ''");
            foreach ($backdrops as $row) {
                if (!empty($row['backdrop_image'])) {
                    $files[] = (string) $row['backdrop_image'];
                }
            }
        } catch (\Throwable $e) {
            // Optional backdrop column may not exist on minimal schemas.
        }

        return $files;
    }
}
