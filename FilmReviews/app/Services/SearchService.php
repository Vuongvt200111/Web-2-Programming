<?php
declare(strict_types=1);

namespace App\Services;

use App\Core\Config;

/**
 * Multi-tier Intelligent Search, Typo Auto-Correction & Semantic Tokusatsu Expansion Service.
 */
final class SearchService
{
    /** @var array<string, mixed> */
    private array $dictionary;

    public function __construct(Config $config)
    {
        $this->dictionary = $config->section('search_dictionary');
    }

    public function removeVietnameseAccents(string $str): string
    {
        $accents = [
            'a' => ['à','á','ạ','ả','ã','â','ầ','ấ','ậ','ẩ','ẫ','ă','ằ','ắ','ặ','ẳ','ẵ'],
            'e' => ['è','é','ẹ','ẻ','ẽ','ê','ề','ế','ệ','ể','ễ'],
            'i' => ['ì','í','ị','ỉ','ĩ'],
            'o' => ['ò','ó','ọ','ỏ','õ','ô','ồ','ố','ộ','ổ','ỗ','ơ','ờ','ớ','ợ','ở','ỡ'],
            'u' => ['ù','ú','ụ','ủ','ũ','ư','ừ','ứ','ự','ử','ữ'],
            'y' => ['ỳ','ý','ỵ','ỷ','ỹ'],
            'd' => ['đ'],
            'A' => ['À','Á','Ạ','Ả','Ã','Â','Ầ','Ấ','Ậ','Ẩ','Ẫ','Ă','Ằ','Ắ','Ặ','Ẳ','Ẵ'],
            'E' => ['È','É','Ẹ','Ẻ','Ẽ','Ê','Ề','Ế','Ệ','Ể','Ễ'],
            'I' => ['Ì','Í','Ị','Ỉ','Ĩ'],
            'O' => ['Ò','Ó','Ọ','Ỏ','Õ','Ô','Ồ','Ố','Ộ','Ổ','Ỗ','Ơ','Ờ','Ớ','Ợ','Ở','Ỡ'],
            'U' => ['Ù','Ú','Ụ','Ủ','Ũ','Ư','Ừ','Ứ','Ự','Ử','Ữ'],
            'Y' => ['Ỳ','Ý','Ỵ','Ỷ','Ỹ'],
            'D' => ['Đ'],
        ];

        foreach ($accents as $nonAccent => $accentChars) {
            $str = str_replace($accentChars, $nonAccent, $str);
        }

        return $str;
    }

    /**
     * @return array{
     *   original: string,
     *   resolved: string,
     *   unaccented: string,
     *   corrected_from: ?string,
     *   matched_studio: ?string,
     *   studio_categories: array<int, string>,
     *   semantic_categories: array<int, string>,
     *   semantic_title_keywords: array<int, string>,
     *   tokens: array<int, string>
     * }
     */
    public function resolveSearchTerm(string $search): array
    {
        $clean = mb_substr(trim($search), 0, 50, 'UTF-8');
        $clean = (string) preg_replace('/[\x00-\x1F\x7F]/u', '', $clean);
        $cleanLower = mb_strtolower($clean, 'UTF-8');
        $unaccented = strtolower($this->removeVietnameseAccents($cleanLower));

        $phraseTypoMap = $this->dictionary['phrase_typo_map'] ?? [];
        $wordTypoMap = $this->dictionary['word_typo_map'] ?? [];
        $knownTokens = $this->dictionary['known_tokens'] ?? [];
        $studioCategoryMap = $this->dictionary['studio_category_map'] ?? [];
        $semanticCategoryMap = $this->dictionary['semantic_category_map'] ?? [];
        $semanticTitleMap = $this->dictionary['semantic_title_map'] ?? [];
        $stopWords = $this->dictionary['stop_words'] ?? [];

        $resolved = $cleanLower;
        $correctedFrom = null;

        if (isset($phraseTypoMap[$cleanLower])) {
            $resolved = $phraseTypoMap[$cleanLower];
            if ($resolved !== $cleanLower) {
                $correctedFrom = $clean;
            }
        } elseif (isset($phraseTypoMap[$unaccented])) {
            $resolved = $phraseTypoMap[$unaccented];
            $correctedFrom = $clean;
        } else {
            $words = preg_split('/\s+/u', $cleanLower, -1, PREG_SPLIT_NO_EMPTY) ?: [];
            $unaccWords = preg_split('/\s+/u', $unaccented, -1, PREG_SPLIT_NO_EMPTY) ?: [];
            $hasReplaced = false;
            $newWords = [];

            foreach ($unaccWords as $idx => $w) {
                if (isset($wordTypoMap[$w])) {
                    $newWords[] = $wordTypoMap[$w];
                    $hasReplaced = true;
                } else {
                    $newWords[] = $words[$idx] ?? $w;
                }
            }

            if ($hasReplaced) {
                $candidate = implode(' ', $newWords);
                $resolved = $phraseTypoMap[$candidate] ?? $candidate;
                $correctedFrom = $clean;
            } elseif (mb_strlen($unaccented, 'UTF-8') <= 20) {
                foreach ($knownTokens as $known) {
                    $knownUnacc = $this->removeVietnameseAccents(mb_strtolower((string) $known, 'UTF-8'));
                    if (levenshtein($unaccented, $knownUnacc) <= 1) {
                        $resolved = (string) $known;
                        $correctedFrom = $clean;
                        break;
                    }
                }
            }
        }

        $matchedStudio = null;
        $studioCategories = [];
        foreach ($studioCategoryMap as $studioKey => $categories) {
            $studioPattern = '/(?<![\p{L}\p{N}])' . preg_quote((string) $studioKey, '/') . '(?![\p{L}\p{N}])/iu';
            if ($resolved === $studioKey || preg_match($studioPattern, $resolved) || preg_match($studioPattern, $unaccented)) {
                $matchedStudio = ucfirst((string) $studioKey);
                $studioCategories = $categories;
                break;
            }
        }

        $semanticCategories = [];
        $resolvedUnacc = $this->removeVietnameseAccents(mb_strtolower($resolved, 'UTF-8'));
        foreach ($semanticCategoryMap as $concept => $cats) {
            $conceptUnacc = $this->removeVietnameseAccents((string) $concept);
            if ($resolved === $concept || $resolvedUnacc === $conceptUnacc) {
                $semanticCategories = array_values(array_unique(array_merge($semanticCategories, $cats)));
            }
        }

        $semanticTitleKeywords = [];
        foreach ($semanticTitleMap as $concept => $titleKws) {
            $conceptUnacc = $this->removeVietnameseAccents((string) $concept);
            if ($resolved === $concept || $resolvedUnacc === $conceptUnacc) {
                $semanticTitleKeywords = $titleKws;
                break;
            }
            $patternConcept = '/(?<![\p{L}\p{N}])' . preg_quote((string) $concept, '/') . '(?![\p{L}\p{N}])/iu';
            $patternUnacc = '/(?<![\p{L}\p{N}])' . preg_quote($conceptUnacc, '/') . '(?![\p{L}\p{N}])/iu';
            if (preg_match($patternConcept, $resolved) || preg_match($patternUnacc, $resolvedUnacc)) {
                $semanticTitleKeywords = array_values(array_unique(array_merge($semanticTitleKeywords, $titleKws)));
            }
        }

        $rawTokens = preg_split('/\s+/u', $resolved, 4, PREG_SPLIT_NO_EMPTY) ?: [];
        $tokens = [];
        foreach ($rawTokens as $t) {
            $cleanT = mb_substr($t, 0, 20, 'UTF-8');
            $tLower = mb_strtolower($cleanT, 'UTF-8');
            if (mb_strlen($cleanT, 'UTF-8') >= 1 && !in_array($tLower, $stopWords, true)) {
                $tokens[] = $cleanT;
            }
        }

        return [
            'original'                => $search,
            'resolved'                => $resolved,
            'unaccented'              => $unaccented,
            'corrected_from'          => $correctedFrom,
            'matched_studio'          => $matchedStudio,
            'studio_categories'       => $studioCategories,
            'semantic_categories'     => $semanticCategories,
            'semantic_title_keywords' => $semanticTitleKeywords,
            'tokens'                  => $tokens,
        ];
    }

    public function buildUnicodeWordBoundaryRegex(string $term): string
    {
        $lower = mb_strtolower(trim($term), 'UTF-8');
        return '(*UTF8)(*UCP)(?<![\p{L}\p{N}])' . preg_quote($lower, '/') . '(?![\p{L}\p{N}])';
    }
}
