<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\AuthContext;
use App\Core\AuthMiddleware;
use App\Core\Config;
use App\Core\Flash;
use App\Core\Response;
use App\Models\UserModel;
use App\Services\AuditLogger;
use App\Services\MailerService;
use App\Services\SecurityService;
use App\Services\TabSession;

/**
 * Handles Login, Registration, Logout, and Password Recovery flows.
 */
final class AuthController extends BaseController
{
    private UserModel $users;
    private MailerService $mailer;

    public function __construct(
        Config $config,
        Response $response,
        Flash $flash,
        SecurityService $security,
        AuthMiddleware $auth,
        AuditLogger $auditLogger,
        UserModel $users,
        MailerService $mailer
    ) {
        parent::__construct($config, $response, $flash, $security, $auth, $auditLogger);
        $this->users = $users;
        $this->mailer = $mailer;
    }

    public function login(): array
    {
        $error = (string) $this->flash->pull('system_error', '');
        $systemNotice = (string) $this->flash->pull('system_notice', '');
        $statusAlert = $this->mapStatusAlert((string) ($_GET['status'] ?? ''), [
            'reset_success' => [
                'type'    => 'success',
                'message' => 'Password reset successfully! Please log in with your new password.',
            ],
        ]);
        if ($statusAlert === null && $systemNotice !== '') {
            $statusAlert = [
                'type'    => 'success',
                'message' => $systemNotice,
            ];
        }

        $this->users->syncExpiredBlocks();

        if ($this->isPost()) {
            $this->security->validateCsrfRequest();

            $email = trim((string) ($_POST['email'] ?? ''));
            $password = (string) ($_POST['password'] ?? '');
            $user = $this->users->findByEmail($email);

            if ($user && password_verify($password, (string) $user['password'])) {
                if (!empty($user['is_deleted']) && (int) $user['is_deleted'] === 1) {
                    $error = 'Your account has been deleted. Please contact the Administrator to restore it.';
                    $this->auditLogger->log(
                        'LOGIN_FAILED',
                        "Attempted login to deactivated/deleted account: '{$user['email']}'",
                        (int) $user['id'],
                        (int) $user['role'],
                        (string) $user['name']
                    );
                } elseif ($this->users->isBlocked($user)) {
                    $untilStr = !empty($user['blocked_until']) ? ' until ' . date('d/m/Y H:i', strtotime((string) $user['blocked_until'])) : '';
                    $reasonStr = !empty($user['block_reason']) ? ' Reason: ' . htmlspecialchars((string) $user['block_reason'], ENT_QUOTES, 'UTF-8') : '';
                    $error = "Your account has been temporarily blocked by administration{$untilStr}.{$reasonStr} Please contact support.";
                    $this->auditLogger->log(
                        'BLOCKED_LOGIN_ATTEMPT',
                        "Blocked user '{$user['name']}' (ID: {$user['id']}) attempted login while suspended{$untilStr}",
                        (int) $user['id'],
                        (int) $user['role'],
                        (string) $user['name']
                    );
                } elseif (TabSession::isUserLoggedInElsewhere((int) $user['id'], (int) $this->config->get('app', 'tab_timeout', 28800))) {
                    $error = 'Login denied: This account is already logged in on another active session! Please log out from the first one.';
                    $this->auditLogger->log(
                        'CONCURRENT_LOGIN_BLOCKED',
                        "Blocked concurrent login attempt on another tab for already-active account: '{$user['email']}'",
                        (int) $user['id'],
                        (int) $user['role'],
                        (string) $user['name']
                    );
                } else {
                    if (session_status() === PHP_SESSION_ACTIVE && !headers_sent()) {
                        session_regenerate_id(true);
                    }
                    TabSession::login($user);

                    $this->auditLogger->log(
                        'USER_LOGIN',
                        "User '{$user['name']}' ({$user['email']}) logged in successfully",
                        (int) $user['id'],
                        (int) $user['role'],
                        (string) $user['name']
                    );

                    $tabParam = '&tab_id=' . urlencode(TabSession::getId());
                    $targetRoute = AuthContext::isAdminRecord($user) ? 'admin/dashboard' : 'film/list';
                    $this->redirect('index.php?route=' . $targetRoute . $tabParam);
                }
            } else {
                $error = 'Email or password is incorrect!';
                $this->auditLogger->log(
                    'LOGIN_FAILED',
                    "Failed login attempt for email: '{$email}' (Invalid credentials)",
                    null,
                    0,
                    $email !== '' ? $email : 'Guest'
                );
            }
        }

        return $this->view('auth/login', null, 'Login - Tokusatsu Universe', [
            'error'       => $error,
            'statusAlert' => $statusAlert,
        ]);
    }

    public function register(): array
    {
        $error = '';
        $success = '';
        $domain = strtolower((string) $this->config->get('app', 'internal_email_domain', '@tokusatsu.com'));

        if ($this->isPost()) {
            $this->security->validateCsrfRequest();

            $name = trim((string) ($_POST['name'] ?? ''));
            $email = trim((string) ($_POST['email'] ?? ''));
            $rawPassword = (string) ($_POST['password'] ?? '');

            if (!str_ends_with(strtolower($email), $domain)) {
                $error = "Error: System only accepts internal emails ending with {$domain}!";
            } elseif ($name === '' || $rawPassword === '') {
                $error = 'Please fill in all required fields!';
            } elseif ($this->users->findByEmail($email)) {
                $error = 'This email already exists in the system!';
            } else {
                try {
                    $newUserId = (int) $this->users->insert([
                        'name'              => $name,
                        'email'             => $email,
                        'password'          => password_hash($rawPassword, PASSWORD_DEFAULT),
                        'role'              => 0,
                        'credibility_score' => 0,
                        'badge'             => 'Member',
                        'is_blocked'        => 0,
                    ]);

                    $this->auditLogger->log(
                        'USER_REGISTER',
                        "New user registered account: '{$name}' ({$email})",
                        $newUserId,
                        0,
                        $name
                    );
                    $success = 'Registration successful! Please proceed to Log in.';
                } catch (\Throwable $e) {
                    $error = 'System error: ' . $e->getMessage();
                }
            }
        }

        return $this->view('auth/register', null, 'Register - Tokusatsu Universe', [
            'error'   => $error,
            'success' => $success,
        ]);
    }

    public function logout(): never
    {
        $this->security->ensureSession();
        $auth = $this->auth->current();

        if ($auth->isAuthenticated()) {
            $this->auditLogger->log(
                'USER_LOGOUT',
                "User '{$auth->name()}' ({$auth->email()}) logged out successfully",
                $auth->userId(),
                $auth->role(),
                $auth->name()
            );
        }

        $tabId = TabSession::getId();
        TabSession::logout();
        $this->redirect('index.php?route=film/list&tab_id=' . urlencode($tabId));
    }

    public function forgotPassword(): array
    {
        $error = '';
        $success = '';
        $resetInfo = null;

        if ($this->isPost()) {
            $this->security->validateCsrfRequest();

            $email = trim((string) ($_POST['email'] ?? ''));
            $now = time();
            $window = (int) $this->config->get('app', 'forgot_password_window', 900);
            $maxAttempts = (int) $this->config->get('app', 'forgot_password_max_attempt', 5);
            $cooldown = (int) $this->config->get('app', 'forgot_password_cooldown', 300);
            $tokenTtl = (int) $this->config->get('app', 'password_reset_ttl', 3600);

            $attempts = $_SESSION['reset_attempts'] ?? [];
            if (!is_array($attempts)) {
                $attempts = [];
            }
            $attempts = array_values(array_filter(
                $attempts,
                static fn($ts): bool => ($now - (int) $ts) < $window
            ));
            $_SESSION['reset_attempts'] = $attempts;

            if (count($attempts) >= $maxAttempts) {
                $error = 'Too many password recovery attempts from this IP address. For security reasons, please wait 15 minutes before trying again.';
            } elseif ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error = 'Please enter a valid email address!';
            } elseif (isset($_SESSION['reset_cooldown'][$email]) && ($now - (int) $_SESSION['reset_cooldown'][$email]) < $cooldown) {
                $remaining = $cooldown - ($now - (int) $_SESSION['reset_cooldown'][$email]);
                $error = "A reset request was recently submitted for this email. Please wait {$remaining} second(s) before requesting another reset link.";
            } else {
                $user = $this->users->findByEmail($email);

                if (!$user || !empty($user['is_deleted'])) {
                    $_SESSION['reset_attempts'][] = $now;
                    $error = 'Error: The specified email address is not registered in our system. Please check your spelling or sign up for a new account.';
                } elseif (AuthContext::isAdminRecord($user)) {
                    $_SESSION['reset_attempts'][] = $now;
                    $error = 'Access Denied: Self-service password recovery is disabled for administrative accounts due to enterprise security policies. Please contact the System Boss or execute server console recovery.';
                } else {
                    $token = bin2hex(random_bytes(32));
                    $expires = date('Y-m-d H:i:s', $now + $tokenTtl);

                    try {
                        $this->users->update([
                            'id'            => $user['id'],
                            'reset_token'   => $token,
                            'reset_expires' => $expires,
                        ]);

                        $resetLink = $this->baseUrl() . 'index.php?route=user/reset-password&token=' . urlencode($token);
                        $mailResult = $this->mailer->sendPasswordResetEmail((string) $user['email'], (string) $user['name'], $resetLink);

                        $this->flash->set('last_password_reset_email', $mailResult['previewMeta']);
                        $_SESSION['reset_attempts'][] = $now;
                        $_SESSION['reset_cooldown'][$email] = $now;

                        $success = "A password recovery email has been successfully sent to {$email} via SMTP!";
                        $resetInfo = [
                            'link'  => $resetLink,
                            'email' => $email,
                        ];
                    } catch (\Throwable $e) {
                        $error = 'System error during password reset: ' . $e->getMessage();
                    }
                }
            }
        }

        return $this->view('auth/forgot_password', null, 'Forgot Password - Tokusatsu Universe', [
            'error'     => $error,
            'success'   => $success,
            'resetInfo' => $resetInfo,
        ]);
    }

    public function resetPassword(): array
    {
        $error = '';
        $success = '';
        $token = trim((string) ($_GET['token'] ?? $_POST['token'] ?? ''));
        $validToken = false;
        $targetUser = null;

        if ($token === '') {
            $error = 'Password reset token is missing!';
        } else {
            $targetUser = $this->users->findByResetToken($token);

            if ($targetUser && !empty($targetUser['reset_expires'])) {
                if (AuthContext::isAdminRecord($targetUser)) {
                    $error = 'Access Denied: Administrative accounts cannot be reset via public web token. Please contact the System Boss or execute server console recovery.';
                } elseif (strtotime((string) $targetUser['reset_expires']) >= time()) {
                    $validToken = true;
                } else {
                    $error = 'This password reset link has expired! Please request a new reset link.';
                }
            } else {
                $error = 'Invalid or non-existent password reset token!';
            }
        }

        if ($validToken && $this->isPost()) {
            $this->security->validateCsrfRequest();

            $password = (string) ($_POST['password'] ?? '');
            $confirmPassword = (string) ($_POST['confirm_password'] ?? '');

            if (strlen($password) < 6) {
                $error = 'New password must be at least 6 characters long!';
            } elseif ($password !== $confirmPassword) {
                $error = 'Confirm password does not match!';
            } else {
                try {
                    $this->users->update([
                        'id'            => $targetUser['id'],
                        'password'      => password_hash($password, PASSWORD_DEFAULT),
                        'reset_token'   => null,
                        'reset_expires' => null,
                    ]);

                    $this->redirect('index.php?route=user/login&status=reset_success');
                } catch (\Throwable $e) {
                    $error = 'Failed to update password: ' . $e->getMessage();
                }
            }
        }

        return $this->view('auth/reset_password', null, 'Reset Password - Tokusatsu Universe', [
            'error'      => $error,
            'success'    => $success,
            'token'      => $token,
            'validToken' => $validToken,
        ]);
    }
}
