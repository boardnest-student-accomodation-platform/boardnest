# BoardNest V4 — Unified Schema / PHP Validation / Fictional Demo

**Use this README for this edition.** Previous `README_UNIFIED_LANDLORD_V3.md` is historical and describes a different trigger-based edition.

## Purpose

- Preserve the exact original `newschema.sql` text and every original 27-table definition.
- Continue using the 47-table unified schema with all Landlord additions, profile photo and archiving support.
- **Zero triggers, zero views:** business decisions and slot synchronization are handled in PHP services/actions with database transactions; SQL unique keys, FKs and CHECK constraints remain.
- Retain password changes and landlord profile-photo upload/change/remove.
- Supply realistic **fictional** demo accounts, Sri Lankan cities/universities, houses, rooms, slots, bookings, accepted/rejected/cancelled/completed cases, chat, complaints, facilities, announcement and subscription records.

## Fresh demo install (recommended)

1. Back up existing databases and files. Start **Apache** and **MariaDB/MySQL** in XAMPP.
2. In phpMyAdmin, create a **new empty development environment**. This SQL uses the database name `boardnest`. If you already use a populated `boardnest` database, export and remove/rename it **only if you intend to replace it**; otherwise use an isolated XAMPP instance or adapt the DB name in both SQL and config before importing.
3. Import **only** `DATABASE_UNIFIED/01_FRESH_FULL_SCHEMA_PHP_VALIDATION.sql` (same as root `schema.sql`), not both copies.
4. Import `DATABASE_UNIFIED/06_OPTIONAL_LOCATION_MASTER_SEED.sql` (required by demo property/city references).
5. Import `DATABASE_UNIFIED/03_REALISTIC_FICTIONAL_MOCK_DATA.sql` **on that clean database, once only**.
6. Verify `config/db.php` uses the corresponding DB and credentials. Confirm PHP 8.2+, extensions `pdo_mysql`, `mbstring`, `fileinfo`, `openssl` and `gd` if your environment needs it.
7. From the project root run `php scripts/check_unified_db.php --demo`; it is **read-only**. Resolve any FAIL reports.
8. Open `http://localhost/boardnest-merged/login.php`, or adapt URL to the extracted folder name.

## Demo credentials (fictional; NEVER use in production)

| Role | Login | Password | Remarks |
|---|---|---|---|
| Admin | `admin@boardnest.lk` | `BoardNestDemo#2026` | Updated by demo seed only; existing admin from fresh schema |
| Landlord | `demo.landlord01@example.test` | `BoardNestDemo#2026` | Active and approved, Pro plan, multiple listings |
| Landlord | `demo.landlord05@example.test` | `BoardNestDemo#2026` | Active and approved, Standard plan |
| Landlord | `demo.landlord11@example.test` | `BoardNestDemo#2026` | Pending identity review; **cannot** manage listings |
| Student | `demo.student01@example.test` | `BoardNestDemo#2026` | Verified Tier 2, occupied slot |
| Student | `demo.student17@example.test` | `BoardNestDemo#2026` | Verified Tier 2, pending booking |
| Student | `demo.student33@example.test` | `BoardNestDemo#2026` | Tier 1 (should not book) |
| Field Agent | `demo.agent01@example.test` | `BoardNestDemo#2026` | Active agent |

Email domain `example.test` is intentionally non-deliverable. All displayed names, phone numbers, addresses, agent actions and messages are **fictional examples**. Included house-room photos are inherited test images and are repeatedly used as placeholders; they are **not** photos of the stated addresses.

## V4 core rule placement

| Rule | Implementation |
|---|---|
| Landlord registration / identity | `modules/landlord/mvc/modules/user/services/RegistrationService.php`, `core/Auth.php` |
| Approval / Pro plan counts | `modules/landlord/mvc/modules/property/services/PropertyService.php`, `RoomService.php`, guarded transactions |
| 15-minute property/room details window | `PropertyService::update`, `RoomService::update` with row locks |
| Rent edits and 1-year cooldown | `RoomService::update`, `Room::windows`, `room_rent_history` |
| Room slot generation/capacity changes | `Room::create`, `RoomService::update`, transaction/booking history checks |
| Student verification and no double booking | `public/student/actions/submit_booking.php`, transaction locks and `slot_reservations` UNIQUE constraints |
| Landlord booking accept/reject/move-in/move-out | `BookingService::decide`, `Booking::change` and explicit group/slot/reservation synchronized updates |
| Student cancellation | `public/student/actions/cancel_booking.php` with rollback on failures |
| Password & photo changes | `modules/landlord/mvc/modules/user/controllers/ProfileController.php`, `models/Landlord.php` |
| Student availability display | `public/student/includes/booking.php` and database query (not a view) |

**Without triggers:** Other modules must not write booking/slot/price fields outside these transaction-aware flows. MySQL unique constraints and row locks help prevent races but do not enforce all domain logic against arbitrary SQL clients. The shared login was tightened to use password hashes only; existing old plaintext accounts must be migrated rather than accepted insecurely.

## Demo scenarios

- 12 landlords (10 approved/active, 1 submitted, 1 rejected); 36 students (32 verified Tier 2, 4 Tier 1) and 4 field agents.
- 18 properties and 36 rooms; 63 physical slots, of which 1 is explicitly maintenance-blocked.
- 32 bookings: 8 occupied, 6 accepted awaiting move-in, 6 pending, 5 completed, 6 landlord-rejected and 1 student-cancelled.
- 20 current slot reservations, plus group bookings, payments, plan history, facilities, inspections, complaints, chat and announcements.
- Rooms/properties awaiting verification should not appear bookable to students. A verified one-slot private room with an active booking should appear fully booked.

## Caution: actual runtime testing

The source underwent **PHP lint, JavaScript syntax and static schema/demo checks** here. No installed MariaDB server nor PHP `pdo_mysql` extension was available in the packaging environment: **fresh import, real SQL execution, concurrent booking requests and full HTTP end-to-end tests are not certified**. The supplied `scripts/check_unified_db.php` can run on your XAMPP to verify real data and FK-consistent state. Run login/registration/booking/payments/approval cross-role tests before any release.

## Upgrading existing populated databases

**Do not import the full clean SQL or demo SQL into populated `boardnest`.** Use `DATABASE_UNIFIED/04_READ_ONLY_PREFLIGHT.sql`, identify the current variant, then create an explicitly reviewed migration/backfill. The `07_...` and `09_...` scripts are table-addition references only, **not a complete migration** of legacy slot/booking records or earlier triggers. Existing triggers must be reviewed and removed in a controlled maintenance window if moving from V3 to V4. Do not delete production triggers blindly.
