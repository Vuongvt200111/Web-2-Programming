<link rel="stylesheet" href="<?= $viewHelper->asset('public/css/user_edit_review.css?v=2.6') ?>">

<?php
$review = $review ?? [];
$filmId = (int) ($film_id ?? ($review['film_id'] ?? 1));
$reviewId = (int) ($review['id'] ?? 0);
?>

<div class="edit-review-wrapper netflix-bg" style="background-image: url('<?= $viewHelper->e($film['poster_url'] ?? '') ?>');">
    <div class="container">
        <div class="row justify-content-center w-100 m-0">
            <div class="col-md-10 col-lg-6">
                <h2 class="edit-review-title">Edit Your Review</h2>

                <div class="card edit-review-card">
                    <div class="card-body edit-review-card-body">
                        <?php if (!empty($error)): ?>
                            <div class="alert alert-danger py-2"><?= $viewHelper->e($error) ?></div>
                        <?php endif; ?>

                        <form action="<?= $viewHelper->routeUrl('review/edit', ['id' => $reviewId], false) ?>" method="POST">
                            <?= $viewHelper->csrfInput() ?>
                            <?= $viewHelper->tabInput() ?>
                            <input type="hidden" name="review_id" value="<?= $reviewId ?>">

                            <div class="mb-3">
                                <label class="form-label edit-review-label">Quick Verdict:</label>
                                <div class="d-flex gap-3">
                                    <label class="p-2 rounded text-center flex-fill bg-success bg-opacity-25 text-success border border-success" style="cursor: pointer;">
                                        <input type="radio" name="recommend" value="1" <?= ((int) ($review['recommend'] ?? 1) === 1) ? 'checked' : '' ?>>
                                        <strong>👍 Recommended</strong>
                                    </label>
                                    <label class="p-2 rounded text-center flex-fill bg-danger bg-opacity-25 text-danger border border-danger" style="cursor: pointer;">
                                        <input type="radio" name="recommend" value="0" <?= ((int) ($review['recommend'] ?? 1) === 0) ? 'checked' : '' ?>>
                                        <strong>👎 Skip</strong>
                                    </label>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label edit-review-label">Rating:</label>
                                <select name="rating" class="form-select edit-review-input w-auto" required>
                                    <?php for ($i = 5; $i >= 1; $i--): ?>
                                        <option value="<?= $i ?>" <?= ((int) ($review['rating'] ?? 0) === $i) ? 'selected' : '' ?>>
                                            <?= str_repeat('⭐', $i) ?> (<?= $i ?> Stars)
                                        </option>
                                    <?php endfor; ?>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label edit-review-label">Review Headline (Short summary):</label>
                                <input type="text" name="headline" class="form-control edit-review-input" maxlength="150"
                                       value="<?= $viewHelper->e($review['headline'] ?? '') ?>"
                                       placeholder="e.g. Brilliant storyline and stunning visuals...">
                            </div>

                            <div class="mb-3">
                                <label class="form-label edit-review-label">Tags (Comma-separated):</label>
                                <input type="text" name="tags" class="form-control edit-review-input" maxlength="255"
                                       value="<?= $viewHelper->e($review['tags'] ?? '') ?>"
                                       placeholder="#StunningVisuals, #EmotionalStory, #Hilarious...">
                            </div>

                            <div class="mb-3 form-check">
                                <input class="form-check-input" type="checkbox" name="is_spoiler" value="1" id="edit_spoiler"
                                       <?= (!empty($review['is_spoiler']) && (int) $review['is_spoiler'] === 1) ? 'checked' : '' ?>>
                                <label class="form-check-label text-warning small fw-bold" for="edit_spoiler">
                                    ⚠️ This review contains spoilers (Spoiler Alert)
                                </label>
                            </div>

                            <div class="mb-4">
                                <label class="form-label edit-review-label">Review Content:</label>
                                <textarea name="content" class="form-control edit-review-input" rows="4" minlength="5" maxlength="1000" required><?= $viewHelper->e($review['content'] ?? '') ?></textarea>
                            </div>

                            <button type="submit" name="update_review" class="btn btn-warning fw-bold text-dark px-4">Update Review</button>
                            <a href="<?= $viewHelper->routeUrl('review/view', ['id' => $filmId]) ?>" class="btn btn-outline-light ms-3">Cancel &amp; Go Back</a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
