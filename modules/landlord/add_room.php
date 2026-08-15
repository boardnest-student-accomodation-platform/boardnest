<?php
require_once __DIR__ . '/../../includes/session.php';
startSession();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'landlord') {
    header('Location: ../../login.php');
    exit();
}

require_once __DIR__ . '/../../config/db.php';

$user_id     = $_SESSION['user_id'];
$property_id = filter_input(INPUT_GET, 'property_id', FILTER_VALIDATE_INT) ?? filter_input(INPUT_POST, 'property_id', FILTER_VALIDATE_INT);
$error       = '';
$success     = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $room_type     = $_POST['room_type'] ?? '';
    $price         = trim($_POST['price'] ?? '');
    $slot_capacity = filter_input(INPUT_POST, 'slot_capacity', FILTER_VALIDATE_INT) ?: 1;

    $allowed_types = ['single', 'shared'];

    if (!$property_id || !in_array($room_type, $allowed_types, true) || !is_numeric($price)) {
        $error = "Please provide valid room details.";
    } else {
        try {
            // Ownership Verification
            $checkStmt = $pdo->prepare("
                SELECT p.property_id FROM properties p 
                JOIN landlords l ON p.landlord_id = l.landlord_id 
                WHERE p.property_id = ? AND l.user_id = ?
            ");
            $checkStmt->execute([$property_id, $user_id]);

            if ($checkStmt->fetch()) {
                $stmt = $pdo->prepare("
                    INSERT INTO rooms (property_id, room_type, price, slot_capacity, status) 
                    VALUES (?, ?, ?, ?, 'pending')
                ");
                $stmt->execute([$property_id, $room_type, (float)$price, $slot_capacity]);

                header('Location: dashboard.php?success=room_added');
                exit();
            } else {
                $error = "Unauthorized action.";
            }
        } catch (PDOException $e) {
            error_log("Add room error: " . $e->getMessage());
            $error = "Failed to add room.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Room — BoardNest</title>
    <link rel="stylesheet" href="../../public/assets/css/landlord.css">
</head>
<body>

<div class="form-card">
    <div class="page-header">
        <h2>Add Room</h2>
        <a href="dashboard.php" class="btn-back">← Dashboard</a>
    </div>

    <?php if ($error): ?>
        <div class="alert-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" action="">
        <input type="hidden" name="property_id" value="<?= htmlspecialchars($property_id) ?>">

        <div class="form-group">
            <label>Room Type *</label>
            <select name="room_type" required>
                <option value="single">Single</option>
                <option value="shared">Shared</option>
            </select>
        </div>

        <div class="form-group">
            <label>Price (LKR) *</label>
            <input type="number" step="0.01" name="price" required placeholder="e.g. 12000.00">
        </div>

        <div class="form-group">
            <label>Slot Capacity</label>
            <input type="number" name="slot_capacity" value="1" min="1">
        </div>

        <button type="submit" class="btn btn-success btn-block">Add Room</button>
    </form>
</div>

</body>
</html>