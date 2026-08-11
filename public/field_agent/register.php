<?php
// ============================================================
// BoardNest — Field Agent Register (Orchestrator)
// public/field_agent/register.php
// ============================================================
if (session_status() == PHP_SESSION_NONE) session_start();
require_once '../../config/db.php';

if (isset($_SESSION['user_id']) && isset($_SESSION['role']) && $_SESSION['role'] === 'field_agent') {
    header('Location: dashboard.php'); exit();
}

$error   = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name  = trim(isset($_POST['full_name'])   ? $_POST['full_name']   : '');
    $email      = trim(isset($_POST['email'])        ? $_POST['email']       : '');
    $password   = trim(isset($_POST['password'])     ? $_POST['password']    : '');
    $nic_number = trim(isset($_POST['nic_number'])   ? $_POST['nic_number']  : '');
    $mobile     = trim(isset($_POST['mobile'])       ? $_POST['mobile']      : '');
    $city       = trim(isset($_POST['city'])         ? $_POST['city']        : '');

    if (empty($full_name) || empty($email) || empty($password) || empty($nic_number) || empty($mobile) || empty($city)) {
        $error = 'Please fill in all required fields.';
    } elseif (strlen($password) < 8) {
        $error = 'Password must be at least 8 characters long.';
    } else {
        try {
            $pdo->beginTransaction();

            $stmt = $pdo->prepare("SELECT user_id, role FROM users WHERE email = ?");
            $stmt->execute(array($email));
            $existingUser = $stmt->fetch();

            if ($existingUser) {
                if ($existingUser['role'] === 'field_agent') {
                    throw new Exception('This email is already registered as a Field Agent. Please sign in.');
                } else {
                    throw new Exception('This email is used by a ' . ucfirst($existingUser['role']) . ' account. Please use a different email.');
                }
            }

            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmtUser = $pdo->prepare("INSERT INTO users (full_name, email, password_hash, role, status) VALUES (?, ?, ?, 'field_agent', 'pending')");
            $stmtUser->execute(array($full_name, $email, $hash));
            $user_id = $pdo->lastInsertId();

            $stmtAgent = $pdo->prepare("INSERT INTO field_agents (user_id, nic_number, mobile, assigned_city, is_active, recruit_mode) VALUES (?, ?, ?, ?, 1, 'self_registered')");
            $stmtAgent->execute(array($user_id, $nic_number, $mobile, $city));

            $pdo->commit();
            $_SESSION['success'] = 'Application submitted successfully! Your account is pending admin approval.';
            header('Location: login.php'); exit();

        } catch (Exception $e) {
            $pdo->rollBack();
            $error = $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BoardNest — Apply to be a Field Agent</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&family=Outfit:wght@800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/field_agent.css">
</head>
<body class="auth-page-body" style="font-family:'Plus Jakarta Sans',sans-serif;">
    <?php require __DIR__ . '/partials/_register_form.php'; ?>
    <script src="../assets/js/field_agent.js"></script>
</body>
</html>
