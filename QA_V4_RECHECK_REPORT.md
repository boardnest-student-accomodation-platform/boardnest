# BoardNest V4 — QA and Integration Review

**Build:** 2026-10-18 · **Target:** XAMPP / PHP 8.2+ / MariaDB 10.4+ · **Edition:** Unified Schema with PHP validation (zero triggers).

## What was checked in this build

| Check | Result | Meaning |
|---|---|---|
| Python static recheck | **43/43 PASS** | Script checks schema, mock relationships, PHP synchronization presence and file integrity |
| PHP lint | **157/157 PASS** | Syntax parsed by PHP 8.4.24 CLI; one of these is the read-only QA script |
| Landlord JS syntax | **2/2 PASS** | Node.js 22 `--check` |
| Database design | **47 tables, 78 references, 0 triggers, 0 views** | Source-level SQL analysis, not server execution |
| Group schema preservation | **PASS** | Original `newschema.sql` byte-for-byte at start of clean schema |
| Original project file preservation | **PASS for unrelated roles** | V3 code compared: selected Landlord services, 2 Student booking actions and shared security login changed |
| Demo booking model consistency | **PASS (static)** | Booking statuses, landlord decisions, open reservations, student/slot uniqueness and photo paths |
| Live SQL import on MariaDB | **NOT RUN** | MariaDB server unavailable in packaging environment |
| Live MySQL/PDO queries | **NOT RUN** | Packaging PHP has no `pdo_mysql` extension |
| Live HTTP requests / true concurrency | **NOT RUN** | Requires installed XAMPP stack and configured database |
| Cross-module Admin/Field Agent approval | **NOT CERTIFIED** | Other group modules have their own integration dependencies |

**Important:** A PASS on lint/static tests does **not** prove the application is fully working against a real MariaDB server. Test it locally before submission or deployment.

## High-value regression test matrix — run on XAMPP

1. **Import & login**: Fresh SQL, city seed, mock seed; run `php scripts/check_unified_db.php --demo`. Confirm expected 47 tables and zero triggers.
2. **Password**: Log in as approved landlord; enter incorrect current password (reject), valid old password and 12+ byte new password (success); log out; old password should no longer work.
3. **Profile image**: Upload allowed JPEG, replace/remove photo, check avatar in profile/sidebar/header; reject invalid MIME and oversized files.
4. **Registration**: Rejected/not-submitted landlord cannot manage property; Admin approval is required.
5. **Standard/Pro**: Standard landlord max one active property / four rooms; Pro subscribed landlord can add according to enabled package policy.
6. **Room creation**: Create a new room; assert `rooms`, `room_landlord_details` and exactly `slot_capacity` `room_slots` are created in the same transaction.
7. **Room edits**: In first 15 minutes update details; after 15 min detail changes fail; rent edit after initial window locked for a year; rent change after expiry records history.
8. **Capacity**: Increase capacity creates slots; deleting slots associated with historical/current bookings or maintenance block is rejected.
9. **Booking**: Tier-1 cannot book; unverified house/room cannot book; a verified active Tier-2 user can book one free slot; same student cannot hold second current slot; after fully booked status changes on student UI.
10. **Concurrent requests**: Two students race for one free slot; exactly one must succeed; verify one current row in `slot_reservations`.
11. **Landlord decisions**: Pending → accepted → occupied → completed; and pending → rejected. Verify `bookings`, `booking_landlord_actions`, `room_slots`, `slot_reservations` together after each step.
12. **Cancellation**: Student cancels their own pending request; slot becomes available; attempts to cancel other users' or already accepted requests fail.
13. **Messaging, complaints, announcements, image uploads**: Check authorization, ownership, CSRF, permissions and group-module screens.
14. **Security review**: Password hashes only, uploaded-file checks, private NIC documents, CSRF on POST actions and session regeneration.

## Known integration limits

- The 47-table design is deliberately **triggerless**. Domain constraints beyond unique/FK/CHECK keys only apply through the designated PHP controllers/services. Direct SQL writes or older code paths may violate invariants.
- A currently populated V2/V3 database **cannot be upgraded** by importing this clean installer or demo seed. Create reviewed backfill and migration steps first.
- The package adapts the **Landlord module** and necessary Student booking actions, not every old query in the independently maintained Admin/Field Agent/Student modules.
- Mock addresses/users/photos are fictional or reused developer test assets, not real landlord listings.
- PHP 8.2+ with `mbstring`, `pdo_mysql`, `fileinfo`, etc. is required in your XAMPP installation.

## Exact local commands

From `C:\xampp\htdocs\boardnest-merged` in a terminal where PHP is on PATH:

```bash
php scripts/check_unified_db.php --demo
```

`check_unified_db.php` is read-only, prints PASS/FAIL and returns a nonzero exit code on failure. Run `python scripts/static_recheck.py` for source-only checks (requires Python 3 and the `node` executable for JS checks).

**Safety warning:** Do not use the demo's shared password or demo users on a public server. Avoid importing the mock seed into a database containing real users.
