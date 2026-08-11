<?php
require_once '../../includes/session.php';
requireRole('landlord');
require_once '../../config/db.php';

if (isset($_GET['id'])) {
    $property_id = $_GET['id'];
    $landlord_id = $_SESSION['user_id'];

    try {
        $pdo->beginTransaction();

        // 1. Property  Rooms Delete 
        $stmt1 = $pdo->prepare("DELETE FROM rooms WHERE property_id = ?");
        $stmt1->execute([$property_id]);

        // 2. Property  Delete 
        $stmt2 = $pdo->prepare("DELETE FROM properties WHERE property_id = ? AND landlord_id = ?");
        $stmt2->execute([$property_id, $landlord_id]);

        $pdo->commit();
        header("Location: dashboard.php?status=deleted");
        exit();

    } catch (PDOException $e) {
        $pdo->rollBack();
        die("Error deleting property: " . $e->getMessage());
    }
} else {
    header("Location: dashboard.php");
    exit();
}
?>