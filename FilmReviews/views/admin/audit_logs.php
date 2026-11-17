<?php
$logs = is_array($logs ?? null) ? $logs : [];
$scope = (($scope ?? 'reviewer') === 'admin') ? 'admin' : 'reviewer';
$filterAction = (string) ($filterAction ?? 'all');
$activeActions = is_array($activeActions ?? null) ? $activeActions : [];
$reviewerTotalCount = (int) ($reviewerTotalCount ?? 0);
$adminTotalCount = (int) ($adminTotalCount ?? 0);

$scopeLabel = ($scope === 'admin') ? 'Administrator Activities' : 'Reviewers Activities';
$exportQuery = ['scope' => $scope];
if ($filterAction !== '' && $filterAction !== 'all') {
    $exportQuery['action'] = $filterAction;
}
$exportUrl = $viewHelper->routeUrl('admin/export-audit-logs', $exportQuery);
?>

<div class="row align-items-center mb-4">
    <div class="col-md-3 d-none d-md-block"></div>
    <div class="col-12 col-md-6 text-center">
        <h2 class="text-danger fw-bold m-0 text-center">SYSTEM AUDIT TRAIL &amp; LOGS</h2>
    </div>
    <div class="col-12 col-md-3 text-center text-md-end mt-2 mt-md-0">
        <a href="<?= $viewHelper->routeUrl('admin/dashboard') ?>" class="btn btn-secondary fw-bold shadow-sm">
            &#8592; Back to Dashboard
        </a>
    </div>
</div>

<?php if (!empty($message)): ?>
    <div class="alert alert-success fw-bold shadow-sm alert-dismissible fade show" role="alert">
        <?= $viewHelper->e($message) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<?php if (!empty($error)): ?>
    <div class="alert alert-danger fw-bold shadow-sm alert-dismissible fade show" role="alert">
        <?= $viewHelper->e($error) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<div class="card admin-card p-3 p-md-4 shadow mb-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3 pb-3 border-bottom border-secondary">
        <div class="d-flex flex-wrap align-items-center gap-3">
            <a href="<?= $viewHelper->routeUrl('admin/audit-logs', ['scope' => 'reviewer']) ?>"
               class="btn audit-scope-btn audit-btn-reviewer <?= ($scope === 'reviewer') ? 'active-scope' : '' ?> d-inline-flex align-items-center gap-2 px-4 py-2"
               style="border-radius: 0 !important;">
                <span>Reviewers Activities</span>
                <span class="audit-count-badge"><?= $reviewerTotalCount ?></span>
            </a>

            <a href="<?= $viewHelper->routeUrl('admin/audit-logs', ['scope' => 'admin']) ?>"
               class="btn audit-scope-btn audit-btn-admin <?= ($scope === 'admin') ? 'active-scope' : '' ?> d-inline-flex align-items-center gap-2 px-4 py-2"
               style="border-radius: 0 !important;">
                <span>Administrator Activities</span>
                <span class="audit-count-badge"><?= $adminTotalCount ?></span>
            </a>
        </div>

        <div>
            <a href="<?= $viewHelper->e($exportUrl) ?>"
               class="btn btn-sm audit-scope-btn audit-btn-deleted d-inline-flex align-items-center gap-2 px-3 py-2"
               style="border-radius: 0 !important;"
               title="Export <?= $viewHelper->e($scopeLabel) ?> as a .txt file to view in Notepad">
                <span>Export</span>
            </a>
        </div>
    </div>

    <div class="d-flex flex-wrap justify-content-between align-items-end gap-3">
        <div class="flex-grow-1" style="max-width: 580px;">
            <label class="form-label text-warning fw-bold small mb-2 d-block">
                Filter by Action Type (<?= $viewHelper->e($scopeLabel) ?>):
            </label>
            <form method="GET" action="<?= $viewHelper->e($baseUrl . 'index.php') ?>" class="d-flex align-items-center gap-2 m-0">
                <input type="hidden" name="route" value="admin/audit-logs">
                <input type="hidden" name="scope" value="<?= $viewHelper->e($scope) ?>">
                <?= $viewHelper->tabInput() ?>
                <select name="action" class="form-select form-select-sm bg-dark text-white border-secondary" style="min-width: 280px; height: 38px;" data-auto-submit="true">
                    <option value="all" <?= ($filterAction === 'all' || $filterAction === '') ? 'selected' : '' ?>>
                        -- Show All <?= $viewHelper->e($scopeLabel) ?> (<?= count($logs) ?>) --
                    </option>
                    <?php foreach ($activeActions as $act): ?>
                        <option value="<?= $viewHelper->e($act) ?>" <?= ($filterAction === $act) ? 'selected' : '' ?>>
                            <?= $viewHelper->e($act) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <a href="<?= $viewHelper->routeUrl('admin/audit-logs', ['scope' => $scope]) ?>"
                   class="btn btn-sm audit-btn-reset fw-bold shadow-sm d-inline-flex align-items-center justify-content-center text-nowrap"
                   style="height: 38px; padding: 0 16px;">
                    Reset Filter
                </a>
            </form>
        </div>

        <div>
            <form method="POST"
                  action="<?= $viewHelper->routeUrl('admin/prune-orphan-images', [], false) ?>"
                  class="m-0"
                  data-confirm="System Boss Confirmation: Are you sure you want to scan and prune all unreferenced orphan storage files? Protected system assets and in-use media will be preserved.">
                <?= $viewHelper->csrfInput() ?>
                <?= $viewHelper->tabInput() ?>
                <button type="submit" class="btn btn-sm btn-outline-warning fw-bold shadow-sm d-inline-flex align-items-center justify-content-center text-nowrap" style="height: 38px; padding: 0 18px;" title="Scan and delete unreferenced image files from server storage">
                    Prune Orphan Storage
                </button>
            </form>
        </div>
    </div>
</div>

<div class="card admin-card p-4 shadow">
    <div class="table-responsive">
        <table class="table table-dark table-hover align-middle mb-0">
            <thead class="table-secondary text-dark">
                <tr>
                    <th style="width: 85px;">ID</th>
                    <th style="width: 170px;">Timestamp</th>
                    <th style="width: 180px;">Operator / Role</th>
                    <th style="width: 175px;">Action</th>
                    <th>Audit Details</th>
                    <th style="width: 130px;">IP Address</th>
                    <th style="width: 110px;" class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($logs)): ?>
                    <tr>
                        <td colspan="7" class="text-center text-light py-5">
                            No audit log entries recorded under <strong class="text-info"><?= $viewHelper->e($scopeLabel) ?></strong><?php if ($filterAction !== '' && $filterAction !== 'all'): ?> for action type: <strong class="text-warning"><?= $viewHelper->e($filterAction) ?></strong><?php endif; ?>.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($logs as $log): ?>
                        <?php
                            $logId = (int) ($log['id'] ?? 0);
                            $roleInt = (int) ($log['role_int'] ?? ($log['user_role'] ?? 0));
                            $badgeClass = (string) ($log['badge_class'] ?? 'bg-secondary text-white');
                            $roleLabel = (string) ($log['role_label'] ?? 'Reviewer / Member');
                        ?>
                        <tr>
                            <td>
                                <span class="audit-id-badge">#<?= $logId ?></span>
                            </td>
                            <td class="small text-white-50">
                                <?= $viewHelper->e($log['created_at_formatted'] ?? 'N/A') ?>
                            </td>
                            <td>
                                <div class="fw-bold text-white small"><?= $viewHelper->e($log['user_name'] ?? '') ?></div>
                                <?php if ($roleInt === 1): ?>
                                    <span class="badge bg-danger py-0 px-2" style="font-size: 0.68rem;"><?= $viewHelper->e($roleLabel) ?></span>
                                <?php elseif ($roleInt >= 2): ?>
                                    <span class="badge bg-warning text-dark py-0 px-2" style="font-size: 0.68rem;"><?= $viewHelper->e($roleLabel) ?></span>
                                <?php else: ?>
                                    <span class="badge bg-secondary py-0 px-2" style="font-size: 0.68rem;"><?= $viewHelper->e($roleLabel) ?></span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge <?= $viewHelper->e($badgeClass) ?> fw-bold py-1 px-2" style="font-size: 0.75rem;">
                                    <?= $viewHelper->e($log['action'] ?? '') ?>
                                </span>
                            </td>
                            <td class="small text-light">
                                <?= $viewHelper->e($log['details'] ?? '') ?>
                            </td>
                            <td class="font-monospace small text-white-50">
                                <?= $viewHelper->e($log['ip_address'] ?? '') ?>
                            </td>
                            <td class="text-end text-nowrap">
                                <form method="POST"
                                      action="<?= $viewHelper->routeUrl('admin/delete-audit-log', [], false) ?>"
                                      class="d-inline m-0 p-0"
                                      data-confirm="System Boss Confirmation: Are you sure you want to delete audit log entry #<?= $logId ?>?">
                                    <?= $viewHelper->csrfInput() ?>
                                    <?= $viewHelper->tabInput() ?>
                                    <input type="hidden" name="log_id" value="<?= $logId ?>">
                                    <input type="hidden" name="current_scope" value="<?= $viewHelper->e($scope) ?>">
                                    <input type="hidden" name="current_filter" value="<?= $viewHelper->e($filterAction) ?>">
                                    <button type="submit" class="btn btn-outline-danger btn-sm" title="Delete audit log entry (Boss Only)">
                                        Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
