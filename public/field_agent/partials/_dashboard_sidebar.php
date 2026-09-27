<?php
// Partial: dashboard_sidebar.php
// Left sidebar: agent profile card + nav tabs + area report link
// Expects: $active_tab, $city, $count_pending, $count_claimed, $count_complaints, $count_completed, $agent_id
?>
<aside class="sidebar-custom">
    <!-- Agent Profile Summary Card -->
    <div class="agent-profile-card">
        <div class="fa-agent-info">
            <div class="fa-agent-avatar">
                <?php 
                $agent_name = isset($_SESSION['full_name']) ? $_SESSION['full_name'] : 'Field Agent';
                echo strtoupper(substr($agent_name, 0, 1)); 
                ?>
            </div>
            <div>
                <div class="fa-agent-name"><?php echo htmlspecialchars($agent_name); ?></div>
                <div class="fa-agent-id">Agent ID: <?php echo sprintf('#AGT-%03d', (int)$agent_id); ?></div>
            </div>
        </div>
        <div class="fa-agent-footer">
            <span class="fa-agent-region">Region: <strong><?php echo htmlspecialchars($city); ?></strong></span>
            <?php $status_display = (isset($agent_status) && $agent_status === 'suspended') ? '🔴 Suspended' : '🟢 Active'; ?>
            <span class="fa-status-active"><?php echo $status_display; ?></span>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <nav class="fa-sidebar-nav" aria-label="Sidebar Navigation">
        <a href="dashboard.php?tab=pending" class="sidebar-nav-item <?php echo $active_tab === 'pending' ? 'active' : ''; ?>" <?php echo $active_tab === 'pending' ? 'aria-current="page"' : ''; ?>>
            <span class="fa-nav-item-content">📌 Pending Pool</span>
            <span class="nav-badge"><?php echo (int)$count_pending; ?></span>
        </a>
        <a href="dashboard.php?tab=claimed" class="sidebar-nav-item <?php echo $active_tab === 'claimed' ? 'active' : ''; ?>" <?php echo $active_tab === 'claimed' ? 'aria-current="page"' : ''; ?>>
            <span class="fa-nav-item-content">📋 Claimed Tasks</span>
            <span class="nav-badge"><?php echo (int)$count_claimed; ?></span>
        </a>
        <a href="dashboard.php?tab=complaints" class="sidebar-nav-item <?php echo $active_tab === 'complaints' ? 'active' : ''; ?>" <?php echo $active_tab === 'complaints' ? 'aria-current="page"' : ''; ?>>
            <span class="fa-nav-item-content">🚨 Complaints</span>
            <span class="nav-badge complaint <?php echo $active_tab === 'complaints' ? 'active' : ''; ?>"><?php echo (int)$count_complaints; ?></span>
        </a>
        <a href="dashboard.php?tab=history" class="sidebar-nav-item <?php echo $active_tab === 'history' ? 'active' : ''; ?>" <?php echo $active_tab === 'history' ? 'aria-current="page"' : ''; ?>>
            <span class="fa-nav-item-content">✅ Completed History</span>
            <span class="nav-badge"><?php echo (int)$count_completed; ?></span>
        </a>

        <!-- Area Report Link (Added per PR feedback) -->
        <a href="area_report.php" class="sidebar-nav-item">
            <span class="fa-nav-item-content">📍 Submit Area Report</span>
        </a>
    </nav>

</aside>
