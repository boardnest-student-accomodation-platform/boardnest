<?php
// Partial: task_audit_form.php
// Full Property Verification Audit Protocol form (Supports Active Editable Mode & Completed Read-Only Mode)
// Expects: $task_id (int), optional $report (array), optional $task (array)
// Requires: task_checklist_item.php already included
require_once __DIR__ . '/icons.php';

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
            <span><?php echo fa_icon('lock', '', 12); ?> Submitted (Read Only)</span>
        </div>
    </div>

    <!-- Metadata Banner -->
    <div class="fa-audit-meta-banner">
        <div>
            <span class="fa-audit-meta-label">Submitted Timestamp</span>
            <div class="fa-audit-meta-val-dark">
                <?php echo fa_icon('calendar', '', 13); ?> <?php echo !empty($report['submitted_at']) ? date('F j, Y — g:i A', strtotime($report['submitted_at'])) : 'Completed'; ?>
            </div>
        </div>
        <div>
            <span class="fa-audit-meta-label">GPS Presence Verification</span>
            <?php if (!isset($report['gps_match']) || $report['gps_match'] === '' || $report['gps_match'] === null): ?>
                <div class="fa-audit-meta-val-unrecorded"><?php echo fa_icon('dot-gray'); ?> Not recorded</div>
            <?php elseif ((int)$report['gps_match'] === 1): ?>
                <div class="fa-audit-meta-val-green"><?php echo fa_icon('check-circle', '', 14, '#0f5132'); ?> Verified Device Location Match</div>
            <?php else: ?>
                <div class="fa-audit-meta-val-red"><?php echo fa_icon('x-circle', '', 14, '#dc3545'); ?> GPS Location Mismatch</div>
            <?php endif; ?>
        </div>
    </div>

    <!-- SECTION 01 -->
    <div class="section-label">SECTION 01 — Building &amp; Infrastructure Compliance</div>
    <?php
    renderChecklistItemReadOnly('Structural Integrity & Foundation', 'Solid walls, secure locks, absence of severe structural cracks', isset($report['structural_safety']) ? $report['structural_safety'] : null);
    renderChecklistItemReadOnly('Electrical Wiring & Breaker System', 'No exposed wiring, functional trip switch breakers', isset($report['electrical_safety']) ? $report['electrical_safety'] : null);
    renderChecklistItemReadOnly('Fire Exit & Emergency Access', 'Unobstructed escape pathways, clear safety routes', isset($report['fire_exit']) ? $report['fire_exit'] : null);
    ?>

    <!-- Star Rating -->
    <div class="fa-audit-rating-box">
        <label class="fa-audit-rating-label">Neighborhood &amp; Safety Rating</label>
        <div class="fa-audit-rating-stars">
            <?php 
            $has_rating = isset($report['neighborhood_safety']) && $report['neighborhood_safety'] !== '' && $report['neighborhood_safety'] !== null;
            if ($has_rating):
                $rating = (int)$report['neighborhood_safety'];
            ?>
                <div class="fa-rating-stars-inner">
                    <?php 
                    for ($s = 1; $s <= 5; $s++) {
                        echo ($s <= $rating) ? fa_icon('star', '', 16, '#f59e0b') : '<span class="fa-star-inactive">' . fa_icon('star', '', 16, '#d1d5db') . '</span>';
                    }
                    ?>
                </div>
                <span class="fa-audit-rating-text"><?php echo $rating; ?> out of 5 Stars</span>
            <?php else: ?>
                <span class="fa-audit-rating-text" style="color: #6c757d; font-style: italic;">Not recorded</span>
            <?php endif; ?>
        </div>
    </div>

    <!-- SECTION 02 -->
    <div class="section-label fa-mt-14">SECTION 02 — Room Facilities &amp; Rent Cross-Check</div>
    <?php
    renderChecklistItemReadOnly('Furnishing & Inventory Match', 'Beds, wardrobes, and desks match landlord listing', isset($report['furnishing_match']) ? $report['furnishing_match'] : null);
    renderChecklistItemReadOnly('Bathroom Facility Designation', 'Attached vs shared bathroom status matches description', isset($report['bathroom_match']) ? $report['bathroom_match'] : null);
    renderChecklistItemReadOnly('Wi-Fi Connectivity & Signal Coverage', 'Active broadband signal accessible from bedrooms', isset($report['wifi_match']) ? $report['wifi_match'] : null);
    renderChecklistItemReadOnly('Financial Terms & Key Money Deposit', 'Monthly rent and deposit match listed values', isset($report['finance_match']) ? $report['finance_match'] : null);
    renderChecklistItemReadOnly('Kitchen Facilities & Nearby Dining Access', 'Functional cooking amenities on-site OR verified food/eateries nearby (within 500m)', isset($report['kitchen_food_match']) ? $report['kitchen_food_match'] : null);
    ?>

    <!-- SECTION 04 -->
    <div class="section-label fa-mt-14">SECTION 04 — Regional Area Profile Verification</div>
    <?php
    $t_details = !empty($report['transport_details']) ? $report['transport_details'] : '';
    $a_details = !empty($report['amenities_details']) ? $report['amenities_details'] : '';
    $s_details = !empty($report['safety_details'])    ? $report['safety_details']    : '';

    $is_t_match = !empty($t_details) && stripos($t_details, 'discrepancy') === false;
    $is_a_match = !empty($a_details) && stripos($a_details, 'discrepancy') === false;
    $is_s_match = !empty($s_details) && stripos($s_details, 'discrepancy') === false;

    renderChecklistItemReadOnly('Transport & Transit Proximity', 'Public transit & accessibility check', !empty($t_details) ? ($is_t_match ? 1 : 0) : null, $t_details);
    renderChecklistItemReadOnly('Local Student Amenities', 'Shopping, food, and daily conveniences check', !empty($a_details) ? ($is_a_match ? 1 : 0) : null, $a_details);
    renderChecklistItemReadOnly('Neighborhood Security & Environment', 'Street lighting & residential safety check', !empty($s_details) ? ($is_s_match ? 1 : 0) : null, $s_details);
    ?>

    <!-- SECTION 03 -->
    <div class="section-label fa-mt-14">SECTION 03 — Photographic Evidence &amp; Remarks</div>

    <div class="fa-audit-photo-grid">
        <!-- Photo 1 -->
        <div class="fa-audit-photo-card">
            <label class="fa-audit-photo-label">Photo 1 (Entrance / Exterior)</label>
            <?php if (!empty($report['photo_path_1'])): ?>
                <div class="fa-audit-photo-frame">
                    <img src="<?php echo htmlspecialchars($report['photo_path_1']); ?>" class="fa-audit-photo-img" alt="Photo 1 Entrance">
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
                    <img src="<?php echo htmlspecialchars($report['photo_path_2']); ?>" class="fa-audit-photo-img" alt="Photo 2 Room">
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
<!-- EDITABLE FORM FOR ACTIVE UNLOCKED TASKS (2-STAGE WALKTHROUGH WIZARD) -->
<div class="details-card fa-audit-form-card">
    
    <!-- STICKY TOP AUDIT HEADER: Slim progress + Underlined Tabs -->
    <div class="fa-audit-sticky-header">
        <div class="fa-audit-sticky-header-row">
            <div>
                <span class="fa-audit-sticky-label">Field Inspection Progress</span>
                <div id="auditProgressText" class="fa-audit-sticky-counter">0 of 8 verified</div>
            </div>
            
            <div class="fa-audit-top-actions">
                <button type="button" class="fa-btn-hazard-top" onclick="triggerEmergencyHazard()" title="Report Immediate Severe Hazard">
                    <?php echo fa_icon('alert', '', 14, '#FFFFFF'); ?> Flag Critical Hazard
                </button>
            </div>
        </div>

        <!-- Slim progress track bar -->
        <div class="fa-audit-progress-track">
            <div id="auditProgressFill" class="fa-audit-progress-fill" style="width: 0%;"></div>
        </div>

        <!-- TAPPABLE TAB BAR (Underline style: brown active, grey inactive, completion ticks) -->
        <div class="fa-tab-nav-underline" role="tablist">
            <button type="button" id="tab_btn_outside" class="fa-tab-underline-btn active" onclick="switchAuditTab(1)">
                <span>Outside</span>
                <span id="outside_tick" class="tab-tick" style="display:none;">✓</span>
            </button>
            <button type="button" id="tab_btn_inside" class="fa-tab-underline-btn" onclick="switchAuditTab(2)">
                <span>Inside &amp; Photos</span>
                <span id="inside_tick" class="tab-tick" style="display:none;">✓</span>
            </button>
        </div>
    </div>

    <!-- GPS Match Status Bar -->
    <?php $has_geofence = !empty($_SESSION['geofence_passed_' . $task_id]); ?>
    <div class="fa-audit-gps-strip">
        <div class="fa-audit-gps-status">
            <span class="fa-audit-badge-gps-dot" style="<?php echo $has_geofence ? '' : 'background:#ffc107;box-shadow:0 0 0 2px rgba(255,193,7,0.25);'; ?>"></span>
            <span id="gps_status_text"><?php echo $has_geofence ? 'GPS Verified On-Site' : 'GPS Pending Verification'; ?></span>
        </div>
        <button type="button" id="btn_demo_bypass_gps" onclick="document.getElementById('gps_match_input').value='1'; document.getElementById('gps_status_text').innerText='GPS Verified (Demo Bypass)'; this.style.display='none';" class="fa-btn-demo-pill">
            Demo Bypass
        </button>
    </div>

    <form id="auditForm" action="actions/submit_report.php" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="task_id" value="<?php echo $task_id; ?>">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
        <input type="hidden" name="gps_match" id="gps_match_input" value="<?php echo $has_geofence ? '1' : '0'; ?>">

        <!-- Compiled Area Profile Hidden Inputs -->
        <input type="hidden" id="transport_details" name="transport_details">
        <input type="hidden" id="amenities_details" name="amenities_details">
        <input type="hidden" id="safety_details"    name="safety_details">

        <!-- ========================================================
             TAB 1: OUTSIDE THE PROPERTY
             ======================================================== -->
        <div id="audit_tab_1" class="fa-audit-tab-pane">
            <div class="section-label">SECTION 01 — Building &amp; Infrastructure Compliance</div>
            
            <?php
            renderChecklistItem('structural', 'Structural Integrity & Foundation',
                'Solid walls, secure entrance locks, absence of severe structural cracks', 'structural_safety');
            renderChecklistItem('electrical', 'Electrical Wiring & Breaker System',
                'No exposed live wiring, functional trip switch breakers', 'electrical_safety');
            renderChecklistItem('fire', 'Fire Exit & Emergency Access',
                'Unobstructed escape pathways, clear safety exit routes', 'fire_exit');
            ?>

            <!-- Neighborhood Star Rating -->
            <div class="fa-audit-rating-box">
                <label class="form-label form-label--required fa-audit-star-label">Neighborhood Safety &amp; Environment Rating</label>
                <div class="fa-rating-stars-lg">
                    <?php for ($s = 1; $s <= 5; $s++): ?>
                    <span class="star-item-amber" data-value="<?php echo $s; ?>"
                          onclick="setStarRating(<?php echo $s; ?>)"
                          onmouseover="hoverStarRating(<?php echo $s; ?>)"
                          onmouseout="resetStarRating()"><?php echo fa_icon('star', '', 28); ?></span>
                    <?php endfor; ?>
                </div>
                <span id="ratingLabel" class="fa-audit-star-hint">Tap stars to rate neighborhood safety (1 = Low, 5 = Excellent)</span>
                <input type="hidden" name="neighborhood_safety" id="ratingInput" value="" required>
            </div>

            <!-- SECTION 04: Regional Area Profile Cross-Verification -->
            <div class="section-label fa-mt-20">SECTION 04 — Regional Area Profile &amp; Observations</div>
            <p class="fa-accordion-lead-desc">
                Review the location access and neighborhood features assigned by the landlord. Tap <strong>Verified Match</strong> if confirmed accurate on-site, or <strong>Issue Found</strong> to log a discrepancy.
            </p>

            <?php
            $prop_address = !empty($task['address']) ? $task['address'] : 'Property Location';
            $prop_city    = !empty($task['city']) ? $task['city'] : 'Colombo';

            $landlord_transport = 'Direct access to ' . $prop_address . ' (' . $prop_city . '), public bus transport routes within 200m–400m, and active on-demand tuk-tuk / rideshare connectivity to campus.';

            $landlord_amenities = !empty($task['facilities'])
                ? $task['facilities'] . '; daily student groceries, pharmacy, eateries, and commercial conveniences available within 500m in ' . $prop_city . '.'
                : 'Local supermarket/grocery within 500m, affordable student food spots, pharmacy access, and bank ATMs located in ' . $prop_city . '.';

            $landlord_safety = 'Well-lit municipal road access, secure residential perimeter, and standard neighborhood safety conditions.';

            renderChecklistItem(
                'area_transport',
                'Transport & Transit Proximity',
                'Verify on-site accessibility matches landlord declaration',
                'area_transport',
                $landlord_transport
            );

            renderChecklistItem(
                'area_amenities',
                'Student Amenities & Conveniences',
                'Verify neighborhood shopping, food, and dining match landlord declaration',
                'area_amenities',
                $landlord_amenities
            );

            renderChecklistItem(
                'area_safety',
                'Neighborhood Security & Lighting',
                'Verify street lighting, safety, and residential environment match landlord declaration',
                'area_safety',
                $landlord_safety
            );
            ?>

            <!-- Tab 1 Action: Advance to Inside Inspection -->
            <div class="fa-tab-action-row">
                <button type="button" class="fa-btn-tab-next" onclick="switchAuditTab(2)">
                    <span>Next: Inside &amp; Photos &rarr;</span>
                </button>
            </div>
        </div>

        <!-- ========================================================
             TAB 2: INSIDE THE PROPERTY & PHOTOS
             ======================================================== -->
        <div id="audit_tab_2" class="fa-audit-tab-pane" style="display: none;">
            <div class="section-label">SECTION 02 — Room Facilities &amp; Rent Cross-Check</div>
            <?php
            renderChecklistItem('furnishing', 'Furnishing & Inventory Match',
                'Beds, wardrobes, study desks match landlord listing', 'furnishing_match');
            renderChecklistItem('bathroom', 'Bathroom Facility Designation',
                'Attached vs shared bathroom status matches landlord description', 'bathroom_match');
            renderChecklistItem('wifi', 'Wi-Fi Connectivity & Signal Coverage',
                'Active broadband signal accessible and functional from bedrooms', 'wifi_match');
            renderChecklistItem('finance', 'Financial Terms & Key Money Deposit',
                'Monthly rent amount and deposit match listed values', 'finance_match');
            renderChecklistItem('kitchen_food', 'Kitchen Facilities & Nearby Dining Access',
                'Functional cooking amenities on-site OR verified affordable eateries nearby (within 500m)', 'kitchen_food_match');
            ?>

            <!-- SECTION 03: Photos -->
            <div class="section-label fa-mt-20">SECTION 03 — Photographic Verification</div>
            
            <!-- Exterior Photo Reminder Tip -->
            <div class="fa-photo-tip-callout">
                <span class="fa-photo-tip-icon"><?php echo fa_icon('camera', '', 16, '#6F4E37'); ?></span>
                <div>
                    <strong>Photo Inspection Reminder:</strong>
                    <span>Take the <em>Entrance / Exterior</em> photo before entering, or step back out to capture it. Both Entrance and Room Interior photos are mandatory.</span>
                </div>
            </div>

            <div class="fa-photo-status-header">
                <span class="fa-spec-label fa-m-0">Mandatory Verification Photos</span>
                <span id="photo_count_badge" class="fa-photo-count-badge pending">Photos: 0 of 2 required</span>
            </div>

            <div id="photo_grid_container" class="fa-audit-photo-grid">
                <!-- Photo 1: Entrance / Exterior -->
                <div class="photo-card">
                    <label class="form-label form-label--required fa-audit-photo-label">Photo 1 (Entrance / Exterior)</label>
                    <div id="photo1_initial_btn">
                        <button type="button" class="btn-camera-capture fa-audit-camera-btn" onclick="openLiveCameraModal('photo1Input')" style="width: 100%;">
                            <?php echo fa_icon('camera', '', 16); ?> Open Live Camera
                        </button>
                    </div>
                    <div id="photo1_preview_box" style="display: none;" class="fa-mt-6">
                        <div class="fa-audit-photo-frame">
                            <img id="photo1Input_preview" class="photo-preview-thumb" alt="Preview Photo 1">
                            <span id="photo1_badge" class="photo-timestamp-badge"><?php echo fa_icon('clock', '', 12); ?> Captured Today</span>
                        </div>
                        <button type="button" class="btn-retake" onclick="clearPhotoInput('photo1Input','photo1_preview_box','photo1_initial_btn'); updatePhotoCount();">
                            <?php echo fa_icon('trash', '', 13); ?> Retake Entrance Photo
                        </button>
                    </div>
                    <input type="file" id="photo1Input" name="photo1" accept="image/*" capture="environment" style="display: none;"
                           onchange="updateFilename('photo1Input','photo1_preview_box','photo1_initial_btn'); updatePhotoCount();" required>
                </div>

                <!-- Photo 2: Room Interior -->
                <div class="photo-card">
                    <label class="form-label form-label--required fa-audit-photo-label">Photo 2 (Room Interior)</label>
                    <div id="photo2_initial_btn">
                        <button type="button" class="btn-camera-capture fa-audit-camera-btn" onclick="openLiveCameraModal('photo2Input')" style="width: 100%;">
                            <?php echo fa_icon('camera', '', 16); ?> Open Live Camera
                        </button>
                    </div>
                    <div id="photo2_preview_box" style="display: none;" class="fa-mt-6">
                        <div class="fa-audit-photo-frame">
                            <img id="photo2Input_preview" class="photo-preview-thumb" alt="Preview Photo 2">
                            <span id="photo2_badge" class="photo-timestamp-badge"><?php echo fa_icon('clock', '', 12); ?> Captured Today</span>
                        </div>
                        <button type="button" class="btn-retake" onclick="clearPhotoInput('photo2Input','photo2_preview_box','photo2_initial_btn'); updatePhotoCount();">
                            <?php echo fa_icon('trash', '', 13); ?> Retake Room Photo
                        </button>
                    </div>
                    <input type="file" id="photo2Input" name="photo2" accept="image/*" capture="environment" style="display: none;"
                           onchange="updateFilename('photo2Input','photo2_preview_box','photo2_initial_btn'); updatePhotoCount();" required>
                </div>
            </div>

            <!-- Extra Proof Photo Button -->
            <div class="fa-mt-10">
                <button type="button" onclick="addExtraPhotoCard()" class="fa-audit-photo-btn-add">
                    <?php echo fa_icon('plus', '', 14); ?> Capture Additional Proof Photo (Optional)
                </button>
            </div>

            <!-- Field Agent Remarks Card -->
            <div class="checklist-card fa-mt-14">
                <label class="form-label form-label--required fa-audit-remarks-label">Field Agent Inspection Remarks *</label>
                <div class="fa-checklist-subtitle fa-mb-8">Provide executive summary of on-site inspection, physical condition vs listing description, and overall property recommendation.</div>
                <textarea class="textarea-styled fa-audit-remarks-text" id="agent_comments_raw"
                          placeholder="Provide executive summary of on-site inspection, landlord cooperation, physical condition vs listing description, and overall property recommendation..."
                          oninput="if(typeof checkSubmitReadiness==='function') checkSubmitReadiness();"
                          required></textarea>
                <input type="hidden" name="agent_comments" id="agent_comments_hidden">
            </div>

            <!-- Tab 2 Actions (Sticky Bottom Action Bar on Mobile) -->
            <div class="fa-tab-action-row fa-tab-footer-sticky">
                <button type="button" class="fa-btn-tab-back" onclick="switchAuditTab(1)">
                    &larr; Back
                </button>
                <button type="submit" id="btnSubmitAudit" class="fa-audit-submit-btn disabled" disabled>
                    <span>Submit Report</span>
                    <?php echo fa_icon('send', '', 16); ?>
                </button>
            </div>
            <div id="submit_missing_notice" class="fa-submit-missing-notice"></div>
        </div>
    </form>
</div>
<?php endif; ?>
