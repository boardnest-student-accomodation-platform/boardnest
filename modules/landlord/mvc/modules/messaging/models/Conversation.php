<?php
declare(strict_types=1);
/**
 * LEARNING EDITION - READ THIS FIRST
 * File: modules/messaging/models/Conversation.php
 * Role: Landlord MVC component; read the method notes and its calling controller to understand its responsibilities.
 * PHP comments below do not execute and do not change business logic.
 * Flow: HTTP request -> controller -> service -> model -> PDO/database.
 * Security: request values must be validated and scoped to the logged-in user.
 */

/**
 * VIVA GUIDE - FILE PURPOSE
 * Database access for chat threads/messages; ownership checks ensure landlords only access conversations linked to their bookings.
 */
namespace BoardNest\Modules\Messaging\Models;
use BoardNest\Core\Database;
final class Conversation {
    /** STUDY - __construct(): Initialization: receive services or dependencies; avoid database writes here. */
    public function __construct(private Database $db){}
    /** VIVA: List chat threads for this landlord. */
    public function all(int $landlordId): array {
        return $this->db->all('SELECT c.*,u.full_name AS student_name,(SELECT COUNT(*) FROM messages m WHERE m.conversation_id=c.conversation_id AND m.read_at IS NULL AND m.sender_user_id<>?) AS unread FROM conversations c JOIN students s ON s.student_id=c.student_id JOIN users u ON u.user_id=s.user_id WHERE c.landlord_id=? ORDER BY COALESCE(c.last_message_at,c.created_at) DESC',[(int)$this->db->value('SELECT user_id FROM landlords WHERE landlord_id=?',[$landlordId]),$landlordId]);
    }
    /** VIVA: Check conversation ownership before exposing any messages. */
    public function owned(int $conversationId,int $landlordId): array {
        $row=$this->db->one('SELECT c.*,u.full_name AS student_name FROM conversations c JOIN students s ON s.student_id=c.student_id JOIN users u ON u.user_id=s.user_id WHERE c.conversation_id=? AND c.landlord_id=?',[$conversationId,$landlordId]);
        if(!$row)throw new \DomainException('Conversation not found.');return $row;
    }
    /** VIVA: Return a bounded batch of recent messages in chronological order. */
    public function messages(int $conversationId): array {
        return $this->db->all('SELECT * FROM (SELECT m.message_id,m.message_text,m.sender_user_id,m.sent_at,u.full_name AS sender_name FROM messages m JOIN users u ON u.user_id=m.sender_user_id WHERE m.conversation_id=? AND m.is_deleted=0 ORDER BY m.message_id DESC LIMIT 300) recent ORDER BY message_id ASC',[$conversationId]);
    }
    /** VIVA: Mark only messages sent by the other participant as read. */
    public function markRead(int $conversationId,int $self): void {
        $this->db->run('UPDATE messages SET read_at=NOW() WHERE conversation_id=? AND sender_user_id<>? AND read_at IS NULL',[$conversationId,$self]);
    }
    /** VIVA: Save a message and update the thread last-message timestamp. */
    public function send(int $conversationId,int $senderId,string $message): void {
        $this->db->run('INSERT INTO messages(conversation_id,sender_user_id,message_text) VALUES (?,?,?)',[$conversationId,$senderId,$message]);
        $this->db->run('UPDATE conversations SET last_message_at=NOW() WHERE conversation_id=?',[$conversationId]);
    }
    /** VIVA: Lock and verify the booking, then reuse/create a thread tied to that booking. */
    public function start(int $bookingId,int $landlordId): int {
        $pdo=$this->db->pdo();$pdo->beginTransaction();
        try{
            $b=$this->db->one('SELECT b.student_id,s.room_id FROM bookings b JOIN room_slots s ON s.slot_id=b.slot_id JOIN rooms r ON r.room_id=s.room_id JOIN properties p ON p.property_id=r.property_id WHERE b.booking_id=? AND p.landlord_id=? FOR UPDATE',[$bookingId,$landlordId]);
            if(!$b)throw new \DomainException('Booking does not belong to you.');
            $row=$this->db->one('SELECT conversation_id FROM conversations WHERE booking_id=? AND landlord_id=? ORDER BY conversation_id LIMIT 1',[$bookingId,$landlordId]);
            if($row){$pdo->commit();return (int)$row['conversation_id'];}
            $this->db->run('INSERT INTO conversations(student_id,landlord_id,room_id,booking_id) VALUES (?,?,?,?)',[(int)$b['student_id'],$landlordId,(int)$b['room_id'],$bookingId]);
            $id=(int)$pdo->lastInsertId();$pdo->commit();return $id;
        }catch(\Throwable $e){$pdo->rollBack();throw $e;}
    }
}
