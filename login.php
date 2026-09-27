
<?php
require_once __DIR__ . '/includes/session.php';
startSession();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once __DIR__ . '/config/db.php';

    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        $error = 'Please enter your email and password.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email or password.';
    } else {
        try {
            $stmt = $pdo->prepare(
                "SELECT user_id, full_name, email,
                        password_hash, role, status
                 FROM users
                 WHERE email = ?
                 LIMIT 1"
            );

            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (
                $user &&
                password_verify($password, $user['password_hash'])
            ) {
                // Check account status
                if (
                    $user['role'] === 'landlord' &&
                    $user['status'] === 'pending'
                ) {
                    $error = 'Your landlord account is waiting for admin approval. Please try again after approval.';
                } elseif ($user['status'] !== 'active') {
                    $error = 'Your account is not active. Please contact the administrator.';
                } else {
                    // Regenerate session ID after login
                    session_regenerate_id(true);

                    $_SESSION['user_id'] = $user['user_id'];
                    $_SESSION['role'] = $user['role'];
                    $_SESSION['full_name'] = $user['full_name'];

                    // Redirect according to user role
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
                            header('Location: modules/admin/dashboard.php');
                            break;

                        default:
                            $_SESSION = [];
                            session_destroy();
                            header('Location: login.php');
                            exit();
                    }

                    exit();
                }
            } else {
                $error = 'Invalid email or password.';
            }
        } catch (PDOException $e) {
            error_log('Login database error: ' . $e->getMessage());
            $error = 'A system error occurred. Please try again later.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>BoardNest — Login</title>

    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

    <div class="login-container">
        <h1>BoardNest</h1>
        <h2>Login</h2>

        <?php if ($error !== ''): ?>
            <p class="error" role="alert">
                <?= htmlspecialchars(
                    $error,
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>
            </p>
        <?php endif; ?>

        <form method="POST" action="login.php">
            <label for="email">Email</label>

            <input
                type="email"
                id="email"
                name="email"
                required
                autocomplete="email"
                value="<?= htmlspecialchars(
                    $_POST['email'] ?? '',
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>"
            >

            <label for="password">Password</label>

            <input
                type="password"
                id="password"
                name="password"
                required
                autocomplete="current-password"
            >

            <button type="submit">Login</button>
        </form>

        <p>
            Don't have an account?
            <a href="modules/landlord/register.php">
                Register as Landlord
            </a>
        </p>
    </div>

</body>
</html>