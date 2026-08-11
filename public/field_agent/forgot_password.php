<?php
// ============================================================
// BoardNest — Forgot Password (Orchestrator)
// public/field_agent/forgot_password.php
// ============================================================
require_once '../../includes/session.php';
require_once '../../config/db.php';

$error   = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email            = isset($_POST['email'])            ? trim($_POST['email'])            : '';
    $nic              = isset($_POST['nic_number'])        ? trim($_POST['nic_number'])        : '';
    $new_password     = isset($_POST['new_password'])     ? $_POST['new_password']            : '';
    $confirm_password = isset($_POST['confirm_password']) ? $_POST['confirm_password']        : '';

    if (empty($email) || empty($nic) || empty($new_password) || empty($confirm_password)) {
        $error = 'All fields are required.';
    } elseif ($new_password !== $confirm_password) {
        $error = 'Passwords do not match. Please re-enter matching passwords.';
    } elseif (strlen($new_password) < 8) {
        $error = 'New password must be at least 8 characters long.';
    } else {
        $stmt = $pdo->prepare("
            SELECT u.user_id
            FROM users u
            INNER JOIN field_agents fa ON u.user_id = fa.user_id
            WHERE u.email = ? AND u.role = 'field_agent' AND fa.nic_number = ?
        ");
        $stmt->execute(array($email, $nic));
        $user = $stmt->fetch();

        if (!$user) {
            $error = 'No Field Agent account found matching this Email and NIC Number.';
        } else {
            $new_hash = password_hash($new_password, PASSWORD_DEFAULT);
            $stmtUpdate = $pdo->prepare("UPDATE users SET password_hash = ? WHERE user_id = ?");
            $stmtUpdate->execute(array($new_hash, $user['user_id']));
            $_SESSION['success'] = 'Password reset successfully! Please sign in with your new password.';
            header('Location: login.php'); exit();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BoardNest — Forgot Password</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&family=Outfit:wght@800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/field_agent.css">
</head>
<body class="auth-page-body" style="font-family:'Plus Jakarta Sans',sans-serif;">
    <?php require __DIR__ . '/partials/_forgot_password_form.php'; ?>
    <script src="../assets/js/field_agent.js"></script>
</body>
</html>
