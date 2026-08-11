<?php
require_once __DIR__ . '/../../includes/session.php';
startSession();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'landlord') {
    header('Location: ../../login.php');
    exit();
}

require_once __DIR__ . '/../../config/db.php';

$landlord_id = $_SESSION['user_id'];
$success = '';
$error = '';

// get Landlord Properties 
$propStmt = $pdo->prepare("SELECT property_id, title FROM properties WHERE landlord_id = ?");
$propStmt->execute([$landlord_id]);
$properties = $propStmt->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $property_id = $_POST['property_id'] ?? '';
    $checklist_items = $_POST['items'] ?? [];
    $notes = trim($_POST['notes'] ?? '');

    if (empty($property_id)) {
        $error = "Please select a property.";
    } else {
        try {
            $items_json = json_encode($checklist_items);

            $sql = "INSERT INTO property_checklists (property_id, landlord_id, items, notes, created_at) 
                    VALUES (?, ?, ?, ?, NOW())";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$property_id, $landlord_id, $items_json, $notes]);

            $success = "Checklist saved successfully!";
        } catch (PDOException $e) {
            $error = "Failed to save checklist: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Property Inspection Checklist — BoardNest</title>
    <link rel="stylesheet" href="../../public/assets/css/style.css">
    <style>
        .checklist-card {
            max-width: 600px;
            margin: 40px auto;
            padding: 25px;
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-family: Arial, sans-serif;
            box-shadow: 0 4px 10px rgba(0,0,0,0.05);
        }
        .header-action {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }
        .btn-back {
            text-decoration: none;
            color: #007bff;
            font-weight: bold;
            font-size: 14px;
        }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: bold; }
        .form-group select, .form-group textarea { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        .checkbox-group { margin-bottom: 12px; display: flex; align-items: center; gap: 10px; }
        .checkbox-group input { width: 18px; height: 18px; cursor: pointer; }
        .checkbox-group label { font-weight: normal; margin: 0; cursor: pointer; }
        .btn-submit { width: 100%; padding: 12px; background: #28a745; color: #fff; border: none; border-radius: 4px; font-weight: bold; font-size: 16px; cursor: pointer; }
        .msg-error { background: #f8d7da; color: #721c24; padding: 10px; border-radius: 4px; margin-bottom: 15px; }
        .msg-success { background: #d4edda; color: #155724; padding: 12px; border-radius: 4px; margin-bottom: 15px; text-align: center; }
        .btn-dashboard {
            display: inline-block;
            margin-top: 10px;
            padding: 8px 16px;
            background: #007bff;
            color: #fff;
            text-decoration: none;
            border-radius: 4px;
            font-weight: bold;
        }
        .section-title { border-bottom: 2px solid #eee; padding-bottom: 5px; margin: 20px 0 10px 0; font-size: 16px; color: #007bff; }
    </style>
</head>
<body>

<div class="checklist-card">
    <div class="header-action">
        <h2 style="margin: 0;">Property Checklist</h2>
        <!-- Back to Dashboard Link -->
        <a href="dashboard.php" class="btn-back">← Back to Dashboard</a>
    </div>
    
    <p style="color: #666; font-size: 14px; margin-bottom: 20px;">Verify property conditions before handing over to tenants.</p>

    <?php if ($error): ?>
        <div class="msg-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <?php if ($success): ?>
        <!-- afer Save  Message Dashboard Button -->
        <div class="msg-success">
            <p style="margin: 0 0 10px 0; font-weight: bold;"><?= htmlspecialchars($success) ?></p>
            <a href="dashboard.php" class="btn-dashboard">Go to Dashboard</a>
        </div>
    <?php else: ?>
        <form method="POST" action="">
            <div class="form-group">
                <label>Select Property *</label>
                <select name="property_id" required>
                    <option value="">-- Choose Property --</option>
                    <?php foreach ($properties as $prop): ?>
                        <option value="<?= $prop['property_id'] ?>"><?= htmlspecialchars($prop['title']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="section-title">1. Furniture & Basic Amenities</div>
            <div class="checkbox-group">
                <input type="checkbox" name="items[bed_mattress]" id="bed_mattress" value="ok">
                <label for="bed_mattress">Bed & Mattress in Good Condition</label>
            </div>
            <div class="checkbox-group">
                <input type="checkbox" name="items[study_table_chair]" id="study_table_chair" value="ok">
                <label for="study_table_chair">Study Table & Chair Provided</label>
            </div>
            <div class="checkbox-group">
                <input type="checkbox" name="items[wardrobe]" id="wardrobe" value="ok">
                <label for="wardrobe">Wardrobe / Cupboard Usable</label>
            </div>

            <div class="section-title">2. Utilities & Electrical Items</div>
            <div class="checkbox-group">
                <input type="checkbox" name="items[lights_fans]" id="lights_fans" value="ok">
                <label for="lights_fans">Lights & Ceiling Fans Working Properly</label>
            </div>
            <div class="checkbox-group">
                <input type="checkbox" name="items[power_sockets]" id="power_sockets" value="ok">
                <label for="power_sockets">Electrical Plug Sockets Functional</label>
            </div>
            <div class="checkbox-group">
                <input type="checkbox" name="items[water_supply]" id="water_supply" value="ok">
                <label for="water_supply">Bathroom Water Supply & Taps Checked</label>
            </div>

            <div class="section-title">3. Safety & Security</div>
            <div class="checkbox-group">
                <input type="checkbox" name="items[door_locks]" id="door_locks" value="ok">
                <label for="door_locks">Door Locks & Keys Working</label>
            </div>
            <div class="checkbox-group">
                <input type="checkbox" name="items[windows_latches]" id="windows_latches" value="ok">
                <label for="windows_latches">Windows & Latches Secure</label>
            </div>

            <div class="form-group" style="margin-top: 20px;">
                <label>Additional Inspection Notes</label>
                <textarea name="notes" rows="3" placeholder="Mention any existing damages, meter readings, etc."></textarea>
            </div>

            <button type="submit" class="btn-submit">Save Checklist</button>
        </form>
    <?php endif; ?>
</div>

</body>
</html>