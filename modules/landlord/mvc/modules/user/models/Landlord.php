<?php
declare(strict_types=1);
/**
 * LEARNING EDITION - READ THIS FIRST
 * File: modules/user/models/Landlord.php
 * Role: PDO persistence for landlord profile and registration details.
 * PHP comments below do not execute and do not change business logic.
 * Flow: HTTP request -> controller -> service -> model -> PDO/database.
 * Security: request values must be validated and scoped to the logged-in user.
 */

/** V3 unified schema: base group tables remain unchanged; extra identity metadata is stored separately. */
namespace BoardNest\Modules\User\Models;
use BoardNest\Core\Database;
final class Landlord {
    /** STUDY - __construct(): Initialization: receive services or dependencies; avoid database writes here. */
    public function __construct(private Database $db) {}
    /** STUDY - account(): Read parameters, authorization predicates and return value; consider failures and side effects. */
    public function account(string $email): ?array {
        return $this->db->one('SELECT user_id,role,status,password_hash FROM users WHERE email=? LIMIT 1',[$email]);
    }
    /** A single transaction creates the user, landlord row and independent registration details. */
    public function create(string $name,string $email,string $phone,string $hash): int {
        $pdo=$this->db->pdo();$pdo->beginTransaction();
        try {
            $this->db->run("INSERT INTO users(full_name,email,password_hash,role,status) VALUES (?,?,?,'landlord','pending')",[$name,$email,$hash]);
            $id=(int)$pdo->lastInsertId();
            $this->db->run('INSERT INTO landlords(user_id,mobile,consent_agreed) VALUES (?,?,1)',[$id,$phone]);
            $lid=(int)$pdo->lastInsertId();
            $this->db->run("INSERT INTO landlord_registration_details(landlord_id,registration_stage,registration_deadline) VALUES (?,'identity',DATE_ADD(NOW(),INTERVAL 30 DAY))",[$lid]);
            $pdo->commit();return $id;
        } catch(\Throwable $e){$pdo->rollBack();throw $e;}
    }
    /** Never update shared users.status: only an Admin may activate the account. */
    public function identity(int $landlordId,string $nic,string $phone,string $document): void {
        $pdo=$this->db->pdo();$pdo->beginTransaction();
        try {
            $this->db->run('UPDATE landlords SET nic_number=?,mobile=? WHERE landlord_id=?',[$nic,$phone,$landlordId]);
            $this->db->run("INSERT INTO landlord_registration_details(landlord_id,identity_status,registration_stage,nic_document_path) VALUES (?,'submitted','submitted',?) ON DUPLICATE KEY UPDATE identity_status='submitted',registration_stage='submitted',nic_document_path=VALUES(nic_document_path),rejection_reason=NULL",[$landlordId,$document]);
            $pdo->commit();
        } catch(\Throwable $e){$pdo->rollBack();throw $e;}
    }
    /** Shared full name belongs to users; mobile/address belong to landlords. */
    public function updateProfile(int $id,string $name,string $phone,string $address): void {
        $pdo=$this->db->pdo();$pdo->beginTransaction();
        try {
            $this->db->run("UPDATE users SET full_name=? WHERE user_id=? AND role='landlord'",[$name,$id]);
            $this->db->run('UPDATE landlords SET mobile=?,address=? WHERE user_id=?',[$phone,$address,$id]);
            $pdo->commit();
        }catch(\Throwable $e){$pdo->rollBack();throw $e;}
    }
    /** Password is always hashed and only changes after checking the current secret. */
    public function changePassword(int $id,string $current,string $replacement): void {
        $pdo=$this->db->pdo();$pdo->beginTransaction();
        try {
            $row=$this->db->one("SELECT password_hash FROM users WHERE user_id=? AND role='landlord' FOR UPDATE",[$id]);
            if(!$row || !password_verify($current,(string)$row['password_hash']))throw new \DomainException('Current password is incorrect.');
            if(password_verify($replacement,(string)$row['password_hash']))throw new \DomainException('New password must differ from the current password.');
            $this->db->run("UPDATE users SET password_hash=? WHERE user_id=? AND role='landlord'",[password_hash($replacement,PASSWORD_DEFAULT),$id]);
            $pdo->commit();
        }catch(\Throwable $e){if($pdo->inTransaction())$pdo->rollBack();throw $e;}
    }
    /** STUDY - saveProfilePhoto(): Read parameters, authorization predicates and return value; consider failures and side effects. */
    public function saveProfilePhoto(int $landlordId,string $path): ?string {
        $prior=$this->db->one('SELECT file_path FROM landlord_profile_photos WHERE landlord_id=?',[$landlordId]);
        $this->db->run('INSERT INTO landlord_profile_photos(landlord_id,file_path) VALUES (?,?) ON DUPLICATE KEY UPDATE file_path=VALUES(file_path),updated_at=CURRENT_TIMESTAMP',[$landlordId,$path]);
        return $prior?(string)$prior['file_path']:null;
    }
    /** STUDY - removeProfilePhoto(): Read parameters, authorization predicates and return value; consider failures and side effects. */
    public function removeProfilePhoto(int $landlordId): ?string {
        $prior=$this->db->one('SELECT file_path FROM landlord_profile_photos WHERE landlord_id=?',[$landlordId]);
        $this->db->run('DELETE FROM landlord_profile_photos WHERE landlord_id=?',[$landlordId]);
        return $prior?(string)$prior['file_path']:null;
    }
    /** STUDY - profilePayments(): Read parameters, authorization predicates and return value; consider failures and side effects. */
    public function profilePayments(int $landlordId): array {
        return $this->db->all('SELECT * FROM subscription_payments WHERE landlord_id=? ORDER BY created_at DESC LIMIT 50',[$landlordId]);
    }
}
