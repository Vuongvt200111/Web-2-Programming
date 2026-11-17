<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $viewHelper->e($title ?? 'Setup Admin - Tokusatsu Universe') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= $viewHelper->asset('public/css/setup_admin.css?v=1.0') ?>">
</head>
<body class="bg-dark text-light d-flex flex-column min-vh-100 setup-admin-body">
    <main class="container my-auto py-5">
        <div class="row justify-content-center">
            <div class="col-md-5">
                <div class="card bg-dark bg-opacity-75 border-danger shadow-lg">
                    <div class="card-header bg-danger text-center text-white fw-bold fs-5">
                        SETUP ADMIN
                    </div>
                    <div class="card-body p-4">
                        <?php if (!empty($error)): ?>
                            <div class="alert alert-danger py-2"><?= $viewHelper->e($error) ?></div>
                        <?php endif; ?>

                        <?php if (!empty($success)): ?>
                            <div class="alert alert-success py-2"><?= $viewHelper->e($success) ?></div>
                            <a href="<?= $viewHelper->routeUrl('login') ?>" class="btn btn-warning w-100 fw-bold">Go to Login</a>
                        <?php else: ?>
                            <form action="<?= $viewHelper->routeUrl('setup-admin', [], false) ?>" method="POST">
                                <?= $viewHelper->csrfInput() ?>
                                <?= $viewHelper->tabInput() ?>
                                <div class="mb-3">
                                    <label class="form-label text-warning fw-bold">Boss Name:</label>
                                    <input type="text" name="name" class="form-control" placeholder="Enter Boss Name" required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label text-warning fw-bold">Email Admin:</label>
                                    <input type="email" name="email" class="form-control" placeholder="admin@tokusatsu.com" required>
                                </div>
                                <div class="mb-4">
                                    <label class="form-label text-warning fw-bold">Password:</label>
                                    <input type="password" name="password" class="form-control" required>
                                </div>
                                <button type="submit" class="btn btn-danger w-100 fw-bold">Establish System Authority</button>
                            </form>
                        <?php endif; ?>

                        <div class="text-center mt-3 pt-3 border-top border-secondary">
                            <a href="<?= $viewHelper->routeUrl('film/list') ?>" class="text-warning text-decoration-none small fw-bold">
                                &larr; Return to Film Catalog
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <footer class="site-footer w-100 mt-auto py-4 text-center" style="background-color: #0b0f19; border-top: 1px solid rgba(255, 255, 255, 0.1);">
        <div class="container text-center">
            <p class="mb-1 text-secondary">&copy; <?= $viewHelper->e($currentYear) ?> <strong class="text-white">Tokusatsu Universe Cinema Reviews</strong></p>
            <p class="small text-secondary mb-0">Course: COMP1842 - Web Programming 2 | University of Greenwich</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= $viewHelper->asset('public/js/tab-session.js') ?>"></script>
    <script src="<?= $viewHelper->asset('public/js/app.js') ?>"></script>
</body>
</html>
