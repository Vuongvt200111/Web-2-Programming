<link rel="stylesheet" href="<?= $viewHelper->asset('public/css/profile.css?v=3.0') ?>">

<?php
$userRole = (int) ($userInfo['role'] ?? 0);
$isBoss = !empty($isTargetBoss);
$isReviewer = !empty($isReviewerOnly);
$canChangePassword = !empty($canChangeOwnPassword);
$currentSection = (string) ($activeSection ?? 'profile');
$myFilmsList = $myFilms ?? [];
$avatarMeta = $profileAvatar ?? ['hasImage' => false, 'url' => null, 'initial' => 'U'];
?>

<div class="profile-container">
    <h2 class="profile-title">ACCOUNT INFORMATION</h2>
    <div class="profile-layout">
        <div class="profile-card">
            <?php if (!empty($avatarMeta['hasImage']) && !empty($avatarMeta['url'])): ?>
                <img src="<?= $viewHelper->e($avatarMeta['url']) ?>"
                     alt="Avatar of <?= $viewHelper->e($userInfo['name'] ?? '') ?>"
                     class="avatar">
            <?php else: ?>
                <div class="avatar d-flex align-items-center justify-content-center bg-dark text-warning border-3 border-warning fw-bold fs-1" style="border-radius: 50%;">
                    <?= $viewHelper->e($avatarMeta['initial'] ?? 'U') ?>
                </div>
            <?php endif; ?>

            <h4 class="username"><?= $viewHelper->e($userInfo['name'] ?? '') ?></h4>

            <?php if ($isBoss): ?>
                <span class="badge bg-danger p-2 fs-6">System Boss</span>

                <div class="profile-sidebar-menu">
                    <div class="sidebar-my-account-box">
                        <button type="button" id="my-account-toggle-btn" class="sidebar-top-btn sidebar-btn-my-account justify-content-center position-relative">
                            <span>My account</span>
                            <span id="my-account-arrow" style="font-size: 0.8rem; position: absolute; right: 14px;">▾</span>
                        </button>
                        <div id="my-account-submenu" class="sidebar-submenu">
                            <button type="button" id="nav-btn-profile" data-profile-section="profile" class="sidebar-sub-link <?= ($currentSection === 'profile') ? 'active' : '' ?>">
                                Profile Setting
                            </button>
                            <button type="button" id="nav-btn-password" data-profile-section="password" class="sidebar-sub-link <?= ($currentSection === 'password') ? 'active' : '' ?>">
                                Change Password
                            </button>
                        </div>
                    </div>
                </div>
            <?php elseif (!empty($isTargetAdmin)): ?>
                <span class="badge bg-warning text-dark p-2 fs-6">Sub-Admin</span>
            <?php else: ?>
                <span class="badge bg-success p-2 fs-6">Member</span>

                <div class="profile-sidebar-menu">
                    <a href="<?= $viewHelper->routeUrl('user/contact') ?>" class="sidebar-top-btn sidebar-btn-notification justify-content-center">
                        <span>💬 Notification</span>
                    </a>

                    <div class="sidebar-my-account-box">
                        <button type="button" id="my-account-toggle-btn" class="sidebar-top-btn sidebar-btn-my-account justify-content-center position-relative">
                            <span>My account</span>
                            <span id="my-account-arrow" style="font-size: 0.8rem; position: absolute; right: 14px;">▾</span>
                        </button>
                        <div id="my-account-submenu" class="sidebar-submenu">
                            <button type="button" id="nav-btn-profile" data-profile-section="profile" class="sidebar-sub-link <?= ($currentSection === 'profile') ? 'active' : '' ?>">
                                Profile Setting
                            </button>
                            <button type="button" id="nav-btn-films" data-profile-section="films" class="sidebar-sub-link <?= ($currentSection === 'films') ? 'active' : '' ?>">
                                <span>My Films</span>
                                <span class="badge rounded-pill bg-info text-dark ms-auto"><?= count($myFilmsList) ?></span>
                            </button>
                            <button type="button" id="nav-btn-password" data-profile-section="password" class="sidebar-sub-link <?= ($currentSection === 'password') ? 'active' : '' ?>">
                                Change Password
                            </button>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($isReviewer): ?>
                <div class="delete-box mt-3">
                    <form method="POST" action="<?= $viewHelper->routeUrl('user/delete-account', [], false) ?>"
                          data-confirm="Are you sure you want to delete your account? This action will deactivate your profile." style="margin: 0;">
                        <?= $viewHelper->csrfInput() ?>
                        <?= $viewHelper->tabInput() ?>
                        <button type="submit" class="btn-delete" style="cursor: pointer; width: 100%;">Delete account</button>
                    </form>
                </div>
            <?php endif; ?>
        </div>

        <div class="profile-form fw-bold">
            <?php if (!empty($error)): ?>
                <div class="alert error"><?= $viewHelper->e($error) ?></div>
            <?php endif; ?>

            <?php if (!empty($success)): ?>
                <div class="alert success"><?= $viewHelper->e($success) ?></div>
            <?php endif; ?>

            <div id="section-profile-setting" style="display: <?= ($currentSection === 'profile') ? 'block' : 'none' ?>;">
                <h4 class="mb-3">Profile Setting</h4>

                <form action="<?= $viewHelper->routeUrl('user/profile', ['section' => 'profile'], false) ?>" method="POST" enctype="multipart/form-data">
                    <?= $viewHelper->csrfInput() ?>
                    <?= $viewHelper->tabInput() ?>

                    <div class="form-group">
                        <label class="text-info fw-bold mb-1">Change Name</label>
                        <input type="text" name="name" value="<?= $viewHelper->e($userInfo['name'] ?? '') ?>" required>
                    </div>

                    <div class="form-group">
                        <label class="text-info fw-bold mb-1">Avatar</label>
                        <div class="d-flex w-100 align-items-stretch" style="width: 100%;">
                            <label class="btn btn-warning text-dark fw-bold" for="profile_avatar_input"
                                   style="cursor: pointer; padding: 10px 18px; border-radius: 6px 0 0 6px; margin: 0; white-space: nowrap; display: inline-flex; align-items: center; justify-content: center; font-size: 14px; border: none; height: 44px;">
                                Choose File
                            </label>
                            <span class="text-truncate flex-grow-1" id="profile_avatar_name"
                                  style="background: rgba(167, 166, 166, 0.85); color: #000; padding: 10px 14px; border-radius: 0 6px 6px 0; font-size: 14px; display: flex; align-items: center; border: none; min-width: 0; height: 44px;">
                                No file chosen
                            </span>
                            <input type="file" name="avatar" id="profile_avatar_input" class="visually-hidden" accept="image/*" data-file-label="profile_avatar_name">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="text-info fw-bold mb-1">Favorite Tokusatsu genre</label>
                        <select name="favorite_genre" class="form-control">
                            <option value="">-- Select Genre --</option>
                            <?php foreach (($categories ?? []) as $cat): ?>
                                <option value="<?= $viewHelper->e($cat['id']) ?>"
                                        <?= (isset($userInfo['favorite_genre']) && (string) $userInfo['favorite_genre'] === (string) $cat['id']) ? 'selected' : '' ?>>
                                    <?= $viewHelper->e($cat['category_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="text-info fw-bold mb-1">Bio</label>
                        <textarea name="bio" maxlength="500"><?= $viewHelper->e($userInfo['bio'] ?? '') ?></textarea>
                    </div>

                    <button type="submit" name="update_profile" class="btn-update">
                        UPDATE PROFILE
                    </button>
                </form>
            </div>

            <?php if ($canChangePassword): ?>
                <div id="section-change-password" style="display: <?= ($currentSection === 'password') ? 'block' : 'none' ?>;">
                    <h4 class="text-warning mb-3">Change Password</h4>
                    <form action="<?= $viewHelper->routeUrl('user/profile', ['section' => 'password'], false) ?>" method="POST">
                        <?= $viewHelper->csrfInput() ?>
                        <?= $viewHelper->tabInput() ?>

                        <div class="form-group">
                            <label class="text-info fw-bold mb-1">Current Password</label>
                            <input type="password" name="current_password" placeholder="Enter your current password" required>
                        </div>

                        <div class="form-group">
                            <label class="text-info fw-bold mb-1">New Password</label>
                            <input type="password" name="new_password" placeholder="At least 6 characters" minlength="6" required>
                        </div>

                        <div class="form-group">
                            <label class="text-info fw-bold mb-1">Confirm New Password</label>
                            <input type="password" name="confirm_password" placeholder="Re-enter your new password" minlength="6" required>
                        </div>

                        <button type="submit" name="change_password" class="btn-update" style="background: linear-gradient(135deg, #f59e0b, #d97706); color: #0b0f19;">
                            CHANGE PASSWORD
                        </button>
                    </form>
                </div>
            <?php endif; ?>

            <?php if ($isReviewer): ?>
                <div id="section-my-films" style="display: <?= ($currentSection === 'films') ? 'block' : 'none' ?>;">
                    <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-3 pb-3 border-bottom border-secondary">
                        <div>
                            <h4 class="text-white mb-1">My Submitted Films</h4>
                            <p class="text-white-50 small fw-normal mb-0">
                                Track your pending approvals and manage your published reviews.
                            </p>
                        </div>
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <a href="<?= $viewHelper->routeUrl('film/list', ['mine' => 1]) ?>"
                               class="btn btn-outline-info btn-sm fw-bold rounded-pill px-3">
                                Category: My Posts
                            </a>
                        </div>
                    </div>

                    <?php if (empty($myFilmsList)): ?>
                        <div class="text-center py-5 px-3 rounded-3 bg-dark bg-opacity-50 border border-secondary">
                            <div class="fs-2 mb-2">🎞️</div>
                            <h5 class="text-white mb-1">You haven't submitted any films yet</h5>
                            <p class="text-white-50 small fw-normal mb-0">
                                Use <strong>+ Post Film</strong> in the top navigation bar to submit your first Tokusatsu film review.
                            </p>
                        </div>
                    <?php else: ?>
                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                            <div class="d-flex align-items-center flex-wrap gap-2" id="my-films-status-pills">
                                <button type="button" class="my-films-filter-pill active" data-status-filter="all">
                                    All (<?= count($myFilmsList) ?>)
                                </button>
                                <button type="button" class="my-films-filter-pill" data-status-filter="pending">
                                    Pending (<?= (int) ($pendingCount ?? 0) ?>)
                                </button>
                                <button type="button" class="my-films-filter-pill" data-status-filter="approved">
                                    Approved (<?= (int) ($approvedCount ?? 0) ?>)
                                </button>
                            </div>
                            <input type="search" id="my-films-search"
                                   class="form-control form-control-sm bg-dark text-white border-secondary"
                                   placeholder="Filter title or category..."
                                   style="max-width: 230px;">
                        </div>

                        <div class="my-films-list" id="my-films-list">
                            <?php foreach ($myFilmsList as $mf): ?>
                                <?php
                                    $filmId = (int) ($mf['id'] ?? 0);
                                    $filmStatus = (string) ($mf['normalized_status'] ?? 'pending');
                                    $filmTitle = (string) ($mf['title'] ?? 'Untitled Film');
                                    $filmCat = (string) ($mf['category_name'] ?? 'General');
                                    $filmDate = (string) ($mf['created_at_formatted'] ?? '');
                                    $avgScore = (string) ($mf['avg_score_formatted'] ?? '0.0');
                                    $totalReviews = (int) ($mf['total_reviews'] ?? 0);
                                ?>
                                <div class="my-film-item"
                                     data-status="<?= $viewHelper->e($filmStatus) ?>"
                                     data-search="<?= $viewHelper->e($mf['search_haystack'] ?? '') ?>">
                                    <div class="d-flex align-items-center gap-3 min-w-0">
                                        <img src="<?= $viewHelper->e($mf['poster_url'] ?? '') ?>"
                                             alt="<?= $viewHelper->e($filmTitle) ?>"
                                             class="my-film-poster">
                                        <div class="min-w-0">
                                            <div class="d-flex align-items-center flex-wrap gap-1 mb-1">
                                                <?php if ($filmStatus === 'pending'): ?>
                                                    <span class="badge bg-warning text-dark">Pending Approval</span>
                                                <?php else: ?>
                                                    <span class="badge bg-success">✓ Approved</span>
                                                <?php endif; ?>
                                                <?php if (!empty($mf['is_anniversary'])): ?>
                                                    <span class="badge bg-info text-dark">⭐ Anniversary</span>
                                                <?php endif; ?>
                                                <span class="badge bg-secondary"><?= $viewHelper->e($filmCat) ?></span>
                                            </div>
                                            <h6 class="text-white fw-bold mb-1 text-truncate" title="<?= $viewHelper->e($filmTitle) ?>">
                                                <?= $viewHelper->e($filmTitle) ?>
                                            </h6>
                                            <div class="small text-white-50 fw-normal">
                                                <?php if ($filmDate !== ''): ?>
                                                    Submitted <?= $viewHelper->e($filmDate) ?> •
                                                <?php endif; ?>
                                                <?php if ($filmStatus === 'approved'): ?>
                                                    ⭐ <?= $viewHelper->e($avgScore) ?> (<?= $totalReviews ?> review<?= $totalReviews === 1 ? '' : 's' ?>)
                                                <?php else: ?>
                                                    Waiting for Admin moderation
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="d-flex align-items-center gap-2 flex-shrink-0">
                                        <?php if ($filmStatus === 'approved'): ?>
                                            <a href="<?= $viewHelper->routeUrl('review/view', ['id' => $filmId]) ?>"
                                               class="btn btn-outline-light btn-sm fw-semibold">
                                                View
                                            </a>
                                        <?php endif; ?>
                                        <a href="<?= $viewHelper->routeUrl('film/edit', ['id' => $filmId]) ?>"
                                           class="btn btn-outline-warning btn-sm fw-semibold">
                                            Edit
                                        </a>
                                        <form method="POST"
                                              action="<?= $viewHelper->routeUrl('film/delete', [], false) ?>"
                                              class="m-0 p-0 d-inline-block"
                                              data-confirm="Are you sure you want to delete this movie? This action cannot be undone.">
                                            <?= $viewHelper->csrfInput() ?>
                                            <?= $viewHelper->tabInput() ?>
                                            <input type="hidden" name="id" value="<?= $filmId ?>">
                                            <input type="hidden" name="return_to" value="profile">
                                            <button type="submit" class="btn btn-outline-danger btn-sm fw-semibold">
                                                Delete
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <div id="my-films-empty-filter" class="text-center text-white-50 py-4 fw-normal" style="display: none;">
                            No submissions match the selected filter.
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script src="<?= $viewHelper->asset('public/js/profile.js') ?>"></script>
