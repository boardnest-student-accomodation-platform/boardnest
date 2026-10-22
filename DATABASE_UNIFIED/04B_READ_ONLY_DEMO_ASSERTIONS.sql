-- BoardNest V4 read-only test queries. These do NOT modify any data.
-- Run after importing the clean V4 schema, locations and fictional demo.
USE boardnest;
SELECT 'tables' AS measure, COUNT(*) AS actual, 47 AS expected FROM information_schema.tables WHERE table_schema=DATABASE() AND table_type='BASE TABLE'
UNION ALL SELECT 'triggers',COUNT(*),0 FROM information_schema.triggers WHERE trigger_schema=DATABASE()
UNION ALL SELECT 'views',COUNT(*),0 FROM information_schema.views WHERE table_schema=DATABASE()
UNION ALL SELECT 'landlords',COUNT(*),12 FROM landlords
UNION ALL SELECT 'students',COUNT(*),36 FROM students
UNION ALL SELECT 'properties',COUNT(*),18 FROM properties
UNION ALL SELECT 'rooms',COUNT(*),36 FROM rooms
UNION ALL SELECT 'room_slots',COUNT(*),63 FROM room_slots
UNION ALL SELECT 'bookings',COUNT(*),32 FROM bookings
UNION ALL SELECT 'current_reservations',COUNT(*),20 FROM slot_reservations
UNION ALL SELECT 'conversations',COUNT(*),14 FROM conversations
UNION ALL SELECT 'messages',COUNT(*),42 FROM messages;

-- Should return ZERO on a consistent database:
SELECT COUNT(*) AS invalid_room_capacity
FROM rooms r LEFT JOIN (SELECT room_id, COUNT(*) n FROM room_slots GROUP BY room_id) x ON x.room_id=r.room_id
WHERE r.slot_capacity<>COALESCE(x.n,0);
SELECT COUNT(*) AS mismatched_slot_reservation_status
FROM slot_reservations sr JOIN room_slots s ON s.slot_id=sr.slot_id
WHERE NOT ((sr.reservation_status='occupied' AND s.status='occupied')
      OR (sr.reservation_status='reserved' AND s.status='pending'));
SELECT COUNT(*) AS current_bookings_without_reservation
FROM bookings b LEFT JOIN slot_reservations sr ON sr.booking_id=b.booking_id
WHERE b.bk_status IN ('pending','confirmed') AND sr.booking_id IS NULL;
SELECT COUNT(*) AS orphaned_booking_actions
FROM bookings b LEFT JOIN booking_landlord_actions a ON a.booking_id=b.booking_id
WHERE a.booking_id IS NULL;
SELECT COUNT(*) AS maintenance_blocks_with_reservations
FROM room_slot_blocks x JOIN slot_reservations sr ON sr.slot_id=x.slot_id;
SELECT COUNT(*) AS active_students_with_multiple_current_reservations FROM (
 SELECT student_id FROM slot_reservations GROUP BY student_id HAVING COUNT(*)>1
) x;

-- Explore live boarding status scenarios:
SELECT p.title AS property, rd.room_label,
 r.room_id, r.status AS room_verification,
 COUNT(s.slot_id) AS capacity,
 SUM(s.status='available' AND bl.slot_id IS NULL) AS available,
 SUM(s.status='pending') AS reserved,
 SUM(s.status='occupied') AS occupied,
 SUM(bl.slot_id IS NOT NULL) AS blocked
FROM rooms r JOIN properties p ON p.property_id=r.property_id
LEFT JOIN room_landlord_details rd ON rd.room_id=r.room_id
LEFT JOIN room_slots s ON s.room_id=r.room_id
LEFT JOIN room_slot_blocks bl ON bl.slot_id=s.slot_id
GROUP BY p.title,rd.room_label,r.room_id,r.status
ORDER BY r.room_id LIMIT 36;
