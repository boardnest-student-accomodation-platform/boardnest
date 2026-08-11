<?php
require_once '../../includes/session.php';
requireRole('landlord');
require_once '../../config/db.php';

$landlord_id = $_SESSION['user_id'] ?? null;
$property_id = $_GET['property_id'] ?? null;

if (!$property_id) {
    header("Location: dashboard.php");
    exit();
}

// 1. Property has Rooms  Count 
$countStmt = $pdo->prepare("
    SELECT COUNT(*) FROM rooms r 
    JOIN properties p ON r.property_id = p.property_id 
    WHERE r.property_id = ? AND p.landlord_id = ?
");
$countStmt->execute([$property_id, $landlord_id]);
$roomCount = $countStmt->fetchColumn();

// rooms up to 4 cancel
if ($roomCount >= 4) {
    die("<h3 style='color:red;'>Free Version Limit Reached! You can only add up to 4 rooms per property.</h3><a href='dashboard.php'>Back to Dashboard</a>");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Room - BoardNest</title>
    <link rel="stylesheet" href="/boardnest/public/assets/css/style.css">
</head>
<body>
<div style="max-width: 500px; margin: 40px auto; padding: 20px; border: 1px solid #ccc; border-radius: 8px;">
    <h2>Add New Room (Room <?= $roomCount + 1 ?> of 4)</h2>
    <p style="color: #666; font-size: 14px;">Free tier limit: Maximum 4 rooms allowed per property.</p>

    <!-- Form  submit to actions/save_room.php-->
    <form action="actions/save_room.php" method="POST">
        <!-- Property ID as Hidden Input -->
        <input type="hidden" name="property_id" value="<?= htmlspecialchars($property_id) ?>">

        <label>Room Type</label>
        <select name="room_type" style="width:100%; margin-bottom:10px; padding:8px;">
            <option value="single">Single Room</option>
            <option value="shared">Shared Room</option>
        </select>

        <label>Monthly Rent (LKR)</label>
        <input type="number" name="price" required style="width:100%; margin-bottom:10px; padding:8px;">

        <label>Slot Capacity (Beds/Spaces)</label>
        <input type="number" name="slot_capacity" value="1" required style="width:100%; margin-bottom:15px; padding:8px;">

        <button type="submit" style="background:#28a745; color:#fff; padding:10px 20px; border:none; border-radius:4px; cursor:pointer;">+ Save Room</button>
        <a href="dashboard.php" style="margin-left:10px; text-decoration:none; color:#666;">Cancel</a>
    </form>
</div>
</body>
</html>