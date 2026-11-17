<link rel="stylesheet" href="<?= $viewHelper->asset('public/css/review.css?v=1.0') ?>">

<?php
$filmId = (int) ($film['id'] ?? 0);
$currentUid = (int) ($auth['user_id'] ?? 0);
$currentRole = (int) ($auth['role'] ?? 0);
$isBossViewer = !empty($auth['is_boss']);
$sampleTags = [
    '#StunningVisuals',
    '#EmotionalStory',
    '#Hilarious',
    '#UnexpectedPlotTwist',
    '#GreatSoundtrack',
    '#SuperbActing',
    '#GreatCostumes',
    '#Disappointing',
];
$recRate = (int) ($metrics['recommend_rate'] ?? 0);
$totalReviewsCount = (int) ($metrics['total_reviews'] ?? 0);
$totalR = max(1, $totalReviewsCount);
?>

<div class="netflix-bg" style="background-image: linear-gradient(rgba(11, 15, 25, 0.88), rgba(11, 15, 25, 0.94)), url('<?= $viewHelper->e($film['poster_url'] ?? '') ?>');">
    <div class="overlay-bg-custom">
        <div class="container pt-4 mb-4 d-flex justify-content-between align-items-center">
            <a href="<?= $viewHelper->routeUrl('film/list') ?>" class="btn btn-outline-light btn-sm fw-bold px-3 py-2 shadow-sm rounded-pill">
                &larr; Back to Films
            </a>
            <div>
                <?php if (!empty($auth['is_authenticated'])): ?>
                    <span class="text-white-50 small me-2">Welcome, <strong class="text-warning"><?= $viewHelper->e($auth['name'] ?? 'User') ?></strong></span>
                    <a href="<?= $viewHelper->routeUrl('user/profile') ?>" class="btn btn-sm btn-outline-warning rounded-pill px-3">Profile</a>
                <?php else: ?>
                    <a href="<?= $viewHelper->routeUrl('user/login') ?>" class="btn btn-sm btn-warning fw-bold rounded-pill px-3">Sign In</a>
                <?php endif; ?>
            </div>
        </div>

        <div class="container">
            <div class="row g-4">
                <div class="col-lg-5">
                    <div class="glass-card p-4 mb-4">
                        <div class="position-relative mb-3">
                            <img src="<?= $viewHelper->e($film['poster_url'] ?? '') ?>"
                                 class="img-fluid rounded-3 shadow w-100"
                                 style="max-height: 480px; object-fit: cover;"
                                 alt="<?= $viewHelper->e($film['title'] ?? 'Film Poster') ?>">
                            <span class="badge bg-danger position-absolute top-0 start-0 m-3 px-3 py-2 fs-6 shadow">
                                <?= $viewHelper->e($film['category_name'] ?? 'General') ?>
                            </span>
                        </div>

                        <h2 class="text-warning fw-bold mb-2"><?= $viewHelper->e($film['title'] ?? 'Untitled') ?></h2>
                        <div class="d-flex flex-wrap text-white-50 small mb-3 gap-3">
                            <span>Posted by: <strong class="text-light"><?= $viewHelper->e($film['uploader_name'] ?? 'Tokusatsu Universe') ?></strong></span>
                            <span>Release Date: <strong class="text-light"><?= $viewHelper->e($film['created_at_formatted'] ?? 'N/A') ?></strong></span>
                        </div>

                        <div class="p-3 mb-4 rounded-3" style="background: rgba(31, 41, 55, 0.7); border: 1px solid rgba(234, 179, 8, 0.25);">
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <div>
                                    <div class="text-warning fw-bold fs-3 mb-0 d-flex align-items-center">
                                        ⭐ <?= number_format((float) ($metrics['avg_rating'] ?? 0), 1) ?>
                                        <span class="text-white-50 fs-6 fw-normal ms-1">/ 5.0</span>
                                    </div>
                                    <div class="text-white-50 small">
                                        Based on <strong><?= $totalReviewsCount ?></strong> community reviews
                                    </div>
                                </div>

                                <div class="text-end">
                                    <div class="badge <?= $recRate >= 60 ? 'bg-success' : ($recRate >= 40 ? 'bg-warning text-dark' : 'bg-danger') ?> fs-6 px-3 py-2 shadow-sm">
                                        <?= $recRate ?>% Recommended
                                    </div>
                                    <div class="text-white-50 small mt-1">
                                        <?= (int) ($metrics['verified_count'] ?? 0) ?> verified tickets
                                    </div>
                                </div>
                            </div>

                            <div class="small text-white-50">
                                <?php for ($s = 5; $s >= 1; $s--): ?>
                                    <?php
                                        $cnt = (int) ($metrics["stars_{$s}"] ?? 0);
                                        $pct = round(($cnt / $totalR) * 100);
                                    ?>
                                    <div class="d-flex align-items-center mb-1">
                                        <span style="width: 45px;"><?= $s ?> stars</span>
                                        <div class="progress flex-grow-1 mx-2 progress-star">
                                            <div class="progress-bar bg-warning" role="progressbar" style="width: <?= $pct ?>%;"></div>
                                        </div>
                                        <span style="width: 32px;" class="text-end"><?= $cnt ?></span>
                                    </div>
                                <?php endfor; ?>
                            </div>
                        </div>

                        <h5 class="text-light fw-bold mb-2">Film Synopsis:</h5>
                        <p class="text-light text-opacity-75 mb-0" style="line-height: 1.7; text-align: justify; font-size: 0.95rem;">
                            <?= nl2br($viewHelper->e($film['description'] ?? 'No detailed description available for this film.')) ?>
                        </p>
                    </div>
                </div>

                <div class="col-lg-7">
                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger fw-bold shadow-sm alert-dismissible fade show" role="alert">
                            <?= $viewHelper->e($error) ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($success)): ?>
                        <div class="alert alert-success fw-bold shadow-sm alert-dismissible fade show" role="alert">
                            <?= $viewHelper->e($success) ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($auth['is_authenticated'])): ?>
                        <div class="glass-card p-4 mb-4">
                            <h4 class="text-warning fw-bold mb-3 d-flex align-items-center justify-content-between">
                                <span>Write Your Review</span>
                                <span class="badge bg-secondary text-light fw-normal fs-6">Audience</span>
                            </h4>

                            <form action="<?= $viewHelper->routeUrl('review/add', [], false) ?>" method="POST" enctype="multipart/form-data">
                                <?= $viewHelper->csrfInput() ?>
                                <?= $viewHelper->tabInput() ?>
                                <input type="hidden" name="film_id" value="<?= $filmId ?>">

                                <div class="mb-3">
                                    <label class="form-label text-light fw-semibold small mb-2">Quick Verdict:</label>
                                    <div class="d-flex gap-3">
                                        <label class="verdict-btn selected flex-fill p-2 rounded-3 text-center bg-success bg-opacity-25 text-success border border-success" id="verdict-yes-label">
                                            <input type="radio" name="recommend" value="1" checked class="d-none" id="verdict-yes">
                                            <span class="fs-5">👍</span> <strong class="ms-1">Recommended</strong>
                                        </label>

                                        <label class="verdict-btn flex-fill p-2 rounded-3 text-center bg-danger bg-opacity-25 text-danger border border-danger" id="verdict-no-label">
                                            <input type="radio" name="recommend" value="0" class="d-none" id="verdict-no">
                                            <span class="fs-5">👎</span> <strong class="ms-1">Skip</strong>
                                        </label>
                                    </div>
                                </div>

                                <div class="row g-3 mb-3">
                                    <div class="col-md-5">
                                        <label class="form-label text-light fw-semibold small">Satisfaction Level:</label>
                                        <select name="rating" class="form-select form-select-sm bg-dark text-white border-secondary" required>
                                            <option value="5">⭐⭐⭐⭐⭐ (5 Stars - Masterpiece / Excellent)</option>
                                            <option value="4" selected>⭐⭐⭐⭐ (4 Stars - Great, Worth Watching)</option>
                                            <option value="3">⭐⭐⭐ (3 Stars - Average, Entertaining)</option>
                                            <option value="2">⭐⭐ (2 Stars - Below Average)</option>
                                            <option value="1">⭐ (1 Star - Disappointing, Skip)</option>
                                        </select>
                                    </div>

                                    <div class="col-md-7">
                                        <label class="form-label text-light fw-semibold small">Review Headline (Optional):</label>
                                        <input type="text" name="headline" maxlength="150"
                                               class="form-control form-control-sm bg-dark text-white border-secondary"
                                               placeholder="e.g. Brilliant storytelling and stunning visuals...">
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <label class="form-label text-light fw-semibold small m-0">Review Content:</label>
                                        <span class="text-white-50 small" id="char-counter">0 / 1000 characters</span>
                                    </div>
                                    <textarea name="content" id="review-content" class="form-control bg-dark text-white border-secondary"
                                              rows="3" minlength="5" maxlength="1000"
                                              placeholder="Share your concise thoughts on this film for the community..." required></textarea>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label text-white-50 small mb-1">Quick Emotion Tags (Click to select):</label>
                                    <div class="d-flex flex-wrap gap-2" id="tags-container">
                                        <?php foreach ($sampleTags as $tag): ?>
                                            <span class="tag-pill" data-tag-pill="<?= $viewHelper->e($tag) ?>">
                                                <?= $viewHelper->e($tag) ?>
                                            </span>
                                        <?php endforeach; ?>
                                    </div>
                                    <input type="hidden" name="tags[]" id="selected-tags-input">
                                </div>

                                <div class="p-3 mb-3 rounded-3" style="background: rgba(31, 41, 55, 0.5); border: 1px dashed rgba(156, 163, 175, 0.4);">
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" name="is_spoiler" value="1" id="is_spoiler_check">
                                        <label class="form-check-label text-warning small fw-bold" for="is_spoiler_check">
                                            ⚠️ This review contains spoilers (Spoiler Alert - content will be blurred)
                                        </label>
                                    </div>

                                    <div class="row align-items-center">
                                        <div class="col-md-7">
                                            <label class="form-label text-info small fw-bold mb-0">
                                                Attach Movie Ticket Stub Photo (Optional):
                                            </label>
                                            <div class="text-white-50" style="font-size: 0.75rem;">
                                                Attach a ticket photo to receive the <strong>Verified Watcher</strong> badge and protect rating authenticity.
                                            </div>
                                        </div>
                                        <div class="col-md-5">
                                            <div class="input-group input-group-sm">
                                                <label class="btn btn-outline-info text-nowrap" for="ticket_proof_input" style="cursor: pointer;">
                                                    📁 Choose File
                                                </label>
                                                <span class="form-control form-control-sm bg-dark text-white-50 text-truncate border-secondary" id="ticket_proof_name" style="line-height: 1.8;">No file chosen</span>
                                                <input type="file" name="ticket_proof" id="ticket_proof_input" class="visually-hidden"
                                                       accept="image/png, image/jpeg, image/webp" data-file-label="ticket_proof_name">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <button type="submit" name="submit_review" class="btn btn-warning fw-bold text-dark px-4 py-2 shadow-sm rounded-pill w-100">
                                    🎬 Post Film Review
                                </button>
                            </form>
                        </div>
                    <?php else: ?>
                        <div class="glass-card p-4 mb-4 text-center">
                            <h5 class="text-warning mb-2">Have you watched this film?</h5>
                            <p class="text-white-50 small mb-3">Sign in to share your quick review and rating with the community!</p>
                            <a href="<?= $viewHelper->routeUrl('user/login') ?>" class="btn btn-warning fw-bold rounded-pill px-4">
                                Sign In to Write a Review
                            </a>
                        </div>
                    <?php endif; ?>

                    <div class="glass-card p-3 mb-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
                        <div class="d-flex align-items-center gap-2">
                            <span class="text-warning fw-bold small">Sort by:</span>
                            <div class="btn-group btn-group-sm" role="group">
                                <a href="<?= $viewHelper->routeUrl('review/view', ['id' => $filmId, 'sort' => 'helpful', 'filter' => $filter]) ?>"
                                   class="btn <?= $sort === 'helpful' ? 'btn-warning text-dark fw-bold' : 'btn-outline-secondary text-light' ?>">
                                    Most Helpful
                                </a>
                                <a href="<?= $viewHelper->routeUrl('review/view', ['id' => $filmId, 'sort' => 'newest', 'filter' => $filter]) ?>"
                                   class="btn <?= $sort === 'newest' ? 'btn-warning text-dark fw-bold' : 'btn-outline-secondary text-light' ?>">
                                    Newest
                                </a>
                                <a href="<?= $viewHelper->routeUrl('review/view', ['id' => $filmId, 'sort' => 'highest', 'filter' => $filter]) ?>"
                                   class="btn <?= $sort === 'highest' ? 'btn-warning text-dark fw-bold' : 'btn-outline-secondary text-light' ?>">
                                    Highest Rating
                                </a>
                            </div>
                        </div>

                        <div class="d-flex align-items-center gap-2">
                            <span class="text-white-50 small">Filter:</span>
                            <div class="btn-group btn-group-sm" role="group">
                                <a href="<?= $viewHelper->routeUrl('review/view', ['id' => $filmId, 'sort' => $sort, 'filter' => 'all']) ?>"
                                   class="btn <?= $filter === 'all' ? 'btn-secondary text-white fw-bold' : 'btn-outline-secondary text-white-50' ?>">
                                    All
                                </a>
                                <a href="<?= $viewHelper->routeUrl('review/view', ['id' => $filmId, 'sort' => $sort, 'filter' => 'verified']) ?>"
                                   class="btn <?= $filter === 'verified' ? 'btn-info text-dark fw-bold' : 'btn-outline-secondary text-white-50' ?>" title="Only show reviews with verified ticket proof">
                                    Verified Tickets
                                </a>
                                <a href="<?= $viewHelper->routeUrl('review/view', ['id' => $filmId, 'sort' => $sort, 'filter' => 'recommended']) ?>"
                                   class="btn <?= $filter === 'recommended' ? 'btn-success text-white fw-bold' : 'btn-outline-secondary text-white-50' ?>">
                                    👍 Recommended
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="reviews-feed">
                        <?php if (!empty($reviews)): ?>
                            <?php foreach ($reviews as $rev): ?>
                                <?php
                                    $revId = (int) ($rev['id'] ?? 0);
                                    $isVoted = !empty($rev['is_voted']);
                                    $isSpoiler = ((int) ($rev['is_spoiler'] ?? 0) === 1);
                                    $isVerified = ((int) ($rev['is_verified_watcher'] ?? 0) === 1);
                                    $recommend = (int) ($rev['recommend'] ?? 1);
                                    $isRevByBoss = !empty($rev['is_rev_by_boss']);
                                    $avatarMeta = $rev['reviewer_avatar_meta'] ?? ['hasImage' => false, 'url' => null, 'initial' => 'U'];
                                ?>
                                <div class="glass-card p-3 mb-3" id="rev-<?= $revId ?>">
                                    <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom border-secondary border-opacity-50">
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="rounded-circle overflow-hidden bg-secondary d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                                                <?php if (!empty($avatarMeta['hasImage']) && !empty($avatarMeta['url'])): ?>
                                                    <img src="<?= $viewHelper->e($avatarMeta['url']) ?>" alt="Avatar" data-avatar-fallback="true" style="width:100%; height:100%; object-fit:cover;">
                                                    <span class="fw-bold text-white" style="display:none;"><?= $viewHelper->e($avatarMeta['initial'] ?? 'U') ?></span>
                                                <?php else: ?>
                                                    <span class="fw-bold text-white"><?= $viewHelper->e($avatarMeta['initial'] ?? 'U') ?></span>
                                                <?php endif; ?>
                                            </div>

                                            <div>
                                                <div class="d-flex align-items-center gap-2">
                                                    <strong class="text-warning small"><?= $viewHelper->e($rev['reviewer_name'] ?? 'Anonymous') ?></strong>
                                                    <?php if ($isVerified): ?>
                                                        <span class="badge bg-info text-dark fw-bold py-0 px-2" style="font-size: 0.68rem;" title="User has verified their cinema attendance with ticket proof">
                                                            Verified Watcher
                                                        </span>
                                                    <?php endif; ?>
                                                </div>
                                                <span class="text-white-50" style="font-size: 0.72rem;">
                                                    <?= $viewHelper->e($rev['created_at_formatted'] ?? '') ?>
                                                </span>
                                            </div>
                                        </div>

                                        <div>
                                            <?php if ($currentUid > 0 && $currentUid === (int) ($rev['user_id'] ?? 0)): ?>
                                                <a href="<?= $viewHelper->routeUrl('review/edit', ['id' => $revId]) ?>" class="btn btn-outline-info btn-sm py-0 px-2 me-1" style="font-size: 0.72rem;">Edit</a>
                                            <?php endif; ?>

                                            <?php if ($isBossViewer): ?>
                                                <form method="POST" action="<?= $viewHelper->routeUrl('admin/delete-review', [], false) ?>" class="d-inline" data-confirm="System Boss Authority: Delete this review?">
                                                    <?= $viewHelper->csrfInput() ?>
                                                    <?= $viewHelper->tabInput() ?>
                                                    <input type="hidden" name="id" value="<?= $revId ?>">
                                                    <input type="hidden" name="film_id" value="<?= $filmId ?>">
                                                    <button type="submit" class="btn btn-outline-danger btn-sm py-0 px-2" style="font-size: 0.72rem;">Delete</button>
                                                </form>
                                            <?php elseif ($currentRole >= 2): ?>
                                                <?php if ($isRevByBoss): ?>
                                                    <button type="button" class="btn btn-outline-secondary btn-sm py-0 px-2" style="font-size: 0.72rem;" disabled title="Sub-Admins cannot delete reviews by the System Boss">🔒 Boss Protected</button>
                                                <?php else: ?>
                                                    <form method="POST" action="<?= $viewHelper->routeUrl('admin/delete-review', [], false) ?>" class="d-inline" data-confirm="Administrator: Delete this review?">
                                                        <?= $viewHelper->csrfInput() ?>
                                                        <?= $viewHelper->tabInput() ?>
                                                        <input type="hidden" name="id" value="<?= $revId ?>">
                                                        <input type="hidden" name="film_id" value="<?= $filmId ?>">
                                                        <button type="submit" class="btn btn-outline-danger btn-sm py-0 px-2" style="font-size: 0.72rem;">Delete (Admin)</button>
                                                    </form>
                                                <?php endif; ?>
                                            <?php elseif ($currentUid > 0 && $currentUid === (int) ($rev['user_id'] ?? 0)): ?>
                                                <form method="POST" action="<?= $viewHelper->routeUrl('review/delete', [], false) ?>" class="d-inline" data-confirm="Are you sure you want to delete this review?">
                                                    <?= $viewHelper->csrfInput() ?>
                                                    <?= $viewHelper->tabInput() ?>
                                                    <input type="hidden" name="id" value="<?= $revId ?>">
                                                    <input type="hidden" name="film_id" value="<?= $filmId ?>">
                                                    <button type="submit" class="btn btn-outline-danger btn-sm py-0 px-2" style="font-size: 0.72rem;">Delete</button>
                                                </form>
                                            <?php endif; ?>
                                        </div>
                                    </div>

                                    <div class="d-flex align-items-center gap-2 mb-2">
                                        <span class="text-warning small"><?= str_repeat('⭐', max(1, min(5, (int) ($rev['rating'] ?? 5)))) ?></span>
                                        <span class="badge <?= $recommend === 1 ? 'bg-success bg-opacity-25 text-success border border-success' : 'bg-danger bg-opacity-25 text-danger border border-danger' ?> py-1 px-2" style="font-size: 0.72rem;">
                                            <?= $recommend === 1 ? '👍 Recommended' : '👎 Skip' ?>
                                        </span>
                                    </div>

                                    <?php if (!empty($rev['headline'])): ?>
                                        <h6 class="text-light fw-bold mb-2" style="font-size: 1rem;">
                                            <?= $viewHelper->e($rev['headline']) ?>
                                        </h6>
                                    <?php endif; ?>

                                    <?php if (!empty($rev['tags_list'])): ?>
                                        <div class="mb-2 d-flex flex-wrap gap-1">
                                            <?php foreach ($rev['tags_list'] as $tagItem): ?>
                                                <span class="submitted-tag-badge">
                                                    <?= $viewHelper->e($tagItem) ?>
                                                </span>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>

                                    <div class="<?= $isSpoiler ? 'spoiler-content' : '' ?>">
                                        <?php if ($isSpoiler): ?>
                                            <div class="spoiler-banner">
                                                <span>⚠️ This review contains spoilers - Click to reveal</span>
                                            </div>
                                        <?php endif; ?>

                                        <p class="<?= $isSpoiler ? 'spoiler-blur' : '' ?> text-light text-opacity-90 mb-3" style="line-height: 1.6; font-size: 0.92rem;">
                                            <?= nl2br($viewHelper->e($rev['content'] ?? '')) ?>
                                        </p>
                                    </div>

                                    <div class="d-flex justify-content-between align-items-center pt-2 border-top border-secondary border-opacity-25">
                                        <form method="POST" action="<?= $viewHelper->routeUrl('review/vote', [], false) ?>" class="m-0 p-0">
                                            <?= $viewHelper->csrfInput() ?>
                                            <?= $viewHelper->tabInput() ?>
                                            <input type="hidden" name="film_id" value="<?= $filmId ?>">
                                            <input type="hidden" name="review_id" value="<?= $revId ?>">
                                            <button type="submit"
                                                    class="btn btn-sm <?= $isVoted ? 'btn-warning text-dark fw-bold' : 'btn-outline-secondary text-light' ?> py-1 px-3 rounded-pill"
                                                    style="font-size: 0.78rem;"
                                                    title="<?= $isVoted ? 'Click to remove helpful vote' : 'Mark this review as helpful' ?>">
                                                👍 Helpful (<?= (int) ($rev['helpful_count'] ?? 0) ?>)
                                            </button>
                                        </form>

                                        <?php if (!empty($rev['ticket_proof_url'])): ?>
                                            <a href="<?= $viewHelper->e($rev['ticket_proof_url']) ?>" target="_blank" rel="noopener"
                                               class="text-info text-decoration-none small fw-semibold" style="font-size: 0.75rem;">
                                                View verified ticket stub &nearr;
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="glass-card p-5 text-center text-white-50">
                                <div class="fs-1 mb-2">🎬</div>
                                <h5>No reviews match the selected filter</h5>
                                <p class="small mb-0">Be the first to share your cinematic review for this film!</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="<?= $viewHelper->asset('public/js/review.js') ?>"></script>
