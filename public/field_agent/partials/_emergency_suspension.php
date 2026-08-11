<?php
// Partial: _emergency_suspension.php
// Collapsible emergency hazard drawer
// Expects: $task_id (int)
?>
<details style="background:#FFF5F5;border:1.5px solid #F8D7DA;border-radius:16px;overflow:hidden;margin-top:20px;">
    <summary style="padding:16px 20px;font-weight:800;font-size:13px;color:#842029;cursor:pointer;display:flex;align-items:center;justify-content:space-between;user-select:none;">
        <span>⚠️ Flag Critical Safety Hazard (Emergency Suspension)</span>
        <span style="font-size:11px;background:#F8D7DA;color:#842029;padding:4px 10px;border-radius:50px;font-weight:800;">Expand Drawer ▾</span>
    </summary>
    <div style="padding:20px;border-top:1px solid #F5C2C7;background:#FFFFFF;">
        <p style="font-size:12px;color:#842029;margin:0 0 14px 0;line-height:1.4;">
            If you identify severe immediate safety hazards (e.g. broken locks, structural instability, dangerous wiring),
            trigger an emergency suspension to hide the listing from students.
        </p>
        <form action="actions/update_task.php" method="POST">
            <input type="hidden" name="task_id" value="<?php echo $task_id; ?>">
            <input type="hidden" name="action_type" value="suspend">
            <div style="margin-bottom:12px;">
                <label style="font-size:11px;font-weight:800;color:#842029;text-transform:uppercase;display:block;margin-bottom:4px;">Immediate Safety Hazard Reason</label>
                <textarea class="textarea-styled" name="reason" placeholder="Explain the severe safety hazard in detail..." required
                          style="border-color:#F5C2C7;background:#FFFFFF;min-height:60px;font-size:12px;"></textarea>
            </div>
            <button type="submit"
                    style="background:#842029;color:#FFFFFF;border:none;padding:12px 20px;border-radius:8px;font-size:12px;font-weight:800;cursor:pointer;transition:all 0.2s ease;"
                    onclick="return confirm('⚠️ EMERGENCY CONFIRMATION: Are you sure you want to trigger an immediate suspension for this property listing? This will hide the property from all student searches.');">
                ⚠️ Confirm &amp; Trigger Instant Suspension
            </button>
        </form>
    </div>
</details>
