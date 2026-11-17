<?php
declare(strict_types=1);

namespace App\Presenters;

use App\Services\TabSession;

/**
 * Builds canonical catalog filter URLs while preserving active tab session parameters.
 */
final class CatalogFilterPresenter
{
    /**
     * @param array<string, mixed> $state
     * @param array<string, mixed> $overrides
     */
    public static function buildFilterUrl(string $baseUrl, array $state, array $overrides = []): string
    {
        $params = [];
        $cat = (string) ($overrides['category'] ?? $state['current_category'] ?? 'all');
        $query = (string) ($overrides['search'] ?? $state['search'] ?? '');
        $anniv = array_key_exists('anniversary', $overrides)
            ? (int) $overrides['anniversary']
            : (!empty($state['anniversary']) ? 1 : 0);
        $mine = array_key_exists('mine', $overrides)
            ? (int) $overrides['mine']
            : (int) ($state['mine'] ?? 0);
        $sort = (string) ($overrides['sort'] ?? $state['sort'] ?? 'newest');

        if ($cat !== 'all' && $cat !== '') {
            $params['category'] = $cat;
        }
        if ($query !== '') {
            $params['search'] = $query;
        }
        if ($anniv === 1) {
            $params['anniversary'] = 1;
        }
        if ($mine === 1) {
            $params['mine'] = 1;
        }
        if ($sort !== '' && $sort !== 'newest') {
            $params['sort'] = $sort;
        }
        if (array_key_exists('page', $overrides) && (int) $overrides['page'] > 1) {
            $params['page'] = (int) $overrides['page'];
        }

        $url = $baseUrl . 'index.php?route=film/list';
        if ($params !== []) {
            $url .= '&' . http_build_query($params);
        }

        return TabSession::decorateUrl($url);
    }

    /**
     * Build pagination metadata & preserved filter links for the catalog grid.
     *
     * @param array<string, mixed> $state
     * @return array<string, mixed>
     */
    public static function buildPaginationData(
        string $baseUrl,
        array $state,
        int $currentPage,
        int $totalPages,
        int $totalResults,
        int $perPage
    ): array {
        $safeCurrent = max(1, min($currentPage, max(1, $totalPages)));
        $safeTotalPages = max(1, $totalPages);

        $pageNumbers = [];
        if ($safeTotalPages <= 7) {
            $pageNumbers = range(1, $safeTotalPages);
        } else {
            $pageNumbers[] = 1;
            $windowStart = max(2, $safeCurrent - 1);
            $windowEnd = min($safeTotalPages - 1, $safeCurrent + 1);

            if ($windowStart > 2) {
                $pageNumbers[] = null;
            }
            for ($p = $windowStart; $p <= $windowEnd; $p++) {
                $pageNumbers[] = $p;
            }
            if ($windowEnd < $safeTotalPages - 1) {
                $pageNumbers[] = null;
            }
            $pageNumbers[] = $safeTotalPages;
        }

        $items = [];
        foreach ($pageNumbers as $entry) {
            if ($entry === null) {
                $items[] = [
                    'type'   => 'ellipsis',
                    'page'   => null,
                    'active' => false,
                    'url'    => '',
                ];
            } else {
                $items[] = [
                    'type'   => 'page',
                    'page'   => $entry,
                    'active' => $entry === $safeCurrent,
                    'url'    => self::buildFilterUrl($baseUrl, $state, ['page' => $entry]),
                ];
            }
        }

        $startItem = $totalResults > 0 ? (($safeCurrent - 1) * $perPage) + 1 : 0;
        $endItem = $totalResults > 0 ? min($totalResults, $safeCurrent * $perPage) : 0;

        return [
            'currentPage'  => $safeCurrent,
            'totalPages'   => $safeTotalPages,
            'totalResults' => $totalResults,
            'perPage'      => $perPage,
            'startItem'    => $startItem,
            'endItem'      => $endItem,
            'hasPrevious'  => $safeCurrent > 1,
            'previousUrl'  => $safeCurrent > 1 ? self::buildFilterUrl($baseUrl, $state, ['page' => $safeCurrent - 1]) : '',
            'hasNext'      => $safeCurrent < $safeTotalPages,
            'nextUrl'      => $safeCurrent < $safeTotalPages ? self::buildFilterUrl($baseUrl, $state, ['page' => $safeCurrent + 1]) : '',
            'items'        => $items,
        ];
    }
}
