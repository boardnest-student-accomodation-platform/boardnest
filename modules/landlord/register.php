<?php
// get Root folder session.php 
require_once __DIR__ . '/../../includes/session.php';
startSession();

//if User Logged Dashboard Direct 
if (isset($_SESSION['user_id'])) {
    if (isset($_SESSION['role']) && $_SESSION['role'] === 'landlord') {
        header('Location: dashboard.php');
        exit();
    }
}

$error = '';
$success = '';

$full_name = '';
$email     = '';
$phone     = '';
$address   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // get Root folder db.php 
    require_once __DIR__ . '/../../config/db.php';

    $full_name = trim($_POST['full_name'] ?? '');
    $email     = trim($_POST['email'] ?? '');
    $phone     = trim($_POST['phone'] ?? '');
    $address   = trim($_POST['address'] ?? '');
    $password  = $_POST['password'] ?? '';

    // Validation
    if (empty($full_name) || empty($email) || empty($phone) || empty($password)) {
        $error = "Please fill in all required fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters long.";
    } else {
        try {
            $checkStmt = $pdo->prepare("SELECT user_id FROM users WHERE email = ?");
            $checkStmt->execute([$email]);

            if ($checkStmt->fetch()) {
                $error = "This email is already registered.";
            } else {
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

                $sql = "INSERT INTO users (full_name, email, phone, address, password_hash, role, status) 
                        VALUES (?, ?, ?, ?, ?, 'landlord', 'active')";
                
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$full_name, $email, $phone, $address, $hashedPassword]);

                $success = "Registration successful! You can now <a href='../../login.php'>Login here</a>.";
                
                $full_name = $email = $phone = $address = '';
            }
        } catch (PDOException $e) {
            $error = "Registration failed due to a system error. Please try again later.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Landlord Registration — BoardNest</title>
    <link rel="stylesheet" href="../../public/assets/css/style.css">
    <style>
        .register-card {
            max-width: 480px;
            margin: 40px auto;
            padding: 25px;
            background: #fff;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.05);
            font-family: Arial, sans-serif;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
        }
        .form-group input, .form-group textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }
        .btn-submit {
            width: 100%;
            padding: 12px;
            background: #28a745;
            color: #fff;
            border: none;
            border-radius: 4px;
            font-weight: bold;
            font-size: 16px;
            cursor: pointer;
        }
        .msg-error {
            color: #721c24;
            background: #f8d7da;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 15px;
        }
        .msg-success {
            color: #155724;
            background: #d4edda;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>

<div class="register-card">
    <h2>Landlord Registration</h2>
    <p style="color: #666; font-size: 14px; margin-bottom: 20px;">Create an account to list your properties on BoardNest.</p>

    <?php if (!empty($error)): ?>
        <div class="msg-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <?php if (!empty($success)): ?>
        <div class="msg-success"><?= $success ?></div>
    <?php else: ?>
        <form method="POST" action="">
            <div class="form-group">
                <label>Full Name *</label>
                <input type="text" name="full_name" value="<?= htmlspecialchars($full_name) ?>" required placeholder="e.g. Perera A.B.">
            </div>

            <div class="form-group">
                <label>Email Address *</label>
                <input type="email" name="email" value="<?= htmlspecialchars($email) ?>" required placeholder="e.g. landlord@gmail.com">
            </div>

            <div class="form-group">
                <label>Phone Number *</label>
                <input type="text" name="phone" value="<?= htmlspecialchars($phone) ?>" required placeholder="e.g. 0771234567">
            </div>

            <div class="form-group">
                <label>Address</label>
                <textarea name="address" rows="2" placeholder="e.g. No. 45, Galle Road, Colombo"><?= htmlspecialchars($address) ?></textarea>
            </div>

            <div class="form-group">
                <label>Password *</label>
                <input type="password" name="password" required placeholder="Create a password (min 6 characters)">
            </div>

            <button type="submit" class="btn-submit">Register as Landlord</button>
        </form>
    <?php endif; ?>

    <p style="margin-top: 20px; text-align: center;">
        Already have an account? <a href="../../login.php">Login here</a>
    </p>
</div>

</body>
</html>