<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $viewHelper->e($title ?? 'Tokusatsu Universe') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= $viewHelper->asset('public/css/list_films.css?v=3.7') ?>">
    <link rel="stylesheet" href="<?= $viewHelper->asset('public/css/filter.css?v=3.7') ?>">
</head>

<body class="d-flex flex-column min-vh-100 <?= $viewHelper->e($body_class ?? '') ?>">

    <nav class="navbar navbar-expand-lg navbar-dark bg-dark bg-opacity-75 shadow sticky-top" style="backdrop-filter: blur(10px);">
        <div class="container">
            <a class="navbar-brand text-danger fw-bold" href="<?= $viewHelper->routeUrl('film/list') ?>">
                🎬 TOKUSATSU FILMS
            </a>

            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link" href="<?= $viewHelper->routeUrl('film/home') ?>">Home</a>
                </li>
            </ul>

            <div class="d-flex align-items-center flex-wrap gap-2">
                <?php if (!empty($auth['is_authenticated'])): ?>
                    <a href="<?= $viewHelper->routeUrl('user/profile') ?>" class="btn btn-info me-1 fw-bold btn-sm">Profile</a>

                    <?php if (empty($auth['is_admin'])): ?>
                        <a href="<?= $viewHelper->routeUrl('user/contact') ?>" class="btn btn-primary me-1 fw-bold btn-sm">Contact us</a>
                    <?php endif; ?>

                    <?php if (!empty($auth['is_admin'])): ?>
                        <a href="<?= $viewHelper->routeUrl('admin/add-film') ?>" class="btn btn-warning me-1 fw-bold btn-sm">+ Post Film</a>
                        <a href="<?= $viewHelper->routeUrl('admin/dashboard') ?>" class="btn btn-danger me-1 fw-bold btn-sm">Control panel</a>
                    <?php else: ?>
                        <a href="<?= $viewHelper->routeUrl('film/add') ?>" class="btn btn-warning me-1 fw-bold btn-sm">+ Post Film</a>
                    <?php endif; ?>

                    <div class="d-flex align-items-center gap-2 me-1">
                        <?php $avatarMeta = $auth['avatar_meta'] ?? ['hasImage' => false, 'url' => null, 'initial' => 'U']; ?>
                        <a href="<?= $viewHelper->routeUrl('user/profile') ?>" title="View Profile: <?= $viewHelper->e($auth['name'] ?? 'User') ?>" class="text-decoration-none">
                            <div class="user-avatar-mini rounded-circle overflow-hidden d-flex align-items-center justify-content-center flex-shrink-0"
                                 style="width: 38px; height: 38px; border: 2px solid rgba(255, 214, 90, 0.85); background: #1a1a24; box-shadow: 0 0 8px rgba(255, 193, 7, 0.35);">
                                <?php if (!empty($avatarMeta['hasImage']) && !empty($avatarMeta['url'])): ?>
                                    <img src="<?= $viewHelper->e($avatarMeta['url']) ?>" alt="Avatar" data-avatar-fallback="true" style="width: 100%; height: 100%; object-fit: cover;">
                                    <span class="fw-bold text-warning" style="display: none; font-size: 0.95rem; line-height: 1;"><?= $viewHelper->e($avatarMeta['initial'] ?? 'U') ?></span>
                                <?php else: ?>
                                    <span class="fw-bold text-warning" style="font-size: 0.95rem; line-height: 1;"><?= $viewHelper->e($avatarMeta['initial'] ?? 'U') ?></span>
                                <?php endif; ?>
                            </div>
                        </a>

                        <div class="user-badge text-end">
                            <span class="text-white-50 small d-block" style="line-height: 1; font-size: 0.68rem;">Logged in as:</span>
                            <div class="d-inline-flex align-items-center gap-1">
                                <span class="text-warning fw-bold fs-6" style="white-space: nowrap;">
                                    <?= $viewHelper->e($auth['name'] ?? 'User') ?>
                                </span>
                                <?php if (!empty($auth['is_boss'])): ?>
                                    <span class="badge bg-danger ms-1" style="font-size: 0.68rem;">System Boss</span>
                                <?php elseif (!empty($auth['is_admin'])): ?>
                                    <span class="badge bg-warning text-dark ms-1" style="font-size: 0.68rem;">Sub-Admin</span>
                                <?php else: ?>
                                    <span class="badge bg-info text-dark ms-1" style="font-size: 0.68rem;">Member</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <a href="<?= $viewHelper->routeUrl('user/logout') ?>" class="btn btn-danger btn-sm fw-bold">
                        Log out
                    </a>
                <?php else: ?>
                    <a href="<?= $viewHelper->routeUrl('user/login') ?>" class="btn btn-primary btn-sm me-1">Log in</a>
                    <a href="<?= $viewHelper->routeUrl('user/register') ?>" class="btn btn-outline-light btn-sm">Register</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <main class="flex-grow-1">
        <?= $output ?? ($content ?? '') ?>
    </main>

    <footer class="site-footer">
        <div class="container text-center">
            <p class="mb-1">&copy; <?= $viewHelper->e($currentYear) ?> <strong class="text-white">Tokusatsu Universe Cinema Reviews</strong></p>
            <p class="small text-secondary mb-0">Course: COMP1842 - Web Programming 2 | University of Greenwich</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= $viewHelper->asset('public/js/tab-session.js') ?>"></script>
    <script src="<?= $viewHelper->asset('public/js/app.js') ?>"></script>
</body>
</html>
