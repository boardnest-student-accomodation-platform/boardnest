<?php
// ============================================================
// BoardNest — Field Agent Login (Orchestrator)
// public/field_agent/login.php
// ============================================================
if (session_status() == PHP_SESSION_NONE) session_start();
require_once '../../config/db.php';

if (isset($_SESSION['user_id']) && isset($_SESSION['role']) && $_SESSION['role'] === 'field_agent') {
    header('Location: dashboard.php'); exit();
}

$error = '';
$success_msg = isset($_SESSION['success']) ? $_SESSION['success'] : '';
unset($_SESSION['success']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = isset($_POST['email'])    ? trim($_POST['email'])    : '';
    $password = isset($_POST['password']) ? trim($_POST['password']) : '';

    if ($email !== '' && $password !== '') {
        try {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute(array($email));
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password_hash'])) {
                if ($user['role'] !== 'field_agent') {
                    $error = 'Access denied. This portal is only for Field Agents.';
                } elseif ($user['status'] !== 'active') {
                    $error = 'Your account is currently ' . htmlspecialchars($user['status']) . '.';
                } else {
                    $_SESSION['user_id']   = $user['user_id'];
                    $_SESSION['role']      = $user['role'];
                    $_SESSION['full_name'] = $user['full_name'];
                    header('Location: dashboard.php'); exit();
                }
            } else {
                $error = 'Invalid email or password.';
            }
        } catch (Exception $e) {
            $error = 'Database error: ' . $e->getMessage();
        }
    } else {
        $error = 'Please fill in all fields.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BoardNest — Field Agent Portal</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&family=Outfit:wght@800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/field_agent.css">
</head>
<body class="auth-page-body" style="font-family:'Plus Jakarta Sans',sans-serif;">
    <?php require __DIR__ . '/partials/_login_form.php'; ?>
    <script src="../assets/js/field_agent.js"></script>
</body>
</html>
