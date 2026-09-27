
<?php
require_once __DIR__ . '/../../includes/session.php';
startSession();

if (
    !isset($_SESSION['user_id']) ||
    !isset($_SESSION['role']) ||
    $_SESSION['role'] !== 'landlord'
) {
    header('Location: ../../login.php');
    exit();
}

require_once __DIR__ . '/../../config/db.php';

// Generate CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$csrf_token = $_SESSION['csrf_token'];
$user_id = $_SESSION['user_id'];

// Get flash messages from property deletion
$delete_success = $_SESSION['delete_property_success'] ?? '';
$delete_error = $_SESSION['delete_property_error'] ?? '';

unset(
    $_SESSION['delete_property_success'],
    $_SESSION['delete_property_error']
);

// Get landlord ID
$landlord_id = null;
$properties = [];

try {
    $lStmt = $pdo->prepare(
        "SELECT landlord_id FROM landlords WHERE user_id = ?"
    );
    $lStmt->execute([$user_id]);
    $landlord_id = $lStmt->fetchColumn();

    if ($landlord_id) {
        $stmt = $pdo->prepare(
            "SELECT * FROM properties
             WHERE landlord_id = ?
             ORDER BY created_at DESC"
        );
        $stmt->execute([$landlord_id]);
        $properties = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (PDOException $e) {
    error_log('Dashboard load error: ' . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Landlord Dashboard — BoardNest</title>
    <link rel="stylesheet" href="../../public/assets/css/landlord.css?v=2">
</head>
<body>

<?php include __DIR__ . '/nav.php'; ?>

<div class="landlord-container">

    <div class="page-header">
        <h2>Landlord Dashboard</h2>
    </div>

    <?php if ($delete_success): ?>
        <div class="alert-success" role="status">
            <?= htmlspecialchars($delete_success, ENT_QUOTES, 'UTF-8') ?>
        </div>
    <?php endif; ?>

    <?php if ($delete_error): ?>
        <div class="alert-error" role="alert">
            <?= htmlspecialchars($delete_error, ENT_QUOTES, 'UTF-8') ?>
        </div>
    <?php endif; ?>

    <?php if (empty($landlord_id)): ?>

        <div class="property-card">
            <p>Landlord profile not found. Please contact the administrator.</p>
        </div>

    <?php elseif (empty($properties)): ?>

        <div class="property-card">
            <p>
                No properties registered yet.
                Click "Add Property" in the navigation bar to get started.
            </p>
        </div>

    <?php else: ?>

        <?php foreach ($properties as $prop): ?>

            <?php
            // Fetch rooms for this property
            $rooms = [];

            try {
                $roomsStmt = $pdo->prepare(
                    "SELECT * FROM rooms WHERE property_id = ?"
                );
                $roomsStmt->execute([$prop['property_id']]);
                $rooms = $roomsStmt->fetchAll(PDO::FETCH_ASSOC);
            } catch (PDOException $e) {
                error_log('Dashboard rooms error: ' . $e->getMessage());
            }
            ?>

            <div class="property-card">

                <div class="property-title-row">

                    <div>
                        <h3>
                            <?= htmlspecialchars(
                                $prop['title'] ?? '',
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>
                        </h3>

                        <span class="badge badge-<?= htmlspecialchars(
                            $prop['status'] ?? 'pending',
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>">
                            <?= htmlspecialchars(
                                ucfirst($prop['status'] ?? 'pending'),
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>
                        </span>
                    </div>

                    <div class="action-buttons">

                        <a
                            href="checklist.php?property_id=<?= (int)$prop['property_id'] ?>"
                            class="btn btn-secondary"
                        >
                            Checklist
                        </a>

                        <a
                            href="add_room.php?property_id=<?= (int)$prop['property_id'] ?>"
                            class="btn btn-success"
                        >
                            + Add Room
                        </a>

                        <a
                            href="edit_property.php?id=<?= (int)$prop['property_id'] ?>"
                            class="btn btn-warning"
                        >
                            Edit
                        </a>

                        <!-- Secure property deletion -->
                        <form
                            method="POST"
                            action="delete_property.php"
                            onsubmit="return confirm('Are you sure you want to delete this property? This action cannot be undone.');"
                            style="display: inline;"
                        >
                            <input
                                type="hidden"
                                name="id"
                                value="<?= (int)$prop['property_id'] ?>"
                            >

                            <input
                                type="hidden"
                                name="csrf_token"
                                value="<?= htmlspecialchars(
                                    $csrf_token,
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>"
                            >

                            <button type="submit" class="btn btn-danger">
                                Delete
                            </button>
                        </form>

                    </div>
                </div>

                <p>
                    <strong>Address:</strong>
                    <?= htmlspecialchars(
                        $prop['address'] ?? '',
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?>
                </p>

                <?php if (!empty($prop['rent_amount'])): ?>
                    <p>
                        <strong>Base Rent:</strong>
                        LKR <?= number_format(
                            (float)$prop['rent_amount'],
                            2
                        ) ?>
                    </p>
                <?php endif; ?>

                <h4>Rooms</h4>

                <?php if (empty($rooms)): ?>

                    <p>No rooms added to this property yet.</p>

                <?php else: ?>

                    <table class="custom-table">
                        <thead>
                            <tr>
                                <th>Room ID</th>
                                <th>Type</th>
                                <th>Price</th>
                                <th>Slot Capacity</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php foreach ($rooms as $room): ?>

                                <tr>
                                    <td>
                                        #<?= (int)$room['room_id'] ?>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars(
                                            ucfirst($room['room_type'] ?? ''),
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>
                                    </td>

                                    <td>
                                        LKR <?= number_format(
                                            (float)($room['price'] ?? 0),
                                            2
                                        ) ?>
                                    </td>

                                    <td>
                                        <?= (int)($room['slot_capacity'] ?? 0) ?>
                                    </td>

                                    <td>
                                        <span class="badge badge-<?= htmlspecialchars(
                                            $room['status'] ?? 'pending',
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>">
                                            <?= htmlspecialchars(
                                                ucfirst($room['status'] ?? 'pending'),
                                                ENT_QUOTES,
                                                'UTF-8'
                                            ) ?>
                                        </span>
                                    </td>

                                    <td>
                                        <div class="action-buttons">

                                            <form
                                                method="POST"
                                                action="delete_room.php"
                                                onsubmit="return confirm('Delete this room?');"
                                                style="display: inline;"
                                            >
                                                <input
                                                    type="hidden"
                                                    name="id"
                                                    value="<?= (int)$room['room_id'] ?>"
                                                >

                                                <input
                                                    type="hidden"
                                                    name="csrf_token"
                                                    value="<?= htmlspecialchars(
                                                        $csrf_token,
                                                        ENT_QUOTES,
                                                        'UTF-8'
                                                    ) ?>"
                                                >

                                                <button
                                                    type="submit"
                                                    class="btn btn-danger"
                                                >
                                                    Delete
                                                </button>
                                            </form>

                                        </div>
                                    </td>
                                </tr>

                            <?php endforeach; ?>
                        </tbody>
                    </table>

                <?php endif; ?>

            </div>

        <?php endforeach; ?>

    <?php endif; ?>

</div>

</body>
</html>