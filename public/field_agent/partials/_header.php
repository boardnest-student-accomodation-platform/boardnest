<?php
// Partial: _header.php
// Sticky top navbar for field agent task_view
// Expects: $city (string)
?>
<header style="background:#FFFFFF;border-bottom:1.5px solid #E8DDD4;padding:14px 32px;display:flex;align-items:center;justify-content:space-between;position:sticky;top:0;z-index:100;">
    <a href="../../index.html" style="font-family:'Outfit',sans-serif;font-size:26px;font-weight:900;color:#6F4E37;letter-spacing:-0.8px;text-decoration:none;">BoardNest</a>

    <div style="display:flex;align-items:center;gap:12px;">
        <button type="button" onclick="openAgentGuide()" style="background:#FFF8F5;border:1.5px solid #E8DDD4;color:#6F4E37;padding:6px 14px;border-radius:50px;font-size:12px;font-weight:800;cursor:pointer;display:flex;align-items:center;gap:6px;">
            📖 Inspection Guide
        </button>
        <span style="display:inline-block;background:#3B3330;color:#FFFFFF;font-size:11px;font-weight:700;padding:4px 12px;border-radius:50px;">
            📍 <?php echo htmlspecialchars($city); ?> Agent
        </span>
        <a href="dashboard.php" style="background:#FFF8F5;border:1.5px solid #E8DDD4;color:#3B3330;padding:6px 16px;border-radius:50px;font-size:12px;font-weight:700;text-decoration:none;">← Back to Dashboard</a>
    </div>
</header>
