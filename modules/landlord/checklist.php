
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

$error = '';
$success = '';

// CSRF protection
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

// Get landlord ID
$lStmt = $pdo->prepare(
    "SELECT landlord_id FROM landlords WHERE user_id = ?"
);
$lStmt->execute([$user_id]);
$landlord = $lStmt->fetch(PDO::FETCH_ASSOC);

$landlord_id = $landlord ? (int) $landlord['landlord_id'] : null;

// Get property ID
$raw_property_id = $_SERVER['REQUEST_METHOD'] === 'POST'
    ? ($_POST['property_id'] ?? '')
    : ($_GET['property_id'] ?? '');

$property_id = filter_var(
    $raw_property_id,
    FILTER_VALIDATE_INT,
    ['options' => ['min_range' => 1]]
);

$property_id = $property_id !== false ? $property_id : null;

// Check whether property belongs to this landlord
$valid_property = false;

if ($property_id && $landlord_id) {
    $propertyStmt = $pdo->prepare(
        "SELECT property_id
         FROM properties
         WHERE property_id = ? AND landlord_id = ?
         LIMIT 1"
    );
    $propertyStmt->execute([$property_id, $landlord_id]);
    $valid_property = (bool) $propertyStmt->fetchColumn();
}

if (!$landlord_id) {
    $error = 'Landlord account not found.';
} elseif (!$property_id || !$valid_property) {
    $error = 'Invalid property or you do not have permission to access it.';
}

// Save or delete checklist
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $valid_property) {

    $submitted_token = $_POST['csrf_token'] ?? '';

    if (!hash_equals($csrf_token, $submitted_token)) {
        $error = 'Invalid request. Please refresh the page and try again.';
    } else {
        $action = $_POST['action'] ?? '';

        if ($action === 'save_checklist') {
            $items = trim($_POST['items'] ?? '');
            $notes = trim($_POST['notes'] ?? '');

            if ($items === '') {
                $error = 'Please enter at least one checklist item.';
            } else {
                try {
                    $stmt = $pdo->prepare(
                        "INSERT INTO property_checklists
                         (property_id, landlord_id, items, notes)
                         VALUES (?, ?, ?, ?)"
                    );

                    $stmt->execute([
                        $property_id,
                        $landlord_id,
                        $items,
                        $notes
                    ]);

                    $success = 'Checklist saved successfully!';
                } catch (PDOException $e) {
                    error_log('Save checklist error: ' . $e->getMessage());
                    $error = 'Failed to save checklist. Please try again.';
                }
            }
        } elseif ($action === 'delete_checklist') {
            $checklist_id = filter_var(
                $_POST['checklist_id'] ?? '',
                FILTER_VALIDATE_INT,
                ['options' => ['min_range' => 1]]
            );

            if ($checklist_id === false) {
                $error = 'Invalid checklist ID.';
            } else {
                try {
                    $deleteStmt = $pdo->prepare(
                        "DELETE FROM property_checklists
                         WHERE checklist_id = ?
                         AND property_id = ?
                         AND landlord_id = ?"
                    );

                    $deleteStmt->execute([
                        $checklist_id,
                        $property_id,
                        $landlord_id
                    ]);

                    if ($deleteStmt->rowCount() > 0) {
                        $success = 'Checklist deleted successfully!';
                    } else {
                        $error = 'Checklist not found or you do not have permission to delete it.';
                    }
                } catch (PDOException $e) {
                    error_log('Delete checklist error: ' . $e->getMessage());
                    $error = 'Failed to delete checklist. Please try again.';
                }
            }
        }
    }
}

// Fetch saved checklists
$checklists = [];

if ($valid_property) {
    try {
        $fetchStmt = $pdo->prepare(
            "SELECT checklist_id, items, notes, created_at
             FROM property_checklists
             WHERE property_id = ? AND landlord_id = ?
             ORDER BY created_at DESC"
        );

        $fetchStmt->execute([$property_id, $landlord_id]);
        $checklists = $fetchStmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log('Fetch checklist error: ' . $e->getMessage());
        $error = 'Unable to load saved checklists.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Property Checklist — BoardNest</title>

    <link rel="stylesheet"
          href="../../public/assets/css/landlord.css?v=2">
</head>
<body>

<?php include __DIR__ . '/nav.php'; ?>

<div class="form-card">

    <div class="page-header">
        <h2>Property Move-in Checklist</h2>
    </div>

    <?php if ($error !== ''): ?>
        <div class="alert-error" role="alert">
            <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
        </div>
    <?php endif; ?>

    <?php if ($success !== ''): ?>
        <div class="alert-success" role="status">
            <?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?>
        </div>
    <?php endif; ?>

    <?php if ($valid_property): ?>

        <form method="POST" action="">
            <input type="hidden" name="property_id"
                   value="<?= (int) $property_id ?>">

            <input type="hidden" name="csrf_token"
                   value="<?= htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8') ?>">

            <input type="hidden" name="action"
                   value="save_checklist">

            <div class="form-group">
                <label for="items">
                    Checklist Items (Comma separated or new lines)
                </label>

                <textarea
                    id="items"
                    name="items"
                    rows="4"
                    required
                    placeholder="Keys handed over, Electricity meter read, Fan working..."
                ></textarea>
            </div>

            <div class="form-group">
                <label for="notes">Additional Notes</label>

                <textarea
                    id="notes"
                    name="notes"
                    rows="3"
                    placeholder="Condition remarks..."
                ></textarea>
            </div>

            <button type="submit"
                    class="btn btn-primary btn-block">
                Save Checklist
            </button>
        </form>

        <h3 style="margin-top: 30px;">Saved Checklists</h3>

        <?php if (!empty($checklists)): ?>
            <div style="overflow-x: auto;">
                <table class="custom-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Items</th>
                            <th>Notes</th>
                            <th>Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php foreach ($checklists as $c): ?>
                            <tr>
                                <td>
                                    <?= htmlspecialchars(
                                        $c['created_at'] ?? '',
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>
                                </td>

                                <td>
                                    <?= nl2br(htmlspecialchars(
                                        $c['items'] ?? '',
                                        ENT_QUOTES,
                                        'UTF-8'
                                    )) ?>
                                </td>

                                <td>
                                    <?= nl2br(htmlspecialchars(
                                        $c['notes'] ?? '',
                                        ENT_QUOTES,
                                        'UTF-8'
                                    )) ?>
                                </td>

                                <td>
                                    <form method="POST"
                                          action=""
                                          onsubmit="return confirm('Are you sure you want to delete this checklist?');">

                                        <input type="hidden"
                                               name="property_id"
                                               value="<?= (int) $property_id ?>">

                                        <input type="hidden"
                                               name="csrf_token"
                                               value="<?= htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8') ?>">

                                        <input type="hidden"
                                               name="action"
                                               value="delete_checklist">

                                        <input type="hidden"
                                               name="checklist_id"
                                               value="<?= (int) $c['checklist_id'] ?>">

                                        <button type="submit"
                                                class="btn btn-danger">
                                            Delete
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p>No saved checklists yet.</p>
        <?php endif; ?>

    <?php endif; ?>

</div>

</body>
</html>