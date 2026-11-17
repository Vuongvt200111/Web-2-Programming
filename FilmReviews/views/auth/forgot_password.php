<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $viewHelper->e($title ?? 'Forgot Password - Tokusatsu Universe') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= $viewHelper->asset('public/css/login.css?v=3.1') ?>">
</head>
<body>
    <div class="auth-page-container">
        <div class="wrapper" style="max-width: 460px;">
            <div class="login-box">
                <form action="<?= $viewHelper->routeUrl('user/forgot-password', [], false) ?>" method="POST" data-disable-on-submit="true" data-loading-text="Sending...">
                    <?= $viewHelper->csrfInput() ?>
                    <?= $viewHelper->tabInput() ?>
                    <h2>Recover Password</h2>

                    <p style="text-align: center; font-size: 13.5px; color: #475569; margin-bottom: 20px; line-height: 1.5;">
                        Enter your registered email address to receive password reset instructions via automated SMTP.
                    </p>

                    <?php if (!empty($error)): ?>
                        <div class="message error" style="margin-bottom: 20px;">
                            <?= $viewHelper->e($error) ?>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($success)): ?>
                        <div class="message success" style="margin-bottom: 20px; text-align: left; padding: 12px 14px;">
                            <p style="margin: 0 0 8px 0; font-weight: 600; text-align: center;"><?= $viewHelper->e($success) ?></p>

                            <?php if (!empty($resetInfo['link'])): ?>
                                <div style="margin-top: 10px; border-top: 1px dashed #166534; padding-top: 8px; text-align: center;">
                                    <span style="font-size: 12px; color: #166534; font-weight: bold;">[Coursework Local Demo Shortcut]:</span><br>
                                    <a href="<?= $viewHelper->e($resetInfo['link']) ?>"
                                       style="display: inline-block; margin-top: 6px; padding: 6px 14px; background: #16a34a; color: #fff; text-decoration: none; border-radius: 6px; font-size: 12px; font-weight: bold;">
                                         Click here to Set New Password
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                    <div class="input-box">
                        <input type="email" name="email" required autocomplete="email" placeholder=" ">
                        <label>Registered Email</label>
                    </div>

                    <button type="submit" class="btn">Send Reset Link</button>

                    <div class="register-link" style="margin-top: 16px;">
                        <p>Remembered your password? <a href="<?= $viewHelper->routeUrl('user/login') ?>">Back to Login</a></p>
                    </div>

                    <div class="card-back-home">
                        <a href="<?= $viewHelper->routeUrl('film/list') ?>">&larr; Return to Film Catalog</a>
                    </div>

                    <div style="margin-top: 15px; padding: 10px; background: rgba(0, 0, 0, 0.05); border-radius: 12px 0 12px 0; font-size: 11.5px; color: #475569; text-align: center; line-height: 1.4;">
                         <strong>Enterprise Security Policy:</strong> Self-service reset is reserved for member accounts. Administrative accounts (Admin / Boss) must be recovered offline via the server console.
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
