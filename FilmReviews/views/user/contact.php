<link rel="stylesheet" href="<?= $viewHelper->asset('public/css/contact_us.css?v=3.1') ?>">

<div class="contact-admin-wrapper py-5">
    <div class="container">
        <div class="row justify-content-center w-100 m-0">
            <div class="col-md-10 col-lg-9">
                <div class="card contact-card shadow-lg mb-5">
                    <div class="contact-header">
                        💬 CONTACT ADMINISTRATOR
                    </div>

                    <div class="card-body p-4 p-md-5">
                        <?php if (!empty($error_msg)): ?>
                            <div class="alert alert-danger fw-bold py-2 shadow-sm"><?= $viewHelper->e($error_msg) ?></div>
                        <?php endif; ?>

                        <?php if (!empty($success_msg)): ?>
                            <div class="alert alert-success fw-bold py-2 shadow-sm"><?= $viewHelper->e($success_msg) ?></div>
                        <?php endif; ?>

                        <form action="<?= $viewHelper->routeUrl('user/contact', [], false) ?>" method="POST"
                              data-disable-on-submit="true" data-loading-text="Sending...">
                            <?= $viewHelper->csrfInput() ?>
                            <?= $viewHelper->tabInput() ?>

                            <div class="mb-4">
                                <label class="contact-label text-warning fw-bold">Sending Account:</label>
                                <input type="text" class="form-control contact-input-disabled bg-dark text-white border-secondary"
                                       value="<?= $viewHelper->e($currentUser['name'] ?? ($auth['name'] ?? 'User')) ?> (<?= $viewHelper->e($currentUser['email'] ?? ($auth['email'] ?? '')) ?>)" disabled>
                                <small class="text-white-50">Verified User Session ID: #<?= (int) ($auth['user_id'] ?? 0) ?></small>
                            </div>

                            <div class="mb-4">
                                <label class="contact-label text-warning fw-bold">Your Message / Inquiry:</label>
                                <textarea name="user_message" rows="4" class="form-control contact-textarea bg-dark text-white border-secondary"
                                          required minlength="1" maxlength="1000"
                                          placeholder="Enter your feedback, questions, or requests for the administrator..."></textarea>
                            </div>

                            <button type="submit" class="btn btn-warning fw-bold w-100 fs-5 text-dark shadow">
                                Send Inquiry to Admin
                            </button>
                        </form>
                    </div>
                </div>

                <div class="card bg-dark text-white border-secondary shadow-lg p-4">
                    <div class="d-flex justify-content-between align-items-center mb-4 border-bottom border-secondary pb-3 flex-wrap gap-2">
                        <div>
                            <h4 class="text-warning fw-bold m-0">MY SUPPORT INQUIRIES &amp; ADMIN REPLIES</h4>
                            <small class="text-white-50">Track your past inquiries and responses from the administration team</small>
                        </div>
                        <span class="badge bg-secondary fs-6 px-3 py-2">
                            Total: <?= !empty($userMessages) ? count($userMessages) : 0 ?>
                        </span>
                    </div>

                    <?php if (empty($userMessages)): ?>
                        <div class="text-center py-4">
                            <p class="text-muted fs-6 mb-0">You have not submitted any inquiries yet. Send a message above if you need assistance!</p>
                        </div>
                    <?php else: ?>
                        <div class="d-flex flex-column gap-3">
                            <?php foreach ($userMessages as $msg): ?>
                                <div class="reply-card p-4">
                                    <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap gap-2">
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="badge bg-dark border border-secondary text-light">#<?= (int) ($msg['id'] ?? 0) ?></span>
                                            <small class="text-white-50">Sent on: <?= $viewHelper->e($msg['created_at_formatted'] ?? '') ?></small>
                                        </div>

                                        <?php if (($msg['status'] ?? '') === 'replied'): ?>
                                            <span class="badge bg-success px-3 py-2 fw-bold">Admin Replied</span>
                                        <?php elseif (($msg['status'] ?? '') === 'seen'): ?>
                                            <span class="badge bg-info text-dark px-3 py-2 fw-bold">Seen by Admin</span>
                                        <?php else: ?>
                                            <span class="badge bg-warning text-dark px-3 py-2 fw-bold">Pending Review</span>
                                        <?php endif; ?>
                                    </div>

                                    <div class="user-msg-bubble mb-2">
                                        <div class="fw-bold text-warning small mb-1">Your Question:</div>
                                        <p class="mb-0 text-white" style="white-space: pre-wrap;"><?= $viewHelper->e($msg['user_message'] ?? '') ?></p>
                                    </div>

                                    <?php if (($msg['status'] ?? '') === 'replied' && !empty($msg['admin_reply'])): ?>
                                        <div class="admin-reply-bubble">
                                            <div class="d-flex justify-content-between align-items-center mb-1">
                                                <span class="fw-bold text-success small">Official Response from Administrator:</span>
                                                <small class="text-white-50"><?= $viewHelper->e($msg['updated_at_formatted'] ?? '') ?></small>
                                            </div>
                                            <p class="mb-0 text-white fs-6" style="white-space: pre-wrap;"><?= $viewHelper->e($msg['admin_reply']) ?></p>
                                        </div>
                                    <?php else: ?>
                                        <div class="text-white-50 small fst-italic mt-2 ps-2">
                                            Our support team is reviewing your inquiry. Please check back here for updates.
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
