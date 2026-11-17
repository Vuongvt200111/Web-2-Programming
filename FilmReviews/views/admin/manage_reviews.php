<?php
$reviews = is_array($reviews ?? null) ? $reviews : [];
$isBoss = !empty($isBoss);
?>

<div class="card admin-card p-4 border-top border-4 border-warning shadow-lg">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="text-warning fw-bold m-0">REVIEWS MANAGEMENT</h3>
        <span class="badge bg-warning text-dark fs-6">Total: <?= count($reviews) ?> reviews have been submitted.</span>
    </div>

    <?php if (!empty($alert) && is_array($alert)): ?>
        <div class="alert alert-<?= $viewHelper->e($alert['type'] ?? 'info') ?> fw-bold shadow-sm<?= !empty($alert['dismissible']) ? ' alert-dismissible fade show' : '' ?>">
            <?= $viewHelper->e($alert['message'] ?? '') ?>
            <?php if (!empty($alert['dismissible'])): ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <div class="table-responsive shadow rounded">
        <table class="table table-dark table-hover table-bordered align-middle border-secondary m-0">
            <thead class="table-secondary text-dark">
                <tr>
                    <th class="text-center" width="5%">ID</th>
                    <th width="20%">The film was reviewed</th>
                    <th width="15%">Reviewer</th>
                    <th width="45%">Content &amp; Rating</th>
                    <th class="text-center" width="15%">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($reviews)): ?>
                    <tr>
                        <td colspan="5" class="text-center text-secondary py-5 fw-bold fs-5">
                            No reviews have been submitted yet...
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($reviews as $rv): ?>
                        <?php
                            $reviewId = (int) ($rv['id'] ?? 0);
                            $rating = (int) ($rv['rating'] ?? 0);
                            $isRevByBoss = !empty($rv['is_boss_author']);
                        ?>
                        <tr>
                            <td class="text-center fw-bold text-light"><?= $reviewId ?></td>

                            <td class="fw-bold text-info">
                                <?= $viewHelper->e($rv['film_title'] ?? 'The film has been deleted') ?>
                            </td>

                            <td>
                                <div class="fw-bold text-warning">
                                    <?= $viewHelper->e($rv['user_name'] ?? '') ?>
                                </div>
                                <div class="text-secondary small mt-1">
                                    <?= $viewHelper->e($rv['created_at_formatted'] ?? 'N/A') ?>
                                </div>
                            </td>

                            <td>
                                <div class="text-warning mb-2 fs-6">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <?= $i <= $rating ? '★' : '☆' ?>
                                    <?php endfor; ?>
                                    <span class="text-light ms-2 badge bg-secondary"><?= $rating ?>/5</span>
                                </div>
                                <div class="p-2 bg-dark bg-opacity-50 rounded text-light border-start border-3 border-warning" style="max-height: 80px; overflow-y: auto;">
                                    <?= nl2br($viewHelper->e($rv['content'] ?? '')) ?>
                                </div>
                            </td>

                            <td class="text-center">
                                <?php if ($isRevByBoss && !$isBoss): ?>
                                    <button type="button" class="btn btn-sm btn-outline-secondary fw-bold w-100 py-2" disabled title="Sub-Admins cannot delete reviews published by the System Boss">
                                        🔒 Boss Protected
                                    </button>
                                <?php else: ?>
                                    <form method="POST"
                                          action="<?= $viewHelper->routeUrl('admin/manage-reviews', [], false) ?>"
                                          data-confirm="Are you sure you want to PERMANENTLY DELETE this review, Admin?">
                                        <?= $viewHelper->csrfInput() ?>
                                        <?= $viewHelper->tabInput() ?>
                                        <input type="hidden" name="delete_id" value="<?= $reviewId ?>">
                                        <button type="submit" class="btn btn-sm btn-danger fw-bold neon-border w-100 shadow-sm py-2">
                                            Delete Review
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        <a href="<?= $viewHelper->routeUrl('admin/dashboard') ?>" class="btn btn-secondary fw-bold shadow-sm">
            &#8592; Back to Dashboard
        </a>
    </div>
</div>
