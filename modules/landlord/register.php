
<?php
require_once __DIR__ . '/../../includes/session.php';
startSession();

// Redirect logged-in landlords
if (
    isset($_SESSION['user_id']) &&
    ($_SESSION['role'] ?? '') === 'landlord'
) {
    header('Location: dashboard.php');
    exit();
}

require_once __DIR__ . '/../../config/db.php';

$error = '';
$success = '';

$full_name = '';
$email = '';
$phone = '';
$address = '';

// CSRF protection
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $password = $_POST['password'] ?? '';
    $csrf_token = $_POST['csrf_token'] ?? '';

    // Validate CSRF token
    if (
        !hash_equals(
            $_SESSION['csrf_token'],
            $csrf_token
        )
    ) {
        $error = 'Invalid request. Please refresh the page.';
    } elseif (
        $full_name === '' ||
        $email === '' ||
        $phone === '' ||
        $password === ''
    ) {
        $error = 'Please fill in all required fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters long.';
    } elseif (strlen($full_name) > 100) {
        $error = 'Full name is too long.';
    } elseif (strlen($email) > 255) {
        $error = 'Email address is too long.';
    } elseif (strlen($phone) > 20) {
        $error = 'Phone number is too long.';
    } else {
        try {
            // Check whether email already exists
            $checkStmt = $pdo->prepare(
                "SELECT user_id FROM users WHERE email = ? LIMIT 1"
            );
            $checkStmt->execute([$email]);

            if ($checkStmt->fetch(PDO::FETCH_ASSOC)) {
                $error = 'This email is already registered.';
            } else {
                // Hash password
                $hashedPassword = password_hash(
                    $password,
                    PASSWORD_DEFAULT
                );

                if ($hashedPassword === false) {
                    throw new RuntimeException(
                        'Password hashing failed.'
                    );
                }

                // Save both records together
                $pdo->beginTransaction();

                // Insert user account
                $userSql = "
                    INSERT INTO users
                    (
                        full_name,
                        email,
                        phone,
                        address,
                        password_hash,
                        role,
                        status
                    )
                    VALUES (?, ?, ?, ?, ?, 'landlord', 'active')
                ";

                $userStmt = $pdo->prepare($userSql);
                $userStmt->execute([
                    $full_name,
                    $email,
                    $phone,
                    $address !== '' ? $address : null,
                    $hashedPassword
                ]);

                // Get newly created user ID
                $user_id = (int) $pdo->lastInsertId();

                // Create landlord profile
                // Standard subscription is assigned by default.
                $landlordSql = "
                    INSERT INTO landlords
                    (
                        user_id,
                        mobile,
                        address,
                        subsc_tier
                    )
                    VALUES (?, ?, ?, 'standard')
                ";

                $landlordStmt = $pdo->prepare($landlordSql);
                $landlordStmt->execute([
                    $user_id,
                    $phone,
                    $address !== '' ? $address : null
                ]);

                // Both inserts succeeded
                $pdo->commit();

                $success = 'Registration successful! You can now log in.';

                // Clear form values
                $full_name = '';
                $email = '';
                $phone = '';
                $address = '';

                // Generate a new CSRF token
                $_SESSION['csrf_token'] = bin2hex(
                    random_bytes(32)
                );
            }
        } catch (PDOException $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            // Handle duplicate email safely
            if ($e->getCode() === '23000') {
                $error = 'This email is already registered.';
            } else {
                error_log(
                    'Landlord registration error: ' .
                    $e->getMessage()
                );
                $error = 'Registration failed. Please try again later.';
            }
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            error_log(
                'Landlord registration error: ' .
                $e->getMessage()
            );
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

    <title>Landlord Registration — BoardNest</title>

    <link rel="stylesheet"
          href="../../public/assets/css/landlord.css?v=3">

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            background: #f7f1e9;
            color: #39291f;
            font-family: Arial, sans-serif;
        }

        .register-card {
            width: calc(100% - 30px);
            max-width: 480px;
            margin: 40px auto;
            padding: 30px;
            background: #fffaf3;
            border: 1px solid #e8d9c8;
            border-radius: 16px;
            box-shadow: 0 8px 25px rgba(74, 48, 30, 0.10);
        }

        .register-card h2 {
            margin: 0 0 10px;
            color: #593d2b;
            font-size: 26px;
            text-align: center;
        }

        .register-description {
            color: #806e5e;
            font-size: 14px;
            line-height: 1.6;
            text-align: center;
            margin-bottom: 25px;
        }

        .form-group {
            margin-bottom: 18px;
        }

        .form-group label {
            display: block;
            margin-bottom: 7px;
            color: #593d2b;
            font-weight: 600;
            font-size: 14px;
        }

        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 12px 14px;
            border: 1px solid #d8c6b3;
            border-radius: 9px;
            background: #fff;
            color: #39291f;
            font-size: 14px;
            outline: none;
            transition: border-color 0.2s,
                        box-shadow 0.2s;
        }

        .form-group input:focus,
        .form-group textarea:focus {
            border-color: #79543a;
            box-shadow: 0 0 0 3px rgba(121, 84, 58, 0.12);
        }

        .form-group textarea {
            resize: vertical;
        }

        .btn-submit {
            width: 100%;
            padding: 13px;
            background: #65452f;
            color: #fffaf3;
            border: none;
            border-radius: 9px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: background 0.2s;
        }

        .btn-submit:hover {
            background: #49301f;
        }

        .msg-error,
        .msg-success {
            padding: 12px 14px;
            border-radius: 9px;
            margin-bottom: 20px;
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
            color: #806e5e;
        }

        .login-link a,
        .msg-success a {
            color: #65452f;
            font-weight: bold;
            text-decoration: none;
        }

        .login-link a:hover,
        .msg-success a:hover {
            text-decoration: underline;
        }

        @media (max-width: 480px) {
            .register-card {
                margin: 25px auto;
                padding: 22px;
            }

            .register-card h2 {
                font-size: 23px;
            }
        }
    </style>
</head>

<body>

<?php include __DIR__ . '/nav.php'; ?>

<div class="register-card">

    <h2>Landlord Registration</h2>

    <p class="register-description">
        Create an account to list your properties on BoardNest.
    </p>

    <?php if ($error !== ''): ?>
        <div class="msg-error" role="alert">
            <?= htmlspecialchars(
                $error,
                ENT_QUOTES,
                'UTF-8'
            ) ?>
        </div>
    <?php endif; ?>

    <?php if ($success !== ''): ?>

        <div class="msg-success" role="status">
            <?= htmlspecialchars(
                $success,
                ENT_QUOTES,
                'UTF-8'
            ) ?>
            <br><br>
            <a href="../../login.php">Login here</a>
        </div>

    <?php else: ?>

        <form method="POST"
              action=""
              autocomplete="on">

            <input
                type="hidden"
                name="csrf_token"
                value="<?= htmlspecialchars(
                    $_SESSION['csrf_token'],
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>"
            >

            <div class="form-group">
                <label for="full_name">Full Name *</label>
                <input
                    type="text"
                    id="full_name"
                    name="full_name"
                    value="<?= htmlspecialchars(
                        $full_name,
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?>"
                    maxlength="100"
                    required
                    autocomplete="name"
                    placeholder="e.g. Perera A.B."
                >
            </div>

            <div class="form-group">
                <label for="email">Email Address *</label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    value="<?= htmlspecialchars(
                        $email,
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?>"
                    maxlength="255"
                    required
                    autocomplete="email"
                    placeholder="e.g. landlord@gmail.com"
                >
            </div>

            <div class="form-group">
                <label for="phone">Phone Number *</label>
                <input
                    type="tel"
                    id="phone"
                    name="phone"
                    value="<?= htmlspecialchars(
                        $phone,
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?>"
                    maxlength="20"
                    required
                    autocomplete="tel"
                    placeholder="e.g. 0771234567"
                >
            </div>

            <div class="form-group">
                <label for="address">Address</label>
                <textarea
                    id="address"
                    name="address"
                    rows="3"
                    autocomplete="street-address"
                    placeholder="e.g. No. 45, Galle Road, Colombo"
                ><?= htmlspecialchars(
                    $address,
                    ENT_QUOTES,
                    'UTF-8'
                ) ?></textarea>
            </div>

            <div class="form-group">
                <label for="password">Password *</label>
                <input
                    type="password"
                    id="password"
                    name="password"
                    minlength="6"
                    required
                    autocomplete="new-password"
                    placeholder="Create a password (min 6 characters)"
                >
            </div>

            <button type="submit" class="btn-submit">
                Register as Landlord
            </button>

        </form>

    <?php endif; ?>

    <p class="login-link">
        Already have an account?
        <a href="../../login.php">Login here</a>
    </p>

</div>

</body>
</html>