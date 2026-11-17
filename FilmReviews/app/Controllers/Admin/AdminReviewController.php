<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Core\AuthContext;
use App\Core\AuthMiddleware;
use App\Core\Config;
use App\Core\Flash;
use App\Core\Response;
use App\Models\ReviewModel;
use App\Models\UserModel;
use App\Services\AuditLogger;
use App\Services\ReviewService;
use App\Services\SecurityService;

/**
 * Administrative Review Moderation Controller.
 */
final class AdminReviewController extends BaseController
{
    private ReviewModel $reviews;
    private UserModel $users;
    private ReviewService $reviewService;

    public function __construct(
        Config $config,
        Response $response,
        Flash $flash,
        SecurityService $security,
        AuthMiddleware $auth,
        AuditLogger $auditLogger,
        ReviewModel $reviews,
        UserModel $users,
        ReviewService $reviewService
    ) {
        parent::__construct($config, $response, $flash, $security, $auth, $auditLogger);
        $this->reviews = $reviews;
        $this->users = $users;
        $this->reviewService = $reviewService;
    }

    public function deleteFromFilm(): never
    {
        $auth = $this->auth->requireAdmin();
        $this->auth->requirePost('Method Not Allowed. Deletion requires POST request.');

        $reviewId = (int) ($_POST['id'] ?? 0);
        $filmId = (int) ($_POST['film_id'] ?? 0);
        $redirectBack = $filmId > 0
            ? 'index.php?route=review/view&id=' . $filmId
            : 'index.php?route=film/list';

        $this->security->validateCsrfRequest($this->baseUrl() . $redirectBack);

        if ($reviewId > 0) {
            $review = $this->reviews->findById($reviewId);
            if ($review) {
                $author = $this->users->findById((int) ($review['user_id'] ?? 0));
                $isByBoss = ((int) ($review['user_id'] ?? 0) === 1) || ($author && AuthContext::isBossRecord($author));

                if ($isByBoss && !$auth->isBoss()) {
                    $this->auditLogger->log(
                        'SECURITY_VIOLATION',
                        "Sub-Admin attempted unauthorized deletion of System Boss review ID #{$reviewId}"
                    );
                    $this->flash->set(
                        'review_error',
                        'Access denied: Sub-Admins cannot delete reviews published by the System Boss!'
                    );
                    $this->redirect($redirectBack);
                }

                $this->reviewService->deleteWithAssets($reviewId);
                $this->auditLogger->log(
                    'DELETE_REVIEW',
                    "Administrator moderated and deleted review ID #{$reviewId} on film ID #{$filmId}"
                );
            }
        }

        if ($filmId > 0) {
            $this->redirect('index.php?route=review/view&id=' . $filmId . '&status=review_deleted');
        }

        $this->redirect('index.php?route=film/list');
    }

    public function manage(): array
    {
        $auth = $this->auth->requireAdmin();
        $alert = null;

        if ($this->isPost() && isset($_POST['delete_id'])) {
            $this->security->validateCsrfRequest($this->baseUrl() . 'index.php?route=admin/manage-reviews');

            $deleteId = (int) $_POST['delete_id'];
            $targetRev = $this->reviews->findById($deleteId);
            if ($targetRev) {
                $author = $this->users->findById((int) ($targetRev['user_id'] ?? 0));
                $isByBoss = ((int) ($targetRev['user_id'] ?? 0) === 1) || ($author && AuthContext::isBossRecord($author));

                if ($isByBoss && !$auth->isBoss()) {
                    $this->auditLogger->log(
                        'SECURITY_VIOLATION',
                        "Sub-Admin attempted unauthorized deletion of System Boss review ID #{$deleteId} from Manage Reviews panel"
                    );
                    $alert = [
                        'type'        => 'danger',
                        'message'     => 'Access denied: Sub-Admins cannot delete reviews published by the System Boss!',
                        'dismissible' => true,
                    ];
                } else {
                    $this->reviewService->deleteWithAssets($deleteId);
                    $this->auditLogger->log(
                        'DELETE_REVIEW',
                        "Administrator deleted review ID #{$deleteId} from Manage Reviews panel"
                    );
                    $alert = [
                        'type'        => 'warning',
                        'message'     => 'The review has been successfully removed from the cinema system!',
                        'dismissible' => true,
                    ];
                }
            }
        }

        $reviews = $this->reviews->findAllWithDetailsForAdmin();
        foreach ($reviews as &$rev) {
            $rev['is_boss_author'] = AuthContext::isBossRecord([
                'id'   => (int) ($rev['user_id'] ?? 0),
                'role' => (int) ($rev['user_role'] ?? 0),
            ]);
            $rev['created_at_formatted'] = !empty($rev['created_at'])
                ? date('d/m/Y H:i', strtotime((string) $rev['created_at']))
                : 'N/A';
        }
        unset($rev);

        return $this->view('admin/manage_reviews', 'layouts/admin', 'Manage Reviews', [
            'reviews' => $reviews,
            'alert'   => $alert,
            'isBoss'  => $auth->isBoss(),
        ]);
    }
}
