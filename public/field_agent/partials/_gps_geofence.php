<?php
// Partial: _gps_geofence.php
// GPS Geofence Presence Match card
// Supports both verification tasks ($task) and complaint investigations ($complaint)

$is_complaint_mode = isset($complaint_id) && $complaint_id > 0 && isset($complaint);
$geo_sess_key = $is_complaint_mode ? ('geofence_passed_comp_' . $complaint_id) : ('geofence_passed_' . $task_id);
$geo_lat = $is_complaint_mode ? (float)$complaint['latitude'] : (float)(isset($task['latitude']) ? $task['latitude'] : 0);
$geo_lng = $is_complaint_mode ? (float)$complaint['longitude'] : (float)(isset($task['longitude']) ? $task['longitude'] : 0);
$unlock_title = $is_complaint_mode ? "Investigation Form Unlocked" : "Verification Form Unlocked";
$lock_title = $is_complaint_mode ? "🔒 Investigation Form Locked" : "🔒 Checklist Locked";
?>
<div class="details-card">
    <div class="section-label">GPS Geofence Presence Match</div>
    <p style="font-size:13px;color:#8C7B74;margin:0 0 16px 0;">
        You must be physically present at the property (within 100m) to unlock the <?php echo $is_complaint_mode ? 'dispute investigation form' : 'verification checklist'; ?>.
    </p>

    <?php if (isset($_SESSION[$geo_sess_key])): ?>
        <div class="geofence-box-custom unlocked">
            <div style="font-size:14px;font-weight:800;color:#0F5132;">🟢 GPS Geofence Verified</div>
            <div style="font-size:12px;color:#0F5132;margin-top:2px;">Device location matches property coordinates (within 100m). <?php echo $unlock_title; ?>.</div>
        </div>
    <?php else: ?>
        <div class="geofence-box-custom locked" id="geofenceBox">
            <div style="font-size:14px;font-weight:800;color:#842029;" id="geofenceStatus"><?php echo $lock_title; ?></div>
            <div style="font-size:12px;color:#842029;margin-top:2px;margin-bottom:12px;" id="geofenceDesc">Click button below to capture device GPS location.</div>
            <div style="display:flex;gap:8px;justify-content:center;flex-wrap:wrap;">
                <button class="btn-camera-capture" id="btnVerifyGPS" onclick="verifyGPSLocation()">Verify My GPS Location</button>
                <button type="button" class="btn-camera-capture" onclick="simulateGPSMatch()" style="background:#A4856D;">[Test] Bypass GPS</button>
            </div>
        </div>

        <form id="geofenceSuccessForm" action="actions/update_task.php" method="POST" class="hidden">
            <?php if ($is_complaint_mode): ?>
                <input type="hidden" name="complaint_id" value="<?php echo $complaint_id; ?>">
            <?php else: ?>
                <input type="hidden" name="task_id" value="<?php echo $task_id; ?>">
            <?php endif; ?>
            <input type="hidden" name="action_type" value="claim">
            <input type="hidden" name="geofence_override" value="1">
        </form>
    <?php endif; ?>
</div>

<!-- GPS coordinates injected for field_agent.js -->
<script>
    var propLat     = <?php echo $geo_lat; ?>;
    var propLng     = <?php echo $geo_lng; ?>;
    var taskId      = <?php echo (int)$task_id; ?>;
    var complaintId = <?php echo (int)$complaint_id; ?>;
</script>
