<?php
declare(strict_types=1);
/**
 * BoardNest trigger-free V4 read-only MariaDB integration audit.
 * CLI: php scripts/check_unified_db.php [--demo]
 * Never changes production or demo data.
 */
if (PHP_SAPI !== 'cli') { http_response_code(403); exit('CLI only.'); }
require dirname(__DIR__) . '/config/db.php';
if (!isset($pdo) || !$pdo instanceof PDO) { fwrite(STDERR, "Database unavailable\n"); exit(2); }
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
$demo = in_array('--demo', $argv, true);
$fail = 0;
function scalar(PDO $db, string $sql): int { return (int)$db->query($sql)->fetchColumn(); }
function check(string $name, bool $passes, string $detail = ''): void {
    global $fail;
    printf("[%s] %-52s %s\n", $passes?'PASS':'FAIL', $name, $detail);
    if (!$passes) $fail++;
}
try {
    $database = (string)$pdo->query('SELECT DATABASE()')->fetchColumn();
    check('Connected to boardnest (expected DB)', $database === 'boardnest', $database);
    $tables = scalar($pdo,"SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_TYPE='BASE TABLE'");
    check('Unified schema: exactly 47 tables', $tables === 47, "found {$tables}");
    $triggers = scalar($pdo,'SELECT COUNT(*) FROM information_schema.TRIGGERS WHERE TRIGGER_SCHEMA=DATABASE()');
    check('Trigger-free schema', $triggers === 0, "found {$triggers}");
    $views = scalar($pdo,"SELECT COUNT(*) FROM information_schema.VIEWS WHERE TABLE_SCHEMA=DATABASE()");
    check('No SQL views', $views === 0, "found {$views}");
    foreach (['users','cities','universities','landlords','students','properties','rooms','room_slots',
              'bookings','booking_landlord_actions','slot_reservations','landlord_profile_photos',
              'landlord_property_archives','messages','subscription_payments'] as $table) {
        check("Table exists: {$table}", scalar($pdo,"SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=".$pdo->quote($table))===1);
    }
    $invariants = [
        'No reserved slot duplicates' => 'SELECT COUNT(*) FROM (SELECT slot_id FROM slot_reservations GROUP BY slot_id HAVING COUNT(*)>1) t',
        'No student with two current reservations' => 'SELECT COUNT(*) FROM (SELECT student_id FROM slot_reservations GROUP BY student_id HAVING COUNT(*)>1) t',
        'Slot count matches room capacity' => 'SELECT COUNT(*) FROM rooms r LEFT JOIN (SELECT room_id,COUNT(*) AS slots FROM room_slots GROUP BY room_id) x ON x.room_id=r.room_id WHERE r.slot_capacity<>COALESCE(x.slots,0)',
        'Every live slot has a current reservation' => "SELECT COUNT(*) FROM room_slots s LEFT JOIN slot_reservations sr ON sr.slot_id=s.slot_id WHERE s.status IN ('pending','occupied') AND sr.slot_id IS NULL",
        'Reservation and physical slot states match' => "SELECT COUNT(*) FROM slot_reservations sr JOIN room_slots s ON s.slot_id=sr.slot_id WHERE NOT ((sr.reservation_status='occupied' AND s.status='occupied') OR (sr.reservation_status='reserved' AND s.status='pending'))",
        'Active booking has reservation' => "SELECT COUNT(*) FROM bookings b LEFT JOIN slot_reservations sr ON sr.booking_id=b.booking_id WHERE b.bk_status IN ('pending','confirmed') AND sr.booking_id IS NULL",
        'Closed booking has no active reservation' => "SELECT COUNT(*) FROM bookings b JOIN slot_reservations sr ON sr.booking_id=b.booking_id WHERE b.bk_status IN ('cancelled','completed')",
        'Each current reservation points at matching booking' => 'SELECT COUNT(*) FROM slot_reservations sr JOIN bookings b ON b.booking_id=sr.booking_id WHERE sr.student_id<>b.student_id OR sr.slot_id<>b.slot_id',
        'Every booking has landlord action metadata' => 'SELECT COUNT(*) FROM bookings b LEFT JOIN booking_landlord_actions a ON a.booking_id=b.booking_id WHERE a.booking_id IS NULL',
        'Every booking action belongs to slot landlord' => 'SELECT COUNT(*) FROM booking_landlord_actions a JOIN bookings b ON b.booking_id=a.booking_id JOIN room_slots s ON s.slot_id=b.slot_id JOIN rooms r ON r.room_id=s.room_id JOIN properties p ON p.property_id=r.property_id WHERE a.landlord_id<>p.landlord_id',
        'No active reservation on maintenance blocked slot' => 'SELECT COUNT(*) FROM room_slot_blocks x JOIN slot_reservations sr ON sr.slot_id=x.slot_id',
        'All current slot student IDs agree with reservation' => 'SELECT COUNT(*) FROM slot_reservations sr JOIN room_slots s ON s.slot_id=sr.slot_id WHERE s.student_id<>sr.student_id OR s.student_id IS NULL',
        'No orphaned room slots' => 'SELECT COUNT(*) FROM room_slots s LEFT JOIN rooms r ON r.room_id=s.room_id WHERE r.room_id IS NULL',
        'No invalid locked rent date' => 'SELECT COUNT(*) FROM room_landlord_details WHERE rent_last_changed_at IS NOT NULL AND rent_locked_until<rent_last_changed_at',
    ];
    foreach ($invariants as $name=>$sql) {
        $problems = scalar($pdo,$sql);
        check($name,$problems===0,"issues {$problems}");
    }
    if ($demo) {
        foreach (['landlords'=>12,'students'=>36,'field_agents'=>4,'properties'=>18,'rooms'=>36,
                  'room_slots'=>63,'bookings'=>32,'slot_reservations'=>20,'conversations'=>14,'messages'=>42] as $table=>$expected) {
            $count=scalar($pdo,"SELECT COUNT(*) FROM `{$table}`");
            check("Demo count {$table}",$count===$expected,"{$count} / {$expected}");
        }
        $st=$pdo->prepare("SELECT password_hash FROM users WHERE email='demo.landlord01@example.test' AND role='landlord'");
        $st->execute();$hash=$st->fetchColumn();
        check('Demo hashed landlord password',is_string($hash)&&password_verify('BoardNestDemo#2026',$hash));
        check('Fully booked published room exists',scalar($pdo,"SELECT COUNT(*) FROM rooms r JOIN properties p ON p.property_id=r.property_id JOIN room_slots s ON s.room_id=r.room_id WHERE p.status='verified' AND r.status='verified' GROUP BY r.room_id HAVING SUM(s.status='available')=0 LIMIT 1")>=1);
        check('Maintenance-blocked slot exists',scalar($pdo,'SELECT COUNT(*) FROM room_slot_blocks')>=1);
    }
} catch (Throwable $e) {
    fwrite(STDERR,"[ERROR] Audit halted: {$e->getMessage()}\n");
    exit(2);
}
printf("\nRESULT: %s (%d failed)\n", $fail===0?'PASS':'FAIL',$fail);
exit($fail===0?0:1);
