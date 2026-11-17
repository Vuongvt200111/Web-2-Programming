<?php
$users = is_array($users ?? null) ? $users : [];
$isBoss = !empty($isBoss);
?>

<div class="card admin-card p-4 border-top border-4 border-info shadow-lg">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h3 class="text-info fw-bold m-0">MANAGE SYSTEM ACCOUNTS</h3>
            <small class="text-white-50">Hierarchical Role-Based Access Control (RBAC)</small>
        </div>
        <span class="badge bg-info text-dark fs-6 px-3 py-2">System recorded: <?= count($users) ?> accounts</span>
    </div>

    <?php if (!empty($message)): ?>
        <div class="alert alert-warning alert-dismissible fade show fw-bold shadow-sm">
            <?= $viewHelper->e($message) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="table-responsive rounded shadow-sm">
        <table class="table table-dark table-hover table-bordered align-middle border-secondary m-0 text-center">
            <thead class="table-secondary text-dark fw-bold">
                <tr>
                    <th width="5%">ID</th>
                    <th width="15%">Display Name</th>
                    <th width="18%">Email</th>
                    <th width="12%">Join Date</th>
                    <th width="11%">Role</th>
                    <th width="13%">Status</th>
                    <th width="26%">Hierarchy Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($users)): ?>
                    <tr><td colspan="7" class="text-center text-secondary py-5 fw-bold">No data available.</td></tr>
                <?php else: ?>
                    <?php foreach ($users as $u): ?>
                        <?php
                            $targetId = (int) ($u['id'] ?? 0);
                            $targetRole = (int) ($u['role_int'] ?? ($u['role'] ?? 0));
                            $targetName = (string) ($u['name'] ?? 'No name');
                            $isTargetBoss = !empty($u['is_target_boss']);
                            $isSelf = !empty($u['is_self']);
                            $isTargetBlocked = !empty($u['is_target_blocked']);
                            $isDeleted = !empty($u['is_deleted_flag']);
                            $blockedUntilFmt = $u['blocked_until_formatted'] ?? null;
                        ?>
                        <tr class="<?= $isDeleted ? 'opacity-75' : ($isTargetBlocked ? 'bg-danger bg-opacity-10' : '') ?>">
                            <td class="fw-bold"><?= $targetId ?></td>
                            <td class="text-warning fw-bold"><?= $viewHelper->e($targetName) ?></td>
                            <td class="small text-light"><?= $viewHelper->e($u['email'] ?? '') ?></td>
                            <td class="text-secondary small">
                                <?= $viewHelper->e($u['created_at_formatted'] ?? 'N/A') ?>
                            </td>

                            <td>
                                <?php if ($isTargetBoss): ?>
                                    <span class="badge bg-danger px-2 py-1">System Boss</span>
                                <?php elseif ($targetRole >= 2): ?>
                                    <span class="badge bg-warning text-dark px-2 py-1">Sub-Admin</span>
                                <?php else: ?>
                                    <span class="badge bg-info text-dark px-2 py-1">Member</span>
                                <?php endif; ?>
                            </td>

                            <td>
                                <?php if ($isDeleted): ?>
                                    <span class="badge bg-secondary">Deactivated</span>
                                <?php elseif ($isTargetBlocked): ?>
                                    <span class="badge bg-danger">Blocked</span>
                                    <?php if (!empty($blockedUntilFmt)): ?>
                                        <div class="small text-warning mt-1" style="font-size: 0.68rem;" title="Automatic unblock time">
                                            Until: <?= $viewHelper->e($blockedUntilFmt) ?>
                                        </div>
                                        <div class="text-white-50" style="font-size: 0.62rem;">(Auto-unblocks)</div>
                                    <?php else: ?>
                                        <div class="text-white-50 mt-1" style="font-size: 0.65rem;">(Indefinite)</div>
                                    <?php endif; ?>
                                    <?php if (!empty($u['block_reason'])): ?>
                                        <div class="small text-info text-truncate mt-1 mx-auto" style="max-width: 130px; font-size: 0.65rem;" title="Reason: <?= $viewHelper->e($u['block_reason']) ?>">
                                            <?= $viewHelper->e($u['block_reason']) ?>
                                        </div>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="badge bg-success">Active</span>
                                <?php endif; ?>
                            </td>

                            <td>
                                <div class="d-flex flex-column gap-1 justify-content-center align-items-center">
                                    <?php if ($isDeleted): ?>
                                        <?php if ($targetRole >= 1 && !$isBoss): ?>
                                            <span class="text-muted small">Restorable by Boss only</span>
                                        <?php else: ?>
                                            <form method="POST"
                                                  action="<?= $viewHelper->routeUrl('admin/restore-user', [], false) ?>"
                                                  class="w-100"
                                                  data-confirm="Reactivate account access for <?= $viewHelper->e($targetName) ?>?">
                                                <?= $viewHelper->csrfInput() ?>
                                                <?= $viewHelper->tabInput() ?>
                                                <input type="hidden" name="id" value="<?= $targetId ?>">
                                                <button type="submit" class="btn btn-sm btn-success fw-bold shadow-sm w-100 py-1" style="font-size: 0.75rem;">
                                                    ♻️ Restore Access
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    <?php endif; ?>

                                    <?php if ($isTargetBoss): ?>
                                        <span class="badge bg-dark border border-danger text-danger w-100 py-1" style="font-size: 0.75rem;">
                                            Root Boss (Protected)
                                        </span>
                                    <?php elseif ($isSelf): ?>
                                        <span class="badge bg-secondary w-100 py-1" style="font-size: 0.75rem;">
                                            Your Active Account
                                        </span>
                                    <?php elseif ($isBoss): ?>
                                        <div class="d-flex gap-1 w-100">
                                            <?php if ($targetRole >= 2): ?>
                                                <form method="POST"
                                                      action="<?= $viewHelper->routeUrl('admin/manage-users', [], false) ?>"
                                                      class="flex-fill"
                                                      data-confirm="Demote Sub-Admin <?= $viewHelper->e($targetName) ?> to regular Member?">
                                                    <?= $viewHelper->csrfInput() ?>
                                                    <?= $viewHelper->tabInput() ?>
                                                    <input type="hidden" name="demote_id" value="<?= $targetId ?>">
                                                    <button type="submit" class="btn btn-sm btn-outline-warning w-100 py-1" style="font-size: 0.72rem;">
                                                        Demote
                                                    </button>
                                                </form>
                                            <?php elseif ($targetRole === 0): ?>
                                                <form method="POST"
                                                      action="<?= $viewHelper->routeUrl('admin/manage-users', [], false) ?>"
                                                      class="flex-fill"
                                                      data-confirm="Promote member <?= $viewHelper->e($targetName) ?> to Sub-Admin?">
                                                    <?= $viewHelper->csrfInput() ?>
                                                    <?= $viewHelper->tabInput() ?>
                                                    <input type="hidden" name="promote_id" value="<?= $targetId ?>">
                                                    <button type="submit" class="btn btn-sm btn-outline-info w-100 py-1" style="font-size: 0.72rem;">
                                                        Promote
                                                    </button>
                                                </form>
                                            <?php endif; ?>

                                            <?php if ($isTargetBlocked): ?>
                                                <form method="POST"
                                                      action="<?= $viewHelper->routeUrl('admin/manage-users', [], false) ?>"
                                                      class="flex-fill"
                                                      data-confirm="Unblock user account <?= $viewHelper->e($targetName) ?>?">
                                                    <?= $viewHelper->csrfInput() ?>
                                                    <?= $viewHelper->tabInput() ?>
                                                    <input type="hidden" name="unblock_id" value="<?= $targetId ?>">
                                                    <button type="submit" class="btn btn-sm btn-success w-100 py-1" style="font-size: 0.72rem;">
                                                        Unblock
                                                    </button>
                                                </form>
                                            <?php else: ?>
                                                <button type="button" class="btn btn-sm btn-outline-warning flex-fill py-1" style="font-size: 0.72rem;"
                                                        data-bs-toggle="modal" data-bs-target="#blockUserModal"
                                                        data-user-id="<?= $targetId ?>" data-user-name="<?= $viewHelper->e($targetName) ?>">
                                                    Block
                                                </button>
                                            <?php endif; ?>

                                            <form method="POST"
                                                  action="<?= $viewHelper->routeUrl('admin/manage-users', [], false) ?>"
                                                  class="flex-fill"
                                                  data-confirm="The System Boss confirms deleting account <?= $viewHelper->e($targetName) ?>?">
                                                <?= $viewHelper->csrfInput() ?>
                                                <?= $viewHelper->tabInput() ?>
                                                <input type="hidden" name="delete_id" value="<?= $targetId ?>">
                                                <button type="submit" class="btn btn-sm btn-danger w-100 py-1" style="font-size: 0.72rem;">
                                                    Delete
                                                </button>
                                            </form>
                                        </div>
                                    <?php else: ?>
                                        <?php if ($targetRole >= 1): ?>
                                            <span class="badge bg-dark border border-secondary text-secondary w-100 py-1" style="font-size: 0.75rem;">
                                                Peer Admin (Protected)
                                            </span>
                                        <?php else: ?>
                                            <div class="d-flex gap-1 w-100">
                                                <?php if ($isTargetBlocked): ?>
                                                    <form method="POST"
                                                          action="<?= $viewHelper->routeUrl('admin/manage-users', [], false) ?>"
                                                          class="flex-fill"
                                                          data-confirm="Unblock member account <?= $viewHelper->e($targetName) ?>?">
                                                        <?= $viewHelper->csrfInput() ?>
                                                        <?= $viewHelper->tabInput() ?>
                                                        <input type="hidden" name="unblock_id" value="<?= $targetId ?>">
                                                        <button type="submit" class="btn btn-sm btn-success w-100 py-1" style="font-size: 0.72rem;">
                                                            Unblock
                                                        </button>
                                                    </form>
                                                <?php else: ?>
                                                    <button type="button" class="btn btn-sm btn-outline-warning flex-fill py-1" style="font-size: 0.72rem;"
                                                            data-bs-toggle="modal" data-bs-target="#blockUserModal"
                                                            data-user-id="<?= $targetId ?>" data-user-name="<?= $viewHelper->e($targetName) ?>">
                                                        Block
                                                    </button>
                                                <?php endif; ?>

                                                <form method="POST"
                                                      action="<?= $viewHelper->routeUrl('admin/manage-users', [], false) ?>"
                                                      class="flex-fill"
                                                      data-confirm="Are you sure you want to delete member <?= $viewHelper->e($targetName) ?>?">
                                                    <?= $viewHelper->csrfInput() ?>
                                                    <?= $viewHelper->tabInput() ?>
                                                    <input type="hidden" name="delete_id" value="<?= $targetId ?>">
                                                    <button type="submit" class="btn btn-sm btn-outline-danger w-100 py-1" style="font-size: 0.72rem;">
                                                        Delete
                                                    </button>
                                                </form>
                                            </div>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        <a href="<?= $viewHelper->routeUrl('admin/dashboard') ?>" class="btn btn-secondary fw-bold shadow-sm">
            &#8592; Back to Dashboard
        </a>
    </div>
</div>

<!-- Modal: Block User Account (Temporary / Indefinite Suspension) -->
<div class="modal fade" id="blockUserModal" tabindex="-1" aria-labelledby="blockUserModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content bg-dark text-white border border-secondary shadow-lg">
            <form method="POST" action="<?= $viewHelper->routeUrl('admin/manage-users', [], false) ?>">
                <?= $viewHelper->csrfInput() ?>
                <?= $viewHelper->tabInput() ?>
                <input type="hidden" name="block_id" id="modal_block_user_id" value="">

                <div class="modal-header border-secondary">
                    <h5 class="modal-title text-warning fw-bold" id="blockUserModalLabel">
                        Block User Account
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <div class="alert alert-warning py-2 small mb-3 border-0 text-dark">
                        You are configuring a suspension on: <strong id="modal_block_user_name">User</strong>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-warning small fw-bold">Select Suspension Duration:</label>
                        <select name="duration" class="form-select bg-dark text-white border-secondary" required>
                            <option value="1_hour">1 Hour (Temporary cool-down)</option>
                            <option value="24_hours" selected>24 Hours (1 Day)</option>
                            <option value="3_days">3 Days</option>
                            <option value="7_days">7 Days (1 Week)</option>
                            <option value="14_days">14 Days (2 Weeks)</option>
                            <option value="30_days">30 Days (1 Month)</option>
                            <option value="indefinite">Indefinite / Until manually unblocked</option>
                        </select>
                        <div class="form-text text-white-50 small">
                            Accounts with a duration will automatically restore access when the timer expires.
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-warning small fw-bold">Reason for Suspension (Optional):</label>
                        <input type="text" name="block_reason" class="form-control bg-dark text-white border-secondary" placeholder="e.g. Violation of review policies / spam / temporary cooldown">
                    </div>
                </div>

                <div class="modal-footer border-secondary">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning btn-sm fw-bold px-3">
                        Confirm Account Block
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="<?= $viewHelper->asset('public/js/admin_users.js') ?>"></script>
