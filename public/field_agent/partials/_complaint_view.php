<?php
// Partial: _complaint_view.php
// Complaint investigation details + report form (Supports Active & Read-Only Resolved modes)
// Expects: $complaint (array), optional $show_complaint_part ('header'|'form'|'all')

$is_resolved = isset($complaint['status']) && $complaint['status'] === 'resolved';
$category_labels = array(
    'fee_discrepancy'     => '💰 Fee Discrepancy',
    'amenity_discrepancy' => '⚡ Amenity Discrepancy',
    'maintenance_issue'   => '🔧 Maintenance Issue',
    'security_issue'      => '🔒 Safety & Security Issue'
);
$cat_text = isset($category_labels[$complaint['category']]) 
    ? $category_labels[$complaint['category']] 
    : '📢 ' . htmlspecialchars(str_replace('_', ' ', ucfirst($complaint['category'])));

$part = isset($show_complaint_part) ? $show_complaint_part : 'all';
?>

<?php if ($part === 'all' || $part === 'header'): ?>
<!-- Complaint Overview Header Card -->
<div class="details-card">
    <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:16px;border-bottom:1px solid #E8DDD4;padding-bottom:16px;">
        <div>
            <div style="font-size:11px;font-weight:800;color:#C0392B;text-transform:uppercase;letter-spacing:1px;margin-bottom:4px;">
                🚨 Tenant Dispute Investigation
            </div>
            <h1 class="details-title" style="margin:0 0 6px 0;">Complaint #CP-<?php echo $complaint['complaint_id']; ?></h1>
            <p class="details-subtitle" style="margin:0;">
                📍 Investigating student grievance for property at <strong><?php echo htmlspecialchars($complaint['address']); ?></strong>
            </p>
        </div>
        <div>
            <?php if ($is_resolved): ?>
                <span style="background:rgba(39,174,96,0.12);color:#0F5132;border:1.5px solid #BADBCE;padding:6px 16px;border-radius:50px;font-size:12px;font-weight:800;display:inline-block;">
                    ✓ Resolved
                </span>
            <?php else: ?>
                <span style="background:rgba(192,57,43,0.1);color:#C0392B;border:1.5px solid rgba(192,57,43,0.25);padding:6px 16px;border-radius:50px;font-size:12px;font-weight:800;display:inline-block;">
                    ⏳ Pending Investigation
                </span>
            <?php endif; ?>
        </div>
    </div>

    <!-- Complainant & Category Metadata Grid -->
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:14px;margin-bottom:20px;">
        <!-- Complainant -->
        <div style="background:#FAF7F2;border:1px solid #E8DDD4;border-radius:12px;padding:16px;">
            <span style="font-size:11px;font-weight:800;color:#8C7B74;text-transform:uppercase;letter-spacing:0.5px;display:block;margin-bottom:6px;">
                Complainant Student
            </span>
            <div style="font-weight:800;color:#212529;font-size:15px;">
                👤 <?php echo htmlspecialchars($complaint['student_name']); ?>
            </div>
            <div style="font-size:13px;color:#A4856D;font-weight:700;margin-top:4px;display:flex;align-items:center;gap:6px;">
                📞 <a href="tel:<?php echo htmlspecialchars($complaint['student_mobile']); ?>" style="color:#A4856D;text-decoration:none;">
                    <?php echo htmlspecialchars($complaint['student_mobile']); ?>
                </a>
            </div>
        </div>

        <!-- Dispute Category -->
        <div style="background:#FAF7F2;border:1px solid #E8DDD4;border-radius:12px;padding:16px;">
            <span style="font-size:11px;font-weight:800;color:#8C7B74;text-transform:uppercase;letter-spacing:0.5px;display:block;margin-bottom:6px;">
                Dispute Category
            </span>
            <div style="font-weight:800;color:#3B3330;font-size:14px;background:#FFF8F5;border:1px solid #E8DDD4;padding:6px 12px;border-radius:8px;display:inline-block;">
                <?php echo $cat_text; ?>
            </div>
        </div>
    </div>

    <!-- Grievance Description -->
    <div>
        <span style="font-size:11px;font-weight:800;color:#8C7B74;text-transform:uppercase;letter-spacing:0.5px;display:block;margin-bottom:8px;">
            Student Grievance Statement
        </span>
        <div style="background:#FAF7F2;padding:18px 22px;border-radius:14px;border:1.5px solid #E8DDD4;line-height:1.6;color:#3B3330;font-size:14px;font-style:italic;position:relative;">
            "<?php echo htmlspecialchars($complaint['description']); ?>"
        </div>
    </div>
</div>
<?php endif; ?>

<?php if ($part === 'all' || $part === 'form'): ?>
<?php if ($is_resolved): ?>
<!-- READ-ONLY RESOLUTION FINDINGS -->
<div class="details-card">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;border-bottom:1px solid #E8DDD4;padding-bottom:14px;">
        <h2 style="font-family:'Outfit',sans-serif;font-size:20px;font-weight:900;color:#3B3330;margin:0;">
            Investigation Findings &amp; Resolution (Read Only)
        </h2>
        <span style="font-size:11px;font-weight:800;color:#0F5132;background:#D1E7DD;border:1px solid #BADBCE;padding:4px 12px;border-radius:50px;">
            🔒 Locked Report
        </span>
    </div>

    <!-- Recommendation Badge -->
    <div style="background:#FAF7F2;border:1px solid #E8DDD4;border-radius:12px;padding:16px;margin-bottom:16px;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px;">
        <div>
            <span style="font-size:11px;font-weight:800;color:#8C7B74;text-transform:uppercase;display:block;margin-bottom:4px;">Final Recommendation Outcome</span>
            <?php
            $rec = isset($complaint['recommendation']) ? $complaint['recommendation'] : '';
            if ($rec === 'resolved') {
                echo '<span style="background:rgba(39,174,96,0.1);color:#0F5132;border:1.5px solid #BADBCE;padding:6px 14px;border-radius:50px;font-weight:800;font-size:13px;">🤝 Resolved on Site (Settled)</span>';
            } elseif ($rec === 'uphold') {
                echo '<span style="background:rgba(192,57,43,0.1);color:#C0392B;border:1.5px solid rgba(192,57,43,0.3);padding:6px 14px;border-radius:50px;font-weight:800;font-size:13px;">⚠️ Uphold Complaint (Landlord Violation)</span>';
            } elseif ($rec === 'dismiss') {
                echo '<span style="background:rgba(39,174,96,0.1);color:#0F5132;border:1.5px solid #BADBCE;padding:6px 14px;border-radius:50px;font-weight:800;font-size:13px;">✅ Dismiss Complaint (No Violation)</span>';
            } else {
                echo '<span style="background:rgba(243,156,18,0.1);color:#B7950B;border:1.5px solid #F9E79F;padding:6px 14px;border-radius:50px;font-weight:800;font-size:13px;">🚨 Escalated to Administration</span>';
            }
            ?>
        </div>
        <div>
            <span style="font-size:11px;font-weight:800;color:#8C7B74;text-transform:uppercase;display:block;margin-bottom:4px;">Visit Fee Charged</span>
            <div style="font-size:15px;font-weight:800;color:#212529;">
                <?php 
                $fee = (float)(isset($complaint['visit_fee_charged']) ? $complaint['visit_fee_charged'] : 0);
                echo ($fee > 0) ? ('LKR ' . number_format($fee, 2)) : 'LKR 0.00 (No Charge / Waived)';
                ?>
            </div>
        </div>
    </div>

    <!-- Findings Text -->
    <div style="background:#FAF7F2;border:1px solid #E8DDD4;border-radius:12px;padding:16px;margin-bottom:20px;">
        <span style="font-size:11px;font-weight:800;color:#8C7B74;text-transform:uppercase;display:block;margin-bottom:6px;">Field Agent Findings &amp; Observations</span>
        <div style="font-size:13px;color:#3B3330;line-height:1.6;background:#FFFFFF;border:1px solid #E8DDD4;padding:12px 16px;border-radius:8px;">
            <?php echo htmlspecialchars($complaint['findings']); ?>
        </div>
    </div>

    <div style="text-align:center;">
        <a href="dashboard.php?tab=complaints" style="display:inline-flex;align-items:center;gap:8px;padding:12px 24px;background:#A4856D;color:#FFFFFF;border-radius:8px;text-decoration:none;font-weight:800;font-size:13px;">
            ← Return to Assigned Disputes
        </a>
    </div>
</div>

<?php else: ?>
<!-- ACTIVE INVESTIGATION REPORT FORM -->
<div class="details-card">
    <div style="margin-bottom:20px;border-bottom:1px solid #E8DDD4;padding-bottom:14px;">
        <h2 style="font-family:'Outfit',sans-serif;font-size:22px;font-weight:900;color:#3B3330;margin:0 0 4px 0;">
            Investigation Report &amp; Findings
        </h2>
        <p style="font-size:13px;color:#8C7B74;margin:0;">
            Record your physical inspection observations, interviews with student &amp; landlord, and final dispute recommendation.
        </p>
    </div>

    <form action="actions/submit_complaint_report.php" method="POST">
        <input type="hidden" name="complaint_id" value="<?php echo $complaint['complaint_id']; ?>">

        <!-- Field Findings -->
        <div class="form-group" style="margin-bottom:20px;">
            <label class="form-label form-label--required" style="font-weight:800;color:#212529;font-size:13px;display:block;margin-bottom:6px;text-transform:uppercase;letter-spacing:0.5px;">
                Field Findings &amp; Inspection Observations *
            </label>
            <textarea class="textarea-styled" name="findings"
                      placeholder="Provide detailed summary of physical site inspection, verified utility bills, interviews with student and landlord, and key evidence observed..."
                      required style="min-height:110px;font-size:13px;line-height:1.5;"></textarea>
        </div>

        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:20px;margin-bottom:24px;">
            <!-- Recommendation -->
            <div class="form-group">
                <label class="form-label form-label--required" style="font-weight:800;color:#212529;font-size:13px;display:block;margin-bottom:6px;text-transform:uppercase;letter-spacing:0.5px;">
                    Final Recommendation *
                </label>
                <select class="select-styled" name="recommendation" required style="font-size:13px;font-weight:600;">
                    <option value="">-- Select Formal Resolution --</option>
                    <option value="resolved">Resolved on Site (Dispute settled between parties)</option>
                    <option value="dismiss">Dismiss Complaint (No landlord violation found)</option>
                    <option value="uphold">Uphold Complaint (Landlord violated terms)</option>
                    <option value="escalate">Escalate Complaint (Unresolved / Uncooperative)</option>
                </select>
            </div>

            <!-- Visit Fee Input -->
            <div class="form-group">
                <label class="form-label" style="font-weight:800;color:#212529;font-size:13px;display:block;margin-bottom:6px;text-transform:uppercase;letter-spacing:0.5px;">
                    Visit Fee Charged (LKR)
                </label>
                <div style="position:relative;">
                    <span style="position:absolute;left:14px;top:50%;transform:translateY(-50%);font-weight:800;color:#8C7B74;font-size:13px;">LKR</span>
                    <input type="number" class="form-input" name="visit_fee" min="0" step="50" value="0" placeholder="0.00"
                           style="border-radius:10px;background:#FFFFFF;border:1.5px solid #E8DDD4;padding:12px 12px 12px 52px;box-sizing:border-box;width:100%;font-weight:800;font-size:14px;color:#212529;">
                </div>
                <div style="font-size:12px;color:#8C7B74;margin-top:6px;font-weight:600;">
                    💡 Set to <strong>0</strong> if no fee is charged (e.g. waived or free visit).
                </div>
            </div>
        </div>

        <button type="submit" class="btn-camera-capture" style="width:100%;padding:16px;font-size:14px;font-weight:800;justify-content:center;background:#1E1E1E;color:#FFFFFF;box-shadow:0 4px 12px rgba(0,0,0,0.12);">
            🚀 Submit Complaint Findings Report
        </button>
    </form>
</div>
<?php endif; ?>
<?php endif; ?>
