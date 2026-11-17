<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $viewHelper->e($title ?? 'Login - Tokusatsu Universe') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= $viewHelper->asset('public/css/login.css?v=3.1') ?>">
</head>
<body>
    <div class="auth-page-container">
        <div class="wrapper">
            <div class="login-box">
                <form action="<?= $viewHelper->routeUrl('login', [], false) ?>" method="POST">
                    <?= $viewHelper->csrfInput() ?>
                    <?= $viewHelper->tabInput() ?>
                    <h2>Login</h2>

                    <?php if (!empty($error)): ?>
                        <p class="message error"><?= $viewHelper->e($error) ?></p>
                    <?php endif; ?>

                    <?php if (!empty($statusAlert) && is_array($statusAlert)): ?>
                        <p class="message <?= $viewHelper->e($statusAlert['type'] ?? 'success') ?>">
                            <?= $viewHelper->e($statusAlert['message'] ?? '') ?>
                        </p>
                    <?php endif; ?>

                    <div class="input-box">
                        <input type="email" name="email" required autocomplete="email" placeholder=" ">
                        <label>Email</label>
                    </div>

                    <div class="input-box">
                        <input type="password" name="password" required autocomplete="current-password" placeholder=" ">
                        <label>Password</label>
                    </div>

                    <div class="remember-forgot">
                        <label><input type="checkbox" name="remember_me"> Remember me</label>
                        <a href="<?= $viewHelper->routeUrl('user/forgot-password') ?>">Forgot Password?</a>
                    </div>

                    <button type="submit" class="btn">Login</button>

                    <div class="register-link">
                        <p>Don't have an account? <a href="<?= $viewHelper->routeUrl('register') ?>">Register</a></p>
                    </div>

                    <div class="card-back-home">
                        <a href="<?= $viewHelper->routeUrl('film/list') ?>">&larr; Return to Film Catalog</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

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
