<?php
require_once '../../../includes/session.php';
requireRole('landlord');
require_once '../../../config/db.php';

if (isset($_GET['id']) && isset($_GET['status'])) {
    $booking_id  = $_GET['id'];
    $status      = $_GET['status']; // 'accepted' or 'rejected'
    $landlord_id = $_SESSION['user_id'];

    if (in_array($status, ['accepted', 'rejected'])) {
        // Validation: Booking logged Landlord's Property check
        $stmt = $pdo->prepare("
            UPDATE bookings b
            JOIN rooms r ON b.room_id = r.room_id
            JOIN properties p ON r.property_id = p.property_id
            SET b.bl_status = ?
            WHERE b.booking_id = ? AND p.landlord_id = ?
        ");
        $stmt->execute([$status, $booking_id, $landlord_id]);
    }
}

header('Location: ../dashboard.php?status=booking_updated');
exit();
?>