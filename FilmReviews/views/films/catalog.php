<link rel="stylesheet" href="<?= $viewHelper->asset('public/css/catalog_controls.css?v=1.0') ?>">

<?php
$currentUserId = (int) ($auth['user_id'] ?? 0);
$currentUserRole = (int) ($auth['role'] ?? 0);
$isBossUser = !empty($auth['is_boss']);
$isReviewerOnly = !empty($auth['is_reviewer']);
$currentCategory = (string) ($current_category ?? 'all');
$anniversary = (int) ($anniversary ?? 0);
$mine = (int) ($mine ?? 0);
$sort = (string) ($sort ?? 'newest');
?>

<div class="container py-4">
    <div class="text-center mb-4">
        <h2 class="text-sp mb-2">Superhero repository</h2>
        <p class="text-light opacity-75 small">Discover, review, and rate your favorite Tokusatsu heroes and cinematic masterworks</p>
    </div>

    <?php if (!empty($statusAlert) && is_array($statusAlert)): ?>
        <div class="alert alert-<?= $viewHelper->e($statusAlert['type'] ?? 'info') ?> alert-dismissible fade show fw-bold shadow-sm text-center my-3" role="alert">
            <?= $viewHelper->e($statusAlert['message'] ?? '') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="search-pill-container mb-4">
        <form action="<?= $viewHelper->e($baseUrl) ?>index.php" method="GET" class="d-flex flex-column flex-md-row gap-2 justify-content-center align-items-center">
            <input type="hidden" name="route" value="film/list">
            <?= $viewHelper->tabInput() ?>
            <?php if ($currentCategory !== '' && $currentCategory !== 'all'): ?>
                <input type="hidden" name="category" value="<?= $viewHelper->e($currentCategory) ?>">
            <?php endif; ?>
            <?php if ($anniversary === 1): ?>
                <input type="hidden" name="anniversary" value="1">
            <?php endif; ?>
            <?php if ($mine === 1): ?>
                <input type="hidden" name="mine" value="1">
            <?php endif; ?>

            <div class="input-group search-input-group align-items-center flex-grow-1" style="min-width: 280px;">
                <span class="ps-3 pe-2 text-warning fs-5">🔍</span>
                <input type="search" name="search" class="form-control bg-transparent border-0 text-white shadow-none"
                       placeholder="Search movies, studios (e.g. Toei, Tsuburaya), heroes, keywords..."
                       value="<?= $viewHelper->e($search ?? '') ?>" autocomplete="off" maxlength="100">

                <?php if (!empty($search)): ?>
                    <a href="<?= $viewHelper->e($filterUrls['clearSearch']) ?>"
                       class="btn btn-sm btn-link text-white-50 text-decoration-none me-2" title="Clear Search">
                        ✕ Clear
                    </a>
                <?php endif; ?>

                <button type="submit" class="btn btn-warning rounded-pill px-4 fw-bold text-dark shadow">
                    Search
                </button>
            </div>

            <div class="d-flex align-items-center flex-shrink-0">
                <select name="sort" id="sortSelectControl" class="sort-select-control shadow" title="Sort order">
                    <option value="newest" <?= ($sort === 'newest') ? 'selected' : '' ?>>Newest First</option>
                    <option value="oldest" <?= ($sort === 'oldest') ? 'selected' : '' ?>>Oldest First</option>
                    <option value="rating_desc" <?= ($sort === 'rating_desc') ? 'selected' : '' ?>>Highest Rated</option>
                    <option value="recommended_desc" <?= ($sort === 'recommended_desc') ? 'selected' : '' ?>>Most Recommended</option>
                    <option value="title_asc" <?= ($sort === 'title_asc') ? 'selected' : '' ?>>Title (A - Z)</option>
                    <option value="title_desc" <?= ($sort === 'title_desc') ? 'selected' : '' ?>>Title (Z - A)</option>
                </select>
            </div>
        </form>
    </div>

    <?php if (!empty($searchResolution) && !empty($searchResolution['corrected_from'])): ?>
        <div class="alert text-center py-2 px-4 mb-4 mx-auto shadow"
             style="max-width: 720px; background: rgba(15, 23, 42, 0.92) !important; border: 1.5px solid #ffc107 !important; color: #f8fafc !important; backdrop-filter: blur(8px);">
            <span class="text-warning fw-bold">✨ Smart Search:</span>
            <span style="color: #e2e8f0;">Showing results for</span>
            <strong style="color: #38bdf8; font-size: 1.02rem;">"<?= $viewHelper->e($searchResolution['resolved']) ?>"</strong>
            <span style="color: #cbd5e1;">(Auto-resolved from typo <em class="text-warning fw-semibold">"<?= $viewHelper->e($searchResolution['corrected_from']) ?>"</em>)</span>
            <?php if (!empty($searchResolution['matched_studio'])): ?>
                • <span class="badge bg-warning text-dark fw-bold ms-1">Studio: <?= $viewHelper->e($searchResolution['matched_studio']) ?></span>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <div class="container mb-4">
        <div class="d-flex justify-content-center flex-wrap gap-2">
            <a href="<?= $viewHelper->e($filterUrls['allCategory']) ?>"
               class="filter-btn <?= ($currentCategory === 'all' || $currentCategory === '') ? 'active' : '' ?>" style="text-decoration: none;">
                All
            </a>

            <?php foreach (($categoryLinks ?? []) as $catLink): ?>
                <a href="<?= $viewHelper->e($catLink['url']) ?>"
                   class="filter-btn <?= !empty($catLink['active']) ? 'active' : '' ?>" style="text-decoration: none;">
                    <?= $viewHelper->e($catLink['name']) ?>
                </a>
            <?php endforeach; ?>
        </div>

        <div class="d-flex justify-content-center align-items-center flex-wrap gap-2 mt-3">
            <a href="<?= $viewHelper->e($filterUrls['anniversary']) ?>"
               class="btn anniversary-filter-btn <?= $anniversary === 1 ? 'active' : '' ?> rounded-pill px-4 py-2">
                ⭐ <?= $anniversary === 1 ? 'Anniversary Specials: ACTIVE (Click to Show All)' : '⭐ Filter: Anniversary Specials (Milestone Editions)' ?>
            </a>

            <?php if ($isReviewerOnly): ?>
                <a href="<?= $viewHelper->e($filterUrls['mine']) ?>"
                   id="catalog-my-films-pill"
                   class="btn my-films-filter-btn <?= ($mine === 1) ? 'active' : 'catalog-my-films-pill' ?> rounded-pill px-4 py-2"
                   title="Filter only your approved films in the catalog (pending films are in Profile -> My Films)">
                    <?= ($mine === 1) ? 'My Posted Films: (Click to Show All)' : 'My Posted Films' ?>
                </a>
            <?php endif; ?>
        </div>
    </div>

    <?php if (!empty($search) || ($currentCategory !== 'all' && $currentCategory !== '') || $anniversary === 1 || $mine === 1 || $sort !== 'newest'): ?>
        <div class="d-flex justify-content-between align-items-center bg-dark bg-opacity-75 p-3 rounded-3 mb-4 border border-secondary shadow-sm flex-wrap gap-2">
            <div>
                <span class="text-white">Found <strong><?= (int) ($totalResults ?? count($films ?? [])) ?></strong> movie(s)</span>
                <?php if (!empty($search)): ?>
                    matching: <span class="badge bg-warning text-dark fs-6">"<?= $viewHelper->e($search) ?>"</span>
                    <?php if (!empty($searchResolution['corrected_from']) && $searchResolution['corrected_from'] !== ($searchResolution['resolved'] ?? '')): ?>
                        <span class="badge bg-info text-dark fs-6 ms-1" title="Typo Auto-correction">
                            💡 Auto-corrected: "<?= $viewHelper->e($searchResolution['resolved']) ?>"
                        </span>
                    <?php endif; ?>
                    <?php if (!empty($searchResolution['matched_studio'])): ?>
                        <span class="badge bg-dark border border-secondary text-light fs-6 ms-1">
                            🎬 Studio: <?= $viewHelper->e($searchResolution['matched_studio']) ?>
                        </span>
                    <?php endif; ?>
                <?php endif; ?>
                <?php if ($currentCategory !== 'all' && $currentCategory !== ''): ?>
                    in genre: <span class="badge bg-danger fs-6"><?= $viewHelper->e($currentCategory) ?></span>
                <?php endif; ?>
                <?php if ($anniversary === 1): ?>
                    <span class="badge bg-warning text-dark fs-6 ms-1">⭐ Anniversary Specials</span>
                <?php endif; ?>
                <?php if ($mine === 1): ?>
                    <span class="badge bg-info text-dark fs-6 ms-1">My Posted Films</span>
                <?php endif; ?>
                <?php if ($sort !== 'newest'): ?>
                    <span class="badge bg-secondary text-light fs-6 ms-1">Sort: <?= $viewHelper->e($sort) ?></span>
                <?php endif; ?>
            </div>
            <a href="<?= $viewHelper->tabUrl($filterUrls['reset']) ?>" class="btn btn-outline-light btn-sm fw-bold">
                ✕ Reset All Filters
            </a>
        </div>
    <?php endif; ?>

    <div class="row g-4">
        <?php if (!empty($films)): ?>
            <?php foreach ($films as $film): ?>
                <?php
                    $filmId = (int) ($film['id'] ?? 0);
                    $studio = (string) ($film['studio_name'] ?? 'Tokusatsu Studio');
                    $isBossFilm = !empty($film['is_boss_film']);
                    $avgScore = (float) ($film['avg_score'] ?? 0.0);
                    $totalReviews = (int) ($film['total_reviews'] ?? 0);
                    $recRate = (int) ($film['recommend_rate'] ?? 0);
                    $verifiedCount = (int) ($film['verified_count'] ?? 0);
                ?>
                <div class="col-12 col-md-4 col-lg-3 mb-4">
                    <div class="card h-100 film-card text-white shadow-sm">
                        <img src="<?= $viewHelper->e($film['poster_url']) ?>"
                             alt="Poster of <?= $viewHelper->e($film['title'] ?? 'Tokusatsu Film') ?>"
                             width="100" class="card-img-top" style="height: 350px; object-fit: cover;">

                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap gap-1">
                                <div>
                                    <span class="badge bg-danger me-1"><?= $viewHelper->e($film['category_name'] ?? 'General') ?></span>
                                    <?php if (!empty($film['is_anniversary']) && (int) $film['is_anniversary'] === 1): ?>
                                        <span class="badge bg-warning text-dark fw-bold me-1">Anniversary</span>
                                    <?php endif; ?>
                                    <span class="badge bg-dark border border-secondary text-light me-1" title="Production Studio: <?= $viewHelper->e($studio) ?>">
                                        <?= $viewHelper->e($studio) ?>
                                    </span>
                                </div>

                                <?php if ($currentUserId > 0): ?>
                                    <div class="d-inline-flex align-items-center">
                                        <?php if ($isBossUser): ?>
                                            <a href="<?= $viewHelper->routeUrl('admin/edit-film', ['id' => $filmId]) ?>" class="btn btn-xam-den btn-sm fw-bold me-1 shadow-sm text-decoration-none px-2" title="Edit Film">Edit</a>
                                            <form method="POST" action="<?= $viewHelper->routeUrl('admin/delete-film', [], false) ?>" style="display:inline-block; margin:0; padding:0;"
                                                  data-confirm="System Boss privileges: Are you sure you want to remove this movie from the system?">
                                                <?= $viewHelper->csrfInput() ?>
                                                <?= $viewHelper->tabInput() ?>
                                                <input type="hidden" name="id" value="<?= $filmId ?>">
                                                <button type="submit" class="btn btn-xam-den btn-sm fw-bold shadow-sm px-2">Delete</button>
                                            </form>
                                        <?php elseif ($currentUserRole >= 2): ?>
                                            <?php if ($isBossFilm): ?>
                                                <button type="button" class="btn btn-xam-den btn-sm fw-bold me-1 shadow-sm px-2"
                                                        data-alert-message="🚫 ACCESS DENIED: Sub-Admins are not permitted to modify films published by the System Boss!"
                                                        title="Edit Film">Edit</button>
                                                <button type="button" class="btn btn-xam-den btn-sm fw-bold shadow-sm px-2"
                                                        data-alert-message="🚫 ACCESS DENIED: Sub-Admins are strictly forbidden from deleting films published by the System Boss!"
                                                        title="Delete Film">Delete</button>
                                            <?php else: ?>
                                                <a href="<?= $viewHelper->routeUrl('admin/edit-film', ['id' => $filmId]) ?>" class="btn btn-xam-den btn-sm fw-bold me-1 shadow-sm text-decoration-none px-2">Edit</a>
                                                <form method="POST" action="<?= $viewHelper->routeUrl('admin/delete-film', [], false) ?>" style="display:inline-block; margin:0; padding:0;"
                                                      data-confirm="Administrator privileges: Are you sure you want to remove this movie from the system?">
                                                    <?= $viewHelper->csrfInput() ?>
                                                    <?= $viewHelper->tabInput() ?>
                                                    <input type="hidden" name="id" value="<?= $filmId ?>">
                                                    <button type="submit" class="btn btn-xam-den btn-sm fw-bold shadow-sm px-2">Delete</button>
                                                </form>
                                            <?php endif; ?>
                                        <?php elseif ($currentUserId === (int) ($film['user_id'] ?? 0)): ?>
                                            <a href="<?= $viewHelper->routeUrl('film/edit', ['id' => $filmId]) ?>" class="btn btn-xam-den btn-sm fw-bold text-white me-1 shadow-sm text-decoration-none px-2">Edit</a>
                                            <form method="POST" action="<?= $viewHelper->routeUrl('film/delete', [], false) ?>" style="display:inline-block; margin:0; padding:0;"
                                                  data-confirm="Are you sure you want to delete this movie? This action cannot be undone.">
                                                <?= $viewHelper->csrfInput() ?>
                                                <?= $viewHelper->tabInput() ?>
                                                <input type="hidden" name="id" value="<?= $filmId ?>">
                                                <button type="submit" class="btn btn-xam-den btn-sm fw-bold text-white shadow-sm px-2">Delete</button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <h5 class="card-title fw-bold text-truncate" title="<?= $viewHelper->e($film['title'] ?? '') ?>">
                                <?= $viewHelper->e($film['title'] ?? '') ?>
                            </h5>

                            <p class="card-text small text-light mb-2">
                                Uploader: <strong class="text-warning"><?= $viewHelper->e($film['uploader_name'] ?? 'System') ?></strong>
                            </p>

                            <div class="mb-2 p-2 rounded-3 bg-dark bg-opacity-75 border border-secondary border-opacity-50">
                                <?php if ($totalReviews > 0): ?>
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <div class="d-flex align-items-center gap-1">
                                            <span class="badge bg-warning text-dark fw-bold px-2 py-1 fs-6">
                                                ⭐ <?= number_format($avgScore, 1) ?>
                                            </span>
                                            <span class="text-white-50 small">/ 5.0</span>
                                        </div>
                                        <span class="badge <?= ($recRate >= 60) ? 'bg-success' : 'bg-danger' ?> text-white fw-bold px-2 py-1">
                                            <?= $recRate ?>% Recommended
                                        </span>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center small text-white-50">
                                        <span>💬 <?= $totalReviews ?> review<?= ($totalReviews > 1) ? 's' : '' ?></span>
                                        <?php if ($verifiedCount > 0): ?>
                                            <span class="text-info fw-semibold" title="Verified via ticket stub">✓ <?= $verifiedCount ?> verified</span>
                                        <?php endif; ?>
                                    </div>
                                <?php else: ?>
                                    <div class="d-flex justify-content-between align-items-center py-1">
                                        <span class="text-white-50 small fst-italic">⭐ No reviews yet</span>
                                        <span class="badge bg-secondary text-light small">New</span>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="card-footer border-0 bg-transparent text-center pb-3">
                            <a href="<?= $viewHelper->routeUrl('review/view', ['id' => $filmId]) ?>"
                               class="btn btn-outline-warning w-100 fw-bold">
                                Go to Rate &amp; Review
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12 text-center py-5">
                <div class="alert alert-dark border-secondary p-5 shadow">
                    <h3 class="text-warning fw-bold mb-3">No Films Found</h3>
                    <p class="text-white mb-4">No movie matches your keyword "<?= $viewHelper->e($search ?? '') ?>" or selected category.</p>
                    <a href="<?= $viewHelper->tabUrl($filterUrls['reset']) ?>" class="btn btn-warning px-4 fw-bold">
                        Browse All Movies
                    </a>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <?php if (!empty($pagination) && (int) ($pagination['totalResults'] ?? 0) > 0): ?>
        <div class="catalog-pagination-wrapper p-3 mt-3 d-flex flex-column flex-md-row justify-content-between align-items-center gap-3 shadow-sm">
            <div class="small text-white-50">
                Showing <strong class="text-warning"><?= (int) ($pagination['startItem'] ?? 0) ?>&ndash;<?= (int) ($pagination['endItem'] ?? 0) ?></strong>
                of <strong class="text-white"><?= (int) ($pagination['totalResults'] ?? 0) ?></strong> films
                (Page <strong class="text-warning"><?= (int) ($pagination['currentPage'] ?? 1) ?></strong> / <?= (int) ($pagination['totalPages'] ?? 1) ?>)
            </div>

            <nav aria-label="Film catalog pagination">
                <ul class="pagination pagination-sm catalog-pagination mb-0 flex-wrap justify-content-center">
                    <?php if (!empty($pagination['hasPrevious'])): ?>
                        <li class="page-item">
                            <a class="page-link px-3" href="<?= $viewHelper->e($pagination['previousUrl']) ?>">&laquo; Previous</a>
                        </li>
                    <?php else: ?>
                        <li class="page-item disabled">
                            <span class="page-link px-3">&laquo; Previous</span>
                        </li>
                    <?php endif; ?>

                    <?php foreach (($pagination['items'] ?? []) as $pgItem): ?>
                        <?php if (($pgItem['type'] ?? '') === 'ellipsis'): ?>
                            <li class="page-item disabled">
                                <span class="page-link">&hellip;</span>
                            </li>
                        <?php else: ?>
                            <li class="page-item <?= !empty($pgItem['active']) ? 'active' : '' ?>">
                                <?php if (!empty($pgItem['active'])): ?>
                                    <span class="page-link px-3" aria-current="page"><?= (int) ($pgItem['page'] ?? 1) ?></span>
                                <?php else: ?>
                                    <a class="page-link px-3" href="<?= $viewHelper->e($pgItem['url'] ?? '') ?>"><?= (int) ($pgItem['page'] ?? 1) ?></a>
                                <?php endif; ?>
                            </li>
                        <?php endif; ?>
                    <?php endforeach; ?>

                    <?php if (!empty($pagination['hasNext'])): ?>
                        <li class="page-item">
                            <a class="page-link px-3" href="<?= $viewHelper->e($pagination['nextUrl']) ?>">Next &raquo;</a>
                        </li>
                    <?php else: ?>
                        <li class="page-item disabled">
                            <span class="page-link px-3">Next &raquo;</span>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    <?php endif; ?>
</div>

<script src="<?= $viewHelper->asset('public/js/catalog.js') ?>"></script>
