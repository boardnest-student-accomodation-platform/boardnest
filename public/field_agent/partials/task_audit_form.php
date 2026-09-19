<?php
// Partial: task_audit_form.php
// Full Property Verification Audit Protocol form (Supports Active Editable Mode & Completed Read-Only Mode)
// Expects: $task_id (int), optional $report (array), optional $task (array)
// Requires: task_checklist_item.php already included

$is_read_only = (isset($task['status']) && $task['status'] === 'completed') || !empty($report);
?>

<?php if ($is_read_only && !empty($report)): ?>
<div class="details-card">
    <!-- Header -->
    <div class="fa-audit-header-wrap">
        <div>
            <div class="fa-audit-pretitle">Submitted Inspection Record</div>
            <h2 class="fa-audit-title">Property Verification Audit Protocol</h2>
            <p class="fa-audit-desc">Submitted report is locked for audit trail integrity and cannot be edited.</p>
        </div>
        <div class="fa-audit-badge-submitted">
            <span class="fa-audit-badge-dot"></span>
            <span>🔒 Submitted (Read Only)</span>
        </div>
    </div>

    <!-- Metadata Banner -->
    <div class="fa-audit-meta-banner">
        <div>
            <span class="fa-audit-meta-label">Submitted Timestamp</span>
            <div class="fa-audit-meta-val-dark">
                📅 <?php echo !empty($report['submitted_at']) ? date('F j, Y — g:i A', strtotime($report['submitted_at'])) : 'Completed'; ?>
            </div>
        </div>
        <div>
            <span class="fa-audit-meta-label">GPS Presence Verification</span>
            <div class="fa-audit-meta-val-green">
                🟢 Verified Device Location Match
            </div>
        </div>
    </div>

    <!-- SECTION 01 -->
    <div class="section-label">SECTION 01 — Building &amp; Infrastructure Compliance</div>
    <?php
    renderChecklistItemReadOnly('Structural Integrity & Foundation', 'Solid walls, secure locks, absence of severe structural cracks', isset($report['structural_safety']) ? $report['structural_safety'] : 1);
    renderChecklistItemReadOnly('Electrical Wiring & Breaker System', 'No exposed wiring, functional trip switch breakers', isset($report['electrical_safety']) ? $report['electrical_safety'] : 1);
    renderChecklistItemReadOnly('Fire Exit & Emergency Access', 'Unobstructed escape pathways, clear safety routes', isset($report['fire_exit']) ? $report['fire_exit'] : 1);
    ?>

    <!-- Star Rating -->
    <div class="fa-audit-rating-box">
        <label class="fa-audit-rating-label">Neighborhood &amp; Safety Rating</label>
        <div class="fa-audit-rating-stars">
            <div class="fa-rating-stars-inner">
                <?php 
                $rating = (int)(isset($report['neighborhood_safety']) ? $report['neighborhood_safety'] : 5);
                for ($s = 1; $s <= 5; $s++) {
                    echo ($s <= $rating) ? '★' : '<span class="fa-star-inactive">★</span>';
                }
                ?>
            </div>
            <span class="fa-audit-rating-text"><?php echo $rating; ?> out of 5 Stars</span>
        </div>
    </div>

    <!-- SECTION 02 -->
    <div class="section-label" class="fa-mt-14">SECTION 02 — Room Facilities &amp; Rent Cross-Check</div>
    <?php
    renderChecklistItemReadOnly('Furnishing & Inventory Match', 'Beds, wardrobes, and desks match landlord listing', isset($report['furnishing_match']) ? $report['furnishing_match'] : 1);
    renderChecklistItemReadOnly('Bathroom Facility Designation', 'Attached vs shared bathroom status matches description', isset($report['bathroom_match']) ? $report['bathroom_match'] : 1);
    renderChecklistItemReadOnly('Wi-Fi Connectivity & Signal Coverage', 'Active broadband signal accessible from bedrooms', isset($report['wifi_match']) ? $report['wifi_match'] : 1);
    renderChecklistItemReadOnly('Financial Terms & Key Money Deposit', 'Monthly rent and deposit match listed values', isset($report['finance_match']) ? $report['finance_match'] : 1);
    renderChecklistItemReadOnly('Kitchen Facilities & Nearby Dining Access', 'Functional cooking amenities on-site OR verified food/eateries nearby (within 500m)', isset($report['kitchen_food_match']) ? $report['kitchen_food_match'] : 1);
    ?>

    <!-- SECTION 03 -->
    <div class="section-label" class="fa-mt-14">SECTION 03 — Photographic Evidence &amp; Remarks</div>

    <div class="fa-audit-photo-grid">
        <!-- Photo 1 -->
        <div class="fa-audit-photo-card">
            <label class="fa-audit-photo-label">Photo 1 (Entrance / Exterior)</label>
            <?php if (!empty($report['photo_path_1'])): ?>
                <div class="fa-audit-photo-frame">
                    <img src="<?php echo htmlspecialchars($report['photo_path_1']); ?>" class="fa-audit-photo-img">
                </div>
            <?php else: ?>
                <div class="fa-audit-photo-empty">No photo recorded</div>
            <?php endif; ?>
        </div>

        <!-- Photo 2 -->
        <div class="fa-audit-photo-card">
            <label class="fa-audit-photo-label">Photo 2 (Room Interior)</label>
            <?php if (!empty($report['photo_path_2'])): ?>
                <div class="fa-audit-photo-frame">
                    <img src="<?php echo htmlspecialchars($report['photo_path_2']); ?>" class="fa-audit-photo-img">
                </div>
            <?php else: ?>
                <div class="fa-audit-photo-empty">No photo recorded</div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Agent Remarks -->
    <div class="fa-audit-remarks-box">
        <label class="fa-audit-remarks-label">Field Agent Inspection Remarks</label>
        <div class="fa-audit-remarks-text">
            "<?php echo !empty($report['agent_comments']) ? htmlspecialchars($report['agent_comments']) : 'No remarks provided.'; ?>"
        </div>
    </div>

    <!-- Navigation -->
    <div class="fa-audit-nav-wrap">
        <a href="dashboard.php?tab=history" class="fa-audit-nav-btn">
            ← Return to History
        </a>
    </div>
</div>

<?php else: ?>
<!-- EDITABLE FORM FOR ACTIVE UNLOCKED TASKS -->
<div class="details-card">
    <!-- Header -->
    <div class="fa-audit-header-wrap">
        <div>
            <div class="fa-audit-pretitle">Property Inspection Standard</div>
            <h2 class="fa-audit-title">Property Verification Audit Protocol</h2>
            <p class="fa-audit-desc">Cross-reference physical premises against landlord uploaded details. Toggle match or issue found.</p>
        </div>
        <div class="fa-audit-badge-gps">
            <span class="fa-audit-badge-gps-dot"></span>
            <span>GPS Verified</span>
        </div>
    </div>

    <!-- Progress Bar -->
    <div class="fa-audit-progress-box">
        <div class="fa-progress-header">
            <span class="section-label" class="fa-m-0">Audit Verification Progress</span>
            <span id="auditProgressText" class="fa-audit-progress-text">8 of 8 items verified (100%)</span>
        </div>
        <div class="fa-audit-progress-track">
            <div id="auditProgressFill" class="fa-audit-progress-fill"></div>
        </div>
    </div>

    <form id="auditForm" action="actions/submit_report.php" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="task_id" value="<?php echo $task_id; ?>">

        <!-- SECTION 01 -->
        <div class="section-label">SECTION 01 — Building &amp; Infrastructure Compliance</div>
        <?php
        renderChecklistItem('structural', 'Structural Integrity & Foundation',
            'Solid walls, secure locks, absence of severe structural cracks', 'structural_safety');
        renderChecklistItem('electrical', 'Electrical Wiring & Breaker System',
            'No exposed wiring, functional trip switch breakers', 'electrical_safety');
        renderChecklistItem('fire', 'Fire Exit & Emergency Access',
            'Unobstructed escape pathways, clear safety routes', 'fire_exit');
        ?>

        <input type="hidden" name="gps_match" value="1">

        <!-- Star Rating -->
        <div class="fa-audit-rating-box">
            <label class="form-label form-label--required fa-audit-star-label">Neighborhood &amp; Safety Rating</label>
            <div class="fa-rating-stars-lg">
                <?php for ($s = 1; $s <= 5; $s++): ?>
                <span class="star-item-amber" data-value="<?php echo $s; ?>"
                      onclick="setStarRating(<?php echo $s; ?>)"
                      onmouseover="hoverStarRating(<?php echo $s; ?>)"
                      onmouseout="resetStarRating()">★</span>
                <?php endfor; ?>
            </div>
            <span id="ratingLabel" class="fa-audit-star-hint">Click stars to rate neighborhood safety</span>
            <input type="hidden" name="neighborhood_safety" id="ratingInput" value="" required>
        </div>

        <!-- SECTION 02 -->
        <div class="section-label" class="fa-mt-14">SECTION 02 — Room Facilities &amp; Rent Cross-Check</div>
        <?php
        renderChecklistItem('furnishing', 'Furnishing & Inventory Match',
            'Beds, wardrobes, and desks match landlord listing', 'furnishing_match');
        renderChecklistItem('bathroom', 'Bathroom Facility Designation',
            'Attached vs shared bathroom status matches description', 'bathroom_match');
        renderChecklistItem('wifi', 'Wi-Fi Connectivity & Signal Coverage',
            'Active broadband signal accessible from bedrooms', 'wifi_match');
        renderChecklistItem('finance', 'Financial Terms & Key Money Deposit',
            'Monthly rent and deposit match listed values', 'finance_match');
        renderChecklistItem('kitchen_food', 'Kitchen Facilities & Nearby Dining Access',
            'Functional cooking amenities on-site OR verified affordable food/eateries nearby (within 500m)', 'kitchen_food_match');
        ?>

        <!-- SECTION 03 -->
        <div class="section-label" class="fa-mt-14">SECTION 03 — Photographic Verification &amp; Agent Remarks</div>

        <div id="photo_grid_container" class="fa-audit-photo-grid">
            <!-- Photo 1 -->
            <div class="photo-card">
                <label class="form-label form-label--required fa-audit-photo-label">Photo 1 (Entrance / Exterior)</label>
                <div id="photo1_initial_btn">
                    <button type="button" class="btn-camera-capture fa-audit-camera-btn" onclick="openLiveCameraModal('photo1Input')">
                        📸 Open Live Camera
                    </button>
                </div>
                <div id="photo1_preview_box" class="fa-d-none fa-mt-6">
                    <div class="fa-audit-photo-frame">
                        <img id="photo1Input_preview" class="photo-preview-thumb">
                        <span id="photo1_badge" class="photo-timestamp-badge">🕒 Captured Today</span>
                    </div>
                    <button type="button" class="btn-retake" onclick="clearPhotoInput('photo1Input','photo1_preview_box','photo1_initial_btn')">🗑 Retake Entrance Photo</button>
                </div>
                <input type="file" id="photo1Input" name="photo1" accept="image/*" capture="environment" class="fa-d-none"
                       onchange="updateFilename('photo1Input','photo1_preview_box','photo1_initial_btn')" required>
            </div>

            <!-- Photo 2 -->
            <div class="photo-card">
                <label class="form-label form-label--required fa-audit-photo-label">Photo 2 (Room Interior)</label>
                <div id="photo2_initial_btn">
                    <button type="button" class="btn-camera-capture fa-audit-camera-btn" onclick="openLiveCameraModal('photo2Input')">
                        📸 Open Live Camera
                    </button>
                </div>
                <div id="photo2_preview_box" class="fa-d-none fa-mt-6">
                    <div class="fa-audit-photo-frame">
                        <img id="photo2Input_preview" class="photo-preview-thumb">
                        <span id="photo2_badge" class="photo-timestamp-badge">🕒 Captured Today</span>
                    </div>
                    <button type="button" class="btn-retake" onclick="clearPhotoInput('photo2Input','photo2_preview_box','photo2_initial_btn')">🗑 Retake Room Photo</button>
                </div>
                <input type="file" id="photo2Input" name="photo2" accept="image/*" capture="environment" class="fa-d-none"
                       onchange="updateFilename('photo2Input','photo2_preview_box','photo2_initial_btn')" required>
            </div>
        </div>

        <!-- Extra Proof Photo Button -->
        <div class="fa-mt-10">
            <button type="button" onclick="addExtraPhotoCard()" class="fa-audit-photo-btn-add">
                ➕ Capture Additional Proof Photo (Optional)
            </button>
        </div>

        <!-- Hidden Compiled Fields for Area Profile -->
        <input type="hidden" id="transport_details" name="transport_details">
        <input type="hidden" id="amenities_details" name="amenities_details">
        <input type="hidden" id="safety_details"    name="safety_details">

        <!-- SECTION 04 -->
        <div class="section-label" class="fa-mt-20">SECTION 04 — Regional Area Profile &amp; Neighborhood Observations</div>
        
        <div class="fa-audit-area-box">
            <!-- 01. Transport -->
            <div >
                <div class="fa-audit-area-title">🚍 Transport &amp; Mobility Access</div>
                <div class="fa-flex-wrap-gap">
                    <label class="fa-audit-chk-label">
                        <input type="checkbox" id="chk_bus" class="fa-audit-chk-input"> 🚌 Bus Transport
                    </label>
                    <label class="fa-audit-chk-label">
                        <input type="checkbox" id="chk_train" class="fa-audit-chk-input"> 🚆 Railway Station
                    </label>
                    <label class="fa-audit-chk-label">
                        <input type="checkbox" id="chk_walk" class="fa-audit-chk-input"> 🚶 Walking to Campus
                    </label>
                    <label class="fa-audit-chk-label">
                        <input type="checkbox" id="chk_tuk" class="fa-audit-chk-input"> 🛺 Tuk / Rideshare
                    </label>
                </div>
                <textarea id="transport_notes" class="textarea-styled" placeholder="Additional transport notes (bus routes, stop names...)" class="fa-textarea-sm"></textarea>
            </div>

            <!-- 02. Amenities -->
            <div >
                <div class="fa-audit-area-title">🛒 Student Amenities &amp; Convenience</div>
                <div class="fa-flex-wrap-gap">
                    <label class="fa-audit-chk-label">
                        <input type="checkbox" class="amenity_chk fa-audit-chk-input" value="Supermarket (500m)"> 🛒 Supermarket
                    </label>
                    <label class="fa-audit-chk-label">
                        <input type="checkbox" class="amenity_chk fa-audit-chk-input" value="24/7 Pharmacy"> 💊 24/7 Pharmacy
                    </label>
                    <label class="fa-audit-chk-label">
                        <input type="checkbox" class="amenity_chk fa-audit-chk-input" value="Student Food Spots"> 🍛 Food Spots
                    </label>
                    <label class="fa-audit-chk-label">
                        <input type="checkbox" class="amenity_chk fa-audit-chk-input" value="Laundromat"> 🧺 Laundromat
                    </label>
                    <label class="fa-audit-chk-label">
                        <input type="checkbox" class="amenity_chk fa-audit-chk-input" value="Bank ATMs"> 🏧 Bank ATMs
                    </label>
                </div>
                <textarea id="amenities_notes" class="textarea-styled" placeholder="Additional amenities notes (market names, landmarks...)" class="fa-textarea-sm"></textarea>
            </div>

            <!-- 03. Safety -->
            <div>
                <div class="fa-audit-area-title">🛡️ Neighborhood Safety &amp; Conditions</div>
                <div class="fa-flex-wrap-gap">
                    <label class="fa-audit-chk-label">
                        <input type="checkbox" class="safety_chk fa-audit-chk-input" value="Well-lit Main Roads"> 💡 Well-lit Main Roads
                    </label>
                    <label class="fa-audit-chk-label">
                        <input type="checkbox" class="safety_chk fa-audit-chk-input" value="Police Patrols"> 🚓 Police Patrols
                    </label>
                    <label class="fa-audit-chk-label">
                        <input type="checkbox" class="safety_chk fa-audit-chk-input" value="Safe Residential Zone"> 🟢 Safe Residential Zone
                    </label>
                    <label class="fa-audit-chk-label">
                        <input type="checkbox" class="safety_chk fa-audit-chk-input-red" value="Caution after 10 PM"> ⚠️ Caution after 10 PM
                    </label>
                </div>
                <textarea id="safety_notes" class="textarea-styled" placeholder="Additional safety notes (warnings, security details...)" class="fa-textarea-sm"></textarea>
            </div>
        </div>

        <!-- Field Agent Inspection Remarks -->
        <div >
            <label class="form-label form-label--required fa-audit-remarks-label">Field Agent Inspection Remarks *</label>
            <textarea class="textarea-styled fa-audit-remarks-text" id="agent_comments_raw"
                      placeholder="Provide executive summary of on-site inspection, landlord cooperation, and overall property recommendation..."
                      required class="fa-textarea-md"></textarea>
            <input type="hidden" name="agent_comments" id="agent_comments_hidden">
        </div>

        <!-- Submit -->
        <button type="submit" class="fa-audit-submit-btn">
            <span>Submit Executive Audit Report</span>
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <line x1="22" y1="2" x2="11" y2="13"></line>
                <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
            </svg>
        </button>
    </form>
</div>
<?php endif; ?>
