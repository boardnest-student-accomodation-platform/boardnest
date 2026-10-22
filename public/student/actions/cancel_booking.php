<?php
/** Cancellation releases reservation and slot rows atomically in PHP. */
declare(strict_types=1);
require dirname(__DIR__) . '/includes/booking.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {header('Allow: POST');http_response_code(405);exit('POST only');}
booking_check_csrf();
$pdo=booking_db(); $student=booking_student($pdo);
$bookingId=filter_input(INPUT_POST,'booking_id',FILTER_VALIDATE_INT);
if(!is_int($bookingId)||$bookingId<1){booking_notice('Invalid booking ID.');booking_go('bookings.php');}
try {
    $pdo->beginTransaction();
    $s=$pdo->prepare('SELECT bk_status FROM bookings WHERE booking_id=? AND student_id=? FOR UPDATE');
    $s->execute([$bookingId,(int)$student['student_id']]); $b=$s->fetch(PDO::FETCH_ASSOC);
    if(!$b || $b['bk_status']!=='pending') throw new DomainException('Only your own pending requests may be cancelled.');
    $q=$pdo->prepare("UPDATE bookings SET bk_status='cancelled' WHERE booking_id=? AND student_id=? AND bk_status='pending'");
    $q->execute([$bookingId,(int)$student['student_id']]);
    if($q->rowCount()!==1) throw new DomainException('Booking status changed. Refresh the page.');
    $pdo->prepare('DELETE FROM slot_reservations WHERE booking_id=?')->execute([$bookingId]);
    $pdo->prepare("UPDATE room_slots s JOIN bookings b ON b.slot_id=s.slot_id SET s.status='available',s.student_id=NULL,s.move_in=NULL WHERE b.booking_id=?")->execute([$bookingId]);
    $pdo->commit(); booking_notice('Request cancelled. Its slot is available again.');
} catch(DomainException $e){if($pdo->inTransaction())$pdo->rollBack(); booking_notice($e->getMessage());}
catch(Throwable $e){if($pdo->inTransaction())$pdo->rollBack();error_log('BoardNest booking cancel: '.$e->getMessage());booking_notice('Cancellation failed. Try again.');}
booking_go('bookings.php');
