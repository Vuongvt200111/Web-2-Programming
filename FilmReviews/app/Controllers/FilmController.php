<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\AuthContext;
use App\Core\AuthMiddleware;
use App\Core\Config;
use App\Core\Flash;
use App\Core\Response;
use App\Models\CategoryModel;
use App\Models\FilmModel;
use App\Presenters\CatalogFilterPresenter;
use App\Presenters\FilmPresenter;
use App\Services\AuditLogger;
use App\Services\MediaService;
use App\Services\SearchService;
use App\Services\SecurityService;

/**
 * Handles public home/catalog browsing and Member (Reviewer) film submissions.
 */
final class FilmController extends BaseController
{
    private const CATALOG_PER_PAGE = 12;

    private FilmModel $films;
    private CategoryModel $categories;
    private MediaService $media;
    private SearchService $searchService;

    public function __construct(
        Config $config,
        Response $response,
        Flash $flash,
        SecurityService $security,
        AuthMiddleware $auth,
        AuditLogger $auditLogger,
        FilmModel $films,
        CategoryModel $categories,
        MediaService $media,
        SearchService $searchService
    ) {
        parent::__construct($config, $response, $flash, $security, $auth, $auditLogger);
        $this->films = $films;
        $this->categories = $categories;
        $this->media = $media;
        $this->searchService = $searchService;
    }

    public function home(): array
    {
        return $this->view('films/home', 'layouts/main', 'Tokusatsu Universe', [
            'page' => 'home',
        ]);
    }

    public function list(): array
    {
        $catInput = $_GET['category'] ?? 'all';
        $rawCategory = is_string($catInput) && $catInput !== '' ? trim($catInput) : 'all';
        $currentCategory = $rawCategory !== '' ? $rawCategory : 'all';

        $searchInput = $_GET['search'] ?? '';
        $rawSearch = is_string($searchInput) ? trim($searchInput) : '';
        $search = mb_substr($rawSearch, 0, 50, 'UTF-8');
        $search = (string) preg_replace('/[%_]{2,}/u', '%', $search);

        $anniversary = ((int) ($_GET['anniversary'] ?? 0) === 1) ? 1 : 0;
        $authContext = $this->auth->current();
        $isReviewerOnly = $authContext->isReviewer();
        $mine = ((int) ($_GET['mine'] ?? 0) === 1 && $isReviewerOnly) ? 1 : 0;

        $sortInput = $_GET['sort'] ?? 'newest';
        $rawSort = is_string($sortInput) ? trim($sortInput) : 'newest';
        $sort = array_key_exists($rawSort, FilmModel::SORT_WHITELIST) ? $rawSort : 'newest';
        $requestedPage = max(1, (int) ($_GET['page'] ?? 1));

        $categories = $this->categories->findAllAlphabetical();
        $searchResolution = $search !== '' ? $this->searchService->resolveSearchTerm($search) : null;

        $paginated = $this->films->searchCatalogPaginated(
            [
                'category'     => $currentCategory,
                'search'       => $search,
                'anniversary'  => $anniversary,
                'mine_user_id' => $mine === 1 ? $authContext->userId() : 0,
                'sort'         => $sort,
            ],
            $searchResolution,
            $this->searchService,
            $requestedPage,
            self::CATALOG_PER_PAGE
        );

        $films = $paginated['films'];
        $totalResults = $paginated['total'];
        $currentPage = $paginated['page'];
        $totalPages = $paginated['totalPages'];

        foreach ($films as &$film) {
            $film['studio_name'] = FilmPresenter::getStudioName((string) ($film['category_name'] ?? ''));
            $film['poster_url'] = $this->media->resolvePoster($film['images'] ?? null, $this->baseUrl());
            $film['is_boss_film'] = AuthContext::isBossRecord([
                'id'   => (int) ($film['user_id'] ?? 0),
                'role' => (int) ($film['uploader_role'] ?? 0),
            ]);
        }
        unset($film);

        $filterState = [
            'current_category' => $currentCategory,
            'search'           => $search,
            'anniversary'      => $anniversary,
            'mine'             => $mine,
            'sort'             => $sort,
        ];

        $categoryLinks = [];
        foreach ($categories as $cat) {
            $categoryLinks[] = [
                'name'   => (string) $cat['category_name'],
                'active' => $currentCategory === (string) $cat['category_name'],
                'url'    => CatalogFilterPresenter::buildFilterUrl($this->baseUrl(), $filterState, ['category' => (string) $cat['category_name']]),
            ];
        }

        $statusAlert = $this->mapStatusAlert((string) ($_GET['status'] ?? ''), [
            'denied' => [
                'type'    => 'danger',
                'message' => '🚫 Access Denied: Sub-Admins are not permitted to modify or delete films published by the System Boss!',
            ],
            'deleted' => [
                'type'    => 'warning',
                'message' => 'Movie has been successfully removed from the system.',
            ],
            'edited' => [
                'type'    => 'success',
                'message' => 'Movie details updated successfully!',
            ],
            'added' => [
                'type'    => 'success',
                'message' => 'New movie published successfully!',
            ],
            'account_deleted' => [
                'type'    => 'secondary',
                'message' => 'Your account has been deactivated.',
            ],
        ]);

        return $this->view('films/catalog', 'layouts/catalog', 'List Films - Tokusatsu Universe', [
            'body_class'       => 'home-page',
            'films'            => $films,
            'categories'       => $categories,
            'current_category' => $currentCategory,
            'search'           => $search,
            'anniversary'      => $anniversary,
            'mine'             => $mine,
            'sort'             => $sort,
            'searchResolution' => $searchResolution,
            'totalResults'     => $totalResults,
            'currentPage'      => $currentPage,
            'totalPages'       => $totalPages,
            'pagination'       => CatalogFilterPresenter::buildPaginationData(
                $this->baseUrl(),
                $filterState,
                $currentPage,
                $totalPages,
                $totalResults,
                self::CATALOG_PER_PAGE
            ),
            'statusAlert'      => $statusAlert,
            'categoryLinks'    => $categoryLinks,
            'filterUrls'       => [
                'reset'       => $this->baseUrl() . 'index.php?route=film/list',
                'allCategory' => CatalogFilterPresenter::buildFilterUrl($this->baseUrl(), $filterState, ['category' => 'all']),
                'clearSearch' => CatalogFilterPresenter::buildFilterUrl($this->baseUrl(), $filterState, ['search' => '']),
                'anniversary' => CatalogFilterPresenter::buildFilterUrl($this->baseUrl(), $filterState, ['anniversary' => $anniversary === 1 ? 0 : 1]),
                'mine'        => CatalogFilterPresenter::buildFilterUrl($this->baseUrl(), $filterState, ['mine' => $mine === 1 ? 0 : 1]),
            ],
        ]);
    }

    public function userAdd(): array
    {
        $auth = $this->auth->requireReviewerOnly('film/list');

        $categories = $this->categories->findAllAlphabetical();
        $error = (string) $this->flash->pull('film_add_error', '');
        $success = (string) $this->flash->pull('film_add_success', '');
        $oldInput = $this->flash->pull('film_add_old', null);

        $film = is_array($oldInput) ? [
            'title'          => (string) ($oldInput['title'] ?? ''),
            'description'    => (string) ($oldInput['description'] ?? ''),
            'category_id'    => (string) ($oldInput['category_id'] ?? ''),
            'is_anniversary' => (int) ($oldInput['is_anniversary'] ?? 0),
        ] : ['title' => '', 'description' => '', 'category_id' => '', 'is_anniversary' => 0];

        if ($this->isPost()) {
            $this->security->validateCsrfRequest($this->baseUrl() . 'index.php?route=film/add');

            $filmTitle = trim((string) ($_POST['title'] ?? ''));
            $description = trim((string) ($_POST['description'] ?? ''));
            $categoryId = (string) ($_POST['category_id'] ?? '');
            $isAnniversary = isset($_POST['is_anniversary']) ? 1 : 0;
            $userId = (int) $auth->userId();

            $submittedFilm = [
                'title'          => $filmTitle,
                'description'    => $description,
                'category_id'    => $categoryId,
                'is_anniversary' => $isAnniversary,
            ];

            if ($filmTitle === '' || $description === '' || $categoryId === '') {
                $this->flash->set('film_add_error', 'Please enter all the required information!');
                $this->flash->set('film_add_old', $submittedFilm);
            } elseif (!isset($_FILES['image']) || ($_FILES['image']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
                $this->flash->set('film_add_error', 'Please select a valid poster image!');
                $this->flash->set('film_add_old', $submittedFilm);
            } elseif ($this->films->isRecentDuplicateSubmission($userId, $filmTitle, $description, 60)) {
                $this->flash->set('film_add_success', 'Your film has been successfully posted and is awaiting admin approval. Thank you!');
            } else {
                $uploadResult = $this->media->uploadSecureImage($_FILES['image']);
                if (!$uploadResult['success']) {
                    $this->flash->set('film_add_error', (string) ($uploadResult['error'] ?? 'Upload failed!'));
                    $this->flash->set('film_add_old', $submittedFilm);
                } else {
                    $newFilmId = $this->films->insert([
                        'title'          => $filmTitle,
                        'description'    => $description,
                        'category_id'    => $categoryId,
                        'is_anniversary' => $isAnniversary,
                        'images'         => $uploadResult['filename'],
                        'user_id'        => $userId,
                        'status'         => 'pending',
                        'created_at'     => date('Y-m-d H:i:s'),
                    ]);

                    $this->auditLogger->log(
                        'SUBMIT_FILM',
                        "Member submitted new film '{$filmTitle}' (ID: #{$newFilmId}) for approval"
                    );
                    $this->flash->set('film_add_success', 'Your film has been successfully posted and is awaiting admin approval. Thank you!');
                }
            }

            $this->redirect('index.php?route=film/add');
        }

        return $this->view('films/user_add', 'layouts/catalog', 'Add Film', [
            'categories' => $categories,
            'error'      => $error,
            'success'    => $success,
            'film'       => $film,
            'page_title' => 'POST NEW FILM',
        ]);
    }

    public function userEdit(): array
    {
        $auth = $this->auth->requireReviewerOnly('film/list');

        $filmId = (int) ($_GET['id'] ?? $_POST['film_id'] ?? 0);
        $film = $this->films->findById($filmId);

        if (!$film || (int) $film['user_id'] !== $auth->userId()) {
            $this->redirect('index.php?route=film/list');
        }

        $categories = $this->categories->findAllAlphabetical();
        $error = '';
        $success = '';

        if ($this->isPost()) {
            $this->security->validateCsrfRequest($this->baseUrl() . 'index.php?route=film/edit&id=' . $filmId);

            $filmTitle = trim((string) ($_POST['title'] ?? ''));
            $description = trim((string) ($_POST['description'] ?? ''));
            $categoryId = (string) ($_POST['category_id'] ?? '');
            $isAnniversary = isset($_POST['is_anniversary']) ? 1 : 0;
            $oldImage = (string) ($film['images'] ?? '');
            $image = $oldImage;
            $isNewImageUploaded = false;

            if ($filmTitle === '' || $description === '' || $categoryId === '') {
                $error = 'Please enter all the required information!';
            } else {
                $uploadOk = true;
                if (isset($_FILES['image']) && ($_FILES['image']['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK) {
                    $uploadResult = $this->media->uploadSecureImage($_FILES['image']);
                    if (!$uploadResult['success']) {
                        $error = (string) ($uploadResult['error'] ?? 'Upload failed!');
                        $uploadOk = false;
                    } else {
                        $image = (string) $uploadResult['filename'];
                        $isNewImageUploaded = true;
                    }
                }

                if ($uploadOk) {
                    $this->films->update([
                        'id'             => $filmId,
                        'title'          => $filmTitle,
                        'description'    => $description,
                        'category_id'    => $categoryId,
                        'is_anniversary' => $isAnniversary,
                        'images'         => $image,
                        'status'         => 'pending',
                    ]);
                    $this->auditLogger->log('EDIT_FILM', "Member updated film '{$filmTitle}' (ID: #{$filmId})");

                    if ($isNewImageUploaded && $oldImage !== '' && $oldImage !== $image) {
                        $this->media->deleteSafely($oldImage);
                    }

                    $success = 'Your film has been successfully updated and is awaiting administrator approval. Thank you!';
                    $film['title'] = $filmTitle;
                    $film['description'] = $description;
                    $film['category_id'] = $categoryId;
                    $film['is_anniversary'] = $isAnniversary;
                    $film['images'] = $image;
                }
            }
        }

        $film['poster_url'] = $this->media->resolvePoster($film['images'] ?? null, $this->baseUrl());

        return $this->view('films/user_edit', 'layouts/catalog', 'Edit Film', [
            'film'       => $film,
            'film_id'    => $filmId,
            'categories' => $categories,
            'error'      => $error,
            'success'    => $success,
            'page_title' => 'EDIT INFORMATION FILM',
        ]);
    }

    public function userDelete(): never
    {
        $auth = $this->auth->requireReviewerOnly('film/list');
        $this->auth->requirePost('Method Not Allowed. Deletion requires POST request.');
        $this->security->validateCsrfRequest($this->baseUrl() . 'index.php?route=film/list');

        $filmId = (int) ($_POST['id'] ?? 0);
        if ($filmId > 0) {
            $film = $this->films->findById($filmId);
            if ($film && (int) $film['user_id'] === $auth->userId()) {
                if (!empty($film['images'])) {
                    $this->media->deleteSafely((string) $film['images']);
                }
                $this->films->delete($filmId);
                $this->auditLogger->log('DELETE_FILM', "Member deleted own film '{$film['title']}' (ID: #{$filmId})");
            }
        }

        $target = (($_POST['return_to'] ?? '') === 'profile')
            ? 'index.php?route=user/profile&section=films&status=film_deleted'
            : 'index.php?route=film/list&status=deleted';

        $this->redirect($target);
    }
}
