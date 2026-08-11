<?php
// Partial: _property_sidebar.php
// Left sticky column: property specs, GPS, landlord photos, registered rooms
// Expects: $task (array), $rooms (array)
?>

<!-- Property Details Card -->
<div class="details-card">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
        <span class="badge-pill-status <?php echo $task['status'] === 'completed' ? 'badge-verified-match' : 'badge-discrepancy-found'; ?>">
            <?php echo htmlspecialchars(str_replace('_', ' ', ucfirst($task['status']))); ?>
        </span>
        <span style="font-size:12px;font-weight:700;color:#8C7B74;">Assigned: <?php echo date('M d, Y', strtotime($task['assigned_at'])); ?></span>
    </div>
    <h1 class="details-title">Verification #VT-<?php echo $task['task_id']; ?></h1>
    <p class="details-subtitle">📍 <?php echo htmlspecialchars($task['address']); ?></p>

    <div class="section-label">Property Specifications</div>
    <div class="spec-grid">
        <div>
            <span class="spec-label">Property Type</span>
            <span class="spec-value"><?php echo htmlspecialchars($task['structural_type']); ?></span>
        </div>
        <div>
            <span class="spec-label">Facilities Provided</span>
            <span class="spec-value"><?php echo htmlspecialchars($task['facilities'] ?: 'None'); ?></span>
        </div>
        <div>
            <span class="spec-label">GPS Location</span>
            <span class="spec-value"><?php echo $task['latitude'] . ', ' . $task['longitude']; ?></span>
        </div>
        <div>
            <span class="spec-label">Google Maps</span>
            <a href="<?php echo htmlspecialchars($task['maps_link']); ?>" target="_blank"
               style="color:#A4856D;font-weight:700;font-size:13px;text-decoration:none;">Open Maps ↗</a>
        </div>
    </div>

    <?php
    $landlord_listing_photos = array(
        array('id' => 1, 'title' => 'Exterior / Entrance',  'src' => '../uploads/test_room1.jpg'),
        array('id' => 2, 'title' => 'Room Interior',         'src' => '../uploads/test_room2.jpg'),
        array('id' => 3, 'title' => 'Bathroom Access',       'src' => '../uploads/test_room1.jpg'),
        array('id' => 4, 'title' => 'Kitchen & Amenities',   'src' => '../uploads/test_room2.jpg'),
    );
    ?>
    <div style="display:flex;justify-content:space-between;align-items:center;margin-top:16px;margin-bottom:8px;">
        <div class="section-label" style="margin:0;">Landlord Listing Photos (<?php echo count($landlord_listing_photos); ?>)</div>
        <span id="landlordPhotosStatus" style="font-size:11px;font-weight:800;color:#0F5132;background:#D1E7DD;padding:2px 10px;border-radius:50px;">
            <?php echo count($landlord_listing_photos); ?> of <?php echo count($landlord_listing_photos); ?> Photos Verified (100%)
        </span>
    </div>

    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(130px,1fr));gap:10px;margin-bottom:16px;">
        <?php foreach ($landlord_listing_photos as $lp): ?>
        <div style="border-radius:12px;overflow:hidden;border:1.5px solid #E8DDD4;background:#FAF7F2;position:relative;">
            <img src="<?php echo htmlspecialchars($lp['src']); ?>"
                 alt="Landlord Photo <?php echo $lp['id']; ?>"
                 onclick="openPhotoModal('<?php echo htmlspecialchars($lp['src']); ?>','<?php echo htmlspecialchars($lp['title']); ?>')"
                 style="width:100%;height:110px;object-fit:cover;display:block;cursor:pointer;">
            <div style="font-size:10px;padding:6px 4px;text-align:center;color:#212529;font-weight:700;background:#FFFFFF;border-top:1px solid #E8DDD4;display:flex;flex-direction:column;gap:4px;align-items:center;">
                <span><?php echo htmlspecialchars($lp['title']); ?></span>
                <?php if ($task['status'] === 'completed'): ?>
                    <span style="font-size:9px;padding:3px 8px;background:rgba(39,174,96,0.12);color:#0F5132;border:1.5px solid #BADBCE;border-radius:50px;font-weight:800;">
                        ✓ Photo Verified
                    </span>
                <?php else: ?>
                    <button type="button" class="segmented-btn active-match"
                            id="photo_verify_btn_<?php echo $lp['id']; ?>"
                            onclick="toggleLandlordPhotoVerify(<?php echo $lp['id']; ?>)"
                            style="font-size:9px;padding:3px 8px;">
                        ✓ Photo Verified
                    </button>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <div class="section-label" style="margin-top:16px;">Registered Student Rooms</div>
    <?php foreach ($rooms as $room): ?>
    <div class="room-card-custom">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px;">
            <span style="font-weight:800;font-size:13px;color:#3B3330;">
                <?php echo ucfirst($room['room_type']); ?> Room (Cap: <?php echo $room['slot_capacity']; ?>)
            </span>
            <span style="font-size:12px;font-weight:800;color:#A4856D;">LKR <?php echo number_format($room['price'], 2); ?>/mo</span>
        </div>
        <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:6px;font-size:11px;color:#8C7B74;font-weight:600;">
            <div>Deposit: LKR <?php echo number_format($room['security_deposit']); ?></div>
            <div>Bath: <?php echo ucfirst($room['bathroom_access']); ?></div>
            <div>Wi-Fi: <?php echo $room['wifi_available'] ? 'Yes' : 'No'; ?></div>
        </div>
    </div>
    <?php endforeach; ?>
</div>
