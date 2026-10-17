<?php
// Partial: dashboard_main.php
// Main content area: metric cards + tab panels
// Expects: $active_tab, $city, $success_msg, $error_msg,
//          $count_pending, $count_claimed, $count_complaints, $count_completed,
//          $pending_tasks, $claimed_tasks, $complaints_tasks, $completed_tasks
?>
<main class="fa-main-content">
    <div class="fa-header-group">
        <h1 class="fa-h1">Field Verification Dashboard</h1>
        <p class="fa-subtitle">Overview of property audits and tenant dispute reports in <strong><?php echo htmlspecialchars($city); ?></strong>.</p>
    </div>

    <!-- Metric Cards -->
    <div class="metrics-stats-grid">
        <div class="metric-card-box">
            <div class="metric-card-title">Pending Unclaimed Tasks</div>
            <div class="metric-card-number"><?php echo $count_pending; ?></div>
        </div>
        <div class="metric-card-box">
            <div class="metric-card-title">My Claimed Audits</div>
            <div class="metric-card-number color-primary"><?php echo $count_claimed; ?></div>
        </div>
        <div class="metric-card-box">
            <div class="metric-card-title">Assigned Disputes</div>
            <div class="metric-card-number color-danger"><?php echo $count_complaints; ?></div>
        </div>
        <div class="metric-card-box">
            <div class="metric-card-title">Completed Audits</div>
            <div class="metric-card-number color-success"><?php echo $count_completed; ?></div>
        </div>
    </div>

    <!-- Flash Alerts -->
    <?php if ($success_msg): ?>
        <div class="fa-alert fa-alert-success">
            <?php echo fa_icon('check-circle', '', 16, '#0f5132'); ?> <?php echo htmlspecialchars($success_msg); ?>
        </div>
    <?php endif; ?>
    <?php if ($error_msg): ?>
        <div class="fa-alert fa-alert-danger">
            <?php echo fa_icon('alert', '', 16, '#842029'); ?> <?php echo htmlspecialchars($error_msg); ?>
        </div>
    <?php endif; ?>

    <!-- Tab: Pending -->
    <?php if ($active_tab === 'pending'): ?>
        <h2 class="fa-h2">Pending Pool (Unclaimed Tasks)</h2>
        <?php if (empty($pending_tasks)): ?>
            <div class="empty-state-card">
                <div class="fa-empty-icon">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                </div>
                <h3 class="fa-h3">No Pending Verifications</h3>
                <p class="fa-empty-text">All property listings in <strong><?php echo htmlspecialchars($city); ?></strong> are currently claimed or verified.</p>
            </div>
        <?php else: ?>
            <div class="fa-task-cards-list">
                <?php foreach ($pending_tasks as $task): ?>
                <div class="fa-task-card">
                    <div class="fa-task-card-header">
                        <div class="fa-task-card-id-wrap">
                            <span class="fa-task-card-id">#VT-<?php echo $task['task_id']; ?></span>
                            <span class="tag-custom"><?php echo htmlspecialchars($task['structural_type'] ?? 'Property'); ?></span>
                        </div>
                        <span class="badge badge-warning">Unclaimed</span>
                    </div>

                    <div class="fa-task-card-body">
                        
                        <div class="fa-task-card-address">
                            <?php echo fa_icon('pin', '', 16, '#6F4E37'); ?>
                            <span><?php echo htmlspecialchars($task['address']); ?><?php echo !empty($task['city']) ? ', ' . htmlspecialchars($task['city']) : ''; ?></span>
                        </div>
                    </div>

                    <div class="fa-task-card-actions">
                        <form action="actions/update_task.php" method="POST" style="width: 100%;">
                            <input type="hidden" name="task_id" value="<?php echo $task['task_id']; ?>">
                            <input type="hidden" name="action_type" value="claim">
                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                            <button type="submit" class="fa-task-card-btn-primary">
                                <span>Claim Task</span>
                                <?php echo fa_icon('arrow-right', '', 14); ?>
                            </button>
                        </form>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

    <!-- Tab: Claimed -->
    <?php elseif ($active_tab === 'claimed'): ?>
        <h2 class="fa-h2">My Claimed Tasks (In Progress)</h2>
        <?php if (empty($claimed_tasks)): ?>
            <div class="empty-state-card">
                <div class="fa-empty-icon">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                </div>
                <h3 class="fa-h3">No Claimed Tasks</h3>
                <p class="fa-empty-text">Claim tasks from the Pending Pool to start physical property audits.</p>
            </div>
        <?php else: ?>
            <div class="fa-task-cards-list">
                <?php foreach ($claimed_tasks as $task): ?>
                <div class="fa-task-card">
                    <div class="fa-task-card-header">
                        <div class="fa-task-card-id-wrap">
                            <span class="fa-task-card-id">#VT-<?php echo $task['task_id']; ?></span>
                            <span class="tag-custom"><?php echo htmlspecialchars($task['structural_type'] ?? 'Property'); ?></span>
                        </div>
                        <span class="badge" style="background:#FFF2D7; color:#A4856D; border:1px solid #E8DDD4; font-weight:700;">In Progress</span>
                    </div>

                    <div class="fa-task-card-body">
                        <div class="fa-task-card-address">
                            <?php echo fa_icon('pin', '', 16, '#6F4E37'); ?>
                            <span><?php echo htmlspecialchars($task['address']); ?><?php echo !empty($task['city']) ? ', ' . htmlspecialchars($task['city']) : ''; ?></span>
                        </div>
                    </div>

                    <div class="fa-task-card-actions-row">
                        <a href="task_view.php?task_id=<?php echo $task['task_id']; ?>" class="fa-task-card-btn-primary" style="flex: 2;">
                            <span>Open Task</span>
                            <?php echo fa_icon('arrow-right', '', 14); ?>
                        </a>
                        <form action="actions/update_task.php" method="POST" style="flex: 1;">
                            <input type="hidden" name="task_id" value="<?php echo $task['task_id']; ?>">
                            <input type="hidden" name="action_type" value="withdraw">
                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                            <button type="submit" class="fa-task-card-btn-withdraw" onclick="return confirm('Withdraw from this task?');">Withdraw</button>
                        </form>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

    <!-- Tab: Complaints -->
    <?php elseif ($active_tab === 'complaints'): ?>
        <h2 class="fa-h2">Assigned Complaint Investigations</h2>
        <?php if (empty($complaints_tasks)): ?>
            <div class="empty-state-card">
                <div class="fa-empty-icon danger">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
                </div>
                <h3 class="fa-h3">No Active Complaints</h3>
                <p class="fa-empty-text">No tenant dispute complaints assigned to you in <strong><?php echo htmlspecialchars($city); ?></strong>.</p>
            </div>
        <?php else: ?>
            <div class="fa-task-cards-list">
                <?php foreach ($complaints_tasks as $comp): ?>
                <div class="fa-task-card">
                    <div class="fa-task-card-header">
                        <div class="fa-task-card-id-wrap">
                            <span class="fa-task-card-id">#CP-<?php echo $comp['complaint_id']; ?></span>
                            <span class="tag-custom"><?php echo htmlspecialchars($comp['structural_type'] ?? 'Dispute'); ?></span>
                        </div>
                        <span class="badge badge-danger">Assigned Dispute</span>
                    </div>

                    <div class="fa-task-card-body">
                        <div class="fa-task-card-address">
                            <?php echo fa_icon('pin', '', 16, '#6F4E37'); ?>
                            <span><?php echo htmlspecialchars($comp['address']); ?></span>
                        </div>
                        <div class="fa-task-card-meta">
                            <span class="fa-task-card-meta-item">
                                <?php echo fa_icon('user', '', 13, '#8C7B74'); ?>
                                Complainant: <strong><?php echo htmlspecialchars($comp['student_name']); ?></strong>
                            </span>
                        </div>
                    </div>

                    <div class="fa-task-card-actions">
                        <a href="task_view.php?complaint_id=<?php echo $comp['complaint_id']; ?>" class="fa-task-card-btn-primary">
                            <span>Investigate Dispute</span>
                            <?php echo fa_icon('arrow-right', '', 14); ?>
                        </a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

    <!-- Tab: History -->
    <?php elseif ($active_tab === 'history'): ?>
        <h2 class="fa-h2">Completed History (Read Only)</h2>
        <?php if (empty($completed_tasks)): ?>
            <div class="empty-state-card">
                <div class="fa-empty-icon success">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                </div>
                <h3 class="fa-h3">No Completed Reports</h3>
                <p class="fa-empty-text">Completed verification reports will appear here.</p>
            </div>
        <?php else: ?>
            <div class="fa-task-cards-list">
                <?php foreach ($completed_tasks as $task): ?>
                <div class="fa-task-card">
                    <div class="fa-task-card-header">
                        <div class="fa-task-card-id-wrap">
                            <span class="fa-task-card-id">#VT-<?php echo $task['task_id']; ?></span>
                            <span class="tag-custom"><?php echo htmlspecialchars($task['structural_type'] ?? 'Property'); ?></span>
                        </div>
                        <span class="badge badge-success"><?php echo fa_icon('check', '', 11); ?> Completed</span>
                    </div>

                    <div class="fa-task-card-body">
                        <div class="fa-task-card-address">
                            <?php echo fa_icon('pin', '', 16, '#6F4E37'); ?>
                            <span><?php echo htmlspecialchars($task['address']); ?></span>
                        </div>
                        <div class="fa-task-card-meta">
                            <span class="fa-task-card-meta-item">
                                <?php echo fa_icon('calendar', '', 13, '#8C7B74'); ?>
                                Completed <?php echo !empty($task['submitted_at']) ? date('M d, Y', strtotime($task['submitted_at'])) : 'Verified'; ?>
                            </span>
                        </div>
                    </div>

                    <div class="fa-task-card-actions">
                        <a href="task_view.php?task_id=<?php echo $task['task_id']; ?>" class="fa-task-card-btn-secondary">
                            <span>View Report</span>
                            <?php echo fa_icon('arrow-right', '', 14); ?>
                        </a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- Completed Complaints -->
        <h2 class="fa-h2 fa-mt-20">Completed Complaints (Read Only)</h2>
        <?php if (empty($completed_complaints)): ?>
            <div class="empty-state-card">
                <div class="fa-empty-icon success">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                </div>
                <h3 class="fa-h3">No Completed Complaints</h3>
                <p class="fa-empty-text">Completed complaint investigations will appear here.</p>
            </div>
        <?php else: ?>
            <div class="fa-task-cards-list">
                <?php foreach ($completed_complaints as $comp): ?>
                <div class="fa-task-card">
                    <div class="fa-task-card-header">
                        <div class="fa-task-card-id-wrap">
                            <span class="fa-task-card-id">#CP-<?php echo $comp['complaint_id']; ?></span>
                            <span class="tag-custom"><?php echo htmlspecialchars(ucfirst($comp['status'])); ?></span>
                        </div>
                        <span class="badge badge-success"><?php echo fa_icon('check', '', 11); ?> Resolved</span>
                    </div>

                    <div class="fa-task-card-body">
                        <div class="fa-task-card-address">
                            <?php echo fa_icon('pin', '', 16, '#6F4E37'); ?>
                            <span><?php echo htmlspecialchars($comp['address']); ?></span>
                        </div>
                    </div>

                    <div class="fa-task-card-actions">
                        <a href="task_view.php?complaint_id=<?php echo $comp['complaint_id']; ?>" class="fa-task-card-btn-secondary">
                            <span>View Report</span>
                            <?php echo fa_icon('arrow-right', '', 14); ?>
                        </a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</main>
