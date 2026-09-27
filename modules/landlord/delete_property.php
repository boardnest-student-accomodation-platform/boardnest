<?php
require_once __DIR__ . '/../../includes/session.php';
startSession();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'landlord') {
    header('Location: ../../login.php');
    exit();
}

require_once __DIR__ . '/../../config/db.php';

$user_id     = $_SESSION['user_id'];
$property_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if ($property_id) {
    try {
        // IDOR Check: Ensure the property belongs to this landlord
        $lStmt = $pdo->prepare("SELECT landlord_id FROM landlords WHERE user_id = ?");
        $lStmt->execute([$user_id]);
        $landlord = $lStmt->fetch();
        $landlord_id = $landlord['landlord_id'] ?? null;

        if ($landlord_id) {
            $stmt = $pdo->prepare("DELETE FROM properties WHERE property_id = ? AND landlord_id = ?");
            $stmt->execute([$property_id, $landlord_id]);
        }
    } catch (PDOException $e) {
        error_log("Delete property error: " . $e->getMessage());
    }
}

header('Location: dashboard.php');
exit();