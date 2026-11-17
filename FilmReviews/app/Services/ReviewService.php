<?php
declare(strict_types=1);

namespace App\Services;

use App\Models\ReviewModel;
use App\Models\ReviewVoteModel;

/**
 * Shared Review domain logic (input parsing/normalization and cascaded deletion with ticket proofs).
 */
final class ReviewService
{
    private ReviewModel $reviews;
    private ReviewVoteModel $votes;
    private MediaService $media;

    public function __construct(
        ReviewModel $reviews,
        ReviewVoteModel $votes,
        MediaService $media
    ) {
        $this->reviews = $reviews;
        $this->votes = $votes;
        $this->media = $media;
    }

    /**
     * Normalize and validate review submission or edit inputs.
     *
     * @return array{valid: bool, error: string, data?: array<string, mixed>}
     */
    public function parsePayload(array $post, array $files = [], bool $allowTicketUpload = false): array
    {
        $headline = substr(trim((string) ($post['headline'] ?? '')), 0, 150);
        $rating = max(1, min(5, (int) ($post['rating'] ?? 5)));
        $recommend = isset($post['recommend']) ? (int) $post['recommend'] : 1;
        $isSpoiler = isset($post['is_spoiler']) ? 1 : 0;
        $content = trim((string) ($post['content'] ?? ''));

        $tagsInput = $post['tags'] ?? [];
        $tagsString = '';
        if (is_array($tagsInput)) {
            $cleanTags = array_filter(array_map(static fn($item): string => trim((string) $item), $tagsInput));
            $tagsString = implode(', ', array_slice($cleanTags, 0, 5));
        } elseif (is_string($tagsInput)) {
            $tagsString = substr(trim($tagsInput), 0, 255);
        }

        $ticketProof = null;
        $isVerifiedWatcher = 0;

        if ($allowTicketUpload && isset($files['ticket_proof']) && ($files['ticket_proof']['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK) {
            $uploadRes = $this->media->uploadSecureImage($files['ticket_proof']);
            if (!$uploadRes['success']) {
                return [
                    'valid' => false,
                    'error' => 'Ticket image error: ' . ($uploadRes['error'] ?? 'Upload failed.'),
                ];
            }
            $ticketProof = $uploadRes['filename'];
            $isVerifiedWatcher = 1;
        }

        if ($content === '' || strlen($content) < 5) {
            if ($ticketProof !== null) {
                $this->media->deleteSafely($ticketProof);
            }
            return [
                'valid' => false,
                'error' => 'Review content must be at least 5 characters long!',
            ];
        }

        return [
            'valid' => true,
            'error' => '',
            'data'  => [
                'headline'            => $headline !== '' ? $headline : null,
                'rating'              => $rating,
                'recommend'           => $recommend,
                'is_spoiler'          => $isSpoiler,
                'tags'                => $tagsString !== '' ? $tagsString : null,
                'content'             => $content,
                'ticket_proof'        => $ticketProof,
                'is_verified_watcher' => $isVerifiedWatcher,
            ],
        ];
    }

    public function deleteWithAssets(int $reviewId): bool
    {
        $review = $this->reviews->findById($reviewId);
        if (!$review) {
            return false;
        }

        if (!empty($review['ticket_proof'])) {
            $this->media->deleteSafely((string) $review['ticket_proof']);
        }

        $this->votes->deleteByReviewId($reviewId);
        $this->reviews->delete($reviewId);

        return true;
    }
}
