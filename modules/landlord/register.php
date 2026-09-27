
<?php
require_once __DIR__ . '/../../includes/session.php';
startSession();

require_once __DIR__ . '/../../config/db.php';

// Already logged-in landlords go to dashboard
if (isset($_SESSION['user_id']) &&
    ($_SESSION['role'] ?? '') === 'landlord') {
    header('Location: dashboard.php');
    exit();
}

$error = '';
$success = '';

$full_name = '';
$email = '';
$phone = '';
$address = '';
$nic_number = '';

// CSRF protection
if (empty($_SESSION['register_csrf'])) {
    $_SESSION['register_csrf'] = bin2hex(random_bytes(32));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $nic_number = strtoupper(trim($_POST['nic_number'] ?? ''));
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $consent = isset($_POST['consent']) ? 1 : 0;
    $csrf = $_POST['csrf_token'] ?? '';

    if (!hash_equals($_SESSION['register_csrf'], $csrf)) {
        $error = 'Invalid request. Please refresh and try again.';
    } elseif (
        $full_name === '' || $email === '' ||
        $phone === '' || $nic_number === '' ||
        $password === '' || $confirm_password === ''
    ) {
        $error = 'Please fill in all required fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif (!preg_match('/^(?:\d{9}[VvXx]|\d{12})$/', $nic_number)) {
        $error = 'Please enter a valid Sri Lankan NIC number.';
    } elseif (!preg_match('/^(?:0\d{9}|\+94\d{9})$/', $phone)) {
        $error = 'Please enter a valid phone number.';
    } elseif (strlen($password) < 8) {
        $error = 'Password must contain at least 8 characters.';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match.';
    } elseif (!$consent) {
        $error = 'Please agree to the registration terms.';
    } else {
        try {
            $pdo->beginTransaction();

            // Check whether the email already exists
            $check = $pdo->prepare(
                "SELECT user_id FROM users WHERE email = ?"
            );
            $check->execute([$email]);

            if ($check->fetch()) {
                $pdo->rollBack();
                $error = 'This email is already registered.';
            } else {
                // Create account as pending
                $hashedPassword = password_hash(
                    $password,
                    PASSWORD_DEFAULT
                );

                $userStmt = $pdo->prepare(
                    "INSERT INTO users
                    (full_name, email, phone, address,
                     password_hash, role, status)
                    VALUES (?, ?, ?, ?, ?, 'landlord', 'pending')"
                );

                $userStmt->execute([
                    $full_name,
                    $email,
                    $phone,
                    $address,
                    $hashedPassword
                ]);

                $userId = (int) $pdo->lastInsertId();

                // Create landlord profile
                $landlordStmt = $pdo->prepare(
                    "INSERT INTO landlords
                    (user_id, nic_number, mobile, address,
                     subsc_tier, subsc_expires, consent_agreed)
                    VALUES (?, ?, ?, ?, 'standard', NULL, ?)"
                );

                $landlordStmt->execute([
                    $userId,
                    $nic_number,
                    $phone,
                    $address,
                    $consent
                ]);

                $pdo->commit();

                $success = 'Registration successful! Your account is waiting for admin approval. You can log in after your account is approved.';

                $full_name = '';
                $email = '';
                $phone = '';
                $address = '';
                $nic_number = '';

                // Generate a new CSRF token
                $_SESSION['register_csrf'] =
                    bin2hex(random_bytes(32));
            }
        } catch (PDOException $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            // Log details privately; do not expose DB errors
            error_log('Landlord registration error: ' . $e->getMessage());
            $error = 'Registration failed. Please try again later.';
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
    <title>Landlord Registration | BoardNest</title>

    <link rel="stylesheet"
          href="../../public/assets/css/landlord.css?v=3">

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            background: #f7f0e7;
            color: #493326;
            font-family: Arial, sans-serif;
        }

        .register-card {
            width: min(100% - 32px, 520px);
            margin: 35px auto;
            padding: 30px 32px;
            background: #fffaf2;
            border: 1px solid #e5d3bf;
            border-radius: 16px;
            box-shadow: 0 8px 28px rgba(75, 49, 32, 0.09);
        }

        .register-card h1 {
            margin: 0 0 10px;
            text-align: center;
            color: #704d38;
            font-size: 28px;
        }

        .intro {
            margin: 0 0 26px;
            color: #75685e;
            font-size: 14px;
            line-height: 1.6;
            text-align: center;
        }

        .form-group {
            margin-bottom: 17px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #493326;
            font-weight: 600;
            font-size: 14px;
        }

        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 13px 14px;
            border: 1px solid #dfcbb7;
            border-radius: 10px;
            background: #fff;
            color: #34251c;
            font: inherit;
            font-size: 15px;
            outline: none;
        }

        .form-group input:focus,
        .form-group textarea:focus {
            border-color: #81583e;
            box-shadow: 0 0 0 3px rgba(129, 88, 62, 0.12);
        }

        .form-group textarea {
            min-height: 90px;
            resize: vertical;
        }

        .consent {
            display: flex;
            align-items: flex-start;
            gap: 9px;
            margin: 18px 0;
            font-size: 13px;
            line-height: 1.5;
            color: #625348;
        }

        .consent input {
            margin-top: 3px;
            accent-color: #80583f;
        }

        .btn-submit {
            width: 100%;
            padding: 14px;
            border: none;
            border-radius: 10px;
            background: #80583f;
            color: #fff;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
        }

        .btn-submit:hover {
            background: #65432f;
        }

        .msg-error,
        .msg-success {
            padding: 13px;
            margin-bottom: 18px;
            border-radius: 9px;
            font-size: 14px;
            line-height: 1.5;
        }

        .msg-error {
            color: #842029;
            background: #f8d7da;
            border: 1px solid #f1aeb5;
        }

        .msg-success {
            color: #155724;
            background: #d4edda;
            border: 1px solid #a3cfbb;
        }

        .login-link {
            margin-top: 22px;
            text-align: center;
            font-size: 14px;
        }

        .login-link a {
            color: #80583f;
            font-weight: 700;
        }

        @media (max-width: 480px) {
            .register-card {
                margin: 18px auto;
                padding: 24px 20px;
            }
        }
    </style>
</head>
<body>

<main class="register-card">
    <h1>Landlord Registration</h1>

    <p class="intro">
        Create an account to list your properties on BoardNest.
        Your account will be reviewed by an administrator.
    </p>

    <?php if ($error !== ''): ?>
        <div class="msg-error">
            <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
        </div>
    <?php endif; ?>

    <?php if ($success !== ''): ?>
        <div class="msg-success">
            <?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?>
        </div>
    <?php else: ?>

        <form method="POST" action="">
            <input type="hidden" name="csrf_token"
                   value="<?= htmlspecialchars(
                       $_SESSION['register_csrf'],
                       ENT_QUOTES,
                       'UTF-8'
                   ) ?>">

            <div class="form-group">
                <label for="full_name">Full Name *</label>
                <input type="text" id="full_name"
                       name="full_name"
                       value="<?= htmlspecialchars($full_name, ENT_QUOTES, 'UTF-8') ?>"
                       maxlength="100" required
                       placeholder="e.g. Perera A.B.">
            </div>

            <div class="form-group">
                <label for="email">Email Address *</label>
                <input type="email" id="email"
                       name="email"
                       value="<?= htmlspecialchars($email, ENT_QUOTES, 'UTF-8') ?>"
                       maxlength="254" required
                       placeholder="e.g. landlord@gmail.com">
            </div>

            <div class="form-group">
                <label for="phone">Phone Number *</label>
                <input type="tel" id="phone"
                       name="phone"
                       value="<?= htmlspecialchars($phone, ENT_QUOTES, 'UTF-8') ?>"
                       required
                       placeholder="0771234567">
            </div>

            <div class="form-group">
                <label for="nic_number">NIC Number *</label>
                <input type="text" id="nic_number"
                       name="nic_number"
                       value="<?= htmlspecialchars($nic_number, ENT_QUOTES, 'UTF-8') ?>"
                       maxlength="12" required
                       placeholder="e.g. 200412345678">
            </div>

            <div class="form-group">
                <label for="address">Address</label>
                <textarea id="address" name="address"
                          placeholder="Your residential address"><?= htmlspecialchars($address, ENT_QUOTES, 'UTF-8') ?></textarea>
            </div>

            <div class="form-group">
                <label for="password">Password *</label>
                <input type="password" id="password"
                       name="password" minlength="8"
                       required
                       placeholder="At least 8 characters">
            </div>

            <div class="form-group">
                <label for="confirm_password">Confirm Password *</label>
                <input type="password" id="confirm_password"
                       name="confirm_password" minlength="8"
                       required
                       placeholder="Re-enter your password">
            </div>

            <label class="consent">
                <input type="checkbox" name="consent"
                       value="1" required>
                <span>
                    I agree to the BoardNest registration terms
                    and understand that my account requires
                    administrator approval.
                </span>
            </label>

            <button type="submit" class="btn-submit">
                Register as Landlord
            </button>
        </form>

    <?php endif; ?>

    <p class="login-link">
        Already have an account?
        <a href="../../login.php">Login here</a>
    </p>
</main>

</body>
</html>