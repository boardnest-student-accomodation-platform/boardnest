<?php
require_once '../../includes/session.php';
requireRole('landlord');
require_once '../../config/db.php';

$landlord_id = $_SESSION['user_id'];
$property_id = $_GET['id'] ?? null;

if (!$property_id) {
    header("Location: dashboard.php");
    exit();
}

// old Data Fetch 
$stmt = $pdo->prepare("
    SELECT p.*, r.room_type, r.price, r.slot_capacity 
    FROM properties p 
    LEFT JOIN rooms r ON p.property_id = r.property_id 
    WHERE p.property_id = ? AND p.landlord_id = ?
");
$stmt->execute([$property_id, $landlord_id]);
$data = $stmt->fetch();

if (!$data) {
    die("Property not found or unauthorized access.");
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $address       = trim($_POST['address']);
    $city_id       = $_POST['city_id'];
    $maps_url      = trim($_POST['maps_url']);
    $latitude      = trim($_POST['latitude']);
    $longitude     = trim($_POST['longitude']);
    $room_type     = $_POST['room_type'];
    $price         = $_POST['price'];
    $slot_capacity = $_POST['slot_capacity'];

    try {
        $pdo->beginTransaction();

        // 1. Update Property
        $stmt1 = $pdo->prepare("UPDATE properties SET city_id = ?, address = ?, maps_url = ?, latitude = ?, longitude = ? WHERE property_id = ? AND landlord_id = ?");
        $stmt1->execute([$city_id, $address, $maps_url, $latitude, $longitude, $property_id, $landlord_id]);

        // 2. Update Room
        $stmt2 = $pdo->prepare("UPDATE rooms SET room_type = ?, price = ?, slot_capacity = ? WHERE property_id = ?");
        $stmt2->execute([$room_type, $price, $slot_capacity, $property_id]);

        $pdo->commit();
        header("Location: dashboard.php?status=updated");
        exit();

    } catch (PDOException $e) {
        $pdo->rollBack();
        $message = '<p style="color:red;">Update error: ' . $e->getMessage() . '</p>';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Property - BoardNest</title>
    <link rel="stylesheet" href="../../public/assets/css/style.css">
</head>
<body>
<div style="max-width: 600px; margin: 40px auto; padding: 20px; border: 1px solid #ccc; border-radius: 8px;">
    <h2>Edit Property Details</h2>
    <?= $message ?>
    <form method="POST">
        <label>City ID *</label>
        <input type="number" name="city_id" value="<?= htmlspecialchars($data['city_id']) ?>" required style="width:100%; margin-bottom:10px; padding:8px;">
        
        <label>Full Address *</label>
        <textarea name="address" required style="width:100%; margin-bottom:10px; padding:8px;"><?= htmlspecialchars($data['address']) ?></textarea>
        
        <label>Google Maps URL</label>
        <input type="text" name="maps_url" value="<?= htmlspecialchars($data['maps_url']) ?>" style="width:100%; margin-bottom:10px; padding:8px;">

        <div style="display:flex; gap:10px;">
            <div style="flex:1;">
                <label>Latitude</label>
                <input type="text" name="latitude" value="<?= htmlspecialchars($data['latitude']) ?>" style="width:100%; padding:8px;">
            </div>
            <div style="flex:1;">
                <label>Longitude</label>
                <input type="text" name="longitude" value="<?= htmlspecialchars($data['longitude']) ?>" style="width:100%; padding:8px;">
            </div>
        </div>

        <h3 style="margin-top:15px;">Room Details</h3>
        <label>Room Type</label>
        <select name="room_type" style="width:100%; margin-bottom:10px; padding:8px;">
            <option value="single" <?= $data['room_type'] === 'single' ? 'selected' : '' ?>>Single Room</option>
            <option value="shared" <?= $data['room_type'] === 'shared' ? 'selected' : '' ?>>Shared Room</option>
        </select>

        <label>Monthly Rent (LKR)</label>
        <input type="number" name="price" value="<?= htmlspecialchars($data['price']) ?>" required style="width:100%; margin-bottom:10px; padding:8px;">

        <label>Slot Capacity</label>
        <input type="number" name="slot_capacity" value="<?= htmlspecialchars($data['slot_capacity']) ?>" required style="width:100%; margin-bottom:15px; padding:8px;">

        <button type="submit" style="background:#007bff; color:#fff; padding:10px 20px; border:none; border-radius:4px; cursor:pointer;">Update Property</button>
        <a href="dashboard.php" style="margin-left:10px; text-decoration:none; color:#666;">Cancel</a>
    </form>
</div>
</body>
</html>