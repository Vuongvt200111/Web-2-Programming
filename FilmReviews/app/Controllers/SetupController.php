<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\AuthMiddleware;
use App\Core\Config;
use App\Core\Flash;
use App\Core\Response;
use App\Models\UserModel;
use App\Services\AuditLogger;
use App\Services\SecurityService;

/**
 * First-time System Boss provisioning controller (locked once an admin exists).
 */
final class SetupController extends BaseController
{
    private UserModel $users;

    public function __construct(
        Config $config,
        Response $response,
        Flash $flash,
        SecurityService $security,
        AuthMiddleware $auth,
        AuditLogger $auditLogger,
        UserModel $users
    ) {
        parent::__construct($config, $response, $flash, $security, $auth, $auditLogger);
        $this->users = $users;
    }

    public function setupAdmin(): array
    {
        if ($this->users->hasAnyAdministrator()) {
            $this->response->abort(
                403,
                "<h2 style='color:red; text-align:center; margin-top:50px;'>[ACCESS DENIED] The system already has an active Administrator (System Boss). The website has been permanently locked for security reasons. Please access the Admin Dashboard to manage administrators.</h2>"
            );
        }

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
            } else {
                try {
                    $bossId = (int) $this->users->insert([
                        'name'              => $name,
                        'email'             => $email,
                        'password'          => password_hash($rawPassword, PASSWORD_DEFAULT),
                        'role'              => 1,
                        'credibility_score' => 9999,
                        'badge'             => 'System Boss',
                    ]);

                    $this->auditLogger->log(
                        'SETUP_ADMIN',
                        "Initialized System Boss account '{$name}' ({$email})",
                        $bossId,
                        1,
                        $name
                    );
                    $success = 'Setup System Boss successful! Please proceed to the Login page.';
                } catch (\Throwable $e) {
                    $error = 'Database Error: ' . $e->getMessage();
                }
            }
        }

        return $this->view('auth/setup_admin', null, 'Setup Admin - Tokusatsu Universe', [
            'error'   => $error,
            'success' => $success,
        ]);
    }
}
