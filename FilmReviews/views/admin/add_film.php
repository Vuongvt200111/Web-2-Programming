<link rel="stylesheet" href="<?= $viewHelper->asset('public/css/admin_add_film.css?v=2.6') ?>">

<div class="admin-add-film-wrapper">
    <div class="container">
        <div class="row justify-content-center w-100 m-0">
            <div class="col-md-10 col-lg-8">
                <div class="card admin-card">
                    <div class="admin-card-header">
                        <?= $viewHelper->e($page_title ?? 'Admin - Add New Film') ?>
                    </div>

                    <div class="card-body p-4 p-md-5">
                        <?php if (!empty($error)): ?>
                            <div class="alert alert-warning fw-bold py-2"><?= $viewHelper->e($error) ?></div>
                        <?php endif; ?>

                        <form action="<?= $viewHelper->e($action_url ?? $viewHelper->routeUrl('admin/add-film', [], false)) ?>" method="POST" enctype="multipart/form-data">
                            <?= $viewHelper->csrfInput() ?>
                            <?= $viewHelper->tabInput() ?>

                            <div>
                                <label class="admin-label text-info neon-text">Film Title:</label>
                                <input type="text" name="title" class="form-control admin-input" required>
                            </div>

                            <div>
                                <label class="admin-label text-info neon-text">Category:</label>
                                <select name="category_id" class="form-select admin-input" required>
                                    <option value="">-- Select Category --</option>
                                    <?php foreach (($categories ?? []) as $cat): ?>
                                        <option value="<?= (int) ($cat['id'] ?? 0) ?>"><?= $viewHelper->e($cat['category_name'] ?? '') ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="admin-label text-info neon-text">Poster Image:</label>
                                <div class="input-group">
                                    <label class="btn btn-danger fw-bold" for="admin_add_film_image" style="cursor: pointer;">
                                        📁 Choose File
                                    </label>
                                    <span class="form-control admin-input text-truncate" id="admin_add_film_name" style="background-color: rgba(255, 255, 255, 0.9); color: #000; line-height: 1.8;">No file chosen</span>
                                    <input type="file" name="image" id="admin_add_film_image" class="visually-hidden" accept="image/*" required data-file-label="admin_add_film_name">
                                </div>
                                <small class="text-white-50" style="font-size: 0.8rem;">Accepted formats: PNG, JPG, JPEG, WEBP</small>
                            </div>

                            <div class="form-check my-3 p-3 bg-dark bg-opacity-50 rounded border border-secondary">
                                <input class="form-check-input" type="checkbox" name="is_anniversary" value="1" id="admin_add_anniv">
                                <label class="form-check-label text-warning fw-bold ms-2" for="admin_add_anniv">
                                    ⭐ Mark as Milestone / Anniversary Edition (e.g. 10th or 20th Special)
                                </label>
                            </div>

                            <div>
                                <label class="admin-label text-info">Detailed Description:</label>
                                <textarea name="description" class="form-control admin-input" rows="5" required></textarea>
                            </div>

                            <button type="submit" class="btn btn-danger w-100 fw-bold fs-5 mt-3 shadow">
                                SAVE NEW FILM
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
