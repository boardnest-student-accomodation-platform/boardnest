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

// Get Landlord ID
$lStmt = $pdo->prepare("SELECT landlord_id FROM landlords WHERE user_id = ?");
$lStmt->execute([$user_id]);
$landlord    = $lStmt->fetch();
$landlord_id = $landlord['landlord_id'] ?? null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_checklist'])) {
    $items = trim($_POST['items'] ?? '');
    $notes = trim($_POST['notes'] ?? '');

    if ($property_id && $landlord_id) {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO property_checklists (property_id, landlord_id, items, notes) 
                VALUES (?, ?, ?, ?)
            ");
            $stmt->execute([$property_id, $landlord_id, $items, $notes]);
            $success = "Checklist saved successfully!";
        } catch (PDOException $e) {
            error_log("Save checklist error: " . $e->getMessage());
            $error = "Failed to save checklist.";
        }
    }
}

// Fetch Existing Checklists
$checklists = [];
if ($property_id && $landlord_id) {
    try {
        $fetchStmt = $pdo->prepare("SELECT * FROM property_checklists WHERE property_id = ? AND landlord_id = ? ORDER BY created_at DESC");
        $fetchStmt->execute([$property_id, $landlord_id]);
        $checklists = $fetchStmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Fetch checklist error: " . $e->getMessage());
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Property Checklist — BoardNest</title>
    <link rel="stylesheet" href="../../public/assets/css/landlord.css">
</head>
<body>

<div class="form-card">
    <div class="page-header">
        <h2>Property Move-in Checklist</h2>
        <a href="dashboard.php" class="btn-back">← Dashboard</a>
    </div>

    <?php if ($error): ?>
        <div class="alert-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
        <div class="alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <form method="POST" action="">
        <input type="hidden" name="property_id" value="<?= htmlspecialchars($property_id) ?>">

        <div class="form-group">
            <label>Checklist Items (Comma separated or new lines)</label>
            <textarea name="items" rows="4" placeholder="Keys handed over, Electricity meter read, Fan working..."></textarea>
        </div>

        <div class="form-group">
            <label>Additional Notes</label>
            <textarea name="notes" rows="3" placeholder="Condition remarks..."></textarea>
        </div>

        <button type="submit" name="save_checklist" class="btn btn-primary btn-block">Save Checklist</button>
    </form>

    <?php if (!empty($checklists)): ?>
        <h3 style="margin-top: 30px;">Saved Checklists</h3>
        <table class="custom-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Items</th>
                    <th>Notes</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($checklists as $c): ?>
                    <tr>
                        <td><?= htmlspecialchars($c['created_at']) ?></td>
                        <td><?= nl2br(htmlspecialchars($c['items'] ?? '')) ?></td>
                        <td><?= nl2br(htmlspecialchars($c['notes'] ?? '')) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

</body>
</html>