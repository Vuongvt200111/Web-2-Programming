<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Core\AuthMiddleware;
use App\Core\Config;
use App\Core\Flash;
use App\Core\Response;
use App\Models\MessageModel;
use App\Services\AuditLogger;
use App\Services\SecurityService;

/**
 * Administrative User Inquiry & Feedback Response Controller.
 */
final class MessageController extends BaseController
{
    private MessageModel $messages;

    public function __construct(
        Config $config,
        Response $response,
        Flash $flash,
        SecurityService $security,
        AuthMiddleware $auth,
        AuditLogger $auditLogger,
        MessageModel $messages
    ) {
        parent::__construct($config, $response, $flash, $security, $auth, $auditLogger);
        $this->messages = $messages;
    }

    public function index(): array
    {
        $this->auth->requireAdmin();
        $alert = $this->flash->pull('admin_msg_alert', null);

        if ($this->isPost()) {
            $this->security->validateCsrfRequest($this->baseUrl() . 'index.php?route=admin/messages');

            if (isset($_POST['reply_id'])) {
                $replyId = (int) $_POST['reply_id'];
                $adminReply = trim((string) ($_POST['admin_reply'] ?? ''));

                if ($adminReply !== '') {
                    $this->messages->update([
                        'id'          => $replyId,
                        'admin_reply' => $adminReply,
                        'status'      => 'replied',
                        'updated_at'  => date('Y-m-d H:i:s'),
                    ]);
                    $this->flash->set('admin_msg_alert', [
                        'type'    => 'success',
                        'message' => 'User response successful!',
                    ]);
                }
            }

            if (isset($_POST['delete_id'])) {
                $deleteId = (int) $_POST['delete_id'];
                $this->messages->delete($deleteId);
                $this->flash->set('admin_msg_alert', [
                    'type'    => 'warning',
                    'message' => 'Message deleted successfully!',
                ]);
            }

            $this->redirect('index.php?route=admin/messages');
        }

        $messages = $this->messages->findAllWithUsers();
        foreach ($messages as &$m) {
            $m['created_at_formatted'] = !empty($m['created_at'])
                ? date('d/m/Y H:i', strtotime((string) $m['created_at']))
                : 'N/A';
        }
        unset($m);

        return $this->view('admin/messages', 'layouts/admin', 'Customer Messages', [
            'messages' => $messages,
            'alert'    => is_array($alert) ? $alert : null,
        ]);
    }
}
