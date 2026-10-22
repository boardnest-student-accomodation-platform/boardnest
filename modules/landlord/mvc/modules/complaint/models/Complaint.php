<?php
declare(strict_types=1);
/**
 * LEARNING EDITION - READ THIS FIRST
 * File: modules/complaint/models/Complaint.php
 * Role: Landlord MVC component; read the method notes and its calling controller to understand its responsibilities.
 * PHP comments below do not execute and do not change business logic.
 * Flow: HTTP request -> controller -> service -> model -> PDO/database.
 * Security: request values must be validated and scoped to the logged-in user.
 */

/** Landlord-specific complaints: never repurpose the group's listing complaints. */
namespace BoardNest\Modules\Complaint\Models;
use BoardNest\Core\Database;
final class Complaint {
    /** STUDY - __construct(): Initialization: receive services or dependencies; avoid database writes here. */
    public function __construct(private Database $db){}
    private const FIELDS = 'SELECT c.landlord_complaint_id AS complaint_id,c.*,u.full_name AS target_name FROM landlord_complaints c JOIN students s ON s.student_id=c.target_student_id JOIN users u ON u.user_id=s.user_id';
    /** STUDY - all(): Read parameters, authorization predicates and return value; consider failures and side effects. */
    public function all(int $userId): array {
        return $this->db->all(self::FIELDS.' JOIN landlords l ON l.landlord_id=c.landlord_id WHERE l.user_id=? ORDER BY c.created_at DESC',[$userId]);
    }
    /** STUDY - owned(): Ownership guard: query includes the current landlord ID to block ID guessing. */
    public function owned(int $id,int $userId): array {
        $row=$this->db->one(self::FIELDS.' JOIN landlords l ON l.landlord_id=c.landlord_id WHERE c.landlord_complaint_id=? AND l.user_id=?',[$id,$userId]);
        if(!$row)throw new \DomainException('Complaint not found.');return $row;
    }
    /** STUDY - create(): Create path: validate inputs, enforce access limits, and roll back all writes on failure. */
    public function create(int $userId,int $studentUserId,int $bookingId,string $category,string $description,?string $evidence): void {
        $row=$this->db->one('SELECT l.landlord_id,s.student_id FROM landlords l JOIN bookings b ON b.booking_id=? JOIN students s ON s.student_id=b.student_id WHERE l.user_id=? AND s.user_id=?',[$bookingId,$userId,$studentUserId]);
        if(!$row)throw new \DomainException('Invalid complaint participants.');
        $this->db->run('INSERT INTO landlord_complaints(landlord_id,target_student_id,booking_id,category,description,evidence_path) VALUES (?,?,?,?,?,?)',[(int)$row['landlord_id'],(int)$row['student_id'],$bookingId,$category,$description,$evidence]);
    }
}
