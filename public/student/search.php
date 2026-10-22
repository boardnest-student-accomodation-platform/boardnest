<?php
/** Browse verified boarding-house rooms. Booking is a POST, never a GET. */
declare(strict_types=1);
require __DIR__ . '/includes/booking.php';
$pdo = booking_db();
$student = booking_student($pdo);
$city = filter_input(INPUT_GET, 'city', FILTER_VALIDATE_INT);
$city = is_int($city) && $city > 0 ? $city : 0;
$cities = $pdo->query('SELECT city_id AS id,name,state AS district FROM cities ORDER BY name,state')->fetchAll(PDO::FETCH_ASSOC);
$roomsStmt = $pdo->prepare('SELECT * FROM (' . booking_catalog_sql() . ') catalog WHERE (? = 0 OR city_id = ?) ORDER BY can_request DESC, property_title, room_id LIMIT 150');
$roomsStmt->execute([$city,$city]);
$rooms = $roomsStmt->fetchAll(PDO::FETCH_ASSOC);
$ownStmt = $pdo->prepare('SELECT COUNT(*) FROM slot_reservations WHERE student_id=?');
$ownStmt->execute([(int)$student['student_id']]);
$hasReservation = (int)$ownStmt->fetchColumn() > 0;
$canBook = $student['verf_tier'] === 'tier2' && !$hasReservation;
$notice = booking_notice_take();
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Find Boarding Rooms | BoardNest</title><link rel="stylesheet" href="assets/student_booking.css"></head>
<body><header class="student-header"><div class="student-nav"><a class="student-brand" href="search.php">Board<span>Nest</span></a><nav aria-label="Student navigation"><a aria-current="page" href="search.php">Find rooms</a><a href="bookings.php">My bookings</a><a href="../../logout.php">Sign out</a></nav></div></header>
<main class="student-main"><div class="student-intro"><div><p class="kicker">STUDENT ACCOMMODATION</p><h1>Find your next boarding room</h1><p>Browse verified listings, check live slot availability and send one booking request.</p></div><span class="identity">Hello, <?=booking_e($student['full_name'])?></span></div>
<?php if($notice): ?><div class="notice" role="status"><?=booking_e($notice)?></div><?php endif; ?>
<?php if($student['verf_tier'] !== 'tier2'): ?><div class="notice warning" role="status">Your student verification is Tier 1. Booking requests require Tier 2 verification.</div><?php elseif($hasReservation): ?><div class="notice warning" role="status">You already have a current booking/reservation. <a href="bookings.php">View your booking</a> before requesting another.</div><?php endif; ?>
<form class="filter" method="get" action="search.php"><label for="city">Location</label><select name="city" id="city"><option value="0">All cities</option><?php foreach($cities as $row): ?><option value="<?= (int)$row['id'] ?>" <?= $city === (int)$row['id'] ? 'selected' : '' ?>><?=booking_e($row['name'].' — '.$row['district'])?></option><?php endforeach; ?></select><button class="btn secondary" type="submit">Filter rooms</button></form>
<div class="results-count"><?=count($rooms)?> listed rooms</div><div class="rooms-grid">
<?php foreach($rooms as $r): $status = (string)$r['availability_status']; $available=(int)$r['available_slots']; ?>
<article class="room-card"><div class="room-top"><span class="room-kind"><?=booking_e(ucfirst($r['room_type']))?> room</span><span class="availability <?=booking_e($status)?>"><?=booking_e(booking_status_label($status))?></span></div>
<h2><?=booking_e($r['room_label'] ?: 'Room #'.$r['room_id'])?></h2><p class="house"><?=booking_e($r['property_title'])?></p><p class="location"><?=booking_e($r['city_name'].' · '.$r['city_district'])?></p>
<p class="price">LKR <?=number_format((float)$r['price'],2)?> <small>listed monthly rent</small></p>
<div class="room-stats"><span><strong><?= $available ?></strong> available</span><span><strong><?= (int)$r['reserved_slots'] ?></strong> reserved</span><span><strong><?= (int)$r['occupied_slots'] ?></strong> occupied</span></div>
<form method="post" action="actions/submit_booking.php"><input type="hidden" name="_csrf" value="<?=booking_e(booking_csrf())?>"><input type="hidden" name="room_id" value="<?=(int)$r['room_id']?>">
<button type="submit" class="btn" <?= !$canBook || !(int)$r['can_request'] ? 'disabled aria-disabled="true"' : '' ?>><?= $available===0 ? 'Fully booked / unavailable' : (!$canBook ? 'Booking unavailable for account' : 'Request a slot') ?></button></form>
</article>
<?php endforeach; ?><?php if(!$rooms): ?><p class="empty">No verified rooms match this location yet.</p><?php endif; ?>
</div><p class="explain">Availability can change at any time. The server checks the exact slot again when you submit. Bookings are only accepted for active Tier 2 student accounts.</p></main></body></html>
