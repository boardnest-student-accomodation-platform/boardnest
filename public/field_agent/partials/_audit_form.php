<?php
// Partial: _audit_form.php
// Full Property Verification Audit Protocol form (Supports Active Editable Mode & Completed Read-Only Mode)
// Expects: $task_id (int), optional $report (array), optional $task (array)
// Requires: _checklist_item.php already included

$is_read_only = (isset($task['status']) && $task['status'] === 'completed') || !empty($report);
?>

<?php if ($is_read_only && !empty($report)): ?>
<div class="details-card">
    <!-- Header -->
    <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:16px;border-bottom:1px solid #E8DDD4;padding-bottom:16px;">
        <div>
            <div style="font-size:11px;font-weight:800;color:#A4856D;text-transform:uppercase;letter-spacing:1px;margin-bottom:4px;">Submitted Inspection Record</div>
            <h2 style="font-family:'Outfit',sans-serif;font-size:22px;font-weight:900;color:#3B3330;margin:0 0 4px 0;letter-spacing:-0.5px;">Property Verification Audit Protocol</h2>
            <p style="font-size:13px;color:#8C7B74;margin:0;">Submitted report is locked for audit trail integrity and cannot be edited.</p>
        </div>
        <div style="display:flex;align-items:center;gap:6px;background:rgba(39,174,96,0.1);color:#0F5132;border:1.5px solid #BADBCE;padding:6px 14px;border-radius:50px;font-size:11px;font-weight:800;white-space:nowrap;flex-shrink:0;">
            <span style="display:inline-block;width:6px;height:6px;background:#27AE60;border-radius:50%;"></span>
            <span>🔒 Submitted (Read Only)</span>
        </div>
    </div>

    <!-- Metadata Banner -->
    <div style="background:#FAF7F2;border:1px solid #E8DDD4;border-radius:12px;padding:14px 18px;margin-bottom:24px;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px;">
        <div>
            <span style="font-size:11px;font-weight:800;color:#8C7B74;text-transform:uppercase;letter-spacing:0.5px;">Submitted Timestamp</span>
            <div style="font-size:13px;font-weight:800;color:#212529;margin-top:2px;">
                📅 <?php echo !empty($report['submitted_at']) ? date('F j, Y — g:i A', strtotime($report['submitted_at'])) : 'Completed'; ?>
            </div>
        </div>
        <div>
            <span style="font-size:11px;font-weight:800;color:#8C7B74;text-transform:uppercase;letter-spacing:0.5px;">GPS Presence Verification</span>
            <div style="font-size:13px;font-weight:800;color:#0F5132;margin-top:2px;">
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
    <div style="margin-top:6px;background:#FAF7F2;border:1px solid #E8DDD4;border-radius:12px;padding:16px;margin-bottom:14px;">
        <label style="font-size:12px;font-weight:800;color:#212529;text-transform:uppercase;letter-spacing:0.5px;display:block;">Neighborhood &amp; Safety Rating</label>
        <div style="display:flex;align-items:center;gap:10px;margin-top:6px;">
            <div style="display:flex;gap:4px;font-size:22px;color:#F39C12;">
                <?php 
                $rating = (int)(isset($report['neighborhood_safety']) ? $report['neighborhood_safety'] : 5);
                for ($s = 1; $s <= 5; $s++) {
                    echo ($s <= $rating) ? '★' : '<span style="color:#D5C5B9;">★</span>';
                }
                ?>
            </div>
            <span style="font-size:13px;font-weight:800;color:#3B3330;"><?php echo $rating; ?> out of 5 Stars</span>
        </div>
    </div>

    <!-- SECTION 02 -->
    <div class="section-label" style="margin-top:14px;">SECTION 02 — Room Facilities &amp; Rent Cross-Check</div>
    <?php
    renderChecklistItemReadOnly('Furnishing & Inventory Match', 'Beds, wardrobes, and desks match landlord listing', isset($report['furnishing_match']) ? $report['furnishing_match'] : 1);
    renderChecklistItemReadOnly('Bathroom Facility Designation', 'Attached vs shared bathroom status matches description', isset($report['bathroom_match']) ? $report['bathroom_match'] : 1);
    renderChecklistItemReadOnly('Wi-Fi Connectivity & Signal Coverage', 'Active broadband signal accessible from bedrooms', isset($report['wifi_match']) ? $report['wifi_match'] : 1);
    renderChecklistItemReadOnly('Financial Terms & Key Money Deposit', 'Monthly rent and deposit match listed values', isset($report['finance_match']) ? $report['finance_match'] : 1);
    renderChecklistItemReadOnly('Kitchen Facilities & Nearby Dining Access', 'Functional cooking amenities on-site OR verified food/eateries nearby (within 500m)', isset($report['kitchen_food_match']) ? $report['kitchen_food_match'] : 1);
    ?>

    <!-- SECTION 03 -->
    <div class="section-label" style="margin-top:14px;">SECTION 03 — Photographic Evidence &amp; Remarks</div>

    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:14px;margin-bottom:14px;">
        <!-- Photo 1 -->
        <div style="background:#FAF7F2;border:1px solid #E8DDD4;padding:12px;border-radius:12px;">
            <label style="font-weight:800;color:#212529;font-size:12px;display:block;margin-bottom:8px;">Photo 1 (Entrance / Exterior)</label>
            <?php if (!empty($report['photo_path_1'])): ?>
                <div style="position:relative;border-radius:10px;overflow:hidden;border:1.5px solid #E8DDD4;background:#FFFFFF;">
                    <img src="<?php echo htmlspecialchars($report['photo_path_1']); ?>" style="width:100%;height:160px;object-fit:cover;display:block;">
                </div>
            <?php else: ?>
                <div style="font-size:12px;color:#8C7B74;padding:20px;text-align:center;">No photo recorded</div>
            <?php endif; ?>
        </div>

        <!-- Photo 2 -->
        <div style="background:#FAF7F2;border:1px solid #E8DDD4;padding:12px;border-radius:12px;">
            <label style="font-weight:800;color:#212529;font-size:12px;display:block;margin-bottom:8px;">Photo 2 (Room Interior)</label>
            <?php if (!empty($report['photo_path_2'])): ?>
                <div style="position:relative;border-radius:10px;overflow:hidden;border:1.5px solid #E8DDD4;background:#FFFFFF;">
                    <img src="<?php echo htmlspecialchars($report['photo_path_2']); ?>" style="width:100%;height:160px;object-fit:cover;display:block;">
                </div>
            <?php else: ?>
                <div style="font-size:12px;color:#8C7B74;padding:20px;text-align:center;">No photo recorded</div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Agent Remarks -->
    <div style="background:#FAF7F2;border:1px solid #E8DDD4;border-radius:12px;padding:16px;margin-bottom:20px;">
        <label style="font-weight:800;color:#212529;font-size:12px;display:block;margin-bottom:6px;text-transform:uppercase;letter-spacing:0.5px;">Field Agent Inspection Remarks</label>
        <div style="font-size:13px;color:#3B3330;line-height:1.6;font-style:italic;background:#FFFFFF;border:1px solid #E8DDD4;padding:12px 16px;border-radius:8px;">
            "<?php echo !empty($report['agent_comments']) ? htmlspecialchars($report['agent_comments']) : 'No remarks provided.'; ?>"
        </div>
    </div>

    <!-- Navigation -->
    <div style="text-align:center;padding:16px;background:#FAF7F2;border:1px solid #E8DDD4;border-radius:12px;">
        <a href="dashboard.php?tab=history" style="display:inline-flex;align-items:center;gap:8px;padding:12px 24px;background:#A4856D;color:#FFFFFF;border-radius:8px;text-decoration:none;font-weight:800;font-size:13px;">
            ← Return to History
        </a>
    </div>
</div>

<?php else: ?>
<!-- EDITABLE FORM FOR ACTIVE UNLOCKED TASKS -->
<div class="details-card">
    <!-- Header -->
    <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:16px;border-bottom:1px solid #E8DDD4;padding-bottom:16px;">
        <div>
            <div style="font-size:11px;font-weight:800;color:#A4856D;text-transform:uppercase;letter-spacing:1px;margin-bottom:4px;">Property Inspection Standard</div>
            <h2 style="font-family:'Outfit',sans-serif;font-size:22px;font-weight:900;color:#3B3330;margin:0 0 4px 0;letter-spacing:-0.5px;">Property Verification Audit Protocol</h2>
            <p style="font-size:13px;color:#8C7B74;margin:0;">Cross-reference physical premises against landlord uploaded details. Toggle match or issue found.</p>
        </div>
        <div style="display:flex;align-items:center;gap:6px;background:rgba(39,174,96,0.1);color:#0F5132;border:1.5px solid #BADBCE;padding:6px 14px;border-radius:50px;font-size:11px;font-weight:800;white-space:nowrap;flex-shrink:0;box-shadow:0 2px 6px rgba(39,174,96,0.1);">
            <span style="display:inline-block;width:6px;height:6px;background:#27AE60;border-radius:50%;box-shadow:0 0 0 2px rgba(39,174,96,0.25);"></span>
            <span>GPS Verified</span>
        </div>
    </div>

    <!-- Progress Bar -->
    <div style="background:#FAF7F2;border:1px solid #E8DDD4;border-radius:12px;padding:14px 18px;margin-bottom:24px;">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;">
            <span class="section-label" style="margin:0;">Audit Verification Progress</span>
            <span id="auditProgressText" style="font-size:12px;font-weight:800;color:#0F5132;">8 of 8 items verified (100%)</span>
        </div>
        <div style="width:100%;height:8px;background:#E8DDD4;border-radius:50px;overflow:hidden;">
            <div id="auditProgressFill" style="width:100%;height:100%;background:#27AE60;border-radius:50px;transition:width 0.3s ease;"></div>
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
        <div style="margin-top:6px;background:#FAF7F2;border:1px solid #E8DDD4;border-radius:12px;padding:16px;margin-bottom:10px;">
            <label class="form-label form-label--required" style="font-size:13px;font-weight:800;color:#212529;text-transform:uppercase;letter-spacing:0.5px;display:block;">Neighborhood &amp; Safety Rating</label>
            <div style="display:flex;gap:8px;font-size:28px;cursor:pointer;margin-top:6px;">
                <?php for ($s = 1; $s <= 5; $s++): ?>
                <span class="star-item-amber" data-value="<?php echo $s; ?>"
                      onclick="setStarRating(<?php echo $s; ?>)"
                      onmouseover="hoverStarRating(<?php echo $s; ?>)"
                      onmouseout="resetStarRating()">★</span>
                <?php endfor; ?>
            </div>
            <span id="ratingLabel" style="margin-top:4px;font-weight:700;color:#8C7B74;font-size:12px;display:block;">Click stars to rate neighborhood safety</span>
            <input type="hidden" name="neighborhood_safety" id="ratingInput" value="" required>
        </div>

        <!-- SECTION 02 -->
        <div class="section-label" style="margin-top:14px;">SECTION 02 — Room Facilities &amp; Rent Cross-Check</div>
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
        <div class="section-label" style="margin-top:14px;">SECTION 03 — Photographic Verification &amp; Agent Remarks</div>

        <div id="photo_grid_container" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:14px;">
            <!-- Photo 1 -->
            <div class="photo-card">
                <label class="form-label form-label--required" style="font-weight:800;color:#212529;font-size:12px;display:block;margin-bottom:6px;">Photo 1 (Entrance / Exterior)</label>
                <div id="photo1_initial_btn">
                    <button type="button" class="btn-camera-capture" onclick="openLiveCameraModal('photo1Input')" style="width:100%;justify-content:center;background:#A4856D;color:#FFFFFF;">
                        📸 Open Live Camera
                    </button>
                </div>
                <div id="photo1_preview_box" style="display:none;margin-top:6px;">
                    <div style="position:relative;border-radius:10px;overflow:hidden;border:1.5px solid #E8DDD4;background:#FFFFFF;">
                        <img id="photo1Input_preview" class="photo-preview-thumb">
                        <span id="photo1_badge" class="photo-timestamp-badge">🕒 Captured Today</span>
                    </div>
                    <button type="button" class="btn-retake" onclick="clearPhotoInput('photo1Input','photo1_preview_box','photo1_initial_btn')">🗑 Retake Entrance Photo</button>
                </div>
                <input type="file" id="photo1Input" name="photo1" accept="image/*" capture="environment" style="display:none;"
                       onchange="updateFilename('photo1Input','photo1_preview_box','photo1_initial_btn')" required>
            </div>

            <!-- Photo 2 -->
            <div class="photo-card">
                <label class="form-label form-label--required" style="font-weight:800;color:#212529;font-size:12px;display:block;margin-bottom:6px;">Photo 2 (Room Interior)</label>
                <div id="photo2_initial_btn">
                    <button type="button" class="btn-camera-capture" onclick="openLiveCameraModal('photo2Input')" style="width:100%;justify-content:center;background:#A4856D;color:#FFFFFF;">
                        📸 Open Live Camera
                    </button>
                </div>
                <div id="photo2_preview_box" style="display:none;margin-top:6px;">
                    <div style="position:relative;border-radius:10px;overflow:hidden;border:1.5px solid #E8DDD4;background:#FFFFFF;">
                        <img id="photo2Input_preview" class="photo-preview-thumb">
                        <span id="photo2_badge" class="photo-timestamp-badge">🕒 Captured Today</span>
                    </div>
                    <button type="button" class="btn-retake" onclick="clearPhotoInput('photo2Input','photo2_preview_box','photo2_initial_btn')">🗑 Retake Room Photo</button>
                </div>
                <input type="file" id="photo2Input" name="photo2" accept="image/*" capture="environment" style="display:none;"
                       onchange="updateFilename('photo2Input','photo2_preview_box','photo2_initial_btn')" required>
            </div>
        </div>

        <!-- Extra Proof Photo Button -->
        <div style="margin-top:10px;margin-bottom:6px;">
            <button type="button" onclick="addExtraPhotoCard()" style="background:#FAF7F2;border:1.5px dashed #A4856D;color:#A4856D;padding:12px 16px;border-radius:10px;font-size:12px;font-weight:800;cursor:pointer;width:100%;display:flex;align-items:center;justify-content:center;gap:6px;transition:all 0.2s ease;">
                ➕ Capture Additional Proof Photo (Optional)
            </button>
        </div>

        <!-- Hidden Compiled Fields for Area Profile -->
        <input type="hidden" id="transport_details" name="transport_details">
        <input type="hidden" id="amenities_details" name="amenities_details">
        <input type="hidden" id="safety_details"    name="safety_details">

        <!-- SECTION 04 -->
        <div class="section-label" style="margin-top:20px;">SECTION 04 — Regional Area Profile &amp; Neighborhood Observations</div>
        
        <div style="background:#FAF7F2;border:1.5px solid #E8DDD4;border-radius:14px;padding:18px;margin-bottom:20px;">
            <!-- 01. Transport -->
            <div style="margin-bottom:18px;">
                <div style="font-weight:800;color:#3B3330;font-size:13px;margin-bottom:8px;">🚍 Transport &amp; Mobility Access</div>
                <div style="display:flex;flex-wrap:wrap;gap:8px;margin-bottom:10px;">
                    <label style="display:flex;align-items:center;gap:6px;background:#FFFFFF;border:1px solid #E8DDD4;padding:8px 12px;border-radius:10px;font-size:12px;font-weight:700;cursor:pointer;">
                        <input type="checkbox" id="chk_bus" style="accent-color:#3B3330;"> 🚌 Bus Transport
                    </label>
                    <label style="display:flex;align-items:center;gap:6px;background:#FFFFFF;border:1px solid #E8DDD4;padding:8px 12px;border-radius:10px;font-size:12px;font-weight:700;cursor:pointer;">
                        <input type="checkbox" id="chk_train" style="accent-color:#3B3330;"> 🚆 Railway Station
                    </label>
                    <label style="display:flex;align-items:center;gap:6px;background:#FFFFFF;border:1px solid #E8DDD4;padding:8px 12px;border-radius:10px;font-size:12px;font-weight:700;cursor:pointer;">
                        <input type="checkbox" id="chk_walk" style="accent-color:#3B3330;"> 🚶 Walking to Campus
                    </label>
                    <label style="display:flex;align-items:center;gap:6px;background:#FFFFFF;border:1px solid #E8DDD4;padding:8px 12px;border-radius:10px;font-size:12px;font-weight:700;cursor:pointer;">
                        <input type="checkbox" id="chk_tuk" style="accent-color:#3B3330;"> 🛺 Tuk / Rideshare
                    </label>
                </div>
                <textarea id="transport_notes" class="textarea-styled" placeholder="Additional transport notes (bus routes, stop names...)" style="min-height:55px;font-size:12px;"></textarea>
            </div>

            <!-- 02. Amenities -->
            <div style="margin-bottom:18px;">
                <div style="font-weight:800;color:#3B3330;font-size:13px;margin-bottom:8px;">🛒 Student Amenities &amp; Convenience</div>
                <div style="display:flex;flex-wrap:wrap;gap:8px;margin-bottom:10px;">
                    <label style="display:flex;align-items:center;gap:6px;background:#FFFFFF;border:1px solid #E8DDD4;padding:8px 12px;border-radius:10px;font-size:12px;font-weight:700;cursor:pointer;">
                        <input type="checkbox" class="amenity_chk" value="Supermarket (500m)" style="accent-color:#3B3330;"> 🛒 Supermarket
                    </label>
                    <label style="display:flex;align-items:center;gap:6px;background:#FFFFFF;border:1px solid #E8DDD4;padding:8px 12px;border-radius:10px;font-size:12px;font-weight:700;cursor:pointer;">
                        <input type="checkbox" class="amenity_chk" value="24/7 Pharmacy" style="accent-color:#3B3330;"> 💊 24/7 Pharmacy
                    </label>
                    <label style="display:flex;align-items:center;gap:6px;background:#FFFFFF;border:1px solid #E8DDD4;padding:8px 12px;border-radius:10px;font-size:12px;font-weight:700;cursor:pointer;">
                        <input type="checkbox" class="amenity_chk" value="Student Food Spots" style="accent-color:#3B3330;"> 🍛 Food Spots
                    </label>
                    <label style="display:flex;align-items:center;gap:6px;background:#FFFFFF;border:1px solid #E8DDD4;padding:8px 12px;border-radius:10px;font-size:12px;font-weight:700;cursor:pointer;">
                        <input type="checkbox" class="amenity_chk" value="Laundromat" style="accent-color:#3B3330;"> 🧺 Laundromat
                    </label>
                    <label style="display:flex;align-items:center;gap:6px;background:#FFFFFF;border:1px solid #E8DDD4;padding:8px 12px;border-radius:10px;font-size:12px;font-weight:700;cursor:pointer;">
                        <input type="checkbox" class="amenity_chk" value="Bank ATMs" style="accent-color:#3B3330;"> 🏧 Bank ATMs
                    </label>
                </div>
                <textarea id="amenities_notes" class="textarea-styled" placeholder="Additional amenities notes (market names, landmarks...)" style="min-height:55px;font-size:12px;"></textarea>
            </div>

            <!-- 03. Safety -->
            <div>
                <div style="font-weight:800;color:#3B3330;font-size:13px;margin-bottom:8px;">🛡️ Neighborhood Safety &amp; Conditions</div>
                <div style="display:flex;flex-wrap:wrap;gap:8px;margin-bottom:10px;">
                    <label style="display:flex;align-items:center;gap:6px;background:#FFFFFF;border:1px solid #E8DDD4;padding:8px 12px;border-radius:10px;font-size:12px;font-weight:700;cursor:pointer;">
                        <input type="checkbox" class="safety_chk" value="Well-lit Main Roads" style="accent-color:#3B3330;"> 💡 Well-lit Main Roads
                    </label>
                    <label style="display:flex;align-items:center;gap:6px;background:#FFFFFF;border:1px solid #E8DDD4;padding:8px 12px;border-radius:10px;font-size:12px;font-weight:700;cursor:pointer;">
                        <input type="checkbox" class="safety_chk" value="Police Patrols" style="accent-color:#3B3330;"> 🚓 Police Patrols
                    </label>
                    <label style="display:flex;align-items:center;gap:6px;background:#FFFFFF;border:1px solid #E8DDD4;padding:8px 12px;border-radius:10px;font-size:12px;font-weight:700;cursor:pointer;">
                        <input type="checkbox" class="safety_chk" value="Safe Residential Zone" style="accent-color:#3B3330;"> 🟢 Safe Residential Zone
                    </label>
                    <label style="display:flex;align-items:center;gap:6px;background:#FFFFFF;border:1px solid #E8DDD4;padding:8px 12px;border-radius:10px;font-size:12px;font-weight:700;cursor:pointer;">
                        <input type="checkbox" class="safety_chk" value="Caution after 10 PM" style="accent-color:#C0392B;"> ⚠️ Caution after 10 PM
                    </label>
                </div>
                <textarea id="safety_notes" class="textarea-styled" placeholder="Additional safety notes (warnings, security details...)" style="min-height:55px;font-size:12px;"></textarea>
            </div>
        </div>

        <!-- Field Agent Inspection Remarks -->
        <div style="margin-top:16px;margin-bottom:16px;">
            <label class="form-label form-label--required" style="font-weight:800;color:#212529;font-size:13px;display:block;margin-bottom:6px;text-transform:uppercase;letter-spacing:0.5px;">Field Agent Inspection Remarks *</label>
            <textarea class="textarea-styled" id="agent_comments_raw"
                      placeholder="Provide executive summary of on-site inspection, landlord cooperation, and overall property recommendation..."
                      required style="min-height:95px;font-size:13px;line-height:1.5;"></textarea>
            <input type="hidden" name="agent_comments" id="agent_comments_hidden">
        </div>

        <!-- Submit -->
        <button type="submit" style="background:#1E1E1E;color:#FFFFFF;border:none;padding:16px;border-radius:10px;font-weight:800;font-size:14px;cursor:pointer;transition:all 0.2s ease;box-shadow:0 4px 12px rgba(0,0,0,0.12);width:100%;margin-top:8px;display:flex;align-items:center;justify-content:center;gap:8px;">
            <span>Submit Executive Audit Report</span>
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <line x1="22" y1="2" x2="11" y2="13"></line>
                <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
            </svg>
        </button>
    </form>
</div>
<?php endif; ?>
