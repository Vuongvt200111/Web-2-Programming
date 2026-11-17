<?php
declare(strict_types=1);

namespace App\Presenters;

/**
 * Film presentation logic (studio mapping, badge formatting).
 */
final class FilmPresenter
{
    public static function getStudioName(?string $categoryName): string
    {
        $cat = strtolower(trim($categoryName ?? ''));
        if (str_contains($cat, 'kamen rider') || str_contains($cat, 'super sentai') || str_contains($cat, 'metal hero')) {
            return 'Toei';
        }
        if (str_contains($cat, 'ultraman') || str_contains($cat, 'gridman')) {
            return 'Tsuburaya';
        }
        if (str_contains($cat, 'kaiju') || str_contains($cat, 'godzilla')) {
            return 'Toho';
        }
        if (str_contains($cat, 'garo')) {
            return 'Tohokushinsha';
        }

        return 'Studio';
    }
}
