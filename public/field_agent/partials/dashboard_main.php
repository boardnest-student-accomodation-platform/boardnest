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
            ✅ <?php echo htmlspecialchars($success_msg); ?>
        </div>
    <?php endif; ?>
    <?php if ($error_msg): ?>
        <div class="fa-alert fa-alert-danger">
            ⚠️ <?php echo htmlspecialchars($error_msg); ?>
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
            <div class="table-wrapper-custom">
                <table class="table-custom">
                    <thead><tr><th>Task ID</th><th>Address</th><th>Property Type</th><th>Status</th><th>Action</th></tr></thead>
                    <tbody>
                        <?php foreach ($pending_tasks as $task): ?>
                        <tr>
                            <td><strong>#VT-<?php echo $task['task_id']; ?></strong></td>
                            <td><?php echo htmlspecialchars($task['address']); ?></td>
                            <td><span class="tag-custom"><?php echo htmlspecialchars($task['structural_type']); ?></span></td>
                            <td><span class="badge badge-warning">Unclaimed</span></td>
                            <td>
                                <form action="actions/update_task.php" method="POST" class="fa-inline-form">
                                    <input type="hidden" name="task_id" value="<?php echo $task['task_id']; ?>">
                                    <input type="hidden" name="action_type" value="claim">
                                    <button type="submit" class="btn btn--primary btn--sm fa-btn-table">Claim Task</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
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
            <div class="table-wrapper-custom">
                <table class="table-custom">
                    <thead><tr><th>Task ID</th><th>Address</th><th>Property Type</th><th>Action</th></tr></thead>
                    <tbody>
                        <?php foreach ($claimed_tasks as $task): ?>
                        <tr>
                            <td><strong>#VT-<?php echo $task['task_id']; ?></strong></td>
                            <td><?php echo htmlspecialchars($task['address']); ?></td>
                            <td><span class="tag-custom"><?php echo htmlspecialchars($task['structural_type']); ?></span></td>
                            <td>
                                <div class="flex gap-2">
                                    <a href="task_view.php?task_id=<?php echo $task['task_id']; ?>" class="btn btn--primary btn--sm fa-btn-table">Open Task</a>
                                    <form action="actions/update_task.php" method="POST" class="fa-inline-form">
                                        <input type="hidden" name="task_id" value="<?php echo $task['task_id']; ?>">
                                        <input type="hidden" name="action_type" value="withdraw">
                                        <button type="submit" class="btn btn--ghost btn--sm btn--danger fa-btn-table" onclick="return confirm('Withdraw from this task?');">Withdraw</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
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
            <div class="table-wrapper-custom">
                <table class="table-custom">
                    <thead><tr><th>Complaint ID</th><th>Property Address</th><th>Complainant</th><th>Category</th><th>Action</th></tr></thead>
                    <tbody>
                        <?php foreach ($complaints_tasks as $comp): ?>
                        <tr>
                            <td><strong>#CP-<?php echo $comp['complaint_id']; ?></strong></td>
                            <td><?php echo htmlspecialchars($comp['address']); ?></td>
                            <td><?php echo htmlspecialchars($comp['student_name']); ?></td>
                            <td><span class="tag-custom"><?php echo htmlspecialchars($comp['category']); ?></span></td>
                            <td><a href="task_view.php?complaint_id=<?php echo $comp['complaint_id']; ?>" class="btn btn--primary btn--sm fa-btn-table">Investigate</a></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
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
            <div class="table-wrapper-custom">
                <table class="table-custom">
                    <thead><tr><th>Task ID</th><th>Address</th><th>Property Type</th><th>Completed Date</th><th>Action</th></tr></thead>
                    <tbody>
                        <?php foreach ($completed_tasks as $task): ?>
                        <tr>
                            <td><strong>#VT-<?php echo $task['task_id']; ?></strong></td>
                            <td><?php echo htmlspecialchars($task['address']); ?></td>
                            <td><span class="tag-custom"><?php echo htmlspecialchars($task['structural_type']); ?></span></td>
                            <td><?php echo htmlspecialchars($task['submitted_at']); ?></td>
                            <td><a href="task_view.php?task_id=<?php echo $task['task_id']; ?>" class="btn btn--outline btn--sm fa-btn-table">View Report</a></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</main>
