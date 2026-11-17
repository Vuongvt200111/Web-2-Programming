<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Core\AuthMiddleware;
use App\Core\Config;
use App\Core\Flash;
use App\Core\Response;
use App\Models\AuditLogModel;
use App\Models\FilmModel;
use App\Models\ReviewModel;
use App\Models\UserModel;
use App\Presenters\AuditLogPresenter;
use App\Services\AuditLogger;
use App\Services\MediaService;
use App\Services\SecurityService;

/**
 * System Boss Exclusive Audit Trail Inspection, Notepad Export, Deletion, and Orphan Media Garbage Collector.
 */
final class AuditLogController extends BaseController
{
    private AuditLogModel $auditLogs;
    private UserModel $users;
    private FilmModel $films;
    private ReviewModel $reviews;
    private MediaService $media;

    public function __construct(
        Config $config,
        Response $response,
        Flash $flash,
        SecurityService $security,
        AuthMiddleware $auth,
        AuditLogger $auditLogger,
        AuditLogModel $auditLogs,
        UserModel $users,
        FilmModel $films,
        ReviewModel $reviews,
        MediaService $media
    ) {
        parent::__construct($config, $response, $flash, $security, $auth, $auditLogger);
        $this->auditLogs = $auditLogs;
        $this->users = $users;
        $this->films = $films;
        $this->reviews = $reviews;
        $this->media = $media;
    }

    public function index(): array
    {
        $this->auth->requireBoss('admin/audit-logs');

        $rawScope = isset($_GET['scope']) ? (string) $_GET['scope'] : null;
        $rawAction = (string) ($_GET['filter_action'] ?? ($_GET['action'] ?? 'all'));
        $auditData = $this->auditLogs->getFilteredData($rawScope, $rawAction);

        $enrichedLogs = [];
        foreach ($auditData['logs'] as $log) {
            $roleInt = (int) ($log['user_role'] ?? 0);
            $log['badge_class'] = AuditLogPresenter::getBadgeClass((string) ($log['action'] ?? ''));
            $log['role_label'] = AuditLogPresenter::getRoleLabel($roleInt);
            $log['role_int'] = $roleInt;
            $log['created_at_formatted'] = !empty($log['created_at'])
                ? date('d/m/Y H:i:s', strtotime((string) $log['created_at']))
                : 'N/A';
            $enrichedLogs[] = $log;
        }
        $auditData['logs'] = $enrichedLogs;
        $auditData['message'] = (string) $this->flash->pull('success_message', '');
        $auditData['error'] = (string) $this->flash->pull('error_message', '');

        return $this->view('admin/audit_logs', 'layouts/admin', 'Security Audit Trail & Admin Logs', $auditData);
    }

    public function export(): never
    {
        $auth = $this->auth->requireBoss('admin/export-audit-logs');

        $rawScope = isset($_GET['scope']) ? (string) $_GET['scope'] : null;
        $rawAction = (string) ($_GET['filter_action'] ?? ($_GET['action'] ?? 'all'));
        $auditData = $this->auditLogs->getFilteredData($rawScope, $rawAction);

        $scope = (string) $auditData['scope'];
        $content = AuditLogPresenter::formatExportText(
            $scope,
            (string) $auditData['filterAction'],
            $auditData['logs'],
            $auth->name() !== '' ? $auth->name() : 'Admin (System Boss)'
        );

        $prefix = ($scope === 'admin') ? 'Administrator_Activities_Report_' : 'Reviewers_Activities_Report_';
        $filename = $prefix . date('Y-m-d_H-i-s') . '.txt';

        $this->response->downloadText($filename, $content);
    }

    public function delete(): never
    {
        $this->auth->requireBoss('admin/delete-audit-log');

        $currentScope = strtolower(trim((string) ($_POST['current_scope'] ?? 'reviewer')));
        if (!in_array($currentScope, ['reviewer', 'admin'], true)) {
            $currentScope = 'reviewer';
        }
        $currentFilter = trim((string) ($_POST['current_filter'] ?? ''));

        $redirectPath = 'index.php?route=admin/audit-logs&scope=' . urlencode($currentScope);
        if ($currentFilter !== '' && $currentFilter !== 'all') {
            $redirectPath .= '&action=' . urlencode($currentFilter);
        }

        if ($this->isPost() && isset($_POST['log_id'])) {
            $this->security->validateCsrfRequest($this->baseUrl() . $redirectPath);
            $logId = (int) $_POST['log_id'];
            $targetLog = $this->auditLogs->findById($logId);

            if ($targetLog) {
                $this->auditLogs->delete($logId);
                $this->flash->set('success_message', "Audit log entry #{$logId} has been permanently deleted.");
            } else {
                $this->flash->set('error_message', "Audit log entry #{$logId} not found.");
            }
        }

        $this->redirect($redirectPath);
    }

    public function pruneOrphanImages(): never
    {
        $this->auth->requireBoss('admin/prune-orphan-images');
        $this->auth->requirePost('Method Not Allowed. Storage pruning requires POST request.');
        $this->security->validateCsrfRequest($this->baseUrl() . 'index.php?route=admin/audit-logs');

        $activeFilenames = array_merge(
            $this->users->getActiveAvatarFilenames(),
            $this->films->getActiveMediaFilenames(),
            $this->reviews->getActiveTicketProofs()
        );

        $stats = $this->media->pruneOrphans($activeFilenames);

        $this->auditLogger->log(
            'PRUNE_ORPHAN_FILES',
            "The System Boss pruned {$stats['deletedCount']} orphan storage files (freed {$stats['freedFormatted']})"
        );
        $this->flash->set(
            'success_message',
            "Storage garbage collection completed: Cleaned {$stats['deletedCount']} orphan files, freed {$stats['freedFormatted']}."
        );

        $this->redirect('index.php?route=admin/audit-logs');
    }
}
