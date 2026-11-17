<?php
$categories = is_array($categories ?? null) ? $categories : [];
$isBoss = !empty($isBoss);
?>

<div class="row align-items-center mb-4">
    <div class="col-md-3 d-none d-md-block"></div>
    <div class="col-12 col-md-6 text-center">
        <h2 class="text-warning fw-bold m-0 text-center">MANAGE FILM CATEGORIES</h2>
    </div>
    <div class="col-12 col-md-3 text-center text-md-end mt-2 mt-md-0">
        <a href="<?= $viewHelper->routeUrl('admin/dashboard') ?>" class="btn btn-secondary fw-bold shadow-sm">
            &#8592; Back to Dashboard
        </a>
    </div>
</div>

<?php if (!empty($message)): ?>
    <div class="alert alert-success fw-bold shadow-sm alert-dismissible fade show" role="alert">
        <?= $viewHelper->e($message) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<?php if (!empty($error)): ?>
    <div class="alert alert-danger fw-bold shadow-sm alert-dismissible fade show" role="alert">
        <?= $viewHelper->e($error) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<div class="row g-4">
    <!-- Add New Category Form -->
    <div class="col-lg-4">
        <div class="card admin-card p-4 border-top border-4 border-warning shadow">
            <h5 class="text-warning fw-bold mb-3">Add New Category</h5>
            <form action="<?= $viewHelper->routeUrl('admin/manage-categories', [], false) ?>" method="POST">
                <?= $viewHelper->csrfInput() ?>
                <?= $viewHelper->tabInput() ?>
                <input type="hidden" name="add_category" value="1">

                <div class="mb-3">
                    <label class="form-label text-white fw-bold">Category Name <span class="text-danger">*</span></label>
                    <input type="text" name="category_name" class="form-control bg-dark text-white border-secondary"
                           placeholder="e.g. Metal Heroes, Kaiju, Marvel" required minlength="2" maxlength="100">
                </div>

                <div class="mb-3">
                    <label class="form-label text-white fw-bold">Description</label>
                    <textarea name="description" rows="3" class="form-control bg-dark text-white border-secondary"
                              placeholder="Brief description of this genre..."></textarea>
                </div>

                <button type="submit" class="btn btn-warning w-100 fw-bold shadow">
                    Create Category
                </button>
            </form>
        </div>
    </div>

    <!-- Categories List Table -->
    <div class="col-lg-8">
        <div class="card admin-card p-4 shadow">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="text-white fw-bold m-0">Existing Categories (<?= count($categories) ?>)</h5>
                <span class="badge bg-secondary">Total: <?= count($categories) ?></span>
            </div>

            <div class="table-responsive">
                <table class="table table-dark table-hover align-middle mb-0">
                    <thead class="table-secondary text-dark">
                        <tr>
                            <th style="width: 60px;">ID</th>
                            <th>Category Name</th>
                            <th>Description</th>
                            <th class="text-center" style="width: 100px;">Films</th>
                            <th class="text-end" style="width: 180px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($categories)): ?>
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">No categories found.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($categories as $cat): ?>
                                <?php
                                    $catId = (int) ($cat['id'] ?? 0);
                                    $catName = (string) ($cat['category_name'] ?? '');
                                    $filmCount = (int) ($cat['film_count'] ?? 0);
                                    $confirmDel = "System Boss Authority: Are you sure you want to delete category '" . $catName . "'"
                                        . ($filmCount > 0 ? " (currently associated with {$filmCount} film(s))" : '')
                                        . '?';
                                ?>
                                <tr>
                                    <td class="fw-bold">
                                        <span class="audit-id-badge">#<?= $catId ?></span>
                                    </td>
                                    <td>
                                        <span class="fw-bold text-warning fs-6"><?= $viewHelper->e($catName) ?></span>
                                    </td>
                                    <td class="small text-light">
                                        <?= $viewHelper->e($cat['description'] ?? 'No description') ?>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge <?= ($filmCount > 0) ? 'bg-info text-dark' : 'bg-secondary' ?> rounded-pill px-3 py-2 fw-bold">
                                            <?= $filmCount ?>
                                        </span>
                                    </td>
                                    <td class="text-end text-nowrap">
                                        <div class="d-inline-flex align-items-center justify-content-end gap-1">
                                            <?php if ($isBoss): ?>
                                                <button type="button" class="btn btn-outline-warning btn-sm fw-bold"
                                                        data-toggle-edit-id="<?= $catId ?>">
                                                    Edit
                                                </button>

                                                <form action="<?= $viewHelper->routeUrl('admin/delete-category', [], false) ?>"
                                                      method="POST"
                                                      class="d-inline m-0 p-0"
                                                      data-confirm="<?= $viewHelper->e($confirmDel) ?>">
                                                    <?= $viewHelper->csrfInput() ?>
                                                    <?= $viewHelper->tabInput() ?>
                                                    <input type="hidden" name="id" value="<?= $catId ?>">
                                                    <button type="submit" class="btn btn-outline-danger btn-sm fw-bold">
                                                        Delete
                                                    </button>
                                                </form>
                                            <?php else: ?>
                                                <button type="button" class="btn btn-outline-secondary btn-sm" disabled title="Only the System Boss has permission to edit or delete categories">
                                                    Boss Only
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                                <?php if ($isBoss): ?>
                                    <tr id="edit-row-<?= $catId ?>" style="display: none;" class="bg-black">
                                        <td colspan="5" class="p-3">
                                            <form action="<?= $viewHelper->routeUrl('admin/manage-categories', [], false) ?>" method="POST" class="row g-2 align-items-center">
                                                <?= $viewHelper->csrfInput() ?>
                                                <?= $viewHelper->tabInput() ?>
                                                <input type="hidden" name="edit_category" value="1">
                                                <input type="hidden" name="id" value="<?= $catId ?>">

                                                <div class="col-md-4">
                                                    <input type="text" name="category_name" class="form-control form-control-sm bg-dark text-white border-warning"
                                                           value="<?= $viewHelper->e($catName) ?>" required>
                                                </div>
                                                <div class="col-md-5">
                                                    <input type="text" name="description" class="form-control form-control-sm bg-dark text-white border-secondary"
                                                           value="<?= $viewHelper->e($cat['description'] ?? '') ?>" placeholder="Description">
                                                </div>
                                                <div class="col-md-3 text-end">
                                                    <button type="submit" class="btn btn-success btn-sm fw-bold">Save</button>
                                                    <button type="button" class="btn btn-secondary btn-sm" data-toggle-edit-id="<?= $catId ?>">Cancel</button>
                                                </div>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="<?= $viewHelper->asset('public/js/admin_categories.js') ?>"></script>
