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
        <span class="fa-sidebar-date">Assigned: <?php echo date('M d, Y', strtotime($task['created_at'])); ?></span>
    </div>
    <h1 class="details-title">Verification #VT-<?php echo $task['task_id']; ?></h1>
    <p class="details-subtitle"><?php echo fa_icon('pin', '', 14); ?> <?php echo htmlspecialchars($task['address']); ?><?php if (!empty($task['city'])) echo ', ' . htmlspecialchars($task['city']); ?></p>

    <div class="section-label">Property Specifications</div>
    <div class="spec-grid">
        <div>
            <span class="spec-label">Property Type</span>
            <span class="spec-value"><?php echo htmlspecialchars(!empty($task['structural_type']) ? $task['structural_type'] : 'Apartment'); ?></span>
        </div>
        <div>
            <span class="spec-label">City / Region</span>
            <span class="spec-value"><?php echo htmlspecialchars(!empty($task['city']) ? $task['city'] : 'Colombo'); ?></span>
        </div>
        <div>
            <span class="spec-label">Facilities Provided</span>
            <span class="spec-value"><?php echo htmlspecialchars(!empty($task['facilities']) ? $task['facilities'] : 'None listed'); ?></span>
        </div>
        <div>
            <span class="spec-label">Google Maps</span>
            <?php 
            $maps_href = !empty($task['maps_url']) ? $task['maps_url'] : (!empty($task['maps_link']) ? $task['maps_link'] : '');
            if (empty($maps_href) || $maps_href === '#') {
                if (!empty($task['latitude']) && !empty($task['longitude'])) {
                    $maps_href = 'https://www.google.com/maps?q=' . urlencode($task['latitude'] . ',' . $task['longitude']);
                } else {
                    $maps_href = '#';
                }
            }
            ?>
            <?php if ($maps_href !== '#'): ?>
                <a href="<?php echo htmlspecialchars($maps_href); ?>" target="_blank" rel="noopener noreferrer"
                   class="fa-sidebar-link">Open Maps ↗</a>
            <?php else: ?>
                <span class="spec-value" style="color: #8C7B74;">Not provided</span>
            <?php endif; ?>
        </div>
    </div>

    <?php
    $landlord_listing_photos = array(
        array('id' => 1, 'title' => 'Exterior / Entrance',  'src' => '../upload/fieldagent/exterior_entrance.jpg'),
        array('id' => 2, 'title' => 'Room Interior',         'src' => '../upload/fieldagent/room_interior.jpg'),
        array('id' => 3, 'title' => 'Bathroom Access',       'src' => '../upload/fieldagent/bathroom_access.jpg'),
        array('id' => 4, 'title' => 'Kitchen & Amenities',   'src' => '../upload/fieldagent/kitchen_amenities.jpg'),
    );
    ?>
    <div class="fa-sidebar-photo-header">
        <div class="section-label fa-m-0">Landlord Listing Photos (<?php echo count($landlord_listing_photos); ?>)</div>
        <span id="landlordPhotosStatus" class="fa-sidebar-photo-status" style="background: #F0F0F0; color: #666;">
            0 of <?php echo count($landlord_listing_photos); ?> Photos Verified (0%)
        </span>
    </div>

    <div class="fa-sidebar-photo-grid">
        <?php foreach ($landlord_listing_photos as $lp): ?>
        <div class="fa-sidebar-photo-card" id="landlord_photo_card_<?php echo $lp['id']; ?>">
            <img src="<?php echo htmlspecialchars($lp['src']); ?>"
                 alt="Landlord Photo <?php echo $lp['id']; ?>"
                 onclick="openPhotoModal('<?php echo htmlspecialchars($lp['src']); ?>','<?php echo htmlspecialchars($lp['title']); ?>')"
                 class="fa-sidebar-photo-img">
            <div class="fa-sidebar-photo-footer">
                <span class="fa-sidebar-photo-title"><?php echo htmlspecialchars($lp['title']); ?></span>
                <?php if ($task['status'] === 'completed'): ?>
                    <span class="fa-sidebar-photo-badge">
                        <?php echo fa_icon('check', '', 12); ?> Photo Verified
                    </span>
                <?php else: ?>
                    <div class="fa-sidebar-photo-actions">
                        <button type="button" class="fa-photo-action-btn btn-match"
                                id="photo_verify_btn_<?php echo $lp['id']; ?>"
                                onclick="setLandlordPhotoStatus(<?php echo $lp['id']; ?>, true)">
                            <?php echo fa_icon('check', '', 11); ?> Verify
                        </button>
                        <button type="button" class="fa-photo-action-btn btn-issue"
                                id="photo_discrepancy_btn_<?php echo $lp['id']; ?>"
                                onclick="setLandlordPhotoStatus(<?php echo $lp['id']; ?>, false)">
                            <?php echo fa_icon('x', '', 11); ?> Discrepancy
                        </button>
                    </div>
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
