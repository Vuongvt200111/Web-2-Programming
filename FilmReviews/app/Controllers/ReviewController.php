<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\AuthContext;
use App\Core\AuthMiddleware;
use App\Core\Config;
use App\Core\Flash;
use App\Core\Response;
use App\Models\FilmModel;
use App\Models\ReviewModel;
use App\Models\ReviewVoteModel;
use App\Services\AuditLogger;
use App\Services\MediaService;
use App\Services\ReviewService;
use App\Services\SecurityService;

/**
 * Coordinates community film reviews, voting, and reviewer review lifecycle.
 */
final class ReviewController extends BaseController
{
    private ReviewModel $reviews;
    private FilmModel $films;
    private ReviewVoteModel $votes;
    private MediaService $media;
    private ReviewService $reviewService;

    public function __construct(
        Config $config,
        Response $response,
        Flash $flash,
        SecurityService $security,
        AuthMiddleware $auth,
        AuditLogger $auditLogger,
        ReviewModel $reviews,
        FilmModel $films,
        ReviewVoteModel $votes,
        MediaService $media,
        ReviewService $reviewService
    ) {
        parent::__construct($config, $response, $flash, $security, $auth, $auditLogger);
        $this->reviews = $reviews;
        $this->films = $films;
        $this->votes = $votes;
        $this->media = $media;
        $this->reviewService = $reviewService;
    }

    public function show(): array
    {
        $filmId = (int) ($_GET['id'] ?? 0);
        if ($filmId <= 0) {
            $this->redirect('index.php?route=film/list');
        }

        $film = $this->films->findWithUploaderAndCategory($filmId);
        if (!$film) {
            $this->redirect('index.php?route=film/list');
        }

        $error = (string) $this->flash->pull('review_error', '');
        $success = (string) $this->flash->pull('review_success', '');

        $metrics = $this->reviews->getMetricsByFilm($filmId);
        $sort = (string) ($_GET['sort'] ?? 'helpful');
        $filter = (string) ($_GET['filter'] ?? 'all');
        $reviews = $this->reviews->findByFilmFiltered($filmId, $filter, $sort);

        $authContext = $this->auth->current();
        $userVotedIds = $authContext->isAuthenticated()
            ? $this->votes->findUserVotedReviewIds((int) $authContext->userId())
            : [];

        foreach ($reviews as &$rev) {
            $revId = (int) ($rev['id'] ?? 0);
            $rev['reviewer_avatar_meta'] = $this->media->resolveAvatar(
                $rev['reviewer_avatar'] ?? null,
                (string) ($rev['reviewer_name'] ?? 'U'),
                $this->baseUrl()
            );
            $rev['created_at_formatted'] = !empty($rev['created_at'])
                ? date('d/m/Y H:i', strtotime((string) $rev['created_at']))
                : '';
            $rev['is_rev_by_boss'] = AuthContext::isBossRecord([
                'id'   => (int) ($rev['user_id'] ?? 0),
                'role' => (int) ($rev['reviewer_role'] ?? 0),
            ]);
            $rev['is_voted'] = in_array($revId, $userVotedIds, true);
            $rev['tags_list'] = !empty($rev['tags'])
                ? array_values(array_filter(array_map('trim', explode(',', (string) $rev['tags']))))
                : [];
            $rev['ticket_proof_url'] = !empty($rev['ticket_proof'])
                ? $this->baseUrl() . 'uploads/' . basename((string) $rev['ticket_proof'])
                : null;
        }
        unset($rev);

        $film['poster_url'] = $this->media->resolvePoster($film['images'] ?? null, $this->baseUrl());
        $film['created_at_formatted'] = !empty($film['created_at'])
            ? date('d/m/Y', strtotime((string) $film['created_at']))
            : 'N/A';

        return $this->view(
            'films/review',
            'layouts/catalog',
            'Rate and review films - ' . ($film['title'] ?? ''),
            [
                'film'         => $film,
                'reviews'      => $reviews,
                'metrics'      => $metrics,
                'sort'         => $sort,
                'filter'       => $filter,
                'userVotedIds' => $userVotedIds,
                'error'        => $error,
                'success'      => $success,
            ]
        );
    }

    public function add(): never
    {
        if (!$this->isPost() || !isset($_POST['submit_review'])) {
            $this->redirect('index.php?route=film/list');
        }

        $filmId = (int) ($_POST['film_id'] ?? 0);
        $this->security->validateCsrfRequest($this->baseUrl() . 'index.php?route=review/view&id=' . $filmId);

        $auth = $this->auth->current();
        if (!$auth->isAuthenticated()) {
            $this->flash->set('review_error', 'You must be logged in to write a review!');
            $this->redirect('index.php?route=review/view&id=' . $filmId);
        }

        $parsed = $this->reviewService->parsePayload($_POST, $_FILES, true);
        if (!$parsed['valid']) {
            $this->flash->set('review_error', $parsed['error']);
            $this->redirect('index.php?route=review/view&id=' . $filmId);
        }

        $data = $parsed['data'];
        try {
            $newRevId = $this->reviews->insert([
                'film_id'             => $filmId,
                'user_id'             => $auth->userId(),
                'headline'            => $data['headline'],
                'rating'              => $data['rating'],
                'recommend'           => $data['recommend'],
                'is_spoiler'          => $data['is_spoiler'],
                'tags'                => $data['tags'],
                'ticket_proof'        => $data['ticket_proof'],
                'is_verified_watcher' => $data['is_verified_watcher'],
                'helpful_count'       => 0,
                'content'             => $data['content'],
            ]);

            $this->auditLogger->log(
                'SUBMIT_REVIEW',
                "Reviewer posted a {$data['rating']}-star review (ID: #{$newRevId}) on film ID #{$filmId}"
            );
            $this->flash->set('review_success', 'Thank you! Your cinematic review has been posted successfully.');
        } catch (\Throwable $e) {
            $this->flash->set('review_error', 'An error occurred, unable to submit review: ' . $e->getMessage());
        }

        $this->redirect('index.php?route=review/view&id=' . $filmId);
    }

    public function voteHelpful(): never
    {
        $this->auth->requirePost('Method Not Allowed');

        $filmId = (int) ($_POST['film_id'] ?? 0);
        $reviewId = (int) ($_POST['review_id'] ?? 0);
        $this->security->validateCsrfRequest($this->baseUrl() . 'index.php?route=review/view&id=' . $filmId);

        $auth = $this->auth->current();
        if (!$auth->isAuthenticated()) {
            $this->flash->set('review_error', 'Please log in to vote on reviews!');
            $this->redirect('index.php?route=review/view&id=' . $filmId);
        }

        $review = $this->reviews->findById($reviewId);
        if (!$review) {
            $this->flash->set('review_error', 'Review not found.');
            $this->redirect('index.php?route=review/view&id=' . $filmId);
        }

        if ((int) $review['user_id'] === $auth->userId()) {
            $this->flash->set('review_error', 'You cannot vote for your own review!');
            $this->redirect('index.php?route=review/view&id=' . $filmId);
        }

        $added = $this->votes->toggleHelpful((int) $auth->userId(), $reviewId);
        $this->flash->set('review_success', $added ? 'Marked as helpful!' : 'Helpful vote removed.');

        $totalHelpful = $this->votes->countHelpful($reviewId);
        $this->reviews->update([
            'id'            => $reviewId,
            'helpful_count' => $totalHelpful,
        ]);

        $this->redirect('index.php?route=review/view&id=' . $filmId . '#rev-' . $reviewId);
    }

    public function edit(): array
    {
        $auth = $this->auth->requireAuthenticated('login');

        $reviewId = (int) ($_GET['id'] ?? $_POST['review_id'] ?? 0);
        $review = $this->reviews->findById($reviewId);

        if (!$review || (int) $review['user_id'] !== $auth->userId()) {
            $this->redirect('index.php?route=film/list');
        }

        $filmId = (int) $review['film_id'];
        $film = $this->films->findById($filmId);
        $error = '';

        if ($this->isPost() && isset($_POST['update_review'])) {
            $this->security->validateCsrfRequest($this->baseUrl() . 'index.php?route=review/edit&id=' . $reviewId);

            $parsed = $this->reviewService->parsePayload($_POST, [], false);
            if (!$parsed['valid']) {
                $error = 'Please enter review content with at least 5 characters.';
            } else {
                $data = $parsed['data'];
                $this->reviews->update([
                    'id'         => $reviewId,
                    'headline'   => $data['headline'],
                    'rating'     => $data['rating'],
                    'recommend'  => $data['recommend'],
                    'is_spoiler' => $data['is_spoiler'],
                    'tags'       => $data['tags'],
                    'content'    => $data['content'],
                ]);
                $this->auditLogger->log(
                    'EDIT_REVIEW',
                    "Reviewer updated review ID #{$reviewId} on film ID #{$filmId}"
                );
                $this->flash->set('review_success', 'Review updated successfully!');
                $this->redirect('index.php?route=review/view&id=' . $filmId);
            }
        }

        $filmData = is_array($film) ? $film : [];
        $filmData['poster_url'] = $this->media->resolvePoster($filmData['images'] ?? null, $this->baseUrl());

        return $this->view('films/user_edit_review', 'layouts/catalog', 'Edit Review', [
            'review'  => $review,
            'film'    => $filmData,
            'film_id' => $filmId,
            'error'   => $error,
        ]);
    }

    public function delete(): never
    {
        $auth = $this->auth->requireAuthenticated('login');
        $this->auth->requirePost('Method Not Allowed. Deletion requires POST request.');

        $reviewId = (int) ($_POST['id'] ?? 0);
        $filmId = (int) ($_POST['film_id'] ?? 0);
        $this->security->validateCsrfRequest($this->baseUrl() . 'index.php?route=review/view&id=' . $filmId);

        if ($reviewId > 0 && $filmId > 0) {
            $review = $this->reviews->findById($reviewId);
            if ($review && (int) $review['user_id'] === $auth->userId()) {
                $this->reviewService->deleteWithAssets($reviewId);
                $this->auditLogger->log(
                    'DELETE_REVIEW',
                    "Reviewer deleted own review ID #{$reviewId} on film ID #{$filmId}"
                );
                $this->flash->set('review_success', 'Review deleted successfully!');
            }
        }

        $this->redirect('index.php?route=review/view&id=' . $filmId);
    }
}
