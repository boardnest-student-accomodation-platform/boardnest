<?php
require_once __DIR__ . '/../../includes/session.php';
startSession();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'landlord') {
    header('Location: ../../login.php');
    exit();
}

require_once __DIR__ . '/../../config/db.php';

$user_id = $_SESSION['user_id'];

// Get landlord_id from landlords table
$landlord_id = null;
try {
    $lStmt = $pdo->prepare("SELECT landlord_id FROM landlords WHERE user_id = ?");
    $lStmt->execute([$user_id]);
    $landlord = $lStmt->fetch();
    $landlord_id = $landlord['landlord_id'] ?? null;
} catch (PDOException $e) {
    error_log("Dashboard landlord fetch error: " . $e->getMessage());
}

$properties = [];
if ($landlord_id) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM properties WHERE landlord_id = ? ORDER BY created_at DESC");
        $stmt->execute([$landlord_id]);
        $properties = $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Dashboard properties error: " . $e->getMessage());
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Landlord Dashboard — BoardNest</title>
    <link rel="stylesheet" href="../../public/assets/css/landlord.css">
</head>
<body>

<div class="landlord-container">
    <div class="page-header">
        <h2>Landlord Dashboard</h2>
        <div class="action-buttons">
            <a href="add_property.php" class="btn btn-primary">+ Add New Property</a>
            <a href="task_view.php" class="btn btn-warning">View Tasks</a>
        </div>
    </div>

    <?php if (empty($properties)): ?>
        <div class="property-card">
            <p>No properties registered yet. Click "Add New Property" to get started.</p>
        </div>
    <?php else: ?>
        <?php foreach ($properties as $prop): ?>
            <?php
            // Fetch rooms for each property
            $roomsStmt = $pdo->prepare("SELECT * FROM rooms WHERE property_id = ?");
            $roomsStmt->execute([$prop['property_id']]);
            $rooms = $roomsStmt->fetchAll();
            ?>
            <div class="property-card">
                <div class="property-title-row">
                    <div>
                        <h3><?= htmlspecialchars($prop['title']) ?></h3>
                        <span class="badge badge-<?= htmlspecialchars($prop['status'] ?? 'pending') ?>">
                            <?= htmlspecialchars(ucfirst($prop['status'] ?? 'pending')) ?>
                        </span>
                    </div>
                    <div class="action-buttons">
                        <a href="checklist.php?property_id=<?= $prop['property_id'] ?>" class="btn btn-secondary">Checklist</a>
                        <a href="add_room.php?property_id=<?= $prop['property_id'] ?>" class="btn btn-success">+ Add Room</a>
                        <a href="edit_property.php?id=<?= $prop['property_id'] ?>" class="btn btn-warning">Edit</a>
                        <a href="delete_property.php?id=<?= $prop['property_id'] ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this property?');">Delete</a>
                    </div>
                </div>

                <p><strong>Address:</strong> <?= htmlspecialchars($prop['address']) ?></p>
                <?php if (!empty($prop['rent_amount'])): ?>
                    <p><strong>Base Rent:</strong> LKR <?= number_format($prop['rent_amount'], 2) ?></p>
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
                                    <td>#<?= htmlspecialchars($room['room_id']) ?></td>
                                    <td><?= htmlspecialchars(ucfirst($room['room_type'])) ?></td>
                                    <td>LKR <?= number_format($room['price'], 2) ?></td>
                                    <td><?= htmlspecialchars($room['slot_capacity']) ?></td>
                                    <td>
                                        <span class="badge badge-<?= htmlspecialchars($room['status']) ?>">
                                            <?= htmlspecialchars(ucfirst($room['status'])) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            <a href="delete_room.php?id=<?= $room['room_id'] ?>" class="btn btn-danger" onclick="return confirm('Delete this room?');">Delete</a>
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