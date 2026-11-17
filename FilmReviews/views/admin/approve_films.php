<?php
$pendingFilms = is_array($pending_films ?? null) ? $pending_films : [];
?>

<div class="card admin-card p-4 border-top border-4 border-warning shadow-lg">
    <h3 class="text-warning fw-bold mb-4">FILM APPROVAL WAITING LIST</h3>

    <?php if (!empty($alert) && is_array($alert)): ?>
        <div class="alert alert-<?= $viewHelper->e($alert['type'] ?? 'info') ?> fw-bold shadow-sm alert-dismissible fade show">
            <?= $viewHelper->e($alert['message'] ?? '') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="table-responsive rounded shadow-sm">
        <table class="table table-dark table-hover table-bordered align-middle mb-0">
            <thead class="table-secondary text-dark">
                <tr>
                    <th>Film Title</th>
                    <th>Uploader</th>
                    <th>Image</th>
                    <th>Short Description</th>
                    <th class="text-center">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($pendingFilms)): ?>
                    <tr><td colspan="5" class="text-center py-4">No films are currently waiting for approval.</td></tr>
                <?php else: ?>
                    <?php foreach ($pendingFilms as $film): ?>
                        <?php
                            $filmId = (int) ($film['id'] ?? 0);
                            $rawDesc = (string) ($film['description'] ?? '');
                            $shortDesc = function_exists('mb_substr') ? mb_substr($rawDesc, 0, 80) : substr($rawDesc, 0, 80);
                        ?>
                        <tr>
                            <td class="text-info fw-bold">
                                <?= $viewHelper->e($film['title'] ?? '') ?>
                                <?php if (!empty($film['is_anniversary'])): ?>
                                    <span class="badge bg-warning text-dark ms-1">⭐ Anniversary</span>
                                <?php endif; ?>
                            </td>
                            <td class="fw-bold text-warning">
                                <?= $viewHelper->e($film['uploader_name'] ?? 'Member') ?>
                                <?php if (!empty($film['created_at_formatted'])): ?>
                                    <div class="small text-secondary fw-normal mt-1">
                                        <?= $viewHelper->e($film['created_at_formatted']) ?>
                                    </div>
                                <?php endif; ?>
                            </td>

                            <td>
                                <?php if (!empty($film['images']) && !empty($film['poster_url'])): ?>
                                    <img src="<?= $viewHelper->e($film['poster_url']) ?>" width="150" class="rounded border border-secondary" alt="Poster">
                                <?php else: ?>
                                    <span class="text-secondary">No image</span>
                                <?php endif; ?>
                            </td>

                            <td><?= $viewHelper->e($shortDesc) ?></td>

                            <td class="text-center text-nowrap">
                                <form method="POST" action="<?= $viewHelper->routeUrl('admin/approve-films', [], false) ?>" class="d-inline">
                                    <?= $viewHelper->csrfInput() ?>
                                    <?= $viewHelper->tabInput() ?>
                                    <input type="hidden" name="film_id" value="<?= $filmId ?>">
                                    <input type="hidden" name="action" value="approve">
                                    <button type="submit" class="btn btn-sm btn-success fw-bold">Approve</button>
                                </form>

                                <form method="POST"
                                      action="<?= $viewHelper->routeUrl('admin/approve-films', [], false) ?>"
                                      class="d-inline"
                                      data-confirm="Do you definitely want to cancel this film?">
                                    <?= $viewHelper->csrfInput() ?>
                                    <?= $viewHelper->tabInput() ?>
                                    <input type="hidden" name="film_id" value="<?= $filmId ?>">
                                    <input type="hidden" name="action" value="reject">
                                    <button type="submit" class="btn btn-sm btn-danger fw-bold">Refuse</button>
                                </form>
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
