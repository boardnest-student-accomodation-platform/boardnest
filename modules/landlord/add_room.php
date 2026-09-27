
<?php
require_once __DIR__ . '/../../includes/session.php';
startSession();

if (
    !isset($_SESSION['user_id']) ||
    ($_SESSION['role'] ?? '') !== 'landlord'
) {
    header('Location: ../../login.php');
    exit();
}

require_once __DIR__ . '/../../config/db.php';

$user_id = (int) $_SESSION['user_id'];
$property_id = filter_input(INPUT_GET, 'property_id', FILTER_VALIDATE_INT);

$error = '';

try {
    // Find the logged-in landlord
    $landlordStmt = $pdo->prepare("
        SELECT landlord_id, subsc_tier, subsc_expires
        FROM landlords
        WHERE user_id = ?
        LIMIT 1
    ");
    $landlordStmt->execute([$user_id]);
    $landlord = $landlordStmt->fetch(PDO::FETCH_ASSOC);

    if (!$landlord) {
        throw new RuntimeException('Landlord account not found.');
    }

    $landlord_id = (int) $landlord['landlord_id'];

    // Check whether Pro subscription is active
    $isPro = (
        strtolower($landlord['subsc_tier'] ?? '') === 'pro'
        && (
            empty($landlord['subsc_expires'])
            || $landlord['subsc_expires'] >= date('Y-m-d')
        )
    );

    // Verify property ownership
    $propertyStmt = $pdo->prepare("
        SELECT property_id
        FROM properties
        WHERE property_id = ?
          AND landlord_id = ?
        LIMIT 1
    ");
    $propertyStmt->execute([$property_id, $landlord_id]);

    if (!$property_id || !$propertyStmt->fetch()) {
        throw new RuntimeException('Invalid property or unauthorized access.');
    }

    // Count rooms belonging to this property
    $countStmt = $pdo->prepare("
        SELECT COUNT(*)
        FROM rooms
        WHERE property_id = ?
    ");
    $countStmt->execute([$property_id]);
    $roomCount = (int) $countStmt->fetchColumn();

    // Enforce Free plan limit
    if (!$isPro && $roomCount >= 4) {
        $error = 'Free plan allows a maximum of 4 rooms per property. Upgrade to Pro to add more rooms.';
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$error) {
        $room_type = $_POST['room_type'] ?? '';
        $price = filter_input(INPUT_POST, 'price', FILTER_VALIDATE_FLOAT);
        $slot_capacity = filter_input(
            INPUT_POST,
            'slot_capacity',
            FILTER_VALIDATE_INT
        );

        if (
            !in_array($room_type, ['single', 'shared'], true) ||
            $price === false ||
            $price === null ||
            $price < 0 ||
            $slot_capacity === false ||
            $slot_capacity === null ||
            $slot_capacity < 1
        ) {
            $error = 'Please enter valid room details.';
        } else {
            // Recheck the limit immediately before inserting
            $pdo->beginTransaction();

            try {
                $lockStmt = $pdo->prepare("
                    SELECT property_id
                    FROM properties
                    WHERE property_id = ?
                      AND landlord_id = ?
                    FOR UPDATE
                ");
                $lockStmt->execute([$property_id, $landlord_id]);

                if (!$lockStmt->fetch()) {
                    throw new RuntimeException('Unauthorized property.');
                }

                $countStmt = $pdo->prepare("
                    SELECT COUNT(*)
                    FROM rooms
                    WHERE property_id = ?
                ");
                $countStmt->execute([$property_id]);
                $roomCount = (int) $countStmt->fetchColumn();

                if (!$isPro && $roomCount >= 4) {
                    throw new RuntimeException(
                        'Free plan allows a maximum of 4 rooms.'
                    );
                }

                $insertStmt = $pdo->prepare("
                    INSERT INTO rooms
                        (property_id, room_type, price, slot_capacity, status)
                    VALUES (?, ?, ?, ?, 'pending')
                ");

                $insertStmt->execute([
                    $property_id,
                    $room_type,
                    $price,
                    $slot_capacity
                ]);

                $pdo->commit();

                header('Location: dashboard.php?success=room_added');
                exit();

            } catch (Throwable $e) {
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }

                if ($e instanceof PDOException) {
                    error_log('Add room error: ' . $e->getMessage());
                    $error = 'Unable to add room. Please try again.';
                } else {
                    $error = $e->getMessage();
                }
            }
        }
    }

} catch (Throwable $e) {
    if ($e instanceof PDOException) {
        error_log('Room page error: ' . $e->getMessage());
        $error = 'Unable to load room details.';
    } else {
        $error = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Add Room — BoardNest</title>
    <link rel="stylesheet" href="../../public/assets/css/landlord.css?v=2">
</head>
<body>
    <?php include __DIR__ . '/nav.php'; ?>
<div class="form-card">
    <div class="page-header">
        <h2>Add Room</h2>
        <a href="dashboard.php" class="btn-back">← Dashboard</a>
    </div>

    <?php if ($error): ?>
        <div class="alert-error">
            <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
        </div>
    <?php endif; ?>

    <?php if (!$error || $_SERVER['REQUEST_METHOD'] !== 'POST'): ?>
        <?php if ($roomCount >= 4 && !$isPro): ?>
            <p>
                You have reached the Free plan room limit.
                <a href="upgrade.php">Upgrade to Pro</a>
            </p>
        <?php else: ?>
            <p>
                Rooms added: <?= (int) $roomCount ?>
                <?php if (!$isPro): ?>
                    / 4
                <?php else: ?>
                    / Unlimited
                <?php endif; ?>
            </p>

            <form method="POST"
                  action="add_room.php?property_id=<?= (int) $property_id ?>">
                <div class="form-group">
                    <label for="room_type">Room Type *</label>
                    <select id="room_type" name="room_type" required>
                        <option value="single">Single</option>
                        <option value="shared">Shared</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="price">Price (LKR) *</label>
                    <input
                        type="number"
                        id="price"
                        name="price"
                        min="0"
                        step="0.01"
                        required
                        placeholder="e.g. 12000.00"
                    >
                </div>

                <div class="form-group">
                    <label for="slot_capacity">Slot Capacity</label>
                    <input
                        type="number"
                        id="slot_capacity"
                        name="slot_capacity"
                        value="1"
                        min="1"
                        required
                    >
                </div>

                <button type="submit" class="btn btn-success btn-block">
                    Add Room
                </button>
            </form>
        <?php endif; ?>
    <?php endif; ?>
</div>
</body>
</html>