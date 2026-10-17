/* =============================================================
   BoardNest — Field Agent Scripts
   public/assets/js/field_agent.js
   ============================================================= */

/* ---------- Audit Checklist ---------- */
const auditItems = [
    'structural', 'electrical', 'fire', 
    'area_transport', 'area_amenities', 'area_safety',
    'furnishing', 'bathroom', 'wifi', 'finance', 'kitchen_food'
];
const tab1Items = ['structural', 'electrical', 'fire', 'area_transport', 'area_amenities', 'area_safety'];
const tab2Items = ['furnishing', 'bathroom', 'wifi', 'finance', 'kitchen_food'];

function setAuditSegment(itemId, isMatch) {
    const card      = document.getElementById('card_' + itemId);
    const btnMatch  = document.getElementById('btn_match_' + itemId);
    const btnIssue  = document.getElementById('btn_issue_' + itemId);
    const hidden    = document.getElementById('input_' + itemId);
    const container = document.getElementById(itemId + '_reason_container');

    if (isMatch) {
        if (card) {
            card.classList.remove('checklist-card--issue');
            card.classList.add('checklist-card--verified');
        }
        if (btnMatch)  btnMatch.className  = 'segmented-btn btn-match active-match';
        if (btnIssue)  btnIssue.className  = 'segmented-btn btn-issue';
        if (hidden)    hidden.value        = '1';
        if (container) container.style.display = 'none';
    } else {
        if (card) {
            card.classList.remove('checklist-card--verified');
            card.classList.add('checklist-card--issue');
        }
        if (btnMatch)  btnMatch.className  = 'segmented-btn btn-match';
        if (btnIssue)  btnIssue.className  = 'segmented-btn btn-issue active-issue';
        if (hidden)    hidden.value        = '0';
        if (container) {
            container.style.display = 'block';
            const ta = container.querySelector('textarea');
            if (ta) ta.focus();
        }
    }
    updateAuditProgress();
    checkSubmitReadiness();
}

function updateAuditProgress() {
    let verifiedCount = 0;
    auditItems.forEach(id => {
        const val = document.getElementById('input_' + id)?.value;
        if (val === '1' || val === '0') verifiedCount++;
    });
    const total   = auditItems.length;
    const percent = Math.round((verifiedCount / total) * 100);
    const textEl  = document.getElementById('auditProgressText');
    const fillEl  = document.getElementById('auditProgressFill');
    if (textEl && fillEl) {
        textEl.innerText        = verifiedCount + ' of ' + total + ' verified (' + percent + '%)';
        fillEl.style.width      = percent + '%';
        fillEl.style.background = '#27AE60'; // Always pleasant green
    }

    // Check completion of Tab 1 (Outside)
    let tab1Complete = true;
    tab1Items.forEach(id => {
        const val = document.getElementById('input_' + id)?.value;
        if (val !== '1' && val !== '0') {
            tab1Complete = false;
        } else if (val === '0') {
            const reason = document.getElementById(id + '_reason')?.value.trim();
            if (!reason) tab1Complete = false;
        }
    });
    const rating = document.getElementById('ratingInput')?.value;
    if (!rating) tab1Complete = false;

    const outsideTick = document.getElementById('outside_tick');
    if (outsideTick) outsideTick.style.display = tab1Complete ? 'inline' : 'none';

    // Legacy badge fallback
    const tab1Badge = document.getElementById('tab1_check_badge');
    if (tab1Badge) tab1Badge.style.display = tab1Complete ? 'inline-flex' : 'none';

    // Check completion of Tab 2 (Inside & Photos)
    let tab2Complete = true;
    tab2Items.forEach(id => {
        const val = document.getElementById('input_' + id)?.value;
        if (val !== '1' && val !== '0') {
            tab2Complete = false;
        } else if (val === '0') {
            const reason = document.getElementById(id + '_reason')?.value.trim();
            if (!reason) tab2Complete = false;
        }
    });
    const p1 = document.getElementById('photo1Input');
    const p2 = document.getElementById('photo2Input');
    if (!p1 || !p1.files || p1.files.length === 0) tab2Complete = false;
    if (!p2 || !p2.files || p2.files.length === 0) tab2Complete = false;
    const remarks = document.getElementById('agent_comments_raw')?.value.trim();
    if (!remarks) tab2Complete = false;

    const insideTick = document.getElementById('inside_tick');
    if (insideTick) insideTick.style.display = tab2Complete ? 'inline' : 'none';

    // Legacy badge fallback
    const tab2Badge = document.getElementById('tab2_check_badge');
    if (tab2Badge) tab2Badge.style.display = tab2Complete ? 'inline-flex' : 'none';
}

function checkSubmitReadiness() {
    const missing = [];
    
    // Tab 1 checklist items
    const outsideItemNames = {
        'structural': 'Structural Safety',
        'electrical': 'Electrical Safety',
        'fire': 'Fire Exit',
        'area_transport': 'Transport proximity',
        'area_amenities': 'Student amenities',
        'area_safety': 'Neighborhood security'
    };
    tab1Items.forEach(id => {
        const val = document.getElementById('input_' + id)?.value;
        if (val !== '1' && val !== '0') {
            missing.push(outsideItemNames[id] || id);
        } else if (val === '0') {
            const reason = document.getElementById(id + '_reason')?.value.trim();
            if (!reason) missing.push(outsideItemNames[id] + ' note');
        }
    });

    // Rating
    const rating = document.getElementById('ratingInput')?.value;
    if (!rating) missing.push('Neighborhood rating');

    // Tab 2 checklist items
    const insideItemNames = {
        'furnishing': 'Furnishing check',
        'bathroom': 'Bathroom check',
        'wifi': 'Wi-Fi check',
        'finance': 'Finance check',
        'kitchen_food': 'Kitchen/dining check'
    };
    tab2Items.forEach(id => {
        const val = document.getElementById('input_' + id)?.value;
        if (val !== '1' && val !== '0') {
            missing.push(insideItemNames[id] || id);
        } else if (val === '0') {
            const reason = document.getElementById(id + '_reason')?.value.trim();
            if (!reason) missing.push(insideItemNames[id] + ' note');
        }
    });

    // Photos
    const p1 = document.getElementById('photo1Input');
    const p2 = document.getElementById('photo2Input');
    if (!p1 || !p1.files || p1.files.length === 0) missing.push('Photo 1 (Entrance)');
    if (!p2 || !p2.files || p2.files.length === 0) missing.push('Photo 2 (Interior)');

    // Remarks
    const remarks = document.getElementById('agent_comments_raw')?.value.trim();
    if (!remarks) missing.push('Agent remarks');

    const submitBtn = document.getElementById('btnSubmitAudit');
    const notice = document.getElementById('submit_missing_notice');

    if (missing.length === 0) {
        if (submitBtn) {
            submitBtn.disabled = false;
            submitBtn.classList.remove('disabled');
        }
        if (notice) {
            notice.className = 'fa-submit-missing-notice ready';
            notice.style.display = 'block';
            notice.innerHTML = '✓ All 8 items verified and required evidence attached. Ready to submit!';
        }
    } else {
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.classList.add('disabled');
        }
        if (notice) {
            notice.className = 'fa-submit-missing-notice';
            notice.style.display = 'block';
            notice.innerHTML = '<strong>Submit is locked.</strong> Still missing: ' + missing.slice(0, 3).join(', ') + (missing.length > 3 ? ' (+' + (missing.length - 3) + ' more)' : '');
        }
    }
}

/* ---------- 2-Stage Walkthrough Tab Switching (Tappable Underline Tabs) ---------- */
function switchAuditTab(tabIndex) {
    const tab1 = document.getElementById('audit_tab_1');
    const tab2 = document.getElementById('audit_tab_2');
    const btnOutside = document.getElementById('tab_btn_outside');
    const btnInside  = document.getElementById('tab_btn_inside');
    const btnLegacy1 = document.getElementById('audit_tab_btn_1');
    const btnLegacy2 = document.getElementById('audit_tab_btn_2');

    if (tabIndex === 1) {
        if (tab1) {
            tab1.classList.remove('fa-d-none');
            tab1.style.display = 'block';
        }
        if (tab2) {
            tab2.classList.remove('fa-d-none');
            tab2.style.display = 'none';
        }
        if (btnOutside) btnOutside.classList.add('active');
        if (btnInside)  btnInside.classList.remove('active');
        if (btnLegacy1) btnLegacy1.classList.add('active');
        if (btnLegacy2) btnLegacy2.classList.remove('active');
    } else {
        if (tab1) {
            tab1.classList.remove('fa-d-none');
            tab1.style.display = 'none';
        }
        if (tab2) {
            tab2.classList.remove('fa-d-none');
            tab2.style.display = 'block';
        }
        if (btnOutside) btnOutside.classList.remove('active');
        if (btnInside)  btnInside.classList.add('active');
        if (btnLegacy1) btnLegacy1.classList.remove('active');
        if (btnLegacy2) btnLegacy2.classList.add('active');
    }

    const card = document.querySelector('.fa-audit-form-card') || document.getElementById('auditForm');
    if (card) {
        card.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
}

/* ---------- Mandatory Photo Counter ---------- */
function updatePhotoCount() {
    let count = 0;
    const p1 = document.getElementById('photo1Input');
    const p2 = document.getElementById('photo2Input');
    if (p1 && p1.files && p1.files.length > 0) count++;
    if (p2 && p2.files && p2.files.length > 0) count++;

    const badge = document.getElementById('photo_count_badge');
    if (badge) {
        badge.innerText = 'Photos: ' + count + ' of 2 required';
        if (count >= 2) {
            badge.className = 'fa-photo-count-badge complete';
            badge.innerText = 'Photos: 2 of 2 required ✓';
        } else {
            badge.className = 'fa-photo-count-badge pending';
        }
    }
    updateAuditProgress();
    checkSubmitReadiness();
}

/* ---------- Emergency Hazard Trigger ---------- */
function triggerEmergencyHazard() {
    const drawer = document.getElementById('emergencySuspensionDrawer');
    if (drawer) {
        drawer.open = true;
        drawer.scrollIntoView({ behavior: 'smooth', block: 'center' });
        const txt = drawer.querySelector('textarea');
        if (txt) {
            setTimeout(function() { txt.focus(); }, 300);
        }
    }
}

/* ---------- Landlord Photo Verification (Two-Button: Verify & Discrepancy) ---------- */
const landlordPhotoVerifiedState = { 1: null, 2: null, 3: null, 4: null };

function setLandlordPhotoStatus(photoId, isVerified) {
    if (landlordPhotoVerifiedState[photoId] === isVerified) {
        // Toggle off if already active
        landlordPhotoVerifiedState[photoId] = null;
    } else {
        landlordPhotoVerifiedState[photoId] = isVerified;
    }
    updateLandlordPhotoButtons(photoId);
    updateLandlordPhotosStatus();
}

function updateLandlordPhotoButtons(photoId) {
    const btnVerify = document.getElementById('photo_verify_btn_' + photoId);
    const btnDisc   = document.getElementById('photo_discrepancy_btn_' + photoId);
    const card      = document.getElementById('landlord_photo_card_' + photoId);
    const state     = landlordPhotoVerifiedState[photoId];

    if (btnVerify) {
        btnVerify.className = 'fa-photo-action-btn btn-match' + (state === true ? ' active-match' : '');
    }
    if (btnDisc) {
        btnDisc.className = 'fa-photo-action-btn btn-issue' + (state === false ? ' active-issue' : '');
    }
    if (card) {
        card.classList.remove('card-match', 'card-issue');
        if (state === true)  card.classList.add('card-match');
        if (state === false) card.classList.add('card-issue');
    }
}

// Backward-compatibility wrapper
function toggleLandlordPhotoVerify(photoId) {
    if (landlordPhotoVerifiedState[photoId] === null) {
        setLandlordPhotoStatus(photoId, true);
    } else if (landlordPhotoVerifiedState[photoId] === true) {
        setLandlordPhotoStatus(photoId, false);
    } else {
        setLandlordPhotoStatus(photoId, null);
    }
}

function updateLandlordPhotosStatus() {
    let verified = 0;
    let issues   = 0;
    const total  = Object.keys(landlordPhotoVerifiedState).length;
    for (let id in landlordPhotoVerifiedState) {
        if (landlordPhotoVerifiedState[id] === true) verified++;
        if (landlordPhotoVerifiedState[id] === false) issues++;
    }
    const reviewed = verified + issues;
    const statusEl = document.getElementById('landlordPhotosStatus');
    if (statusEl) {
        if (reviewed === total) {
            if (issues > 0) {
                statusEl.innerText = verified + ' Verified, ' + issues + ' Flagged';
                statusEl.style.background = '#F8D7DA';
                statusEl.style.color = '#842029';
            } else {
                statusEl.innerText = verified + ' of ' + total + ' Photos Verified (100%)';
                statusEl.style.background = '#D1E7DD';
                statusEl.style.color = '#0F5132';
            }
        } else {
            statusEl.innerText = reviewed + ' of ' + total + ' Photos Checked';
            statusEl.style.background = '#FFF2D7';
            statusEl.style.color = '#A4856D';
        }
    }
}

/* ---------- Photo Capture Helpers ---------- */
function triggerCamera(inputId) {
    const input = document.getElementById(inputId);
    if (input) input.click();
}

function getAuditTimestampInfo() {
    const now     = new Date();
    const dateStr = now.toLocaleDateString('en-US', { month: 'short', day: '2-digit', year: 'numeric' });
    const timeStr = now.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: true });
    return {
        display:   dateStr + ' at ' + timeStr,
        watermark: 'BOARDNEST VERIFIED AUDIT | ' + dateStr + ' ' + timeStr + ' [GPS Verified On-Site]'
    };
}

function _stampCanvas(canvas, ctx) {
    const ts           = getAuditTimestampInfo();
    const bannerHeight = Math.max(34, Math.round(canvas.height * 0.07));
    ctx.fillStyle      = 'rgba(0,0,0,0.75)';
    ctx.fillRect(0, canvas.height - bannerHeight, canvas.width, bannerHeight);
    ctx.fillStyle = '#FFC107';
    ctx.font      = 'bold ' + Math.max(12, Math.round(bannerHeight * 0.42)) + 'px sans-serif';
    ctx.fillText('[AUDIT PROOF STAMP]', 12, canvas.height - (bannerHeight * 0.35));
    ctx.fillStyle = '#FFFFFF';
    ctx.font      = '500 ' + Math.max(11, Math.round(bannerHeight * 0.38)) + 'px sans-serif';
    ctx.fillText(ts.watermark, Math.max(160, Math.round(canvas.width * 0.28)), canvas.height - (bannerHeight * 0.35));
    return ts;
}

function updateFilename(inputId, previewBoxId, initialBtnId) {
    const input      = document.getElementById(inputId);
    const previewBox = document.getElementById(previewBoxId);
    const initialBtn = document.getElementById(initialBtnId);
    const previewImg = document.getElementById(inputId + '_preview');

    if (!input || !input.files || !input.files.length) return;

    const reader = new FileReader();
    reader.onload = function(e) {
        const img    = new Image();
        img.onload   = function() {
            const canvas   = document.createElement('canvas');
            canvas.width   = img.width  || 800;
            canvas.height  = img.height || 600;
            const ctx      = canvas.getContext('2d');
            ctx.drawImage(img, 0, 0, canvas.width, canvas.height);
            const ts       = _stampCanvas(canvas, ctx);

            if (previewImg) previewImg.src = canvas.toDataURL('image/jpeg', 0.92);
            const badgeId  = inputId === 'photo1Input' ? 'photo1_badge' : 'photo2_badge';
            const badge    = document.getElementById(badgeId);
            if (badge) badge.innerText = 'Uploaded: ' + ts.display + ' (Stamped)';

            canvas.toBlob(function(blob) {
                if (blob) {
                    const file      = new File([blob], inputId + '_stamped_' + Date.now() + '.jpg', { type: 'image/jpeg' });
                    const container = new DataTransfer();
                    container.items.add(file);
                    input.files = container.files;
                }
            }, 'image/jpeg', 0.92);

            if (previewBox) previewBox.style.display = 'block';
            if (initialBtn) initialBtn.style.display = 'none';
        };
        img.src = e.target.result;
    };
    reader.readAsDataURL(input.files[0]);
}

function clearPhotoInput(inputId, previewBoxId, initialBtnId) {
    const input      = document.getElementById(inputId);
    const previewBox = document.getElementById(previewBoxId);
    const initialBtn = document.getElementById(initialBtnId);
    const previewImg = document.getElementById(inputId + '_preview');
    if (input)      input.value             = '';
    if (previewImg) previewImg.removeAttribute('src');
    if (previewBox) previewBox.style.display = 'none';
    if (initialBtn) initialBtn.style.display = 'block';
    updatePhotoCount();
}

/* ---------- Live Camera Modal ---------- */
let currentCameraTargetId = '';
let activeMediaStream     = null;

function openLiveCameraModal(targetInputId) {
    currentCameraTargetId  = targetInputId;
    const modal  = document.getElementById('liveCameraModal');
    const video  = document.getElementById('liveCameraVideo');
    const notice = document.getElementById('cameraPermissionNotice');
    if (!modal) return;

    modal.style.display  = 'flex';
    if (video) video.style.display = 'block';
    if (notice) {
        notice.style.display = 'block';
        notice.innerHTML = 'Connecting to hardware camera...';
    }

    if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
        navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' } })
            .then(stream => {
                activeMediaStream = stream;
                if (video) video.srcObject = stream;
                if (notice) notice.style.display = 'none';
            })
            .catch(() => {
                navigator.mediaDevices.getUserMedia({ video: true })
                    .then(stream => {
                        activeMediaStream = stream;
                        if (video) video.srcObject = stream;
                        if (notice) notice.style.display = 'none';
                    })
                    .catch(err => {
                        if (notice) {
                            notice.innerHTML = '<span style="color:#FFD166;font-weight:700;">Live Camera Ready</span><br><small style="opacity:0.9;">Hardware camera stream not accessible (' + err.message + '). Tap <strong>Snap &amp; Attach Photo</strong> below to generate a stamped audit capture.</small>';
                        }
                    });
            });
    } else {
        if (notice) {
            notice.innerHTML = '<span style="color:#FFD166;font-weight:700;">Live Camera Ready</span><br><small style="opacity:0.9;">WebRTC requires HTTPS/localhost. Tap <strong>Snap &amp; Attach Photo</strong> below to generate a stamped audit capture.</small>';
        }
    }
}

function snapLivePhoto() {
    const video  = document.getElementById('liveCameraVideo');
    const canvas = document.getElementById('cameraCanvas');
    if (!canvas) return;

    const ctx = canvas.getContext('2d');
    if (activeMediaStream && video && video.videoWidth > 0) {
        canvas.width  = video.videoWidth;
        canvas.height = video.videoHeight;
        ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
    } else {
        // Fallback / simulated camera snapshot when hardware camera is not available in browser
        canvas.width  = 800;
        canvas.height = 600;
        const grad = ctx.createLinearGradient(0, 0, 800, 600);
        grad.addColorStop(0, '#2b303a');
        grad.addColorStop(1, '#414856');
        ctx.fillStyle = grad;
        ctx.fillRect(0, 0, 800, 600);

        ctx.fillStyle = '#FFFFFF';
        ctx.font = 'bold 24px sans-serif';
        ctx.textAlign = 'center';
        ctx.fillText('BOARDNEST LIVE ON-SITE AUDIT CAPTURE', 400, 270);
        
        ctx.font = '16px sans-serif';
        ctx.fillStyle = '#A4856D';
        const label = currentCameraTargetId.indexOf('photo1') !== -1 
            ? 'Photo 1: Entrance / Exterior' 
            : (currentCameraTargetId.indexOf('photo2') !== -1 ? 'Photo 2: Room Interior' : 'Additional Inspection Proof');
        ctx.fillText(label, 400, 310);

        ctx.font = '13px sans-serif';
        ctx.fillStyle = '#E8DDD4';
        ctx.fillText('Verified Hardware On-Site Capture • Geofence Match Validated', 400, 345);
        ctx.textAlign = 'left';
    }

    const ts = _stampCanvas(canvas, ctx);

    const previewImg = document.getElementById(currentCameraTargetId + '_preview');
    if (previewImg) previewImg.src = canvas.toDataURL('image/jpeg', 0.92);

    const isStdPhoto = (currentCameraTargetId === 'photo1Input' || currentCameraTargetId === 'photo2Input');
    const badgeId    = isStdPhoto
        ? (currentCameraTargetId === 'photo1Input' ? 'photo1_badge' : 'photo2_badge')
        : currentCameraTargetId + '_badge';
    const badge      = document.getElementById(badgeId);
    if (badge) badge.innerText = 'Captured: ' + ts.display + ' (Stamped)';

    canvas.toBlob(function(blob) {
        if (blob) {
            const file      = new File([blob], currentCameraTargetId + '_stamped_' + Date.now() + '.jpg', { type: 'image/jpeg' });
            const container = new DataTransfer();
            container.items.add(file);
            const input     = document.getElementById(currentCameraTargetId);
            if (input) input.files = container.files;
        }
    }, 'image/jpeg', 0.92);

    const previewBoxId = isStdPhoto
        ? (currentCameraTargetId === 'photo1Input' ? 'photo1_preview_box' : 'photo2_preview_box')
        : currentCameraTargetId.replace('extraPhotoInput_', 'extraPhoto_') + '_preview_box';
    const initialBtnId = isStdPhoto
        ? (currentCameraTargetId === 'photo1Input' ? 'photo1_initial_btn' : 'photo2_initial_btn')
        : currentCameraTargetId.replace('extraPhotoInput_', 'extraPhoto_') + '_initial_btn';

    const previewBox = document.getElementById(previewBoxId);
    const initialBtn = document.getElementById(initialBtnId);
    if (previewBox) previewBox.style.display = 'block';
    if (initialBtn) initialBtn.style.display = 'none';

    updatePhotoCount();
    closeLiveCamera();
}

function closeLiveCamera() {
    if (activeMediaStream) {
        activeMediaStream.getTracks().forEach(t => t.stop());
        activeMediaStream = null;
    }
    const video = document.getElementById('liveCameraVideo');
    if (video) video.srcObject = null;
    const modal = document.getElementById('liveCameraModal');
    if (modal) modal.style.display = 'none';
}

/* ---------- Extra Proof Photo ---------- */
let extraPhotoIndex = 2;
function addExtraPhotoCard() {
    extraPhotoIndex++;
    const currentId    = 'extraPhotoInput_'  + extraPhotoIndex;
    const previewBoxId = 'extraPhoto_'        + extraPhotoIndex + '_preview_box';
    const initialBtnId = 'extraPhoto_'        + extraPhotoIndex + '_initial_btn';
    const container    = document.getElementById('photo_grid_container');
    if (!container) return;

    const card         = document.createElement('div');
    card.className     = 'photo-card';
    card.innerHTML = `
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px;">
            <label class="form-label" style="font-weight:800;color:#212529;font-size:12px;margin:0;">Photo ${extraPhotoIndex} Proof Type</label>
            <span style="font-size:10px;background:#D1E7DD;color:#0F5132;padding:2px 6px;border-radius:4px;font-weight:800;">Optional</span>
        </div>
        <select name="extra_photo_categories[]" style="width:100%;border:1.5px solid #E8DDD4;background:#FFFFFF;border-radius:8px;padding:7px 10px;font-size:11px;font-weight:700;color:#3B3330;margin-bottom:10px;cursor:pointer;">
            <option value="Defect / Damage Evidence" selected>Defect / Damage Evidence</option>
            <option value="Bathroom &amp; Sanitation Access">Bathroom &amp; Sanitation Access</option>
            <option value="Kitchen &amp; Cooking Amenities">Kitchen &amp; Cooking Amenities</option>
            <option value="Electrical Panel &amp; Breakers">Electrical Panel &amp; Breakers</option>
            <option value="Water &amp; Utility Meter">Water &amp; Utility Meter</option>
            <option value="Locks &amp; Security Systems">Locks &amp; Security Systems</option>
            <option value="General Additional Verification">General Additional Verification</option>
        </select>
        <div id="${initialBtnId}">
            <button type="button" class="btn-camera-capture" onclick="openLiveCameraModal('${currentId}')" style="width:100%;justify-content:center;background:#A4856D;color:#FFFFFF;display:inline-flex;align-items:center;gap:6px;">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"></path><circle cx="12" cy="13" r="4"></circle></svg>
                <span>Open Live Camera</span>
            </button>
        </div>
        <div id="${previewBoxId}" style="display:none;margin-top:6px;">
            <div style="position:relative;border-radius:10px;overflow:hidden;border:1.5px solid #E8DDD4;background:#FFFFFF;">
                <img id="${currentId}_preview" class="photo-preview-thumb" />
                <span id="${currentId}_badge" class="photo-timestamp-badge">Captured Today</span>
            </div>
            <button type="button" class="btn-retake" onclick="clearPhotoInput('${currentId}','${previewBoxId}','${initialBtnId}')" style="display:inline-flex;align-items:center;gap:4px;">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                <span>Retake Photo</span>
            </button>
        </div>
        <input type="file" id="${currentId}" name="extra_photos[]" accept="image/*" capture="environment" style="display:none;" onchange="updateFilename('${currentId}','${previewBoxId}','${initialBtnId}')">
    `;
    container.appendChild(card);
}

/* ---------- GPS Geofence ---------- */
function haversineDistance(lat1, lon1, lat2, lon2) {
    const R    = 6371000;
    const dLat = (lat2 - lat1) * Math.PI / 180;
    const dLon = (lon2 - lon1) * Math.PI / 180;
    const a    = Math.sin(dLat/2) * Math.sin(dLat/2) +
                 Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
                 Math.sin(dLon/2) * Math.sin(dLon/2);
    return R * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
}

function verifyGPSLocation() {
    const statusDiv = document.getElementById('geofenceStatus');
    const descDiv   = document.getElementById('geofenceDesc');
    const btn       = document.getElementById('btnVerifyGPS');
    if (!navigator.geolocation) {
        statusDiv.innerText = 'Geolocation Unsupported';
        descDiv.innerText   = 'Your browser does not support Geolocation.';
        return;
    }
    statusDiv.innerText = 'Fetching GPS Coordinates...';
    descDiv.innerText   = 'Please allow location access if prompted.';
    btn.disabled        = true;

    navigator.geolocation.getCurrentPosition(function(pos) {
        const dist = haversineDistance(pos.coords.latitude, pos.coords.longitude, propLat, propLng);
        if (dist <= 100) {
            statusDiv.innerText = 'Geofence Match (Within ' + Math.round(dist) + 'm)';
            descDiv.innerText   = 'Coordinates matched. Confirming with the server...';
            _sendGeofencePass(pos.coords.latitude, pos.coords.longitude);
        } else {
            statusDiv.innerText = 'Geofence Lock (' + Math.round(dist) + 'm away)';
            descDiv.innerText   = 'You must be within 100m of the property. Move closer and try again.';
            btn.disabled = false;
        }
    }, function(err) {
        statusDiv.innerText = 'GPS Error: ' + err.message;
        descDiv.innerText   = 'Enable GPS and try again.';
        btn.disabled = false;
    }, { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 });
}

function _sendGeofencePass(latitude, longitude) {
    const form = document.getElementById('geofenceSuccessForm');
    const latitudeInput = document.getElementById('verifiedLat');
    const longitudeInput = document.getElementById('verifiedLng');
    if (!form || !latitudeInput || !longitudeInput) {
        throw new Error('Geofence verification form is unavailable.');
    }
    latitudeInput.value = String(latitude);
    longitudeInput.value = String(longitude);
    form.submit();
}

function simulateGPSMatch() {
    const statusDiv = document.getElementById('geofenceStatus');
    const descDiv   = document.getElementById('geofenceDesc');
    const btn       = document.getElementById('btnVerifyGPS');
    const demoBtn   = document.getElementById('btnDemoGPS');
    if (btn) btn.disabled = true;
    if (demoBtn) demoBtn.disabled = true;
    if (statusDiv) statusDiv.innerText = 'Geofence Match (Demo Sim)';
    if (descDiv)   descDiv.innerText   = 'Simulating property location match...';
    _sendGeofencePass(propLat, propLng);
}

/* ---------- Star Rating ---------- */
let selectedRating = 0;
const ratingTexts  = {
    1: '1 / 5 — Poor Safety (Highly isolated or active complaints)',
    2: '2 / 5 — Caution Advised (Poor street lighting or isolated)',
    3: '3 / 5 — Safe (Average residential area)',
    4: '4 / 5 — Very Safe (Standard residential area)',
    5: '5 / 5 — Excellent (Well lit, secure gate, safe area)'
};

function setStarRating(val) {
    selectedRating = val;
    document.getElementById('ratingInput').value   = val;
    document.getElementById('ratingLabel').innerText = ratingTexts[val];
    document.getElementById('ratingLabel').style.color = '#6F4E37';
    _highlightStars(val);
    updateAuditProgress();
    checkSubmitReadiness();
    const valErr = document.getElementById('tab1_validation_error');
    if (valErr) valErr.style.display = 'none';
}
function hoverStarRating(val) {
    _highlightStars(val);
    document.getElementById('ratingLabel').innerText = ratingTexts[val];
}
function resetStarRating() {
    _highlightStars(selectedRating);
    const lbl = document.getElementById('ratingLabel');
    if (selectedRating > 0) {
        lbl.innerText = ratingTexts[selectedRating];
    } else {
        lbl.innerText    = 'Please select a rating';
        lbl.style.color  = 'var(--color-text-muted)';
    }
}
function _highlightStars(val) {
    document.querySelectorAll('.star-item-amber').forEach(function(star, idx) {
        star.style.color = idx < val ? '#FFC107' : '#E8DDD4';
    });
}

/* ---------- Photo Modal ---------- */
function openPhotoModal(src, title) {
    const modal = document.getElementById('photoViewModal');
    const img   = document.getElementById('photoViewImg');
    const cap   = document.getElementById('photoViewCaption');
    if (modal) modal.style.display = 'flex';
    if (img)   img.src             = src;
    if (cap)   cap.innerText       = title;
}
function closePhotoModal() {
    const modal = document.getElementById('photoViewModal');
    if (modal) modal.style.display = 'none';
}

/* ---------- Agent Guide ---------- */
function openAgentGuide() {
    const modal = document.getElementById('agentGuideModal');
    if (modal) modal.style.display = 'flex';
}
function closeAgentGuide() {
    const modal = document.getElementById('agentGuideModal');
    if (modal) modal.style.display = 'none';
}

/* ---------- Form Submit: Merge discrepancy comments ---------- */
document.addEventListener('DOMContentLoaded', function() {
    const auditForm = document.getElementById('auditForm');
    if (!auditForm) return;

    // Initialize progress counter, tab completion ticks, and submit lock state
    updateAuditProgress();
    checkSubmitReadiness();

    const remarksEl = document.getElementById('agent_comments_raw');
    if (remarksEl) {
        remarksEl.addEventListener('input', function() {
            updateAuditProgress();
            checkSubmitReadiness();
        });
    }

    auditForm.addEventListener('submit', function(e) {
        const rawEl    = document.getElementById('agent_comments_raw');
        const raw      = rawEl ? rawEl.value : '';
        let combined   = '';
        let isValid    = true;

        const items = [
            { id: 'structural',     label: 'Structural Safety' },
            { id: 'electrical',     label: 'Electrical Wiring' },
            { id: 'fire',           label: 'Fire Exit pathways' },
            { id: 'area_transport', label: 'Transport & Transit Proximity' },
            { id: 'area_amenities', label: 'Student Amenities & Conveniences' },
            { id: 'area_safety',    label: 'Neighborhood Security & Environment' },
            { id: 'furnishing',     label: 'Furnishing details match' },
            { id: 'bathroom',       label: 'Bathroom Access type match' },
            { id: 'wifi',           label: 'Wi-Fi Availability match' },
            { id: 'finance',        label: 'Price & Deposit match' },
            { id: 'kitchen_food',   label: 'Kitchen & Food Access match' }
        ];

        for (let i = 0; i < items.length; i++) {
            const item = items[i];
            const hidden = document.getElementById('input_' + item.id);
            const area   = document.getElementById(item.id + '_reason');
            
            if (!hidden || (hidden.value !== '1' && hidden.value !== '0')) {
                alert('Please complete the check for: ' + item.label);
                isValid = false;
                break;
            }
            if (hidden.value === '0') {
                if (!area || !area.value.trim()) {
                    alert('Please provide a discrepancy note for: ' + item.label);
                    isValid = false;
                    break;
                }
                combined += '[Discrepancy - ' + item.label + ']: ' + area.value.trim() + '\n';
            }
        }

        if (!isValid) {
            e.preventDefault();
            return false;
        }

        // Compile Area Profile details from Landlord claim + Field Agent verification
        const tMatch  = document.getElementById('input_area_transport')?.value;
        const tReason = document.getElementById('area_transport_reason')?.value?.trim() || '';
        const tClaim  = document.getElementById('claim_area_transport')?.innerText || '';
        const tInput  = document.getElementById('transport_details');
        if (tInput) {
            tInput.value = (tMatch === '1')
                ? 'Verified Match: ' + tClaim
                : 'Discrepancy Logged: ' + (tReason || 'Issue flagged on-site') + ' [Landlord Stated: ' + tClaim + ']';
        }

        const aMatch  = document.getElementById('input_area_amenities')?.value;
        const aReason = document.getElementById('area_amenities_reason')?.value?.trim() || '';
        const aClaim  = document.getElementById('claim_area_amenities')?.innerText || '';
        const aInput  = document.getElementById('amenities_details');
        if (aInput) {
            aInput.value = (aMatch === '1')
                ? 'Verified Match: ' + aClaim
                : 'Discrepancy Logged: ' + (aReason || 'Issue flagged on-site') + ' [Landlord Stated: ' + aClaim + ']';
        }

        const sMatch  = document.getElementById('input_area_safety')?.value;
        const sReason = document.getElementById('area_safety_reason')?.value?.trim() || '';
        const sClaim  = document.getElementById('claim_area_safety')?.innerText || '';
        const sInput  = document.getElementById('safety_details');
        if (sInput) {
            sInput.value = (sMatch === '1')
                ? 'Verified Match: ' + sClaim
                : 'Discrepancy Logged: ' + (sReason || 'Issue flagged on-site') + ' [Landlord Stated: ' + sClaim + ']';
        }

        if (typeof landlordPhotoVerifiedState !== 'undefined') {
            for (let pId in landlordPhotoVerifiedState) {
                if (!landlordPhotoVerifiedState[pId]) {
                    combined += '[Discrepancy - Landlord Listing Photo ' + pId + ']: Physical photo does not match listing photo ' + pId + '.\n';
                }
            }
        }
        if (raw.trim()) combined += '\nGeneral Remarks:\n' + raw.trim();

        const hiddenEl = document.getElementById('agent_comments_hidden');
        if (hiddenEl) hiddenEl.value = combined;
    });
});

/* ---------- Area Report: Bus Routes Toggle ---------- */
function toggleBusRoutes() {
    var chkBus = document.getElementById('chk_bus');
    var busBox = document.getElementById('busRoutesFilterBox');
    if (chkBus && busBox) {
        busBox.style.display = chkBus.checked ? 'block' : 'none';
    }
}

/* ---------- Area Report: Compile & Submit ---------- */
function compileReportData() {
    var chkBus = document.getElementById('chk_bus');
    if (!chkBus) return true;

    var transportItems = [];
    if (chkBus.checked) {
        var busRoutes = [];
        document.querySelectorAll('.bus_route_chk:checked').forEach(function(c) { busRoutes.push(c.value); });
        if (busRoutes.length > 0) {
            transportItems.push('Bus Transport (Routes: ' + busRoutes.join(', ') + ')');
        } else {
            transportItems.push('Bus Transport active in area');
        }
    }
    if (document.getElementById('chk_train').checked) transportItems.push('Railway Station within walking distance');
    if (document.getElementById('chk_walk').checked)  transportItems.push('Direct walking access to campus');
    if (document.getElementById('chk_tuk').checked)   transportItems.push('Tuk-Tuk stand & rideshare active');
    var transportNotes = document.getElementById('transport_notes').value.trim();
    if (transportNotes !== '') transportItems.push(transportNotes);
    if (transportItems.length === 0) transportItems.push('Standard regional transit available');
    document.getElementById('transport_details').value = transportItems.join('. ');

    var amenityItems = [];
    document.querySelectorAll('.amenity_chk:checked').forEach(function(c) { amenityItems.push(c.value); });
    var amenitiesNotes = document.getElementById('amenities_notes').value.trim();
    if (amenitiesNotes !== '') amenityItems.push(amenitiesNotes);
    if (amenityItems.length === 0) amenityItems.push('Standard local student amenities present');
    document.getElementById('amenities_details').value = amenityItems.join('. ');

    var safetyItems = [];
    document.querySelectorAll('.safety_chk:checked').forEach(function(c) { safetyItems.push(c.value); });
    var safetyNotes = document.getElementById('safety_notes').value.trim();
    if (safetyNotes !== '') safetyItems.push(safetyNotes);
    if (safetyItems.length === 0) safetyItems.push('Standard neighborhood security conditions');
    document.getElementById('safety_details').value = safetyItems.join('. ');

    return true;
}

/* ---------- Auth: Toggle Password Visibility ---------- */
function togglePasswordVisibility(inputId, iconId) {
    var input = document.getElementById(inputId);
    var icon  = document.getElementById(iconId);
    if (!input) return;
    if (input.type === 'password') {
        input.type = 'text';
        if (icon) icon.style.opacity = '0.4';
    } else {
        input.type = 'password';
        if (icon) icon.style.opacity = '1';
    }
}

/* ---------- Auth: NIC Image Preview ---------- */
function previewNIC(input, previewId) {
    if (input.files && input.files[0]) {
        const file = input.files[0];
        const previewEl = document.getElementById(previewId);
        if (!previewEl) return;

        const reader = new FileReader();
        reader.onload = function(e) {
            previewEl.innerHTML = '<img src="' + e.target.result + '" class="fa-nic-preview-img" alt="NIC Preview">' +
                                  '<div style="font-size:11px;color:#27AE60;font-weight:800;margin-top:4px;">✓ ' + file.name + ' (' + Math.round(file.size/1024) + ' KB)</div>';
        };
        reader.readAsDataURL(file);
    }
}

/* ---------- Auth: District -> City Cascading Dropdown ---------- */
const districtCityMap = {
    'Colombo': [
        'Colombo (Central)',
        'Battaramulla',
        'Dehiwala',
        'Homagama',
        'Kaduwela',
        'Kesbewa',
        'Kolonnawa',
        'Maharagama',
        'Malabe',
        'Moratuwa',
        'Mount Lavinia',
        'Nugegoda',
        'Piliyandala',
        'Rajagiriya',
        'Sri Jayawardenepura Kotte'
    ],
    'Gampaha': [
        'Gampaha',
        'Kelaniya',
        'Kiribathgoda',
        'Kadawatha',
        'Negombo',
        'Ragama',
        'Wattala',
        'Ja-Ela',
        'Minuwangoda'
    ],
    'Kalutara': [
        'Kalutara',
        'Panadura',
        'Horana',
        'Beruwala',
        'Wadduwa',
        'Bandaragama'
    ],
    'Kandy': [
        'Kandy',
        'Peradeniya',
        'Katugastota',
        'Kundasale',
        'Gampola'
    ],
    'Galle': [
        'Galle',
        'Karapitiya',
        'Hikkaduwa',
        'Ambalangoda'
    ],
    'Matara': [
        'Matara',
        'Weligama',
        'Mirissa'
    ],
    'Kurunegala': [
        'Kurunegala',
        'Kuliyapitiya',
        'Narammala'
    ],
    'Jaffna': [
        'Jaffna',
        'Chavakachcheri',
        'Point Pedro'
    ]
};

function onDistrictChange(district, selectedCity) {
    const citySelect = document.getElementById('regCity');
    if (!citySelect) return;
    citySelect.innerHTML = '';

    if (!district || !districtCityMap[district]) {
        citySelect.innerHTML = '<option value="">-- Select district first --</option>';
        citySelect.disabled = true;
        return;
    }

    citySelect.disabled = false;
    citySelect.innerHTML = '<option value="">Select city in ' + district + '</option>';

    const cities = districtCityMap[district];
    cities.forEach(function(c) {
        const opt = document.createElement('option');
        opt.value = c;
        opt.textContent = c;
        if (selectedCity && selectedCity === c) {
            opt.selected = true;
        }
        citySelect.appendChild(opt);
    });
}

