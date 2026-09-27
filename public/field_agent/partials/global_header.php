<?php
// Partial: global_header.php
// Sticky top navbar for field agent task_view
// Expects: $city (string)
?>
<header class="fa-header">
    <a href="../../index.html" class="fa-header-brand">BoardNest</a>

    <div class="fa-header-actions">
        <button type="button" onclick="openAgentGuide()" class="fa-header-btn-guide">
            📖 Inspection Guide
        </button>
        <span class="fa-report-role">
            📍 <?php echo htmlspecialchars($city); ?> Agent
        </span>
        <a href="dashboard.php" class="fa-report-btn-secondary">← Back to Dashboard</a>
    </div>
</header>
