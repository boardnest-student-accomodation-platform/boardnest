<?php
require_once '../../includes/session.php';
requireRole('landlord');
require_once '../../config/db.php';

$landlord_id = $_SESSION['user_id'];

// get Landlord's all Properties 
$pStmt = $pdo->prepare("SELECT * FROM properties WHERE landlord_id = ?");
$pStmt->execute([$landlord_id]);
$properties = $pStmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Landlord Dashboard — BoardNest</title>
    <link rel="stylesheet" href="/boardnest/public/assets/css/style.css">
    <style>
        .property-box {
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 25px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }
        .property-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid #f0f0f0;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }
        .room-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        .room-table th, .room-table td {
            border: 1px solid #eee;
            padding: 8px 12px;
            text-align: left;
        }
        .btn-sm {
            padding: 4px 8px;
            font-size: 12px;
            text-decoration: none;
            border-radius: 4px;
        }
    </style>
</head>
<body>

<div class="container mt-8">
    <div class="section-header" style="display:flex; justify-content:space-between; align-items:center;">
        <div>
            <h2>Landlord Dashboard</h2>
            <p class="section-subtitle">Welcome, <?= htmlspecialchars($_SESSION['full_name'] ?? 'Landlord') ?>!</p>
        </div>
        <a href="add_property.php" class="btn btn--primary">+ Add New Property</a>
    </div>

    <div class="mt-6">
        <?php if (empty($properties)): ?>
            <p class="text-muted">No properties registered yet. Click "+ Add New Property" to start.</p>
        <?php else: ?>
            <?php foreach ($properties as $property): 
                // Property's Rooms and info Fetch 
                $rStmt = $pdo->prepare("SELECT * FROM rooms WHERE property_id = ?");
                $rStmt->execute([$property['property_id']]);
                $rooms = $rStmt->fetchAll();
                $roomCount = count($rooms);
            ?>
                <div class="property-box">
                    <div class="property-header">
                        <div>
                            <h3 style="margin:0; font-size:18px;"><?= htmlspecialchars($property['address']) ?></h3>
                            <span style="font-size:12px; color:#666;">Rooms Added: <strong><?= $roomCount ?> / 4 (Free Limit)</strong></span>
                        </div>
                        <div>
                            <?php if ($roomCount < 4): ?>
                                <a href="add_room.php?property_id=<?= $property['property_id'] ?>" style="background:#28a745; color:#fff; padding:6px 12px; text-decoration:none; border-radius:4px; font-size:13px; font-weight:bold;">+ Add Room</a>
                            <?php else: ?>
                                <span style="background:#6c757d; color:#fff; padding:6px 12px; border-radius:4px; font-size:12px;">Room Limit Reached (4/4)</span>
                            <?php endif; ?>

                            <a href="edit_property.php?id=<?= $property['property_id'] ?>" style="background:#ffc107; color:#000; padding:6px 12px; text-decoration:none; border-radius:4px; font-size:13px; margin-left:5px;">Edit Property</a>

                            <a href="delete_property.php?id=<?= $property['property_id'] ?>" onclick="return confirm('Delete whole property and all its rooms?');" style="background:#dc3545; color:#fff; padding:6px 12px; text-decoration:none; border-radius:4px; font-size:13px; margin-left:5px;">Delete Property</a>
                        </div>
                    </div>

                    <!-- Rooms Table -->
                    <h4>Rooms List</h4>
                    <?php if (empty($rooms)): ?>
                        <p style="font-size:13px; color:#888;">No rooms added to this property yet.</p>
                    <?php else: ?>
                        <table class="room-table">
                            <thead>
                                <tr style="background:#f9f9f9;">
                                    <th>Room Type</th>
                                    <th>Price (LKR)</th>
                                    <th>Slot Capacity</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($rooms as $room): ?>
                                    <tr>
                                        <td><?= ucfirst(htmlspecialchars($room['room_type'])) ?></td>
                                        <td>LKR <?= number_format($room['price']) ?></td>
                                        <td><?= $room['slot_capacity'] ?> Slots</td>
                                        <td><span class="badge"><?= ucfirst($room['status']) ?></span></td>
                                        <td>
                                            <a href="delete_room.php?room_id=<?= $room['room_id'] ?>" onclick="return confirm('Are you sure you want to delete this room only?');" class="btn-sm" style="background:#dc3545; color:#fff;">Delete Room</a>
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
</div>

</body>
</html>