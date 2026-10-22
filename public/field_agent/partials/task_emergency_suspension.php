<?php
// Partial: task_emergency_suspension.php
// Collapsible emergency hazard drawer
// Expects: $task_id (int)
?>
<details class="fa-emergency-details">
    <summary class="fa-emergency-summary">
        <span>⚠️ Flag Critical Safety Hazard (Emergency Suspension)</span>
        <span class="fa-emergency-badge">Expand Drawer ▾</span>
    </summary>
    <div class="fa-emergency-content">
        <p class="fa-emergency-desc">
            If you identify severe immediate safety hazards (e.g. broken locks, structural instability, dangerous wiring),
            trigger an emergency suspension to hide the listing from students.
        </p>
        <form action="actions/update_task.php" method="POST">
            <input type="hidden" name="task_id" value="<?php echo $task_id; ?>">
            <input type="hidden" name="action_type" value="suspend">
            <div class="fa-mb-12">
                <label class="fa-emergency-label">Immediate Safety Hazard Reason</label>
                <textarea class="textarea-styled fa-emergency-textarea" name="reason" placeholder="Explain the severe safety hazard in detail..." required></textarea>
            </div>
            <button type="submit"
                    class="fa-emergency-submit"
                    onclick="return confirm('⚠️ EMERGENCY CONFIRMATION: Are you sure you want to trigger an immediate suspension for this property listing? This will hide the property from all student searches.');">
                ⚠️ Confirm &amp; Trigger Instant Suspension
            </button>
        </form>
    </div>
</details>
