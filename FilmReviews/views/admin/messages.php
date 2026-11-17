<?php
$messages = is_array($messages ?? null) ? $messages : [];
?>

<div class="card admin-card p-4 border-top border-4 border-success shadow-lg">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="text-success fw-bold m-0">MANAGE MESSAGE &amp; CUSTOMER SUPPORT</h3>
        <span class="badge bg-success text-dark fs-6">Total: <?= count($messages) ?> Messages</span>
    </div>

    <?php if (!empty($alert) && is_array($alert)): ?>
        <div class="alert alert-<?= $viewHelper->e($alert['type'] ?? 'info') ?> fw-bold shadow-sm alert-dismissible fade show">
            <?= $viewHelper->e($alert['message'] ?? '') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="table-responsive shadow rounded">
        <table class="table table-dark table-hover table-bordered align-middle border-secondary m-0">
            <thead class="table-secondary text-dark">
                <tr>
                    <th class="text-center" width="5%">ID</th>
                    <th width="15%">Sender</th>
                    <th width="30%">Message Content (Reviewer)</th>
                    <th width="35%">Admin Response</th>
                    <th class="text-center" width="15%">Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($messages)): ?>
                    <tr><td colspan="5" class="text-center text-secondary py-5 fw-bold fs-5">Message box is empty.</td></tr>
                <?php else: ?>
                    <?php foreach ($messages as $msg): ?>
                        <?php
                            $msgId = (int) ($msg['id'] ?? 0);
                            $status = (string) ($msg['status'] ?? 'pending');
                            $updatedAtFormatted = !empty($msg['updated_at'])
                                ? date('d/m/Y H:i', strtotime((string) $msg['updated_at']))
                                : '';
                        ?>
                        <tr>
                            <td class="text-center fw-bold"><?= $msgId ?></td>
                            <td>
                                <div class="fw-bold text-warning"><?= $viewHelper->e($msg['name'] ?? 'Account deleted') ?></div>
                                <div class="text-info small"><?= $viewHelper->e($msg['email'] ?? '') ?></div>
                                <div class="text-secondary small mt-1" style="font-size: 0.75rem;">
                                    <?= $viewHelper->e($msg['created_at_formatted'] ?? 'N/A') ?>
                                </div>
                            </td>

                            <td>
                                <div class="p-2 bg-secondary bg-opacity-25 rounded text-light border-start border-3 border-warning">
                                    <?= nl2br($viewHelper->e($msg['user_message'] ?? '')) ?>
                                </div>
                            </td>

                            <td>
                                <?php if ($status === 'pending'): ?>
                                    <form method="POST" action="<?= $viewHelper->routeUrl('admin/messages', [], false) ?>">
                                        <?= $viewHelper->csrfInput() ?>
                                        <?= $viewHelper->tabInput() ?>
                                        <input type="hidden" name="reply_id" value="<?= $msgId ?>">
                                        <textarea name="admin_reply" rows="2" class="form-control form-control-sm bg-dark text-light border-info mb-2" placeholder="Enter your reply..." required></textarea>
                                        <button type="submit" class="btn btn-sm btn-info fw-bold w-100">Send Feedback</button>
                                    </form>
                                <?php else: ?>
                                    <div class="p-2 bg-success bg-opacity-10 rounded text-light border-start border-3 border-success mb-2">
                                        <span class="text-success fw-bold small">You have answered:</span><br>
                                        <?= nl2br($viewHelper->e($msg['admin_reply'] ?? '')) ?>
                                    </div>
                                    <?php if ($updatedAtFormatted !== ''): ?>
                                        <div class="text-secondary small text-end">
                                            Time: <?= $viewHelper->e($updatedAtFormatted) ?>
                                        </div>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </td>

                            <td class="text-center">
                                <?php if ($status === 'pending'): ?>
                                    <span class="badge bg-warning text-dark mb-2 d-block shadow-sm">Pending</span>
                                <?php else: ?>
                                    <span class="badge bg-success mb-2 d-block shadow-sm">Replied</span>
                                <?php endif; ?>

                                <form method="POST"
                                      action="<?= $viewHelper->routeUrl('admin/messages', [], false) ?>"
                                      data-confirm="Delete this message from the database?">
                                    <?= $viewHelper->csrfInput() ?>
                                    <?= $viewHelper->tabInput() ?>
                                    <input type="hidden" name="delete_id" value="<?= $msgId ?>">
                                    <button type="submit" class="btn btn-sm btn-danger fw-bold neon-border w-100 shadow-sm">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        <a href="<?= $viewHelper->routeUrl('admin/dashboard') ?>" class="btn btn-secondary fw-bold shadow-sm">&#8592; Back to Dashboard</a>
    </div>
</div>
