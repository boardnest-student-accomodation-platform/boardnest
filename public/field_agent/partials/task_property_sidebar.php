<?php
// Partial: task_property_sidebar.php
// Left sticky column: property specs, GPS, landlord photos, registered rooms
// Expects: $task (array), $rooms (array)
?>

<!-- Property Details Card -->
<div class="details-card">
    <div class="fa-sidebar-header">
        <span class="badge-pill-status <?php echo $task['status'] === 'completed' ? 'badge-verified-match' : 'badge-discrepancy-found'; ?>">
            <?php echo htmlspecialchars(str_replace('_', ' ', ucfirst($task['status']))); ?>
        </span>
        <span class="fa-sidebar-date">Assigned: <?php echo date('M d, Y', strtotime($task['assigned_at'])); ?></span>
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
               class="fa-sidebar-link">Open Maps ↗</a>
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
    <div class="fa-sidebar-photo-header">
        <div class="section-label fa-m-0">Landlord Listing Photos (<?php echo count($landlord_listing_photos); ?>)</div>
        <span id="landlordPhotosStatus" class="fa-sidebar-photo-status">
            <?php echo count($landlord_listing_photos); ?> of <?php echo count($landlord_listing_photos); ?> Photos Verified (100%)
        </span>
    </div>

    <div class="fa-sidebar-photo-grid">
        <?php foreach ($landlord_listing_photos as $lp): ?>
        <div class="fa-sidebar-photo-card">
            <img src="<?php echo htmlspecialchars($lp['src']); ?>"
                 alt="Landlord Photo <?php echo $lp['id']; ?>"
                 onclick="openPhotoModal('<?php echo htmlspecialchars($lp['src']); ?>','<?php echo htmlspecialchars($lp['title']); ?>')"
                 class="fa-sidebar-photo-img">
            <div class="fa-sidebar-photo-footer">
                <span><?php echo htmlspecialchars($lp['title']); ?></span>
                <?php if ($task['status'] === 'completed'): ?>
                    <span class="fa-sidebar-photo-badge">
                        ✓ Photo Verified
                    </span>
                <?php else: ?>
                    <button type="button" class="segmented-btn active-match fa-sidebar-photo-btn"
                            id="photo_verify_btn_<?php echo $lp['id']; ?>"
                            onclick="toggleLandlordPhotoVerify(<?php echo $lp['id']; ?>)">
                        ✓ Photo Verified
                    </button>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <div class="section-label fa-mt-16">Registered Student Rooms</div>
    <?php foreach ($rooms as $room): ?>
    <div class="room-card-custom">
        <div class="fa-sidebar-room-header">
            <span class="fa-sidebar-room-title">
                <?php echo ucfirst($room['room_type']); ?> Room (Cap: <?php echo $room['slot_capacity']; ?>)
            </span>
            <span class="fa-sidebar-room-price">LKR <?php echo number_format($room['price'], 2); ?>/mo</span>
        </div>
        <div class="fa-sidebar-room-specs">
            <div>Deposit: LKR <?php echo number_format($room['security_deposit']); ?></div>
            <div>Bath: <?php echo ucfirst($room['bathroom_access']); ?></div>
            <div>Wi-Fi: <?php echo $room['wifi_available'] ? 'Yes' : 'No'; ?></div>
        </div>
    </div>
    <?php endforeach; ?>
</div>
