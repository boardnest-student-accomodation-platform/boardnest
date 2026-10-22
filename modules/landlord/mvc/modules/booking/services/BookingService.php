<?php
declare(strict_types=1);
/**
 * LEARNING EDITION - READ THIS FIRST
 * File: modules/booking/services/BookingService.php
 * Role: Booking state machine and transaction; booking status, reservation and physical slot must agree.
 * PHP comments below do not execute and do not change business logic.
 * Flow: HTTP request -> controller -> service -> model -> PDO/database.
 * Security: request values must be validated and scoped to the logged-in user.
 */

/**
 * Booking decisions for the V3 unified BoardNest schema.
 * bookings.bk_status belongs to the group; landlord decision/occupancy data
 * belongs to booking_landlord_actions. BookingService synchronizes these fields inside one PHP-controlled transaction.
 */
namespace BoardNest\Modules\Booking\Services;

use BoardNest\Core\App;
use BoardNest\Modules\Booking\Models\Booking;

final class BookingService {
    private Booking $bookings;

    /** STUDY - __construct(): Initialization: receive services or dependencies; avoid database writes here. */
    public function __construct(private App $app) {
        $this->bookings = new Booking($app->db());
    }
    /** STUDY - model(): Read parameters, authorization predicates and return value; consider failures and side effects. */
    public function model(): Booking { return $this->bookings; }

    /** Only allow pending->accepted/rejected, accepted->active, active->completed. */
    public function decide(int $id, string $action, array $landlord): void {
        $transitions = [
            'accept'   => ['pending', 'accepted'],
            'reject'   => ['pending', 'rejected'],
            'move_in'  => ['accepted', 'active'],
            'move_out' => ['active', 'completed'],
        ];
        if (!isset($transitions[$action])) {
            throw new \DomainException('Invalid booking action.');
        }
        [$expected, $next] = $transitions[$action];

        $db = $this->app->db();
        $pdo = $db->pdo();
        $pdo->beginTransaction();
        try {
            // Landlord ownership is checked through the booked physical slot.
            // Locking b/a rows serializes simultaneous booking decisions.
            $row = $db->one(
                'SELECT b.bk_status, a.decision, a.occupancy_status
                 FROM bookings b
                 JOIN room_slots s ON s.slot_id=b.slot_id
                 JOIN rooms r ON r.room_id=s.room_id
                 JOIN properties p ON p.property_id=r.property_id
                 JOIN booking_landlord_actions a ON a.booking_id=b.booking_id
                 WHERE b.booking_id=? AND p.landlord_id=? FOR UPDATE',
                [$id, (int)$landlord['landlord_id']]
            );
            if (!$row) throw new \DomainException('Booking not found.');

            $status = 'pending';
            if ($row['decision'] === 'rejected') $status = 'rejected';
            elseif ($row['occupancy_status'] === 'moved_out' || $row['bk_status'] === 'completed') $status = 'completed';
            elseif ($row['occupancy_status'] === 'occupied') $status = 'active';
            elseif ($row['bk_status'] === 'cancelled') $status = 'cancelled';
            elseif ($row['decision'] === 'accepted' || $row['bk_status'] === 'confirmed') $status = 'accepted';

            if ($status !== $expected) {
                throw new \DomainException('Booking status changed. Reload the page.');
            }

            // The canonical booking and reservation must agree before any transition.
            $reservation = $db->one('SELECT reservation_status FROM slot_reservations WHERE booking_id=? FOR UPDATE',[$id]);
            if (!$reservation) throw new \DomainException('Booking has no current reservation. Refresh the page.');
            if (($expected==='active' && $reservation['reservation_status']!=='occupied') ||
                ($expected!=='active' && $reservation['reservation_status']!=='reserved')) {
                throw new \DomainException('Reservation state is inconsistent. Contact support.');
            }
            $reason = trim($this->app->request()->field('reason'));
            if (mb_strlen($reason) > 1000) throw new \DomainException('Reason is too long.');
            $this->bookings->change($id, $expected, $next, $reason !== '' ? $reason : null);
            if($next==='accepted'){
                if ($db->run("UPDATE bookings SET bk_status='confirmed' WHERE booking_id=? AND bk_status='pending'",[$id]) !== 1)
                    throw new \DomainException('Booking status changed while accepting.');
            }elseif($next==='rejected'){
                if ($db->run("UPDATE bookings SET bk_status='cancelled' WHERE booking_id=? AND bk_status='pending'",[$id]) !== 1)
                    throw new \DomainException('Booking status changed while rejecting.');
                $db->run('DELETE FROM slot_reservations WHERE booking_id=?',[$id]);
                $db->run("UPDATE room_slots s JOIN bookings b ON b.slot_id=s.slot_id SET s.status='available',s.student_id=NULL,s.move_in=NULL WHERE b.booking_id=?",[$id]);
            }elseif($next==='active'){
                $db->run("UPDATE slot_reservations SET reservation_status='occupied' WHERE booking_id=? AND reservation_status='reserved'",[$id]);
                $db->run("UPDATE room_slots s JOIN bookings b ON b.slot_id=s.slot_id SET s.status='occupied',s.move_in=CURRENT_DATE WHERE b.booking_id=?",[$id]);
            }elseif($next==='completed'){
                if ($db->run("UPDATE bookings SET bk_status='completed' WHERE booking_id=? AND bk_status='confirmed'",[$id]) !== 1)
                    throw new \DomainException('Booking status changed while completing.');
                $db->run('DELETE FROM slot_reservations WHERE booking_id=?',[$id]);
                $db->run("UPDATE room_slots s JOIN bookings b ON b.slot_id=s.slot_id SET s.status='available',s.student_id=NULL,s.move_in=NULL WHERE b.booking_id=?",[$id]);
            }
            $pdo->commit();
        } catch (\Throwable $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            throw $e;
        }
    }
}
