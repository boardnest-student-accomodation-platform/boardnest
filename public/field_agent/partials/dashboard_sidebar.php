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
                <?php echo strtoupper(substr($_SESSION['full_name'], 0, 1)); ?>
            </div>
            <div>
                <div class="fa-agent-name"><?php echo htmlspecialchars($_SESSION['full_name']); ?></div>
                <div class="fa-agent-id">Agent ID: #AGT-00<?php echo $agent_id; ?></div>
            </div>
        </div>
        <div class="fa-agent-footer">
            <span class="fa-agent-region">Region: <strong><?php echo htmlspecialchars($city); ?></strong></span>
            <span class="fa-status-active">🟢 Active</span>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <nav class="fa-sidebar-nav">
        <a href="dashboard.php?tab=pending" class="sidebar-nav-item <?php echo $active_tab === 'pending' ? 'active' : ''; ?>">
            <span class="fa-nav-item-content">📌 Pending Pool</span>
            <span class="nav-badge"><?php echo $count_pending; ?></span>
        </a>
        <a href="dashboard.php?tab=claimed" class="sidebar-nav-item <?php echo $active_tab === 'claimed' ? 'active' : ''; ?>">
            <span class="fa-nav-item-content">📋 Claimed Tasks</span>
            <span class="nav-badge"><?php echo $count_claimed; ?></span>
        </a>
        <a href="dashboard.php?tab=complaints" class="sidebar-nav-item <?php echo $active_tab === 'complaints' ? 'active' : ''; ?>">
            <span class="fa-nav-item-content">🚨 Complaints</span>
            <span class="nav-badge complaint <?php echo $active_tab === 'complaints' ? 'active' : ''; ?>"><?php echo $count_complaints; ?></span>
        </a>
        <a href="dashboard.php?tab=history" class="sidebar-nav-item <?php echo $active_tab === 'history' ? 'active' : ''; ?>">
            <span class="fa-nav-item-content">✅ Completed History</span>
            <span class="nav-badge"><?php echo $count_completed; ?></span>
        </a>
    </nav>

</aside>
