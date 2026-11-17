<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $viewHelper->e($title ?? 'Set New Password - Tokusatsu Universe') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= $viewHelper->asset('public/css/login.css?v=3.1') ?>">
</head>
<body>
    <div class="auth-page-container">
        <div class="wrapper" style="max-width: 460px;">
            <div class="login-box">
                <?php if (!empty($validToken)): ?>
                    <form action="<?= $viewHelper->routeUrl('user/reset-password', ['token' => $token ?? ''], false) ?>" method="POST">
                        <?= $viewHelper->csrfInput() ?>
                        <?= $viewHelper->tabInput() ?>
                        <input type="hidden" name="token" value="<?= $viewHelper->e($token ?? '') ?>">

                        <div style="text-align: center; margin-bottom: 22px;">
                            <div style="width: 58px; height: 58px; margin: 0 auto 12px; background: rgba(2, 132, 199, 0.12); border: 2px solid rgba(2, 132, 199, 0.35); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 24px;">
                                🔒
                            </div>
                            <h2 style="color: #0369a1; font-size: 22px; font-weight: 700; margin-bottom: 6px; text-align: center;">Set New Password</h2>
                            <p style="text-align: center; font-size: 13px; color: #4b5563; margin: 0; line-height: 1.5;">
                                Please choose a strong password with at least 6 characters.
                            </p>
                        </div>

                        <?php if (!empty($error)): ?>
                            <div class="message error" style="margin-bottom: 20px;">
                                <?= $viewHelper->e($error) ?>
                            </div>
                        <?php endif; ?>

                        <div class="input-box">
                            <input type="password" name="password" required minlength="6" placeholder=" ">
                            <label>New Password (min 6 chars)</label>
                        </div>

                        <div class="input-box">
                            <input type="password" name="confirm_password" required minlength="6" placeholder=" ">
                            <label>Confirm New Password</label>
                        </div>

                        <button type="submit" class="btn">Update Password</button>

                        <div class="register-link" style="margin-top: 15px; text-align: center;">
                            <p style="font-size: 13px; color: #4b5563; margin: 0;">
                                Cancel? <a href="<?= $viewHelper->routeUrl('user/login') ?>" style="font-weight: 600; color: #0284c7; text-decoration: none;"> Back to Login</a>
                            </p>
                        </div>

                        <div class="card-back-home">
                            <a href="<?= $viewHelper->routeUrl('film/list') ?>">&larr; Return to Film Catalog</a>
                        </div>
                    </form>
                <?php else: ?>
                    <div style="text-align: center; margin-bottom: 22px;">
                        <div style="width: 60px; height: 60px; margin: 0 auto 14px; background: rgba(239, 68, 68, 0.12); border: 2px solid rgba(239, 68, 68, 0.35); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 26px;">
                            ⚠️
                        </div>
                        <h2 style="color: #b91c1c; font-size: 22px; font-weight: 700; margin-bottom: 8px; text-align: center;">
                            Reset Link Expired
                        </h2>
                        <p style="text-align: center; font-size: 13px; color: #4b5563; margin: 0; line-height: 1.5;">
                            This password reset token is invalid, expired, or has already been used.
                        </p>
                    </div>

                    <?php if (!empty($error)): ?>
                        <div class="message error" style="margin-bottom: 20px;">
                            <?= $viewHelper->e($error) ?>
                        </div>
                    <?php endif; ?>

                    <div style="display: flex; flex-direction: column; align-items: center; gap: 12px;">
                        <a href="<?= $viewHelper->routeUrl('user/forgot-password') ?>" class="btn" style="max-width: 280px; margin: 0;">
                            Request New Reset Link
                        </a>

                        <a href="<?= $viewHelper->routeUrl('user/login') ?>"
                           style="display: inline-flex; align-items: center; justify-content: center; color: #0f172a; font-weight: 600; font-size: 13px; text-decoration: none; padding: 6px 14px; border-radius: 6px; transition: all 0.2s;">
                             Back to Login
                        </a>
                    </div>

                    <div class="card-back-home">
                        <a href="<?= $viewHelper->routeUrl('film/list') ?>">&larr; Return to Film Catalog</a>
                    </div>
                <?php endif; ?>
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
