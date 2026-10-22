<?php
declare(strict_types=1);
/**
 * LEARNING EDITION - READ THIS FIRST
 * File: modules/booking/models/Booking.php
 * Role: Landlord MVC component; read the method notes and its calling controller to understand its responsibilities.
 * PHP comments below do not execute and do not change business logic.
 * Flow: HTTP request -> controller -> service -> model -> PDO/database.
 * Security: request values must be validated and scoped to the logged-in user.
 */

/** Unified booking model: bridge group bookings.bk_status and landlord action metadata. */
namespace BoardNest\Modules\Booking\Models;
use BoardNest\Core\Database;
final class Booking {
    /** STUDY - __construct(): Initialization: receive services or dependencies; avoid database writes here. */
    public function __construct(private Database $db){}
    /** Map group booking status + occupancy metadata to the established landlord UI lifecycle. */
    private const STATUS = "CASE WHEN a.decision='rejected' THEN 'rejected'
      WHEN a.occupancy_status='moved_out' OR b.bk_status='completed' THEN 'completed'
      WHEN a.occupancy_status='occupied' THEN 'active'
      WHEN b.bk_status='cancelled' THEN 'cancelled'
      WHEN a.decision='accepted' OR b.bk_status='confirmed' THEN 'accepted'
      ELSE 'pending' END";
    private const FROM = ' FROM bookings b JOIN room_slots s ON s.slot_id=b.slot_id JOIN rooms r ON r.room_id=s.room_id JOIN properties p ON p.property_id=r.property_id JOIN students st ON st.student_id=b.student_id JOIN users u ON u.user_id=st.user_id LEFT JOIN room_landlord_details rd ON rd.room_id=r.room_id LEFT JOIN booking_landlord_actions a ON a.booking_id=b.booking_id';
    /** STUDY - all(): Read parameters, authorization predicates and return value; consider failures and side effects. */
    public function all(int $landlordId): array {
        return $this->db->all('SELECT b.*,b.bk_date AS requested_at,s.room_id,s.slot_no AS slot_number,rd.room_label,u.full_name AS student_name,p.title AS property_title,'.self::STATUS.' AS status'.self::FROM.' WHERE p.landlord_id=? ORDER BY b.bk_date DESC LIMIT 200',[$landlordId]);
    }
    /** STUDY - owned(): Ownership guard: query includes the current landlord ID to block ID guessing. */
    public function owned(int $id,int $landlordId): array {
        $row=$this->db->one('SELECT b.*,b.bk_date AS requested_at,s.room_id,s.slot_no AS slot_number,rd.room_label,u.user_id AS student_user_id,u.full_name AS student_name,u.email AS student_email,st.mobile AS student_phone,p.title AS property_title,r.price,a.decision_at,a.rejection_reason,a.move_in_date,a.move_out_date,'.self::STATUS.' AS status'.self::FROM.' WHERE b.booking_id=? AND p.landlord_id=?',[$id,$landlordId]);
        if(!$row)throw new \DomainException('Booking not found or access denied.');return $row;
    }
    /** STUDY - occupants(): Read parameters, authorization predicates and return value; consider failures and side effects. */
    public function occupants(int $landlordId): array {
        return $this->db->all("SELECT b.booking_id,b.student_id,u.full_name,p.title AS property_title,rd.room_label,s.slot_no AS slot_number FROM slot_reservations sr JOIN bookings b ON b.booking_id=sr.booking_id JOIN students st ON st.student_id=b.student_id JOIN users u ON u.user_id=st.user_id JOIN room_slots s ON s.slot_id=sr.slot_id JOIN rooms r ON r.room_id=s.room_id JOIN properties p ON p.property_id=r.property_id LEFT JOIN room_landlord_details rd ON rd.room_id=r.room_id WHERE p.landlord_id=? AND sr.reservation_status='occupied' ORDER BY b.booking_id DESC",[$landlordId]);
    }
    /** Persist action metadata. BookingService explicitly synchronizes booking and slot rows in the same transaction. */
    public function change(int $id,string $expected,string $next,?string $reason): void {
        $sql=match($next){
            'accepted'=>"UPDATE booking_landlord_actions SET decision='accepted',decision_at=NOW() WHERE booking_id=? AND decision='pending' AND occupancy_status='not_moved_in'",
            'rejected'=>"UPDATE booking_landlord_actions SET decision='rejected',decision_at=NOW(),rejection_reason=? WHERE booking_id=? AND decision='pending' AND occupancy_status='not_moved_in'",
            'active'=>"UPDATE booking_landlord_actions SET occupancy_status='occupied',move_in_date=CURRENT_DATE WHERE booking_id=? AND decision='accepted' AND occupancy_status='not_moved_in'",
            'completed'=>"UPDATE booking_landlord_actions SET occupancy_status='moved_out',move_out_date=CURRENT_DATE WHERE booking_id=? AND decision='accepted' AND occupancy_status='occupied'",
            default=>throw new \DomainException('Unsupported booking transition.')
        };
        $params=$next==='rejected'?[$reason,$id]:[$id];
        if($this->db->run($sql,$params)!==1)throw new \DomainException('Booking state changed. Refresh and try again.');
    }
    /** STUDY - stats(): Read parameters, authorization predicates and return value; consider failures and side effects. */
    public function stats(int $lid): array {
        $sql="SELECT (SELECT COUNT(*) FROM properties WHERE landlord_id=? AND status<>'suspended') AS properties,
            (SELECT COUNT(*) FROM rooms r JOIN properties p ON p.property_id=r.property_id WHERE p.landlord_id=?) AS rooms,
            (SELECT COUNT(*) FROM bookings b JOIN room_slots s ON s.slot_id=b.slot_id JOIN rooms r ON r.room_id=s.room_id JOIN properties p ON p.property_id=r.property_id WHERE p.landlord_id=? AND b.bk_status='pending') AS bookings,
            (SELECT COUNT(*) FROM slot_reservations sr JOIN room_slots s ON s.slot_id=sr.slot_id JOIN rooms r ON r.room_id=s.room_id JOIN properties p ON p.property_id=r.property_id WHERE p.landlord_id=? AND sr.reservation_status='occupied') AS occupants";
        return $this->db->one($sql,[$lid,$lid,$lid,$lid])??[];
    }
}
