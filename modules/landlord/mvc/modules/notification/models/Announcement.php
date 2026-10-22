<?php
declare(strict_types=1);
/**
 * LEARNING EDITION - READ THIS FIRST
 * File: modules/notification/models/Announcement.php
 * Role: Landlord MVC component; read the method notes and its calling controller to understand its responsibilities.
 * PHP comments below do not execute and do not change business logic.
 * Flow: HTTP request -> controller -> service -> model -> PDO/database.
 * Security: request values must be validated and scoped to the logged-in user.
 */

/** Group's announcements use announcements_id/audience/subject/sent_at. */
namespace BoardNest\Modules\Notification\Models;
use BoardNest\Core\Database;
final class Announcement {
    /** STUDY - __construct(): Initialization: receive services or dependencies; avoid database writes here. */
    public function __construct(private Database $db){}
    /** STUDY - all(): Read parameters, authorization predicates and return value; consider failures and side effects. */
    public function all(int $userId,int $limit=100): array {
        $limit=max(1,min($limit,100));
        return $this->db->all("SELECT a.*,a.announcements_id AS announcement_id,a.subject AS title,a.sent_at AS published_at,ar.read_at FROM announcements a LEFT JOIN announcement_reads ar ON ar.announcements_id=a.announcements_id AND ar.user_id=? WHERE a.audience IN ('all','landlord') ORDER BY a.sent_at DESC LIMIT $limit",[$userId]);
    }
    /** STUDY - markRead(): Read parameters, authorization predicates and return value; consider failures and side effects. */
    public function markRead(int $id,int $userId): void {
        $this->db->run("INSERT IGNORE INTO announcement_reads(announcements_id,user_id) SELECT announcements_id,? FROM announcements WHERE announcements_id=? AND audience IN ('all','landlord')",[$userId,$id]);
    }
}
