<?php
/**
 * Reservation entry point. The UI button is only guidance; this server action
 * locks student and room rows, verifies publication, and writes booking,
 * reservation and physical slot state atomically in PHP. Room slot identity is never accepted from POST.
 */
declare(strict_types=1);
require dirname(__DIR__) . '/includes/booking.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Allow: POST'); http_response_code(405); exit('POST only');
}
booking_check_csrf();
$pdo=booking_db();
$student=booking_student($pdo);
$roomId=filter_input(INPUT_POST, 'room_id', FILTER_VALIDATE_INT);
if (!is_int($roomId) || $roomId < 1) {
    booking_notice('Invalid room selection.'); booking_go('search.php');
}
try {
    // Lock student first so two simultaneous requests from same student serialize.
    $pdo->beginTransaction();
    $lockStudent=$pdo->prepare("SELECT s.verf_tier,u.status FROM students s JOIN users u ON u.user_id=s.user_id WHERE s.student_id=? FOR UPDATE");
    $lockStudent->execute([(int)$student['student_id']]);
    $studentState=$lockStudent->fetch(PDO::FETCH_ASSOC);
    if (!$studentState || $studentState['verf_tier'] !== 'tier2' || $studentState['status'] !== 'active') {
        throw new DomainException('Tier 2 verification and an active student account are required.');
    }
    // Lock parent room before selecting a slot. Another request for same room waits.
    $roomLock=$pdo->prepare('SELECT room_id FROM rooms WHERE room_id=? FOR UPDATE');
    $roomLock->execute([$roomId]);
    if (!$roomLock->fetch()) throw new DomainException('Room no longer exists.');
    // The V3 joined catalog enforces verified property/room and an approved, active landlord.
    $listing=$pdo->prepare('SELECT available_slots,can_request FROM (' . booking_catalog_sql() . ') catalog WHERE room_id=?');
    $listing->execute([$roomId]);
    $visible=$listing->fetch(PDO::FETCH_ASSOC);
    if (!$visible || !(int)$visible['can_request']) {
        throw new DomainException('This room is fully booked or not available for requests.');
    }
    $already=$pdo->prepare('SELECT 1 FROM slot_reservations WHERE student_id=? LIMIT 1');
    $already->execute([(int)$student['student_id']]);
    if ($already->fetchColumn()) throw new DomainException('You already have a current reservation.');
    $slot=$pdo->prepare("SELECT slot_id FROM room_slots WHERE room_id=? AND status='available' AND NOT EXISTS (SELECT 1 FROM room_slot_blocks bl WHERE bl.slot_id=room_slots.slot_id) ORDER BY slot_no LIMIT 1 FOR UPDATE");
    $slot->execute([$roomId]);
    $slotId=$slot->fetchColumn();
    if (!$slotId) throw new DomainException('All slots have just been booked. Refresh the listing.');
    // Transaction + UNIQUE constraints protect concurrent bookings.
    $insert=$pdo->prepare("INSERT INTO bookings (student_id,slot_id,bk_status) VALUES (?,?,'pending')");
    $insert->execute([(int)$student['student_id'],(int)$slotId]);
    $bookingId=(int)$pdo->lastInsertId();
    $landlordQ=$pdo->prepare('SELECT p.landlord_id FROM room_slots s JOIN rooms r ON r.room_id=s.room_id JOIN properties p ON p.property_id=r.property_id WHERE s.slot_id=?');
    $landlordQ->execute([(int)$slotId]);$landlordId=(int)$landlordQ->fetchColumn();
    $pdo->prepare("INSERT INTO slot_reservations(booking_id,student_id,slot_id,reservation_status) VALUES (?,?,?,'reserved')")->execute([$bookingId,(int)$student['student_id'],(int)$slotId]);
    $pdo->prepare("INSERT INTO booking_landlord_actions(booking_id,landlord_id,decision) VALUES (?,?,'pending')")->execute([$bookingId,$landlordId]);
    $reserveSlot=$pdo->prepare("UPDATE room_slots SET status='pending',student_id=? WHERE slot_id=? AND status='available'");
    $reserveSlot->execute([(int)$student['student_id'],(int)$slotId]);
    if ($reserveSlot->rowCount() !== 1) throw new DomainException('Slot availability changed. Please retry.');
    $pdo->commit();
    booking_notice('Booking request sent! The selected slot is reserved until the landlord reviews it.');
    booking_go('bookings.php');
} catch (DomainException $error) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    booking_notice($error->getMessage());
    booking_go('search.php');
} catch (Throwable $error) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    error_log('BoardNest student booking: '.$error->getMessage());
    booking_notice('Unable to reserve this room. It may have been booked already. Please try another.');
    booking_go('search.php');
}
