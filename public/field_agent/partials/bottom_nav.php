<?php
// Partial: bottom_nav.php
// Fixed mobile bottom tab navigation bar for Field Agent mobile / tablet experience
// Expects: $active_tab (optional), $count_pending (optional), $count_claimed (optional), $count_complaints (optional), $count_completed (optional)
require_once __DIR__ . '/icons.php';

$curr_tab = isset($active_tab) ? $active_tab : '';
$cnt_pending = isset($count_pending) ? (int)$count_pending : 0;
$cnt_claimed = isset($count_claimed) ? (int)$count_claimed : 0;
$cnt_complaints = isset($count_complaints) ? (int)$count_complaints : 0;
$cnt_history = isset($count_completed) ? (int)$count_completed : 0;
?>
<nav class="fa-bottom-nav" aria-label="Mobile Navigation">
    <a href="dashboard.php?tab=pending" class="fa-bottom-nav-item <?php echo $curr_tab === 'pending' ? 'active' : ''; ?>">
        <span class="fa-bottom-nav-icon-wrapper">
            <?php echo fa_icon('pin', '', 18); ?>
            <?php if ($cnt_pending > 0): ?>
                <span class="fa-bottom-badge"><?php echo $cnt_pending; ?></span>
            <?php endif; ?>
        </span>
        <span class="fa-bottom-nav-label">Pending</span>
    </a>

    <a href="dashboard.php?tab=claimed" class="fa-bottom-nav-item <?php echo $curr_tab === 'claimed' ? 'active' : ''; ?>">
        <span class="fa-bottom-nav-icon-wrapper">
            <?php echo fa_icon('clipboard', '', 18); ?>
            <?php if ($cnt_claimed > 0): ?>
                <span class="fa-bottom-badge"><?php echo $cnt_claimed; ?></span>
            <?php endif; ?>
        </span>
        <span class="fa-bottom-nav-label">Claimed</span>
    </a>

    <a href="dashboard.php?tab=complaints" class="fa-bottom-nav-item <?php echo $curr_tab === 'complaints' ? 'active' : ''; ?>">
        <span class="fa-bottom-nav-icon-wrapper">
            <?php echo fa_icon('siren', '', 18); ?>
            <?php if ($cnt_complaints > 0): ?>
                <span class="fa-bottom-badge badge-danger"><?php echo $cnt_complaints; ?></span>
            <?php endif; ?>
        </span>
        <span class="fa-bottom-nav-label">Disputes</span>
    </a>

    <a href="dashboard.php?tab=history" class="fa-bottom-nav-item <?php echo $curr_tab === 'history' ? 'active' : ''; ?>">
        <span class="fa-bottom-nav-icon-wrapper">
            <?php echo fa_icon('check-circle', '', 18); ?>
            <?php if ($cnt_history > 0): ?>
                <span class="fa-bottom-badge"><?php echo $cnt_history; ?></span>
            <?php endif; ?>
        </span>
        <span class="fa-bottom-nav-label">History</span>
    </a>

    <a href="area_report.php" class="fa-bottom-nav-item <?php echo $curr_tab === 'area_report' ? 'active' : ''; ?>">
        <span class="fa-bottom-nav-icon-wrapper">
            <?php echo fa_icon('building', '', 18); ?>
        </span>
        <span class="fa-bottom-nav-label">Area Profile</span>
    </a>
</nav>
