<?php
$activePanel = (($activePanel ?? 'create') === 'restore') ? 'restore' : 'create';
$subAdmins = is_array($subAdmins ?? null) ? $subAdmins : [];
?>

<link rel="stylesheet" href="<?= $viewHelper->asset('public/css/admin_add_new_admin.css?v=1.0') ?>">

<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card admin-card p-4 border-top border-4 border-primary">
            <h3 id="admin-account-card-title" class="<?= ($activePanel === 'restore') ? 'text-warning' : 'text-primary' ?> fw-bold mb-4 text-center">
                <?= ($activePanel === 'restore') ? 'RESTORE SUB-ADMIN PASSWORD' : 'GRANT NEW ADMIN PRIVILEGES' ?>
            </h3>

            <div id="admin-auth-switch-stage" class="admin-switch-stage" data-active-panel="<?= $viewHelper->e($activePanel) ?>">
                <div id="create-admin-panel" class="admin-switch-panel panel-create <?= ($activePanel === 'create') ? 'is-active' : '' ?>" aria-hidden="<?= ($activePanel === 'create') ? 'false' : 'true' ?>">
                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger shadow fw-bold"><?= $viewHelper->e($error) ?></div>
                    <?php endif; ?>
                    <?php if (!empty($success)): ?>
                        <div class="alert alert-success shadow fw-bold"><?= $viewHelper->e($success) ?></div>
                    <?php endif; ?>

                    <form action="<?= $viewHelper->routeUrl('admin/add-new-admin', [], false) ?>" method="POST">
                        <?= $viewHelper->csrfInput() ?>
                        <?= $viewHelper->tabInput() ?>
                        <input type="hidden" name="action" value="create_admin">

                        <div class="mb-3">
                            <label class="text-light fw-bold mb-1 neon-text">Name</label>
                            <input type="text" name="name" class="form-control bg-dark text-light border-secondary shadow-sm" required placeholder="Enter Admin's name...">
                        </div>
                        <div class="mb-3">
                            <label class="text-light fw-bold mb-1 neon-text">Email</label>
                            <input type="email" name="email" class="form-control bg-dark text-light border-secondary shadow-sm" required placeholder="admin@tokusatsu.com">
                        </div>
                        <div class="mb-3">
                            <label class="text-light fw-bold mb-1 neon-text">Password</label>
                            <input type="password" name="password" class="form-control bg-dark text-light border-secondary shadow-sm" required placeholder="********">
                        </div>

                        <div class="admin-switch-row">
                            <button type="button" id="btn-show-restore-subadmin" class="admin-switch-link">
                                Restore for sub-admin
                            </button>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 fw-bold shadow-lg p-2 neon-border">
                            CREATE
                        </button>
                    </form>
                </div>

                <div id="restore-subadmin-panel" class="admin-switch-panel panel-restore <?= ($activePanel === 'restore') ? 'is-active' : '' ?>" aria-hidden="<?= ($activePanel === 'restore') ? 'false' : 'true' ?>">
                    <?php if (!empty($restoreError)): ?>
                        <div class="alert alert-danger shadow fw-bold"><?= $viewHelper->e($restoreError) ?></div>
                    <?php endif; ?>
                    <?php if (!empty($restoreSuccess)): ?>
                        <div class="alert alert-success shadow fw-bold"><?= $viewHelper->e($restoreSuccess) ?></div>
                    <?php endif; ?>

                    <form action="<?= $viewHelper->routeUrl('admin/add-new-admin', [], false) ?>" method="POST">
                        <?= $viewHelper->csrfInput() ?>
                        <?= $viewHelper->tabInput() ?>
                        <input type="hidden" name="action" value="restore_sub_admin">

                        <div class="mb-3">
                            <label class="text-light fw-bold mb-1 neon-text">Sub-Admin Email</label>
                            <input type="email" name="sub_admin_email" list="subadmin-email-options"
                                   class="form-control bg-dark text-light border-secondary shadow-sm"
                                   value="<?= $viewHelper->e($restoreEmail ?? '') ?>"
                                   required autocomplete="off" placeholder="Enter or pick Sub-Admin email...">
                            <datalist id="subadmin-email-options">
                                <?php foreach ($subAdmins as $sub): ?>
                                    <option value="<?= $viewHelper->e($sub['email'] ?? '') ?>">
                                        <?= $viewHelper->e(($sub['name'] ?? 'Sub-Admin') . ' (ID #' . (int) ($sub['id'] ?? 0) . ')') ?>
                                    </option>
                                <?php endforeach; ?>
                            </datalist>
                        </div>

                        <div class="mb-3">
                            <label class="text-light fw-bold mb-1 neon-text">New Password</label>
                            <input type="password" name="new_password" class="form-control bg-dark text-light border-secondary shadow-sm"
                                   minlength="6" required placeholder="At least 6 characters">
                        </div>

                        <div class="mb-3">
                            <label class="text-light fw-bold mb-1 neon-text">Confirm New Password</label>
                            <input type="password" name="confirm_password" class="form-control bg-dark text-light border-secondary shadow-sm"
                                   minlength="6" required placeholder="Re-enter new password">
                        </div>

                        <div class="admin-switch-row">
                            <button type="button" id="btn-back-create-admin" class="admin-switch-link back-link">
                                &#8592; Back to Create Account
                            </button>
                        </div>

                        <button type="submit" class="btn btn-warning text-dark w-100 fw-bold shadow-lg p-2">
                            UPDATE PASSWORD
                        </button>
                    </form>
                </div>
            </div>

            <div class="mt-4 text-center">
                <a href="<?= $viewHelper->routeUrl('admin/dashboard') ?>" class="text-white btn btn-secondary fw-bold shadow-sm">
                    &#8592; Back to Dashboard
                </a>
            </div>
        </div>
    </div>
</div>

<script src="<?= $viewHelper->asset('public/js/admin_add_new_admin.js?v=1.0') ?>"></script>
