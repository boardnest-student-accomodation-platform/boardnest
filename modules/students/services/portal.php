<?php

function studentPortalProfile(PDO $pdo, int $userId): ?array
{
    $statement = $pdo->prepare('
        SELECT u.user_id, u.full_name, u.email, u.password_hash, u.status,
               s.student_id, s.nic_number, s.mobile, s.university,
               s.academic_year, s.verf_tier, s.verf_deadline
        FROM users u
        INNER JOIN students s ON s.user_id = u.user_id
        WHERE u.user_id = ? AND u.role = \'student\'
        LIMIT 1
    ');
    $statement->execute([$userId]);
    return $statement->fetch() ?: null;
}

function studentPortalUnreadCount(PDO $pdo, int $userId): int
{
    $statement = $pdo->prepare('SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0');
    $statement->execute([$userId]);
    return (int) $statement->fetchColumn();
}

function studentPortalEligibleComplaintListings(PDO $pdo, int $studentId): array
{
    $statement = $pdo->prepare('
        SELECT DISTINCT l.listing_id, l.title, l.city
        FROM room_slots rs
        INNER JOIN rooms r ON r.room_id = rs.room_id
        INNER JOIN listings l ON l.listing_id = r.listing_id
        WHERE rs.student_id = ? AND rs.status IN (\'occupied\', \'partially_occupied\')
        ORDER BY l.title
    ');
    $statement->execute([$studentId]);
    return $statement->fetchAll();
}

function studentPortalComplaints(PDO $pdo, int $userId): array
{
    $columns = $pdo->query('SHOW COLUMNS FROM complaints')->fetchAll(PDO::FETCH_COLUMN);
    $idColumn = in_array('complaint_id', $columns, true) ? 'complaint_id' : 'id';
    $statement = $pdo->prepare('
        SELECT c.*, c.' . $idColumn . ' AS id, l.title AS listing_title
        FROM complaints c
        INNER JOIN listings l ON l.listing_id = c.listing_id
        WHERE c.complainant_user_id = ?
        ORDER BY c.submitted_at DESC, c.' . $idColumn . ' DESC
    ');
    $statement->execute([$userId]);
    return $statement->fetchAll();
}

function studentPortalNotifications(PDO $pdo, int $userId): array
{
    $statement = $pdo->prepare('SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC, notification_id DESC');
    $statement->execute([$userId]);
    return $statement->fetchAll();
}

function studentPortalSavedListings(PDO $pdo, int $studentId): array
{
    $statement = $pdo->prepare('
        SELECT l.listing_id, l.title, l.description, l.address, l.city,
               l.monthly_rent, sl.saved_at,
               COALESCE(MAX(CASE WHEN lp.is_primary = 1 THEN lp.photo_url END), MAX(lp.photo_url), \'/boardnest/public/assets/uploads/homepageimage.png\') AS image_url
        FROM saved_listings sl
        INNER JOIN listings l ON l.listing_id = sl.listing_id
        LEFT JOIN listing_photos lp ON lp.listing_id = l.listing_id
        WHERE sl.student_id = ?
        GROUP BY sl.saved_id, l.listing_id
        ORDER BY sl.saved_at DESC
    ');
    $statement->execute([$studentId]);
    return $statement->fetchAll();
}

function studentPortalComplaintCategories(): array
{
    return [
        'safety' => 'Safety Violation',
        'false_advertising' => 'False Advertising',
        'fee_discrepancy' => 'Fee Discrepancy',
        'landlord_misconduct' => 'Landlord Misconduct',
        'other' => 'Other',
    ];
}

function studentPortalComplaintStatus(string $status): array
{
    $statuses = [
        'new' => ['New', 'neutral'],
        'under_moderation' => ['Under Moderation', 'yellow'],
        'assigned' => ['Assigned', 'blue'],
        'under_investigation' => ['Under Investigation', 'orange'],
        'resolved' => ['Resolved', 'green'],
        'upheld' => ['Resolved', 'green'],
        'dismissed' => ['Dismissed', 'red'],
        'escalated' => ['Under Moderation', 'yellow'],
    ];
    return $statuses[$status] ?? [ucwords(str_replace('_', ' ', $status)), 'neutral'];
}

function studentPortalNotificationMeta(string $type): array
{
    $types = [
        'booking_accepted' => ['Booking accepted', '&#10003;'],
        'booking_rejected' => ['Booking rejected', '&#10005;'],
        'complaint_update' => ['Complaint update', '!'],
        'announcement' => ['Announcement', '&#9873;'],
        'account_update' => ['Account update', '&#9679;'],
    ];
    return $types[$type] ?? ['Notification', '&#9679;'];
}

function studentPortalInitials(string $name): string
{
    $parts = preg_split('/\s+/', trim($name)) ?: [];
    $initials = '';
    foreach (array_slice($parts, 0, 2) as $part) {
        $initials .= strtoupper(substr($part, 0, 1));
    }
    return $initials ?: 'ST';
}

function studentPortalEnsureCsrf(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function studentPortalValidCsrf(string $token): bool
{
    return !empty($_SESSION['csrf_token']) && $token !== '' && hash_equals($_SESSION['csrf_token'], $token);
}
