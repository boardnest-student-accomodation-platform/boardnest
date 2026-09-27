/* =============================================================
   BoardNest — Field Agent Scripts
   public/assets/js/field_agent.js
   ============================================================= */

/* ---------- Audit Checklist ---------- */
const auditItems = ['structural', 'electrical', 'fire', 'furnishing', 'bathroom', 'wifi', 'finance', 'kitchen_food'];

function setAuditSegment(itemId, isMatch) {
    const btnMatch  = document.getElementById('btn_match_' + itemId);
    const btnIssue  = document.getElementById('btn_issue_' + itemId);
    const hidden    = document.getElementById('input_' + itemId);
    const container = document.getElementById(itemId + '_reason_container');

    if (isMatch) {
        if (btnMatch)  btnMatch.className  = 'segmented-btn active-match';
        if (btnIssue)  btnIssue.className  = 'segmented-btn';
        if (hidden)    hidden.value        = '1';
        if (container) container.style.display = 'none';
    } else {
        if (btnMatch)  btnMatch.className  = 'segmented-btn';
        if (btnIssue)  btnIssue.className  = 'segmented-btn active-issue';
        if (hidden)    hidden.value        = '0';
        if (container) container.style.display = 'block';
    }
    updateAuditProgress();
}

function updateAuditProgress() {
    let matchCount = 0;
    auditItems.forEach(id => {
        const val = document.getElementById('input_' + id)?.value;
        if (val === '1') matchCount++;
    });
    const total   = auditItems.length;
    const percent = Math.round((matchCount / total) * 100);
    const textEl  = document.getElementById('auditProgressText');
    const fillEl  = document.getElementById('auditProgressFill');
    if (textEl && fillEl) {
        textEl.innerText       = matchCount + ' of ' + total + ' items verified (' + percent + '%)';
        fillEl.style.width     = percent + '%';
        fillEl.style.background = percent === 100 ? '#27AE60' : '#A4856D';
    }
}

/* ---------- Landlord Photo Verification ---------- */
const landlordPhotoVerifiedState = { 1: true, 2: true, 3: true, 4: true };

function toggleLandlordPhotoVerify(photoId) {
    landlordPhotoVerifiedState[photoId] = !landlordPhotoVerifiedState[photoId];
    const btn = document.getElementById('photo_verify_btn_' + photoId);
    if (btn) {
        if (landlordPhotoVerifiedState[photoId]) {
            btn.className = 'segmented-btn active-match';
            btn.innerText = '✓ Photo Verified';
        } else {
            btn.className = 'segmented-btn active-issue';
            btn.innerText = '✕ Photo Discrepancy';
        }
    }
    updateLandlordPhotosStatus();
}

function updateLandlordPhotosStatus() {
    let verified = 0;
    const total  = Object.keys(landlordPhotoVerifiedState).length;
    for (let id in landlordPhotoVerifiedState) {
        if (landlordPhotoVerifiedState[id]) verified++;
    }
    const percent   = Math.round((verified / total) * 100);
    const statusEl  = document.getElementById('landlordPhotosStatus');
    if (statusEl) {
        statusEl.innerText       = verified + ' of ' + total + ' Photos Verified (' + percent + '%)';
        statusEl.style.background = percent === 100 ? '#D1E7DD' : '#FFF2D7';
        statusEl.style.color      = percent === 100 ? '#0F5132' : '#A4856D';
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
        watermark: '📍 BOARDNEST VERIFIED AUDIT | 🕒 ' + dateStr + ' ' + timeStr + ' [GPS Verified On-Site]'
    };
}

function _stampCanvas(canvas, ctx) {
    const ts           = getAuditTimestampInfo();
    const bannerHeight = Math.max(34, Math.round(canvas.height * 0.07));
    ctx.fillStyle      = 'rgba(0,0,0,0.75)';
    ctx.fillRect(0, canvas.height - bannerHeight, canvas.width, bannerHeight);
    ctx.fillStyle = '#FFC107';
    ctx.font      = 'bold ' + Math.max(12, Math.round(bannerHeight * 0.42)) + 'px sans-serif';
    ctx.fillText('📍 AUDIT PROOF STAMP', 12, canvas.height - (bannerHeight * 0.35));
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
            if (badge) badge.innerText = '🕒 Uploaded: ' + ts.display + ' (Stamped)';

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
    if (input)      input.value             = '';
    if (previewBox) previewBox.style.display = 'none';
    if (initialBtn) initialBtn.style.display = 'block';
}

/* ---------- Live Camera Modal ---------- */
let currentCameraTargetId = '';
let activeMediaStream     = null;

function openLiveCameraModal(targetInputId) {
    currentCameraTargetId  = targetInputId;
    const modal  = document.getElementById('liveCameraModal');
    const video  = document.getElementById('liveCameraVideo');
    const notice = document.getElementById('cameraPermissionNotice');
    if (!modal || !video || !notice) return;

    modal.style.display  = 'flex';
    notice.style.display = 'block';

    if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
        navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' } })
            .then(stream => {
                activeMediaStream  = stream;
                video.srcObject    = stream;
                notice.style.display = 'none';
            })
            .catch(() => {
                navigator.mediaDevices.getUserMedia({ video: true })
                    .then(stream => {
                        activeMediaStream  = stream;
                        video.srcObject    = stream;
                        notice.style.display = 'none';
                    })
                    .catch(err => {
                        notice.innerHTML = '❌ Camera access denied: ' + err.message;
                    });
            });
    } else {
        triggerCamera(targetInputId);
        modal.style.display = 'none';
    }
}

function snapLivePhoto() {
    const video  = document.getElementById('liveCameraVideo');
    const canvas = document.getElementById('cameraCanvas');
    if (!video || !canvas) return;

    canvas.width  = video.videoWidth  || 800;
    canvas.height = video.videoHeight || 600;
    const ctx     = canvas.getContext('2d');
    ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
    const ts      = _stampCanvas(canvas, ctx);

    const previewImg = document.getElementById(currentCameraTargetId + '_preview');
    if (previewImg) previewImg.src = canvas.toDataURL('image/jpeg', 0.92);

    const isStdPhoto = (currentCameraTargetId === 'photo1Input' || currentCameraTargetId === 'photo2Input');
    const badgeId    = isStdPhoto
        ? (currentCameraTargetId === 'photo1Input' ? 'photo1_badge' : 'photo2_badge')
        : currentCameraTargetId + '_badge';
    const badge      = document.getElementById(badgeId);
    if (badge) badge.innerText = '🕒 Captured: ' + ts.display + ' (Stamped)';

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

    closeLiveCamera();
}

function closeLiveCamera() {
    if (activeMediaStream) {
        activeMediaStream.getTracks().forEach(t => t.stop());
        activeMediaStream = null;
    }
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
            <option value="⚠️ Defect / Damage Evidence" selected>⚠️ Defect / Damage Evidence</option>
            <option value="🚿 Bathroom &amp; Sanitation Access">🚿 Bathroom &amp; Sanitation Access</option>
            <option value="🍳 Kitchen &amp; Cooking Amenities">🍳 Kitchen &amp; Cooking Amenities</option>
            <option value="⚡ Electrical Panel &amp; Breakers">⚡ Electrical Panel &amp; Breakers</option>
            <option value="💧 Water &amp; Utility Meter">💧 Water &amp; Utility Meter</option>
            <option value="🔑 Locks &amp; Security Systems">🔑 Locks &amp; Security Systems</option>
            <option value="📷 General Additional Verification">📷 General Additional Verification</option>
        </select>
        <div id="${initialBtnId}">
            <button type="button" class="btn-camera-capture" onclick="openLiveCameraModal('${currentId}')" style="width:100%;justify-content:center;background:#A4856D;color:#FFFFFF;">
                📸 Open Live Camera
            </button>
        </div>
        <div id="${previewBoxId}" style="display:none;margin-top:6px;">
            <div style="position:relative;border-radius:10px;overflow:hidden;border:1.5px solid #E8DDD4;background:#FFFFFF;">
                <img id="${currentId}_preview" class="photo-preview-thumb" />
                <span id="${currentId}_badge" class="photo-timestamp-badge">🕒 Captured Today</span>
            </div>
            <button type="button" class="btn-retake" onclick="clearPhotoInput('${currentId}','${previewBoxId}','${initialBtnId}')">🗑 Retake Photo</button>
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
        statusDiv.innerText = '❌ Geolocation Unsupported';
        descDiv.innerText   = 'Your browser does not support Geolocation.';
        return;
    }
    statusDiv.innerText = '⌛ Fetching GPS Coordinates...';
    descDiv.innerText   = 'Please allow location access if prompted.';
    btn.disabled        = true;

    navigator.geolocation.getCurrentPosition(function(pos) {
        const dist = haversineDistance(pos.coords.latitude, pos.coords.longitude, propLat, propLng);
        if (dist <= 100) {
            statusDiv.innerText = '🔓 Geofence Match (Within ' + Math.round(dist) + 'm)';
            descDiv.innerText   = 'Coordinates matched. Unlocking checklist...';
            _sendGeofencePass();
        } else {
            statusDiv.innerText = '❌ Geofence Lock (' + Math.round(dist) + 'm away)';
            descDiv.innerText   = 'You must be within 100m of the property. Move closer and try again.';
            btn.disabled = false;
        }
    }, function(err) {
        statusDiv.innerText = '❌ GPS Error: ' + err.message;
        descDiv.innerText   = 'Enable GPS and try again.';
        btn.disabled = false;
    }, { enableHighAccuracy: true, timeout: 10000 });
}

function simulateGPSMatch() {
    const statusDiv = document.getElementById('geofenceStatus');
    const descDiv   = document.getElementById('geofenceDesc');
    statusDiv.innerText = '🔓 Geofence Match (Simulated)';
    descDiv.innerText   = 'Coordinates matched. Unlocking checklist...';
    _sendGeofencePass();
}

function _sendGeofencePass() {
    const xhr = new XMLHttpRequest();
    xhr.open('POST', 'actions/update_task.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4 && xhr.status === 200) window.location.reload();
    };
    if (typeof complaintId !== 'undefined' && complaintId > 0) {
        xhr.send('complaint_id=' + complaintId + '&action_type=claim&geofence_override=1');
    } else {
        xhr.send('task_id=' + taskId + '&action_type=claim&geofence_override=1');
    }
}

/* ---------- Star Rating ---------- */
let selectedRating = 0;
const ratingTexts  = {
    1: '⭐ Poor Safety (Highly isolated or active complaints)',
    2: '⭐⭐ Caution Advised (Poor street lighting or isolated)',
    3: '⭐⭐⭐ Safe (Average residential area)',
    4: '⭐⭐⭐⭐ Very Safe (Standard residential area)',
    5: '⭐⭐⭐⭐⭐ Excellent (Well lit, secure gate, safe area)'
};

function setStarRating(val) {
    selectedRating = val;
    document.getElementById('ratingInput').value   = val;
    document.getElementById('ratingLabel').innerText = ratingTexts[val];
    document.getElementById('ratingLabel').style.color = 'var(--color-primary-dark)';
    _highlightStars(val);
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

    auditForm.addEventListener('submit', function() {
        const rawEl    = document.getElementById('agent_comments_raw');
        const raw      = rawEl ? rawEl.value : '';
        let combined   = '';

        const items = [
            { id: 'structural',   label: 'Structural Safety' },
            { id: 'electrical',   label: 'Electrical Wiring' },
            { id: 'fire',         label: 'Fire Exit pathways' },
            { id: 'furnishing',   label: 'Furnishing details match' },
            { id: 'bathroom',     label: 'Bathroom Access type match' },
            { id: 'wifi',         label: 'Wi-Fi Availability match' },
            { id: 'finance',      label: 'Price & Deposit match' },
            { id: 'kitchen_food', label: 'Kitchen & Food Access match' }
        ];
        items.forEach(function(item) {
            const hidden = document.getElementById('input_' + item.id);
            const area   = document.getElementById(item.id + '_reason');
            if (hidden && hidden.value === '0' && area && area.value.trim()) {
                combined += '❌ [' + item.label + ' Discrepancy]: ' + area.value.trim() + '\n';
            }
        });

        if (typeof landlordPhotoVerifiedState !== 'undefined') {
            for (let pId in landlordPhotoVerifiedState) {
                if (!landlordPhotoVerifiedState[pId]) {
                    combined += '⚠️ [Landlord Listing Photo ' + pId + ' Discrepancy]: Physical photo does not match listing photo ' + pId + '.\n';
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
    var transportItems = [];
    if (document.getElementById('chk_bus').checked) {
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

