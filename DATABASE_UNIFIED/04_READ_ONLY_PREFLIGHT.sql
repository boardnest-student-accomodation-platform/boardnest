-- Safe READ-ONLY queries. Run on a backed-up local development DB.
-- Run the FIRST block against the group's original newschema.sql database,
-- before installing the new extension tables and PHP validation.
USE `boardnest`;
SELECT DATABASE() AS db_name;
SELECT COUNT(*) AS base_table_count FROM information_schema.tables
 WHERE table_schema=DATABASE() AND table_type='BASE TABLE';
SELECT COUNT(*) AS existing_booking_count FROM bookings;
SELECT bk_status,COUNT(*) AS n FROM bookings GROUP BY bk_status;
SELECT COUNT(*) AS invalid_capacity_count FROM rooms
 WHERE slot_capacity<1 OR slot_capacity>127;
SELECT COUNT(*) AS duplicate_capacity_fields_count FROM rooms WHERE slot_cap<>slot_capacity;
SELECT COUNT(*) AS slot_number_out_of_range FROM room_slots WHERE slot_no<1 OR slot_no>127;
SELECT COUNT(*) AS duplicate_booking_slot_student_pairs FROM (
 SELECT student_id,slot_id,COUNT(*) c FROM bookings WHERE bk_status IN ('pending','confirmed')
 GROUP BY student_id,slot_id HAVING COUNT(*)>1
) x;
SELECT COUNT(*) AS slots_with_multiple_open_bookings FROM (
 SELECT slot_id,COUNT(*) c FROM bookings WHERE bk_status IN ('pending','confirmed')
 GROUP BY slot_id HAVING COUNT(*)>1
) x;
SELECT COUNT(*) AS students_with_multiple_open_bookings FROM (
 SELECT student_id,COUNT(*) c FROM bookings WHERE bk_status IN ('pending','confirmed')
 GROUP BY student_id HAVING COUNT(*)>1
) x;
-- Stop if any incompatible historic records exist, and plan deliberate backfill.
-- This script performs NO INSERT, UPDATE, ALTER or DELETE.
