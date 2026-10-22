<?php
/**
 * Student booking helpers (no framework). Only public/student/pages and actions use this.
 * All decisions are repeated on POST. UI-disabled buttons are never considered security.
 */
declare(strict_types=1);
require_once dirname(__DIR__, 3) . '/includes/session.php';
startSession();

function booking_db(): PDO {
    require dirname(__DIR__, 3) . '/config/db.php';
    if (!isset($pdo) || !$pdo instanceof PDO) {
        throw new RuntimeException('BoardNest database connection unavailable.');
    }
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    return $pdo;
}
function booking_student(PDO $pdo): array {
    if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'student') {
        // Login URL is project-root relative, regardless of calling student page depth.
        $script = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
        $project = preg_replace('~/public/student(?:/actions)?/[^/]+$~', '', $script);
        header('Location: ' . rtrim((string)$project, '/') . '/login.php', true, 303);
        exit;
    }
    $stmt = $pdo->prepare("SELECT s.student_id, s.verf_tier, u.full_name, u.status
                            FROM students s JOIN users u ON u.user_id = s.user_id
                            WHERE u.user_id = ? AND u.role = 'student' LIMIT 1");
    $stmt->execute([(int)$_SESSION['user_id']]);
    $student = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$student || $student['status'] !== 'active') {
        http_response_code(403);
        exit('Your student account is not active.');
    }
    return $student;
}
function booking_e(mixed $text): string {
    return htmlspecialchars((string)($text ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
function booking_csrf(): string {
    return (string)$_SESSION['csrf_token'];
}
function booking_check_csrf(): void {
    $token = $_POST['_csrf'] ?? '';
    if (!is_string($token) || !hash_equals(booking_csrf(), $token)) {
        http_response_code(403);
        exit('Invalid request token. Refresh the page and try again.');
    }
}
function booking_notice(string $message): void {
    $_SESSION['student_booking_notice'] = $message;
}
function booking_notice_take(): string {
    $message = (string)($_SESSION['student_booking_notice'] ?? '');
    unset($_SESSION['student_booking_notice']);
    return $message;
}
function booking_go(string $page): never {
    // Redirect only to fixed local pages; caller cannot supply an external URL.
    $allowed = ['search.php', 'bookings.php'];
    header('Location: ../' . (in_array($page, $allowed, true) ? $page : 'search.php'), true, 303);
    exit;
}
function booking_status_label(string $status): string {
    return ucwords(str_replace('_', ' ', $status));
}


/** Read-only room catalog query for the unified schema (zero SQL views). */
function booking_catalog_sql(): string {
    return "SELECT r.room_id,r.room_type,r.price,rd.room_label,p.title AS property_title,
      p.city_id,c.name AS city_name,c.state AS city_district,r.slot_capacity,
      COALESCE(a.available_slots,0) AS available_slots,
      COALESCE(a.reserved_slots,0) AS reserved_slots,
      COALESCE(a.occupied_slots,0) AS occupied_slots,
      COALESCE(a.blocked_slots,0) AS blocked_slots,
      (COALESCE(a.available_slots,0)>0) AS can_request,
      CASE WHEN COALESCE(a.total_slots,0)=0 THEN 'unavailable'
           WHEN a.available_slots=a.total_slots THEN 'available'
           WHEN a.available_slots>0 THEN 'partially_booked'
           WHEN a.reserved_slots=a.total_slots THEN 'fully_reserved'
           WHEN a.occupied_slots=a.total_slots THEN 'fully_occupied'
           WHEN a.reserved_slots+a.occupied_slots=a.total_slots THEN 'fully_booked'
           ELSE 'unavailable' END AS availability_status
    FROM rooms r JOIN properties p ON p.property_id=r.property_id
    JOIN cities c ON c.city_id=p.city_id
    JOIN landlords l ON l.landlord_id=p.landlord_id
    JOIN users u ON u.user_id=l.user_id
    JOIN landlord_registration_details lr ON lr.landlord_id=l.landlord_id
    LEFT JOIN room_landlord_details rd ON rd.room_id=r.room_id
    LEFT JOIN (
       SELECT s.room_id,COUNT(*) AS total_slots,
          SUM(s.status='available' AND bl.slot_id IS NULL) AS available_slots,
          SUM(s.status='pending') AS reserved_slots,
          SUM(s.status='occupied') AS occupied_slots,
          SUM(bl.slot_id IS NOT NULL) AS blocked_slots
       FROM room_slots s LEFT JOIN room_slot_blocks bl ON bl.slot_id=s.slot_id GROUP BY s.room_id
    ) a ON a.room_id=r.room_id
    WHERE p.status='verified' AND r.status='verified'
      AND u.status='active' AND lr.identity_status='approved'";
}
