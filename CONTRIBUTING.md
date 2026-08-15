# Contributing to BoardNest

Welcome to the BoardNest project. This document outlines the rules and requirements every team member must follow before opening or merging a Pull Request. Please read this fully before submitting any code.

---

## Tech Stack

- **Backend:** PHP (no frameworks)
- **Frontend:** HTML, CSS, JavaScript (no libraries or frameworks)
- **Database:** MySQL
- **Version Control:** GitHub

---

## Branch Naming

Always create a new branch for your work. Never commit directly to `main`.

```
feature/your-feature-name     → New features
fix/what-you-fixed            → Bug fixes
security/what-you-secured     → Security patches
chore/what-you-cleaned-up     → Refactoring, cleanup
```

**Examples:**
```
feature/add-room-form
fix/delete-room-ownership-check
security/csrf-token-login
```

---

## Before Opening a PR

Go through this checklist yourself before opening a PR. Reviewers will check these too.

### Security Checklist
- [ ] All state-changing actions (delete, update, insert) use **POST**, not GET
- [ ] All POST forms include a **CSRF token** hidden input
- [ ] All action files validate the CSRF token before processing
- [ ] All queries that act on user-owned data include an **ownership check** (e.g. `AND landlord_id = ?`)
- [ ] No `$e->getMessage()` is exposed to the user — log it server-side or show a generic message
- [ ] No `display_errors`, `display_startup_errors`, or `error_reporting(E_ALL)` in any file — these are dev-only and must be removed before committing

### CSRF Token Pattern (Required for all POST forms)
```php
// In session.php or at top of page — generate once
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
```
```html
<!-- In every POST form -->
<input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
```
```php
// In every action file — validate before processing
$token = $_POST['csrf_token'] ?? '';
if (!hash_equals($_SESSION['csrf_token'], $token)) {
    header("Location: ../dashboard.php?status=invalid_request");
    exit();
}
```

### Code Quality Checklist
- [ ] All user-facing output is wrapped in `htmlspecialchars()`
- [ ] All SQL queries use **prepared statements** — no raw string interpolation
- [ ] No N+1 queries — do not run queries inside `foreach` loops, use JOINs instead
- [ ] Input validation is done before any DB operation (check for empty, type, range)
- [ ] `rowCount()` is checked after UPDATE/DELETE to confirm something actually changed
- [ ] Session is regenerated after login: `session_regenerate_id(true)`
- [ ] Free plan limits are enforced in **action files**, not just in the form — users can bypass form checks by POSTing directly

### Consistency Checklist
- [ ] Use `requireRole('role')` for session handling — do not mix with raw `startSession()` + manual checks
- [ ] CSS path uses `../../public/assets/css/style.css` (relative) — do not use `/boardnest/public/...` (absolute)
- [ ] Do not redefine global CSS classes (e.g. `.container`, `.badge`) locally in `<style>` blocks
- [ ] Inline styles should be avoided — use class names from `style.css` instead
- [ ] Comments in code must be in **English** for consistency

### Database Checklist
- [ ] Any new tables or columns are added to the shared schema file and included in the PR
- [ ] Foreign keys have appropriate `ON DELETE CASCADE` or `ON DELETE RESTRICT` as needed
- [ ] Extension tables (`landlords`, `students`, etc.) are populated alongside `users` inserts — do not insert into `users` alone

---

## PR Requirements

### Title Format
```
[TYPE] Short description of what this PR does
```
**Examples:**
```
[FEATURE] Add room form with free tier limit check
[FIX] Ownership check missing on room delete
[SECURITY] Add CSRF tokens to all landlord action files
```

### PR Description Must Include
1. **What this PR does** — brief summary
2. **Files changed** — list the files and what changed in each
3. **Known issues or TODOs** — list anything not yet fixed with a reason
4. **How to test** — steps to manually verify the feature or fix works

### Template
```
## What this PR does
<!-- Brief description -->

## Files changed
- `modules/landlord/add_room.php` — added room count validation
- `actions/save_room.php` — added CSRF check and ownership verification

## Known issues / TODOs
- [ ] Input validation on price field — will fix in follow-up PR

## How to test
1. Log in as a landlord
2. Navigate to Add Room
3. ...
```

---

## Merge Requirements

A PR **cannot** be merged into `main` unless **both** of the following are met:

1. **At least one teammate has reviewed and approved** the PR on GitHub
2. **All TODO/FIXME comments and known issues listed in the PR are either resolved or have an open GitHub Issue tracking them**

Reviewers: if you spot any item from the checklists above that is missing, request changes and reference the specific checklist item.

---

## Opening GitHub Issues

For bugs and security problems found during review, open a GitHub Issue with:
- A clear title describing the problem
- The file name and line number
- The label `security`, `bug`, or `enhancement` as appropriate

Do not merge a PR that introduces a `security` labelled issue without resolving it first.

---

## Common Mistakes to Avoid

| Mistake | What to do instead |
|---|---|
| `die()` with raw HTML for errors | Redirect to dashboard with a `?status=` param |
| GET request for delete/update | Use a POST form with CSRF token |
| `$e->getMessage()` shown to user | Log it, show generic message |
| Query inside `foreach` loop | Use a JOIN query instead |
| `display_errors = 1` committed | Remove entirely before committing |
| Inserting into `users` without extension table | Always insert into `landlords`/`students` too |
| Hardcoded absolute CSS paths | Use relative paths consistently |

---

## Questions

If you're unsure about anything in this guide, ask in the group chat before opening the PR — not after.
