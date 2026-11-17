<link rel="stylesheet" href="<?= $viewHelper->asset('public/css/user_edit_film.css?v=2.6') ?>">

<div class="user-edit-wrapper">
    <div class="container">
        <div class="row justify-content-center w-100 m-0">
            <div class="col-md-8 col-lg-6">
                <div class="card user-edit-card text-white">
                    <div class="card-header bg-warning text-dark fw-bold fs-5 text-center border-0">
                        <?= $viewHelper->e($page_title ?? 'EDIT FILM') ?>
                    </div>

                    <div class="card-body p-4">
                        <?php if (!empty($error)): ?>
                            <div class="alert alert-danger py-2 fw-bold text-center"><?= $viewHelper->e($error) ?></div>
                        <?php endif; ?>

                        <?php if (!empty($success)): ?>
                            <div class="alert alert-success py-2 fw-bold text-center"><?= $viewHelper->e($success) ?></div>
                        <?php endif; ?>

                        <form action="<?= $viewHelper->routeUrl('film/edit', ['id' => $film_id ?? ($film['id'] ?? '')], false) ?>" method="POST" enctype="multipart/form-data">
                            <?= $viewHelper->csrfInput() ?>
                            <?= $viewHelper->tabInput() ?>
                            <?php if (isset($film_id)): ?>
                                <input type="hidden" name="film_id" value="<?= $viewHelper->e($film_id) ?>">
                            <?php endif; ?>

                            <div class="mb-4">
                                <label class="text-info fw-bold mb-2">Film Title:</label>
                                <input type="text" name="title" class="form-control user-edit-input"
                                       value="<?= $viewHelper->e($film['title'] ?? '') ?>" required>
                            </div>

                            <div class="mb-4">
                                <label class="text-info fw-bold mb-2">Category:</label>
                                <select name="category_id" class="form-select user-edit-input" required>
                                    <option value="" class="text-dark">-- Select Category --</option>
                                    <?php foreach (($categories ?? []) as $cat): ?>
                                        <option value="<?= $viewHelper->e($cat['id']) ?>" class="text-dark"
                                                <?= (isset($film['category_id']) && (string) $film['category_id'] === (string) $cat['id']) ? 'selected' : '' ?>>
                                            <?= $viewHelper->e($cat['category_name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="mb-4">
                                <label class="text-info fw-bold mb-2">Poster Image (leave blank to keep current):</label>
                                <div class="input-group mb-2">
                                    <label class="btn btn-warning text-dark fw-bold" for="user_edit_film_image" style="cursor: pointer;">
                                        📁 Choose File
                                    </label>
                                    <span class="form-control user-edit-input text-truncate" id="user_edit_film_name" style="background-color: rgba(255, 255, 255, 0.15); color: #f8fafc; line-height: 1.8;">No file chosen</span>
                                    <input type="file" name="image" id="user_edit_film_image" class="visually-hidden"
                                           accept="image/*" <?= empty($film['images']) ? 'required' : '' ?> data-file-label="user_edit_film_name">
                                </div>

                                <?php if (!empty($film['poster_url'])): ?>
                                    <div class="mt-3 p-3 text-center border rounded" style="background-color: rgba(0,0,0,0.3); border-color: rgba(255,255,255,0.1) !important;">
                                        <small class="text-warning fw-bold d-block mb-2">Current Image:</small>
                                        <img src="<?= $viewHelper->e($film['poster_url']) ?>" width="200" class="rounded shadow-sm" alt="Current Poster">
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="form-check mb-4 p-3 bg-dark bg-opacity-50 rounded border border-secondary">
                                <input class="form-check-input" type="checkbox" name="is_anniversary" value="1" id="user_edit_anniv" <?= (!empty($film['is_anniversary']) && (int) $film['is_anniversary'] === 1) ? 'checked' : '' ?>>
                                <label class="form-check-label text-warning fw-bold ms-2" for="user_edit_anniv">
                                    ⭐ Mark as Milestone / Anniversary Edition (e.g. 10th or 20th Special)
                                </label>
                            </div>

                            <div class="mb-4">
                                <label class="text-info fw-bold mb-2">Film Introduction (Description):</label>
                                <textarea name="description" class="form-control user-edit-input"
                                          rows="5" required><?= $viewHelper->e($film['description'] ?? '') ?></textarea>
                            </div>

                            <button type="submit" name="submit_film" class="btn btn-warning fw-bold w-100 fs-5 mt-2 shadow">
                                UPDATE INFORMATION
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
