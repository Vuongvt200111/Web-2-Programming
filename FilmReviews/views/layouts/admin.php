<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $viewHelper->e($title ?? 'Admin Control Panel') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= $viewHelper->asset('public/css/admin_style.css?v=3.8') ?>">
</head>
<body class="admin-body d-flex flex-column min-vh-100">

    <nav class="navbar navbar-expand-lg navbar-dark admin-navbar shadow-lg mb-5 sticky-top">
        <div class="container-fluid px-4">
            <a class="navbar-brand text-danger fw-bold fs-4 neon-text" href="<?= $viewHelper->routeUrl('admin/dashboard') ?>">
                CONTROL PANEL
            </a>

            <div class="navbar-nav me-auto ms-3 d-none d-md-flex gap-2">
                <a class="nav-link text-white fw-bold" href="<?= $viewHelper->routeUrl('admin/dashboard') ?>"> Dashboard</a>
                <a class="nav-link text-warning fw-bold" href="<?= $viewHelper->routeUrl('admin/manage-categories') ?>"> Categories</a>
                <a class="nav-link text-info fw-bold" href="<?= $viewHelper->routeUrl('admin/approve-films') ?>"> Approvals</a>
                <a class="nav-link text-success fw-bold" href="<?= $viewHelper->routeUrl('admin/messages') ?>">💬 Messages</a>
                <?php if (!empty($auth['is_boss'])): ?>
                    <a class="nav-link text-danger fw-bold" href="<?= $viewHelper->routeUrl('admin/audit-logs') ?>"> Audit Logs</a>
                <?php endif; ?>
            </div>

            <div class="d-flex align-items-center gap-2 ms-auto">
                <?php $avatarMeta = $auth['avatar_meta'] ?? ['hasImage' => false, 'url' => null, 'initial' => 'A']; ?>
                <a href="<?= $viewHelper->routeUrl('user/profile') ?>" title="View Profile" class="text-decoration-none d-none d-sm-block">
                    <div class="user-avatar-mini rounded-circle overflow-hidden d-flex align-items-center justify-content-center flex-shrink-0"
                         style="width: 38px; height: 38px; border: 2px solid rgba(255, 214, 90, 0.85); background: #1a1a24; box-shadow: 0 0 8px rgba(255, 193, 7, 0.35);">
                        <?php if (!empty($avatarMeta['hasImage']) && !empty($avatarMeta['url'])): ?>
                            <img src="<?= $viewHelper->e($avatarMeta['url']) ?>" alt="Avatar" data-avatar-fallback="true" style="width: 100%; height: 100%; object-fit: cover;">
                            <span class="fw-bold text-warning" style="display: none; font-size: 0.95rem; line-height: 1;"><?= $viewHelper->e($avatarMeta['initial'] ?? 'A') ?></span>
                        <?php else: ?>
                            <span class="fw-bold text-warning" style="font-size: 0.95rem; line-height: 1;"><?= $viewHelper->e($avatarMeta['initial'] ?? 'A') ?></span>
                        <?php endif; ?>
                    </div>
                </a>

                <div class="user-badge d-none d-sm-block text-end">
                    <span class="text-white-50 small d-block" style="line-height: 1;">Logged in as:</span>
                    <span class="text-warning fw-bold fs-6">
                        <?= $viewHelper->e(($auth['name'] ?? '') !== '' ? $auth['name'] : ($auth['email'] ?? 'Administrator')) ?>
                    </span>
                    <?php if (!empty($auth['is_boss'])): ?>
                        <span class="badge bg-danger ms-1"> System Boss</span>
                    <?php else: ?>
                        <span class="badge bg-warning text-dark ms-1"> Sub-Admin</span>
                    <?php endif; ?>
                </div>

                <a href="<?= $viewHelper->routeUrl('film/list') ?>" class="btn btn-outline-info btn-sm fw-bold neon-border">
                    🌐 Web User
                </a>
                <a href="<?= $viewHelper->routeUrl('user/logout') ?>" class="btn btn-danger btn-sm fw-bold shadow-danger">
                     Log out
                </a>
            </div>
        </div>
    </nav>

    <main class="container admin-main-content flex-grow-1">
        <?= $output ?? ($content ?? '') ?>
    </main>

    <footer class="site-footer admin-footer mt-auto py-3">
        <div class="container text-center">
            <p class="mb-1">&copy; <?= $viewHelper->e($currentYear) ?> <strong class="text-white">Tokusatsu Universe Cinema Reviews</strong> - Admin Control Panel</p>
            <p class="small text-secondary mb-0">Course: COMP1842 - Web Programming 2 | University of Greenwich</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= $viewHelper->asset('public/js/tab-session.js') ?>"></script>
    <script src="<?= $viewHelper->asset('public/js/app.js') ?>"></script>
</body>
</html>
