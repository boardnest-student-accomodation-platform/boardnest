<?php

function studentDashboardTableExists(PDO $pdo, string $table): bool
{
    $statement = $pdo->prepare('SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = ?');
    $statement->execute([$table]);
    return (bool) $statement->fetchColumn();
}

function studentDashboardColumnExists(PDO $pdo, string $table, string $column): bool
{
    $statement = $pdo->prepare('SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = ? AND column_name = ?');
    $statement->execute([$table, $column]);
    return (bool) $statement->fetchColumn();
}

function getStudentDashboardData(PDO $pdo, int $userId): ?array
{
    $profileStatement = $pdo->prepare('
        SELECT u.user_id, u.full_name, u.email, u.status AS account_status,
               s.student_id, s.nic_number, s.mobile, s.university,
               s.academic_year, s.verf_tier, s.verf_deadline
        FROM users u
        INNER JOIN students s ON s.user_id = u.user_id
        WHERE u.user_id = ? AND u.role = \'student\'
        LIMIT 1
    ');
    $profileStatement->execute([$userId]);
    $profile = $profileStatement->fetch();
    if (!$profile) {
        return null;
    }

    $studentId = (int) $profile['student_id'];
    $savedStatement = $pdo->prepare('
        SELECT l.listing_id, l.title, l.city, l.address, l.monthly_rent, sl.saved_at,
               COALESCE(MAX(CASE WHEN lp.is_primary = 1 THEN lp.photo_url END), MAX(lp.photo_url), \'/boardnest/public/assets/uploads/homepageimage.png\') AS image_url
        FROM saved_listings sl
        INNER JOIN listings l ON l.listing_id = sl.listing_id
        LEFT JOIN listing_photos lp ON lp.listing_id = l.listing_id
        WHERE sl.student_id = ?
        GROUP BY sl.saved_id, l.listing_id
        ORDER BY sl.saved_at DESC
        LIMIT 3
    ');
    $savedStatement->execute([$studentId]);

    $savedCountStatement = $pdo->prepare('SELECT COUNT(*) FROM saved_listings WHERE student_id = ?');
    $savedCountStatement->execute([$studentId]);
    $reviewCountStatement = $pdo->prepare('SELECT COUNT(*) FROM reviews WHERE student_id = ?');
    $reviewCountStatement->execute([$studentId]);

    $bookings = [];
    if (studentDashboardTableExists($pdo, 'room_slots')) {
        $bookingStatement = $pdo->prepare('
            SELECT rs.slot_id, rs.status, rs.move_in, l.listing_id, l.title, l.city
            FROM room_slots rs
            INNER JOIN rooms r ON r.room_id = rs.room_id
            INNER JOIN listings l ON l.listing_id = r.listing_id
            WHERE rs.student_id = ?
            ORDER BY CASE rs.status WHEN \'pending\' THEN 0 WHEN \'partially_occupied\' THEN 1 ELSE 2 END, rs.slot_id DESC
            LIMIT 5
        ');
        $bookingStatement->execute([$studentId]);
        $bookings = $bookingStatement->fetchAll();
    }

    $complaintCount = 0;
    if (studentDashboardTableExists($pdo, 'complaints')) {
        if (studentDashboardColumnExists($pdo, 'complaints', 'complainant_user_id')) {
            $statement = $pdo->prepare('SELECT COUNT(*) FROM complaints WHERE complainant_user_id = ?');
            $statement->execute([$userId]);
            $complaintCount = (int) $statement->fetchColumn();
        } elseif (studentDashboardColumnExists($pdo, 'complaints', 'student_id')) {
            $statement = $pdo->prepare('SELECT COUNT(*) FROM complaints WHERE student_id = ?');
            $statement->execute([$studentId]);
            $complaintCount = (int) $statement->fetchColumn();
        }
    }

    $notificationCount = 0;
    if (studentDashboardTableExists($pdo, 'notifications')) {
        $notificationStatement = $pdo->prepare('SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0');
        $notificationStatement->execute([$userId]);
        $notificationCount = (int) $notificationStatement->fetchColumn();
    }

    return [
        'profile' => $profile,
        'bookings' => $bookings,
        'saved_listings' => $savedStatement->fetchAll(),
        'counts' => [
            'bookings' => count($bookings),
            'saved' => (int) $savedCountStatement->fetchColumn(),
            'complaints' => $complaintCount,
            'reviews' => (int) $reviewCountStatement->fetchColumn(),
            'notifications' => $notificationCount,
        ],
    ];
}

function studentDashboardInitials(string $name): string
{
    $parts = preg_split('/\s+/', trim($name)) ?: [];
    $initials = '';
    foreach (array_slice($parts, 0, 2) as $part) {
        $initials .= strtoupper(substr($part, 0, 1));
    }
    return $initials ?: 'ST';
}

function studentDashboardBookingState(string $status): array
{
    if ($status === 'pending') {
        return ['Pending', 'pending'];
    }
    if ($status === 'occupied' || $status === 'partially_occupied') {
        return ['Confirmed', 'confirmed'];
    }
    return [ucwords(str_replace('_', ' ', $status)), 'neutral'];
}
