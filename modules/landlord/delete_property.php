
<?php
require_once __DIR__ . '/../../includes/session.php';
startSession();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'landlord') {
    header('Location: ../../login.php');
    exit();
}

require_once __DIR__ . '/../../config/db.php';

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: dashboard.php');
    exit();
}

// Validate CSRF token
$token = $_POST['csrf_token'] ?? '';

if (
    empty($_SESSION['csrf_token']) ||
    !is_string($token) ||
    !hash_equals($_SESSION['csrf_token'], $token)
) {
    $_SESSION['delete_property_error'] =
        'Invalid request. Please try again.';
    header('Location: dashboard.php');
    exit();
}

$property_id = filter_input(
    INPUT_POST,
    'id',
    FILTER_VALIDATE_INT
);

if (!$property_id || $property_id < 1) {
    $_SESSION['delete_property_error'] =
        'Invalid property ID.';
    header('Location: dashboard.php');
    exit();
}

try {
    $pdo->beginTransaction();

    // Get the landlord ID belonging to the logged-in user
    $lStmt = $pdo->prepare(
        "SELECT landlord_id
         FROM landlords
         WHERE user_id = ?"
    );
    $lStmt->execute([$_SESSION['user_id']]);
    $landlord_id = $lStmt->fetchColumn();

    if (!$landlord_id) {
        throw new RuntimeException('Landlord profile not found.');
    }

    // Delete only a property owned by this landlord
    $stmt = $pdo->prepare(
        "DELETE FROM properties
         WHERE property_id = ?
         AND landlord_id = ?"
    );
    $stmt->execute([$property_id, $landlord_id]);

    if ($stmt->rowCount() !== 1) {
        throw new RuntimeException(
            'Property not found or you do not own it.'
        );
    }

    $pdo->commit();

    $_SESSION['delete_property_success'] =
        'Property deleted successfully.';

} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    error_log('Delete property error: ' . $e->getMessage());

    $_SESSION['delete_property_error'] =
        'Could not delete the property. Please check its related records.';
}

header('Location: dashboard.php');
exit();