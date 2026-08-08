# BoardNest — Project Decisions Log

This file documents the key technical and design decisions made for the BoardNest project. Read this before you start building your module so you're aligned with the rest of the team.

---

## 1. Architecture

**Decision:** Three-tier web application — HTML/CSS/JS frontend, PHP backend, MySQL database.

**Why:** Matches the academic requirement of no external frameworks or libraries. Plain PHP gives everyone full visibility into what the code is doing without framework magic hiding the logic.

**No frameworks. No libraries. No exceptions.** Vanilla JS only on the frontend. No jQuery, no React, no Bootstrap, no Tailwind. Plain PHP on the backend — no Laravel, no Symfony.

---

## 2. Folder Structure

```
boardnest/
├── config/         # db.php only — shared by everyone, do not duplicate
├── includes/       # session.php, header.php, footer.php — shared, do not duplicate
├── public/         # all role modules and static assets live here
│   ├── student/
│   ├── landlord/
│   ├── field_agent/
│   ├── admin/
│   ├── assets/     # css, js, images, uploads
│   └── uploads/
├── src/            # reserved for any shared utility PHP classes if needed
├── docs/           # documentation
     ── Decisions.md    # this file
├── login.php       # shared login — do not move or duplicate
├── logout.php      # shared logout — do not move or duplicate
├── index.php       # role-based redirect after login — do not modify without agreement
├── schema.sql      # full database schema — import this to get started

```

**Rule:** Work only inside your own module folder inside `public/`. Do not touch another person's folder.

---

## 3. Database

**Decision:** Single shared MySQL database called `boardnest`. Import `schema.sql` to set up your local database.

**Connection:** Everyone uses `config/db.php`. Do not create your own connection file.

**ORM:** None. Raw SQL with PDO prepared statements only.

**Naming conventions agreed:**
- Table names: lowercase, underscores — `room_slots` not `RoomSlots`
- Primary keys: `tablename_id` — `user_id`, `room_id`, `booking_id`
- Foreign keys: match the primary key name they reference — `user_id` in students table references `user_id` in users table
- Status columns: use ENUM with clearly named values

Before anyone writes backend code, we agree on:

1. Full database schema built together — table names, column names, foreign keys all finalised before individual work starts
2. Everyone uses /config/db.php for database connection — PDO only, no mysqli
3. Everyone uses /includes/session.php for role checks — requireRole('your_role') at the top of every protected page
4. Passwords always use password_hash() on register and password_verify() on login — no exceptions
5. All form submissions go to a separate /actions/ PHP file, never processed on the same page as the UI
6. All SQL uses prepared statements with ? placeholders — no string concatenation with user input ever
7. Folder structure follows the agreed layout — each person works inside their own module folder only
8. Redirect after every POST action (header('Location: ...') then exit()) — no exceptions
9. Use $_SESSION['success'] and $_SESSION['error'] to pass messages between pages consistently
10. Test your CRUD against the shared database before the integration session, not during it
    

**Core table structure:**

The `users` table is shared across all four roles. Each role has its own extension table linked by `user_id`:

```
users (user_id, full_name, email, password_hash, role, status, created_at)
  ├── students    (student_id, user_id, nic_number, mobile, university ...)
  ├── landlords   (landlord_id, user_id, nic_number, mobile, address ...)
  ├── field_agents(agent_id, user_id, nic_number, mobile, assigned_city ...)
  └── admin       (admin_id, user_id)
```

If you need to add a table for your module, add it to `schema.sql` and tell the group so everyone can re-import or run the ALTER statement.

---

## 4. Authentication & Sessions

**Decision:** PHP native sessions. No JWT, no cookies beyond the session cookie.

**How it works:**

After a successful login, the session stores:
```php
$_SESSION['user_id']   // the user's ID from the users table
$_SESSION['role']      // 'student', 'landlord', 'field_agent', or 'admin'
$_SESSION['full_name'] // display name
```

**Every protected page starts with:**
```php
require_once '../../includes/session.php';
requireRole('student'); // use your own role string
require_once '../../config/db.php';
```

`requireRole()` is defined in `includes/session.php`. It checks the session and redirects to `login.php` if the role doesn't match. Never write your own session check — use this function.

**Password hashing:** Always use `password_hash()` on registration and `password_verify()` on login. Never store plain text passwords.

---

## 5. Database Queries

**Decision:** PDO with prepared statements everywhere. No raw string concatenation with user input.

**Always do this:**
```php
$stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
$stmt->execute([$email]);
$user = $stmt->fetch();
```

**Never do this:**
```php
$result = mysqli_query($conn, "SELECT * FROM users WHERE email = '$email'");
```

The second approach is a SQL injection vulnerability. If you write it this way and the panel spots it, it reflects on the whole team.

---

## 6. Form Handling

**Decision:** All form submissions go to a separate action file inside an `actions/` folder. Never process a POST on the same page as the UI.

**Pattern:**
```
public/student/booking.php          ← shows the form (GET)
public/student/actions/submit_booking.php  ← handles the form (POST)
```

After every successful POST action, redirect immediately:
```php
header('Location: ../dashboard.php');
exit();
```

Pass feedback between pages using session flash messages:
```php
$_SESSION['success'] = "Booking submitted successfully.";
$_SESSION['error']   = "Something went wrong.";
```

Display and clear them at the top of the destination page.

---

## 7. File Uploads

**Decision:** All uploaded files (room photos, NIC scans, verification photos) go into `public/uploads/`.

**Rules:**
- Validate file type on the backend — accept only jpg, jpeg, png, pdf depending on context
- Rename uploaded files to a unique name before saving — never use the original filename
- Never execute uploaded files — uploads folder must not allow PHP execution (add a `.htaccess` if needed)

```php
// Safe upload example
$ext      = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
$filename = uniqid('upload_', true) . '.' . $ext;
$dest     = '../../public/uploads/' . $filename;
move_uploaded_file($_FILES['photo']['tmp_name'], $dest);
```

---

## 8. CSS

**Decision:** One global stylesheet at `public/assets/css/style.css`. All shared components (buttons, cards, forms, navbar, badges) are defined there.

Use the global classes first. Only add a module-specific CSS file if you need something truly unique to your pages. Do not redefine global components in your own file.

**Color tokens are defined as CSS variables** — use them, don't hardcode hex values:
```css
color: var(--color-primary);       /* #A4856D */
background: var(--color-card);     /* #FFF2D7 */
color: var(--color-text);          /* #3B3330  */
background: var(--color-bg);       /* #FAF7F2  */
```

---

## 9. Email Notifications

**Decision:** Native PHP `mail()` function only. No SendGrid, no Mailbird, no third-party email service.

Used for: registration confirmation, registration status updates, booking confirmation, complaint outcome notifications.

Internal platform notifications (shown inside the dashboard) are separate from emails and stored in the `internal_notifications` table.

---

## 10. Payment

**Decision:** PayHere sandbox integration for landlord subscription payments only.

Rent payments between landlords and students are completely outside platform scope — all rent is physical cash. The platform only records rent figures for reference. PayHere is used only when a landlord upgrades their subscription (Standard → Pro).

---

## 11. Distance Calculation

**Decision:** Haversine formula implemented in PHP. No Google Maps API, no OpenStreetMap, no external mapping service.

Used for: search by KM radius from a landmark, "Around University" proximity search, and Field Agent GPS geofence validation (100m radius enforcement).

University and landmark coordinates are pre-seeded in the database. Listing coordinates are entered manually by landlords (copied from Google Maps). Google Maps is used only as a URL stored in the database and opened in a new browser tab when a student clicks "View Location" — no Maps API is called.

---

## 12. GPS Geofence (Field Agent Verification)

**Decision:** Browser native Geolocation API (`navigator.geolocation.getCurrentPosition()`) captures the agent's live coordinates. PHP backend runs Haversine to compare against stored property coordinates. Report submission is blocked server-side if the agent is outside the 100m radius.

The frontend hides the checklist form until the GPS check passes — this is for UX only. The real enforcement is the server-side validation at submission time, which cannot be bypassed through browser dev tools.

---

## 13. Git Workflow

**Branching:** Work on your own branch named after your module:
```
student-module
landlord-module
field-agent-module
admin-module
```

**Never push directly to main.** Open a pull request and get one teammate to review before merging.

**Commit messages:** Be specific.
```
# Good
"Add booking request submission form and action handler"
"Fix slot occupancy update query on booking accept"

# Bad
"update"
"fix stuff"
"changes"
```

**Pull before you push — every time:**
```bash
git pull origin main
```

Merge conflicts are everyone's problem if you don't pull first.

---

## 14. What's Out of Scope

Do not build these — if you find yourself going down these paths, stop and check with the group:

- Any payment gateway integration other than PayHere sandbox for subscriptions
- Any third-party map API (Google Maps SDK, Leaflet, OpenStreetMap tiles)
- Any external email or SMS service
- Native mobile app features — web only, mobile responsive
- AI or ML features of any kind
- Real-time features (WebSockets, live chat)
- OAuth or social login

---

*Last updated by: [your name]*
*Update this file whenever a significant new decision is made.*

