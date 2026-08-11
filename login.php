<?php
require_once 'includes/session.php';
startSession();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once 'config/db.php';

    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND status = 'active'");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user) {
        $isPasswordCorrect = false;

        // 1. Password එක Hash එකක්දැයි පරීක්ෂා කිරීම (New Encrypted Passwords)
        if (password_verify($password, $user['password_hash'])) {
            $isPasswordCorrect = true;
        } 
        // 2. Hash නොවන Plain Text Password එකක්දැයි පරීක්ෂා කිරීම (Old Passwords)
        elseif ($password === $user['password_hash']) {
            $isPasswordCorrect = true;

            // Optional: Plain text එකෙන් Log වූ පසු එය ස්වයංක්‍රීයව Hash කර Update කිරීම
            $newHashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $updateStmt = $pdo->prepare("UPDATE users SET password_hash = ? WHERE user_id = ?");
            $updateStmt->execute([$newHashedPassword, $user['user_id']]);
        }

        // Password නිවැරදි නම් Session සාදා Direct කිරීම
        if ($isPasswordCorrect) {
            $_SESSION['user_id']   = $user['user_id'];
            $_SESSION['role']      = $user['role'];
            $_SESSION['full_name'] = $user['full_name'];

            // Redirect based on role
            switch ($user['role']) {
                case 'student':
                    header('Location: student/dashboard.php');
                    break;
                case 'landlord':
                    header('Location: modules/landlord/dashboard.php');
                    break;
                case 'field_agent':
                    header('Location: field_agent/dashboard.php');
                    break;
                case 'admin':
                    header('Location: admin/dashboard.php');
                    break;
            }
            exit();
        } else {
            $error = "Invalid email or password.";
        }
    } else {
        $error = "Invalid email or password.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>BoardNest — Login</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="login-container">
        <h1>BoardNest</h1>
        <h2>Login</h2>
        <?php if ($error): ?>
            <p class="error"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>
        <form method="POST" action="login.php">
            <label>Email</label>
            <input type="email" name="email" required>
            <label>Password</label>
            <input type="password" name="password" required>
            <button type="submit">Login</button>
        </form>
        <p>Don't have an account? <a href="register.php">Register</a></p>
    </div>
</body>
</html>