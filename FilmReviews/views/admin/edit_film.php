<?php
$film = is_array($film ?? null) ? $film : [];
$categories = is_array($categories ?? null) ? $categories : [];
$filmId = (int) ($film['id'] ?? 0);
?>

<link rel="stylesheet" href="<?= $viewHelper->asset('public/css/admin_edit_film.css?v=2.6') ?>">

<div class="admin-update-wrapper">
    <div class="container">
        <div class="row justify-content-center w-100 m-0">
            <div class="col-md-8 col-lg-6">
                <div class="card admin-update-card">
                    <div class="admin-update-header">
                        <?= $viewHelper->e($page_title ?? 'ADMIN - EDIT FILM') ?>
                    </div>

                    <div class="card-body p-4 p-md-5">
                        <?php if (!empty($error)): ?>
                            <div class="alert alert-warning fw-bold py-2"><?= $viewHelper->e($error) ?></div>
                        <?php endif; ?>

                        <form action="<?= $viewHelper->e($action_url ?? $viewHelper->routeUrl('admin/edit-film', ['id' => $filmId], false)) ?>" method="POST" enctype="multipart/form-data">
                            <?= $viewHelper->csrfInput() ?>
                            <?= $viewHelper->tabInput() ?>
                            <input type="hidden" name="film_id" value="<?= $filmId ?>">

                            <div class="mb-3">
                                <label class="admin-update-label text-info text-warning">Film Title:</label>
                                <input type="text" name="title" class="form-control admin-update-input"
                                       value="<?= $viewHelper->e($film['title'] ?? '') ?>" required>
                            </div>

                            <div class="mb-3">
                                <label class="admin-update-label text-info text-warning">Category:</label>
                                <select name="category_id" class="form-select admin-update-input" required>
                                    <?php foreach ($categories as $cat): ?>
                                        <option value="<?= (int) ($cat['id'] ?? 0) ?>" <?= ((string) ($film['category_id'] ?? '') === (string) ($cat['id'] ?? '')) ? 'selected' : '' ?>>
                                            <?= $viewHelper->e($cat['category_name'] ?? '') ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="form-check my-3 p-3 bg-dark bg-opacity-50 rounded border border-secondary">
                                <input class="form-check-input" type="checkbox" name="is_anniversary" value="1" id="admin_edit_anniv" <?= (!empty($film['is_anniversary']) && (int) $film['is_anniversary'] === 1) ? 'checked' : '' ?>>
                                <label class="form-check-label text-warning fw-bold ms-2" for="admin_edit_anniv">
                                    ⭐ Mark as Milestone / Anniversary Edition (e.g. 10th or 20th Special)
                                </label>
                            </div>

                            <div class="mb-3">
                                <label class="admin-update-label text-info text-warning">New Poster Image (leave blank to keep current):</label>
                                <div class="input-group mb-2">
                                    <label class="btn btn-primary fw-bold" for="admin_edit_film_image" style="cursor: pointer;">
                                        📁 Choose File
                                    </label>
                                    <span class="form-control admin-update-input text-truncate" id="admin_edit_film_name" style="background-color: rgba(255, 255, 255, 0.9); color: #000; line-height: 1.8;">No file chosen</span>
                                    <input type="file" name="image" id="admin_edit_film_image" class="visually-hidden" accept="image/*" data-file-label="admin_edit_film_name">
                                </div>

                                <?php if (!empty($film['poster_url'])): ?>
                                    <div class="text-center rounded current-poster-box mt-2">
                                        <small class="text-black fw-bold d-block mb-2">Current Poster:</small>
                                        <img src="<?= $viewHelper->e($film['poster_url']) ?>" width="200" class="img-thumbnail shadow-sm" alt="Current Poster">
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="mb-3">
                                <label class="admin-update-label text-info text-warning">Detailed Description:</label>
                                <textarea name="description" class="form-control admin-update-input" rows="5" required><?= $viewHelper->e($film['description'] ?? '') ?></textarea>
                            </div>

                            <button type="submit" class="btn btn-primary w-100 fw-bold fs-5 mt-3 shadow">
                                UPDATE FILM
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
