<?php
declare(strict_types=1);
/**
 * LEARNING EDITION - READ THIS FIRST
 * File: modules/property/models/Room.php
 * Role: Landlord MVC component; read the method notes and its calling controller to understand its responsibilities.
 * PHP comments below do not execute and do not change business logic.
 * Flow: HTTP request -> controller -> service -> model -> PDO/database.
 * Security: request values must be validated and scoped to the logged-in user.
 */

/** V3 room persistence: no SQL views; group room fields + landlord detail + reservation aggregation. */
namespace BoardNest\Modules\Property\Models;
use BoardNest\Core\Database;
final class Room {
    /** STUDY - __construct(): Initialization: receive services or dependencies; avoid database writes here. */
    public function __construct(private Database $db){}
    /** Alias group column names back to the values expected by the existing templates. */
    private const ROOM = "SELECT r.*,rd.room_label,rd.rent_last_changed_at,rd.rent_locked_until,
        r.security_deposit AS deposit_amount,r.gender_pref AS gender_preference,
        r.partial_occupancy AS partial_occupancy_allowed";
    private const AVAIL = ", COALESCE(a.total_slots,0) AS total_slots,
       COALESCE(a.available_slots,0) AS available_slots,COALESCE(a.reserved_slots,0) AS reserved_slots,
       COALESCE(a.occupied_slots,0) AS occupied_slots,COALESCE(a.blocked_slots,0) AS blocked_slots";
    private const AGG = " LEFT JOIN (
      SELECT s.room_id,COUNT(*) AS total_slots,
       SUM(s.status='available' AND bl.slot_id IS NULL) AS available_slots,
       SUM(s.status='pending') AS reserved_slots,
       SUM(s.status='occupied') AS occupied_slots,
       SUM(bl.slot_id IS NOT NULL) AS blocked_slots
      FROM room_slots s LEFT JOIN room_slot_blocks bl ON bl.slot_id=s.slot_id
      GROUP BY s.room_id
    ) a ON a.room_id=r.room_id";
    /** STUDY - all(): Read parameters, authorization predicates and return value; consider failures and side effects. */
    public function all(int $lid): array {
        return $this->db->all(self::ROOM.',p.title AS property_title'.self::AVAIL.' FROM rooms r JOIN properties p ON p.property_id=r.property_id LEFT JOIN room_landlord_details rd ON rd.room_id=r.room_id'.self::AGG." WHERE p.landlord_id=? AND NOT EXISTS (SELECT 1 FROM landlord_property_archives la WHERE la.property_id=p.property_id) ORDER BY r.created_at DESC",[$lid]);
    }
    /** STUDY - forProperty(): Read parameters, authorization predicates and return value; consider failures and side effects. */
    public function forProperty(int $pid,int $lid): array {
        return $this->db->all(self::ROOM.self::AVAIL.' FROM rooms r JOIN properties p ON p.property_id=r.property_id LEFT JOIN room_landlord_details rd ON rd.room_id=r.room_id'.self::AGG.' WHERE r.property_id=? AND p.landlord_id=? AND NOT EXISTS (SELECT 1 FROM landlord_property_archives la WHERE la.property_id=p.property_id)',[$pid,$lid]);
    }
    /** STUDY - owned(): Ownership guard: query includes the current landlord ID to block ID guessing. */
    public function owned(int $id,int $lid): array {
        $row=$this->db->one(self::ROOM.',p.title AS property_title FROM rooms r JOIN properties p ON p.property_id=r.property_id LEFT JOIN room_landlord_details rd ON rd.room_id=r.room_id WHERE r.room_id=? AND p.landlord_id=? AND NOT EXISTS (SELECT 1 FROM landlord_property_archives la WHERE la.property_id=p.property_id)',[$id,$lid]);
        if(!$row)throw new \DomainException('Room not found or access denied.');return $row;
    }
    /** STUDY - countFor(): Read parameters, authorization predicates and return value; consider failures and side effects. */
    public function countFor(int $id): int {return (int)$this->db->value('SELECT COUNT(*) FROM rooms WHERE property_id=?',[$id]);}
    /** Create the room, metadata and physical slots together inside the RoomService transaction. */
    public function create(int $pid,array $v): int {
        $this->db->run("INSERT INTO rooms(property_id,room_type,price,security_deposit,deposit,slot_capacity,slot_cap,partial_occupancy,furnishing,gender_pref,house_rules,status) VALUES (?,?,?,?,?,?,?,?,?,?,?,'pending')",[$pid,$v['type'],$v['price'],$v['deposit'],$v['deposit'],$v['capacity'],$v['capacity'],$v['partial'],$v['furnishing'],$v['gender'],$v['rules']]);
        $id=(int)$this->db->pdo()->lastInsertId();
        $this->db->run('INSERT INTO room_landlord_details(room_id,room_label,rent_last_changed_at,rent_locked_until) VALUES (?,?,NOW(),DATE_ADD(NOW(), INTERVAL 1 YEAR))',[$id,$v['label']]);
        for ($i=1; $i<=(int)$v['capacity']; $i++) {
            $this->db->run("INSERT INTO room_slots(room_id,slot_no,status) VALUES (?,?,'available')",[$id,$i]);
        }
        return $id;
    }
    /** STUDY - update(): Update path: validate ownership and business locks before committing changes. */
    public function update(int $id,int $pid,array $v): void {
        $this->db->run('UPDATE rooms SET room_type=?,price=?,security_deposit=?,deposit=?,slot_capacity=?,slot_cap=?,partial_occupancy=?,furnishing=?,gender_pref=?,house_rules=? WHERE room_id=? AND property_id=?',[$v['type'],$v['price'],$v['deposit'],$v['deposit'],$v['capacity'],$v['capacity'],$v['partial'],$v['furnishing'],$v['gender'],$v['rules'],$id,$pid]);
        $this->db->run('INSERT INTO room_landlord_details(room_id,room_label) VALUES (?,?) ON DUPLICATE KEY UPDATE room_label=VALUES(room_label)',[$id,$v['label']]);
    }
    /** STUDY - windows(): Read parameters, authorization predicates and return value; consider failures and side effects. */
    public function windows(int $id): array {
        return $this->db->one('SELECT NOW()<=DATE_ADD(r.created_at,INTERVAL 15 MINUTE) AS details_open, (NOW()<=DATE_ADD(r.created_at,INTERVAL 15 MINUTE) OR NOW()>=COALESCE(d.rent_locked_until,DATE_ADD(r.created_at,INTERVAL 1 YEAR))) AS rent_open FROM rooms r LEFT JOIN room_landlord_details d ON d.room_id=r.room_id WHERE r.room_id=?',[$id])??['details_open'=>0,'rent_open'=>0];
    }
    /** STUDY - slots(): Read parameters, authorization predicates and return value; consider failures and side effects. */
    public function slots(int $id): array {
        return $this->db->all("SELECT s.*,s.slot_no AS slot_number,CASE WHEN b.slot_id IS NOT NULL THEN 'blocked' WHEN s.status='pending' THEN 'reserved' ELSE s.status END AS status_display FROM room_slots s LEFT JOIN room_slot_blocks b ON b.slot_id=s.slot_id WHERE s.room_id=? ORDER BY s.slot_no",[$id]);
    }
    /** STUDY - photos(): Read parameters, authorization predicates and return value; consider failures and side effects. */
    public function photos(int $id): array {return $this->db->all('SELECT * FROM room_photos WHERE room_id=? ORDER BY is_primary DESC,uploaded_at DESC',[$id]);}
    /** STUDY - addPhoto(): Read parameters, authorization predicates and return value; consider failures and side effects. */
    public function addPhoto(int $id,string $path,string $caption): void {$this->db->run('INSERT INTO room_photos(room_id,file_path,caption) VALUES (?,?,?)',[$id,$path,$caption]);}
    /** STUDY - photo(): Read parameters, authorization predicates and return value; consider failures and side effects. */
    public function photo(int $id,int $roomId): ?array {return $this->db->one('SELECT * FROM room_photos WHERE photo_id=? AND room_id=?',[$id,$roomId]);}
    /** STUDY - deletePhoto(): Read parameters, authorization predicates and return value; consider failures and side effects. */
    public function deletePhoto(int $id,int $roomId): void {$this->db->run('DELETE FROM room_photos WHERE photo_id=? AND room_id=?',[$id,$roomId]);}
}
