<?php
// Partial: _dashboard_sidebar.php
// Left sidebar: agent profile card + nav tabs + area report link
// Expects: $active_tab, $city, $count_pending, $count_claimed, $count_complaints, $count_completed, $agent_id
?>
<aside class="sidebar-custom">
    <!-- Agent Profile Summary Card -->
    <div class="agent-profile-card">
        <div style="display:flex;align-items:center;gap:12px;margin-bottom:14px;">
            <div style="width:42px;height:42px;border-radius:50%;background:#A4856D;color:#FFFFFF;font-size:18px;font-weight:800;display:flex;align-items:center;justify-content:center;border:2px solid rgba(255,255,255,0.2);">
                <?php echo strtoupper(substr($_SESSION['full_name'], 0, 1)); ?>
            </div>
            <div>
                <div style="font-size:15px;font-weight:800;color:#FFFFFF;line-height:1.2;"><?php echo htmlspecialchars($_SESSION['full_name']); ?></div>
                <div style="font-size:11px;opacity:0.75;font-weight:600;">Agent ID: #AGT-00<?php echo $agent_id; ?></div>
            </div>
        </div>
        <div style="display:flex;align-items:center;justify-content:space-between;font-size:12px;border-top:1px solid rgba(255,255,255,0.15);padding-top:12px;">
            <span style="opacity:0.8;">Region: <strong><?php echo htmlspecialchars($city); ?></strong></span>
            <span style="display:inline-flex;align-items:center;gap:4px;background:rgba(39,174,96,0.2);color:#2ECC71;padding:2px 8px;border-radius:50px;font-size:10px;font-weight:700;">🟢 Active</span>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <nav style="display:flex;flex-direction:column;">
        <a href="dashboard.php?tab=pending" class="sidebar-nav-item <?php echo $active_tab === 'pending' ? 'active' : ''; ?>">
            <span style="display:flex;align-items:center;gap:8px;">📌 Pending Pool</span>
            <span class="nav-badge"><?php echo $count_pending; ?></span>
        </a>
        <a href="dashboard.php?tab=claimed" class="sidebar-nav-item <?php echo $active_tab === 'claimed' ? 'active' : ''; ?>">
            <span style="display:flex;align-items:center;gap:8px;">📋 Claimed Tasks</span>
            <span class="nav-badge"><?php echo $count_claimed; ?></span>
        </a>
        <a href="dashboard.php?tab=complaints" class="sidebar-nav-item <?php echo $active_tab === 'complaints' ? 'active' : ''; ?>">
            <span style="display:flex;align-items:center;gap:8px;">🚨 Complaints</span>
            <span class="nav-badge" style="background:<?php echo $active_tab === 'complaints' ? 'rgba(255,255,255,0.2)' : 'rgba(192,57,43,0.1)'; ?>;color:<?php echo $active_tab === 'complaints' ? '#FFF' : '#C0392B'; ?>;"><?php echo $count_complaints; ?></span>
        </a>
        <a href="dashboard.php?tab=history" class="sidebar-nav-item <?php echo $active_tab === 'history' ? 'active' : ''; ?>">
            <span style="display:flex;align-items:center;gap:8px;">✅ Completed History</span>
            <span class="nav-badge"><?php echo $count_completed; ?></span>
        </a>
    </nav>

</aside>
