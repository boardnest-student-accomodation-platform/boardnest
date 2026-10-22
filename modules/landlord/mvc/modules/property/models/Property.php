<?php
declare(strict_types=1);
/**
 * LEARNING EDITION - READ THIS FIRST
 * File: modules/property/models/Property.php
 * Role: Landlord MVC component; read the method notes and its calling controller to understand its responsibilities.
 * PHP comments below do not execute and do not change business logic.
 * Flow: HTTP request -> controller -> service -> model -> PDO/database.
 * Security: request values must be validated and scoped to the logged-in user.
 */

/** V3 property queries preserve the group's status ENUM and use the extension for verification metadata. */
namespace BoardNest\Modules\Property\Models;
use BoardNest\Core\Database;
final class Property {
    /** STUDY - __construct(): Initialization: receive services or dependencies; avoid database writes here. */
    public function __construct(private Database $db) {}
    private const BASE = "SELECT p.*,p.status AS verification_status,c.name AS city_name, c.state AS city_district,
                 d.verification_submitted_at,(SELECT COUNT(*) FROM rooms r WHERE r.property_id=p.property_id) AS room_count
                 FROM properties p LEFT JOIN cities c ON c.city_id=p.city_id
                 LEFT JOIN property_landlord_details d ON d.property_id=p.property_id LEFT JOIN landlord_property_archives la ON la.property_id=p.property_id";
    /** STUDY - all(): Read parameters, authorization predicates and return value; consider failures and side effects. */
    public function all(int $landlordId): array {
        return $this->db->all(self::BASE." WHERE p.landlord_id=? AND la.property_id IS NULL ORDER BY p.created_at DESC",[$landlordId]);
    }
    /** STUDY - owned(): Ownership guard: query includes the current landlord ID to block ID guessing. */
    public function owned(int $id,int $landlordId): array {
        $row=$this->db->one(self::BASE.' WHERE p.property_id=? AND p.landlord_id=? AND la.property_id IS NULL',[$id,$landlordId]);
        if(!$row)throw new \DomainException('Property not found or access denied.');return $row;
    }
    /** STUDY - cities(): Read parameters, authorization predicates and return value; consider failures and side effects. */
    public function cities(): array {
        return $this->db->all('SELECT city_id AS id,name,state AS district FROM cities ORDER BY name,state');
    }
    /** STUDY - cityExists(): Read parameters, authorization predicates and return value; consider failures and side effects. */
    public function cityExists(int $cityId): bool {
        return (bool)$this->db->value('SELECT 1 FROM cities WHERE city_id=?',[$cityId]);
    }
    /** Creation starts as pending; only Admin/Agent may verify it. */
    public function create(int $landlordId,array $v): int {
        $this->db->run("INSERT INTO properties(landlord_id,title,city_id,address,structural_type,maps_url,latitude,longitude,description,status) VALUES (?,?,?,?,'boarding_house',?,?,?,?,'pending')",[$landlordId,$v['title'],$v['city_id'],$v['address'],$v['maps_url'],$v['latitude'],$v['longitude'],$v['description']]);
        return (int)$this->db->pdo()->lastInsertId();
    }
    /** STUDY - update(): Update path: validate ownership and business locks before committing changes. */
    public function update(int $id,int $landlordId,array $v): void {
        // The original schema retains GPS columns for other roles. If the map
        // link changes, stale old coordinates must NOT be carried forward.
        // If the link is unchanged, preserve any existing GPS values when a
        // short URL supplies no pin. Long links with an explicit pin update
        // the coordinate pair automatically.
        $this->db->run(
            'UPDATE properties SET title=?,city_id=?,address=?,
             latitude=CASE WHEN maps_url <=> ? THEN COALESCE(?,latitude) ELSE ? END,
             longitude=CASE WHEN maps_url <=> ? THEN COALESCE(?,longitude) ELSE ? END,
             maps_url=?,description=? WHERE property_id=? AND landlord_id=?',
            [$v['title'],$v['city_id'],$v['address'],
             $v['maps_url'],$v['latitude'],$v['latitude'],
             $v['maps_url'],$v['longitude'],$v['longitude'],
             $v['maps_url'],$v['description'],$id,$landlordId]
        );
    }
    /** STUDY - isWindowOpen(): Read parameters, authorization predicates and return value; consider failures and side effects. */
    public function isWindowOpen(int $id,int $landlordId): bool {
        return (bool)$this->db->value('SELECT NOW()<=DATE_ADD(created_at,INTERVAL 15 MINUTE) FROM properties WHERE property_id=? AND landlord_id=?',[$id,$landlordId]);
    }
    /** Voluntary archive is recorded separately; the existing status uses suspended to hide the listing. */
    public function archive(int $id,int $landlordId): void {
        $pdo=$this->db->pdo();$pdo->beginTransaction();
        try {
            $row=$this->db->one('SELECT status FROM properties WHERE property_id=? AND landlord_id=? FOR UPDATE',[$id,$landlordId]);
            if(!$row || $row['status']==='suspended')throw new \DomainException('Property cannot be archived in its current state.');
            $current=(int)$this->db->value("SELECT COUNT(*) FROM bookings b JOIN room_slots s ON s.slot_id=b.slot_id JOIN rooms r ON r.room_id=s.room_id WHERE r.property_id=? AND b.bk_status IN ('pending','confirmed')",[$id]);
            if($current)throw new \DomainException('A property with active bookings cannot be archived.');
            $this->db->run('INSERT INTO landlord_property_archives(property_id,landlord_id) VALUES (?,?)',[$id,$landlordId]);
            $this->db->run("UPDATE properties SET status='suspended' WHERE property_id=? AND landlord_id=?",[$id,$landlordId]);
            $pdo->commit();
        }catch(\Throwable $e){if($pdo->inTransaction())$pdo->rollBack();throw $e;}
    }
    /** Submission records the request but never auto-approves the property. */
    public function requestVerification(int $id,int $lid): void {
        $pdo=$this->db->pdo();$pdo->beginTransaction();
        try {
            $row=$this->db->one('SELECT status FROM properties WHERE property_id=? AND landlord_id=? FOR UPDATE',[$id,$lid]);
            if(!$row||!in_array($row['status'],['pending','available'],true))throw new \DomainException('Verification is not available for this property status.');
            $this->db->run("UPDATE properties SET status='under_verification' WHERE property_id=? AND landlord_id=?",[$id,$lid]);
            $this->db->run('INSERT INTO property_landlord_details(property_id,verification_submitted_at) VALUES (?,NOW()) ON DUPLICATE KEY UPDATE verification_submitted_at=VALUES(verification_submitted_at)',[$id]);
            $pdo->commit();
        }catch(\Throwable $e){$pdo->rollBack();throw $e;}
    }
    /** STUDY - facilities(): Read parameters, authorization predicates and return value; consider failures and side effects. */
    public function facilities(int $id): array {return $this->db->all('SELECT * FROM property_facilities WHERE property_id=? ORDER BY created_at DESC',[$id]);}
    /** STUDY - addFacility(): Read parameters, authorization predicates and return value; consider failures and side effects. */
    public function addFacility(int $id,array $v): void {
        $this->db->run('INSERT INTO property_facilities(property_id,facility_type,facility_name,details,distance_meters) VALUES (?,?,?,?,?)',[$id,$v['type'],$v['name'],$v['details'],$v['distance']]);
    }
    /** STUDY - removeFacility(): Read parameters, authorization predicates and return value; consider failures and side effects. */
    public function removeFacility(int $id,int $fid): void {
        $this->db->run("DELETE FROM property_facilities WHERE property_id=? AND facility_id=? AND verification_status='pending'",[$id,$fid]);
    }
    /** STUDY - checklist(): Read parameters, authorization predicates and return value; consider failures and side effects. */
    public function checklist(int $id,int $lid): ?array {
        return $this->db->one('SELECT * FROM property_checklists WHERE property_id=? AND landlord_id=? ORDER BY created_at DESC LIMIT 1',[$id,$lid]);
    }
    /** STUDY - saveChecklist(): Read parameters, authorization predicates and return value; consider failures and side effects. */
    public function saveChecklist(int $id,int $lid,string $items,string $notes): void {
        $this->db->run('INSERT INTO property_checklists(property_id,landlord_id,items,notes) VALUES (?,?,?,?)',[$id,$lid,$items,$notes]);
    }
    /** STUDY - photos(): Read parameters, authorization predicates and return value; consider failures and side effects. */
    public function photos(int $id): array {return $this->db->all('SELECT * FROM property_photos WHERE property_id=? ORDER BY is_primary DESC,uploaded_at DESC',[$id]);}
    /** STUDY - addPhoto(): Read parameters, authorization predicates and return value; consider failures and side effects. */
    public function addPhoto(int $id,string $path,string $caption): void {$this->db->run('INSERT INTO property_photos(property_id,file_path,caption) VALUES (?,?,?)',[$id,$path,$caption]);}
    /** STUDY - photo(): Read parameters, authorization predicates and return value; consider failures and side effects. */
    public function photo(int $id,int $propertyId): ?array {return $this->db->one('SELECT * FROM property_photos WHERE photo_id=? AND property_id=?',[$id,$propertyId]);}
    /** STUDY - deletePhoto(): Read parameters, authorization predicates and return value; consider failures and side effects. */
    public function deletePhoto(int $id,int $pid): void {$this->db->run('DELETE FROM property_photos WHERE photo_id=? AND property_id=?',[$id,$pid]);}
}
