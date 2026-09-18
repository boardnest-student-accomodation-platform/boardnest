<?php
// Partial: complaint_view.php
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
    <div class="fa-complaint-header-wrap">
        <div>
            <div class="fa-complaint-header-title">
                🚨 Tenant Dispute Investigation
            </div>
            <h1 class="details-title" >Complaint #CP-<?php echo $complaint['complaint_id']; ?></h1>
            <p class="details-subtitle" class="fa-m-0">
                📍 Investigating student grievance for property at <strong><?php echo htmlspecialchars($complaint['address']); ?></strong>
            </p>
        </div>
        <div>
            <?php if ($is_resolved): ?>
                <span class="fa-complaint-resolved-badge">
                    ✓ Resolved
                </span>
            <?php else: ?>
                <span class="fa-complaint-pending-badge">
                    ⏳ Pending Investigation
                </span>
            <?php endif; ?>
        </div>
    </div>

    <!-- Complainant & Category Metadata Grid -->
    <div class="fa-complaint-grid">
        <!-- Complainant -->
        <div class="fa-complaint-grid-item">
            <span class="fa-complaint-grid-label">
                Complainant Student
            </span>
            <div class="fa-complaint-grid-value">
                👤 <?php echo htmlspecialchars($complaint['student_name']); ?>
            </div>
            <div class="fa-complaint-grid-phone">
                📞 <a href="tel:<?php echo htmlspecialchars($complaint['student_mobile']); ?>">
                    <?php echo htmlspecialchars($complaint['student_mobile']); ?>
                </a>
            </div>
        </div>

        <!-- Dispute Category -->
        <div class="fa-complaint-grid-item">
            <span class="fa-complaint-grid-label">
                Dispute Category
            </span>
            <div class="fa-complaint-category">
                <?php echo $cat_text; ?>
            </div>
        </div>
    </div>

    <!-- Grievance Description -->
    <div>
        <span class="fa-complaint-desc-label">
            Student Grievance Statement
        </span>
        <div class="fa-complaint-desc-box">
            "<?php echo htmlspecialchars($complaint['description']); ?>"
        </div>
    </div>
</div>
<?php endif; ?>

<?php if ($part === 'all' || $part === 'form'): ?>
<?php if ($is_resolved): ?>
<!-- READ-ONLY RESOLUTION FINDINGS -->
<div class="details-card">
    <div class="fa-complaint-findings-header">
        <h2 class="fa-complaint-findings-title">
            Investigation Findings &amp; Resolution (Read Only)
        </h2>
        <span class="fa-complaint-locked-badge">
            🔒 Locked Report
        </span>
    </div>

    <!-- Recommendation Badge -->
    <div class="fa-complaint-rec-wrap">
        <div>
            <span class="fa-complaint-rec-label">Final Recommendation Outcome</span>
            <?php
            $rec = isset($complaint['recommendation']) ? $complaint['recommendation'] : '';
            if ($rec === 'resolved') {
                echo '<span class="fa-complaint-rec-resolved">🤝 Resolved on Site (Settled)</span>';
            } elseif ($rec === 'uphold') {
                echo '<span class="fa-complaint-rec-uphold">⚠️ Uphold Complaint (Landlord Violation)</span>';
            } elseif ($rec === 'dismiss') {
                echo '<span class="fa-complaint-rec-dismiss">✅ Dismiss Complaint (No Violation)</span>';
            } else {
                echo '<span class="fa-complaint-rec-escalate">🚨 Escalated to Administration</span>';
            }
            ?>
        </div>
        <div>
            <span class="fa-complaint-rec-label">Visit Fee Charged</span>
            <div class="fa-complaint-fee-value">
                <?php 
                $fee = (float)(isset($complaint['visit_fee_charged']) ? $complaint['visit_fee_charged'] : 0);
                echo ($fee > 0) ? ('LKR ' . number_format($fee, 2)) : 'LKR 0.00 (No Charge / Waived)';
                ?>
            </div>
        </div>
    </div>

    <!-- Findings Text -->
    <div class="fa-complaint-findings-box">
        <span class="fa-complaint-rec-label">Field Agent Findings &amp; Observations</span>
        <div class="fa-complaint-findings-text">
            <?php echo htmlspecialchars($complaint['findings']); ?>
        </div>
    </div>

    <div class="fa-text-center">
        <a href="dashboard.php?tab=complaints" class="fa-complaint-return-btn">
            ← Return to Assigned Disputes
        </a>
    </div>
</div>

<?php else: ?>
<!-- ACTIVE INVESTIGATION REPORT FORM -->
<div class="details-card">
    <div class="fa-complaint-form-header">
        <h2 class="fa-complaint-form-title">
            Investigation Report &amp; Findings
        </h2>
        <p class="fa-complaint-form-desc">
            Record your physical inspection observations, interviews with student &amp; landlord, and final dispute recommendation.
        </p>
    </div>

    <form action="actions/submit_complaint_report.php" method="POST">
        <input type="hidden" name="complaint_id" value="<?php echo $complaint['complaint_id']; ?>">

        <!-- Field Findings -->
        <div class="form-group" class="fa-mb-20">
            <label class="form-label form-label--required fa-complaint-form-label">
                Field Findings &amp; Inspection Observations *
            </label>
            <textarea class="textarea-styled fa-complaint-form-textarea" name="findings"
                      placeholder="Provide detailed summary of physical site inspection, verified utility bills, interviews with student and landlord, and key evidence observed..."
                      required></textarea>
        </div>

        <div class="fa-complaint-form-grid">
            <!-- Recommendation -->
            <div class="form-group">
                <label class="form-label form-label--required fa-complaint-form-label">
                    Final Recommendation *
                </label>
                <select class="select-styled fa-complaint-form-select" name="recommendation" required>
                    <option value="">-- Select Formal Resolution --</option>
                    <option value="resolved">Resolved on Site (Dispute settled between parties)</option>
                    <option value="dismiss">Dismiss Complaint (No landlord violation found)</option>
                    <option value="uphold">Uphold Complaint (Landlord violated terms)</option>
                    <option value="escalate">Escalate Complaint (Unresolved / Uncooperative)</option>
                </select>
            </div>

            <!-- Visit Fee Input -->
            <div class="form-group">
                <label class="form-label fa-complaint-form-label">
                    Visit Fee Charged (LKR)
                </label>
                <div class="fa-complaint-fee-wrapper">
                    <span class="fa-complaint-fee-symbol">LKR</span>
                    <input type="number" class="form-input fa-complaint-fee-input" name="visit_fee" min="0" step="50" value="0" placeholder="0.00">
                </div>
                <div class="fa-complaint-fee-hint">
                    💡 Set to <strong>0</strong> if no fee is charged (e.g. waived or free visit).
                </div>
            </div>
        </div>

        <button type="submit" class="btn-camera-capture fa-complaint-submit-btn">
            🚀 Submit Complaint Findings Report
        </button>
    </form>
</div>
<?php endif; ?>
<?php endif; ?>
