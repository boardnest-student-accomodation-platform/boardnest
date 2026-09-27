
<?php
require_once __DIR__ . '/../../../includes/session.php';
startSession();

if (
    !isset($_SESSION['user_id']) ||
    ($_SESSION['role'] ?? '') !== 'landlord'
) {
    header('Location: ../../../login.php');
    exit();
}

require_once __DIR__ . '/../../../config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../dashboard.php');
    exit();
}

$user_id = (int) $_SESSION['user_id'];
$property_id = filter_input(INPUT_POST, 'property_id', FILTER_VALIDATE_INT);
$room_type = $_POST['room_type'] ?? '';
$price = filter_input(INPUT_POST, 'price', FILTER_VALIDATE_FLOAT);
$slot_capacity = filter_input(
    INPUT_POST,
    'slot_capacity',
    FILTER_VALIDATE_INT
);

if (
    !$property_id ||
    !in_array($room_type, ['single', 'shared'], true) ||
    $price === false ||
    $price === null ||
    $price < 0 ||
    $slot_capacity === false ||
    $slot_capacity === null ||
    $slot_capacity < 1
) {
    header('Location: ../dashboard.php?error=invalid_room');
    exit();
}

try {
    // Get landlord and subscription
    $stmt = $pdo->prepare("
        SELECT landlord_id, subsc_tier, subsc_expires
        FROM landlords
        WHERE user_id = ?
        LIMIT 1
    ");
    $stmt->execute([$user_id]);
    $landlord = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$landlord) {
        header('Location: ../dashboard.php?error=unauthorized');
        exit();
    }

    $landlord_id = (int) $landlord['landlord_id'];

    $isPro = (
        strtolower($landlord['subsc_tier'] ?? '') === 'pro'
        && (
            empty($landlord['subsc_expires'])
            || $landlord['subsc_expires'] >= date('Y-m-d')
        )
    );

    $pdo->beginTransaction();

    // Verify property ownership and lock property row
    $propertyStmt = $pdo->prepare("
        SELECT property_id
        FROM properties
        WHERE property_id = ?
          AND landlord_id = ?
        FOR UPDATE
    ");
    $propertyStmt->execute([$property_id, $landlord_id]);

    if (!$propertyStmt->fetch()) {
        $pdo->rollBack();
        header('Location: ../dashboard.php?error=unauthorized');
        exit();
    }

    // Enforce Free plan room limit
    $countStmt = $pdo->prepare("
        SELECT COUNT(*)
        FROM rooms
        WHERE property_id = ?
    ");
    $countStmt->execute([$property_id]);
    $room_count = (int) $countStmt->fetchColumn();

    if (!$isPro && $room_count >= 4) {
        $pdo->rollBack();
        header('Location: ../dashboard.php?error=room_limit');
        exit();
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

    header('Location: ../dashboard.php?success=room_added');
    exit();

} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    error_log('Save room error: ' . $e->getMessage());

    header('Location: ../dashboard.php?error=save_failed');
    exit();
}