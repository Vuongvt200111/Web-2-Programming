<?php if (!empty($error)): ?>
    <div class="alert alert-danger fw-bold text-center shadow alert-dismissible fade show">
        <?= $viewHelper->e($error) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if (!empty($success)): ?>
    <div class="alert alert-success fw-bold text-center shadow alert-dismissible fade show">
        <?= $viewHelper->e($success) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="row mb-5">
    <div class="col-md-6 col-lg-3 mb-3">
        <div class="card admin-card h-100 p-4 text-center border-top border-4 border-warning">
            <h5 class="text-warning fw-bold mb-3">FILMS</h5>
            <h1 class="display-3 text-white fw-bold"><?= (int) ($totalFilms ?? 0) ?></h1>
            <p class="text-white mb-4">Total active films</p>
            <a href="<?= $viewHelper->routeUrl('film/list') ?>" class="btn btn-outline-warning w-100 mt-auto">Manage Films</a>
        </div>
    </div>

    <div class="col-md-6 col-lg-3 mb-3">
        <div class="card admin-card h-100 p-4 text-center border-top border-4 border-success">
            <h5 class="text-success fw-bold mb-3">REVIEWS</h5>
            <h1 class="display-3 text-white fw-bold"><?= (int) ($totalReviews ?? 0) ?></h1>
            <p class="text-white mb-4">Total ratings &amp; reviews</p>
            <a href="<?= $viewHelper->routeUrl('admin/manage-reviews') ?>" class="btn btn-outline-success w-100 mt-auto">Manage Reviews</a>
        </div>
    </div>

    <div class="col-md-6 col-lg-3 mb-3">
        <div class="card admin-card h-100 p-4 text-center border-top border-4 border-info">
            <h5 class="text-info fw-bold mb-3">USERS</h5>
            <h1 class="display-3 text-white fw-bold"><?= (int) ($totalUsers ?? 0) ?></h1>
            <p class="text-white mb-4">Registered members</p>
            <a href="<?= $viewHelper->routeUrl('admin/manage-users') ?>" class="btn btn-outline-info w-100 mt-auto">Manage Accounts</a>
        </div>
    </div>

    <div class="col-md-6 col-lg-3 mb-3">
        <div class="card admin-card h-100 p-4 text-center border-top border-4 border-primary">
            <h5 class="text-primary fw-bold mb-3">CATEGORIES</h5>
            <h1 class="display-3 text-white fw-bold"><?= (int) ($totalCategories ?? 0) ?></h1>
            <p class="text-white mb-4">Film genres &amp; categories</p>
            <a href="<?= $viewHelper->routeUrl('admin/manage-categories') ?>" class="btn btn-outline-primary w-100 mt-auto">Manage Categories</a>
        </div>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-md-10">
        <div class="card admin-card p-4 text-center border-secondary shadow">
            <h4 class="text-light mb-4">EXCLUSIVE CUSTOMER CARE FEATURES</h4>
            <div class="d-flex justify-content-center gap-3 flex-wrap">
                <?php if (!empty($auth['is_boss'])): ?>
                    <a href="<?= $viewHelper->routeUrl('admin/add-new-admin') ?>" class="btn btn-outline-warning px-4 shadow fw-bold">
                        Grant Admin Account (Boss Only)
                    </a>
                <?php endif; ?>

                <a href="<?= $viewHelper->routeUrl('admin/approve-films') ?>" class="btn btn-warning px-4 shadow">
                    Approve Pending Films
                    <?php if ((int) ($pendingCount ?? 0) > 0): ?>
                        <span class="badge bg-dark text-warning ms-1"><?= (int) $pendingCount ?></span>
                    <?php endif; ?>
                </a>

                <a href="<?= $viewHelper->routeUrl('admin/messages') ?>" class="btn btn-success px-4 shadow">
                    💬 View User Messages
                    <?php if ((int) ($totalMessages ?? 0) > 0): ?>
                        <span class="badge bg-dark text-success ms-1"><?= (int) $totalMessages ?></span>
                    <?php endif; ?>
                </a>
            </div>
        </div>
    </div>
</div>
