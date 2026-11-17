<link rel="stylesheet" href="<?= $viewHelper->asset('public/css/user_add_film.css?v=2.6') ?>">

<div class="add-film-wrapper">
    <div class="container">
        <div class="row justify-content-center w-100 m-0">
            <div class="col-md-10 col-lg-8">
                <div class="card add-film-card">
                    <div class="add-film-header">
                        <?= $viewHelper->e($page_title ?? 'POST NEW FILM') ?>
                    </div>

                    <div class="card-body">
                        <?php if (!empty($error)): ?>
                            <div class="alert alert-danger py-2 fw-bold"><?= $viewHelper->e($error) ?></div>
                        <?php endif; ?>

                        <?php if (!empty($success)): ?>
                            <div class="alert alert-success py-2 fw-bold"><?= $viewHelper->e($success) ?></div>
                        <?php endif; ?>

                        <form action="<?= $viewHelper->routeUrl('film/add', [], false) ?>" method="POST" enctype="multipart/form-data">
                            <?= $viewHelper->csrfInput() ?>
                            <?= $viewHelper->tabInput() ?>

                            <div>
                                <label class="add-film-label">Film Title:</label>
                                <input type="text" name="title" class="form-control add-film-input"
                                       value="<?= $viewHelper->e($film['title'] ?? '') ?>" required>
                            </div>

                            <div>
                                <label class="add-film-label">Category:</label>
                                <select name="category_id" class="form-select add-film-input" required>
                                    <option value="">-- Select Category --</option>
                                    <?php foreach (($categories ?? []) as $cat): ?>
                                        <option value="<?= $viewHelper->e($cat['id']) ?>"
                                                <?= ((string) ($film['category_id'] ?? '') === (string) $cat['id']) ? 'selected' : '' ?>>
                                            <?= $viewHelper->e($cat['category_name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="add-film-label">Poster Image:</label>
                                <div class="input-group">
                                    <label class="btn btn-warning text-dark fw-bold" for="user_add_film_image" style="cursor: pointer;">
                                        Choose File
                                    </label>
                                    <span class="form-control add-film-input text-truncate" id="user_add_film_name" style="background-color: #5c636a; color: #f8fafc; line-height: 1.8;">No file chosen</span>
                                    <input type="file" name="image" id="user_add_film_image" class="visually-hidden" accept="image/*" required data-file-label="user_add_film_name">
                                </div>
                                <small class="text-white-50" style="font-size: 0.8rem;">Accepted formats: PNG, JPG, JPEG, WEBP</small>
                            </div>

                            <div class="form-check my-3 p-3 bg-dark bg-opacity-50 rounded border border-secondary">
                                <input class="form-check-input" type="checkbox" name="is_anniversary" value="1" id="is_anniversary_check" <?= (!empty($film['is_anniversary'])) ? 'checked' : '' ?>>
                                <label class="form-check-label text-warning fw-bold ms-2" for="is_anniversary_check">
                                    ⭐ Mark as Milestone / Anniversary Edition (e.g. 10th or 20th Special)
                                </label>
                            </div>

                            <div>
                                <label class="add-film-label">Film Introduction (Description):</label>
                                <textarea name="description" class="form-control add-film-input" rows="6" required><?= $viewHelper->e($film['description'] ?? '') ?></textarea>
                            </div>

                            <button type="submit" name="user_id" class="btn btn-warning fw-bold w-100 fs-5 mt-3 shadow">POST FILM</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
