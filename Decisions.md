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
