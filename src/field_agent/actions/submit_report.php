<?php
// Core server-side logic for submit_report
// Path: src/field_agent/actions/submit_report.php
require_once __DIR__ . '/../../../config/db.php';

// Fetch agent details
$stmt = $pdo->prepare("SELECT agent_id, assigned_city FROM field_agents WHERE user_id = ?");
$stmt->execute(array($_SESSION['user_id']));
$agent = $stmt->fetch();

if (!$agent) {
    $_SESSION['error'] = 'Field agent account not found.';
    header('Location: ../dashboard.php');
    exit();
}
$agent_id = $agent['agent_id'];
$city = $agent['assigned_city'];

// Fetch the task and verify the property is in the agent's city and assigned to current agent
$stmtTask = $pdo->prepare("
    SELECT t.*, p.city, p.property_id 
    FROM agent_tasks t
    INNER JOIN properties p ON t.property_id = p.property_id
    WHERE t.task_id = ?
");
$stmtTask->execute(array($task_id));
$task = $stmtTask->fetch();

if (!$task) {
    $_SESSION['error'] = 'Task not found.';
    header('Location: ../dashboard.php');
    exit();
}

if (intval($task['agent_id']) !== intval($agent_id)) {
    $_SESSION['error'] = 'This task is not assigned to you.';
    header('Location: ../dashboard.php');
    exit();
}

if ($task['status'] === 'completed') {
    $_SESSION['error'] = 'This task has already been completed.';
    header('Location: ../dashboard.php?tab=history');
    exit();
}

// Read Checklist Inputs (Check exact value '1' rather than just isset because hidden inputs always submit)
$structural_safety = (isset($_POST['structural_safety']) && $_POST['structural_safety'] == '1') ? 1 : 0;
$electrical_safety = (isset($_POST['electrical_safety']) && $_POST['electrical_safety'] == '1') ? 1 : 0;
$fire_exit = (isset($_POST['fire_exit']) && $_POST['fire_exit'] == '1') ? 1 : 0;
$gps_match = (isset($_POST['gps_match']) && $_POST['gps_match'] == '1') ? 1 : 0;
$neighborhood_safety = isset($_POST['neighborhood_safety']) ? intval($_POST['neighborhood_safety']) : 0;

$furnishing_match = (isset($_POST['furnishing_match']) && $_POST['furnishing_match'] == '1') ? 1 : 0;
$bathroom_match = (isset($_POST['bathroom_match']) && $_POST['bathroom_match'] == '1') ? 1 : 0;
$wifi_match = (isset($_POST['wifi_match']) && $_POST['wifi_match'] == '1') ? 1 : 0;
$finance_match = (isset($_POST['finance_match']) && $_POST['finance_match'] == '1') ? 1 : 0;
$kitchen_food_match = (isset($_POST['kitchen_food_match']) && $_POST['kitchen_food_match'] == '1') ? 1 : 0;

$agent_comments = isset($_POST['agent_comments']) ? trim($_POST['agent_comments']) : '';

// Validation
if (!$neighborhood_safety || empty($agent_comments)) {
    $_SESSION['error'] = 'Neighborhood safety and inspection remarks are mandatory.';
    header('Location: ../task_view.php?task_id=' . $task_id);
    exit();
}

// File Uploads (2 Photos required)
if (!isset($_FILES['photo1']) || !isset($_FILES['photo2']) || 
    $_FILES['photo1']['error'] !== UPLOAD_ERR_OK || $_FILES['photo2']['error'] !== UPLOAD_ERR_OK) {
    $_SESSION['error'] = 'Both verification photos are mandatory and must be uploaded successfully.';
    header('Location: ../task_view.php?task_id=' . $task_id);
    exit();
}

// Make sure uploads directory exists
$upload_dir = __DIR__ . '/../../../../public/uploads/';
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

// Move photo 1
$file_ext1 = strtolower(pathinfo($_FILES['photo1']['name'], PATHINFO_EXTENSION));
$photo_name1 = 'task_' . $task_id . '_img1_' . time() . '.' . $file_ext1;
$photo_path1 = $upload_dir . $photo_name1;

// Move photo 2
$file_ext2 = strtolower(pathinfo($_FILES['photo2']['name'], PATHINFO_EXTENSION));
$photo_name2 = 'task_' . $task_id . '_img2_' . time() . '.' . $file_ext2;
$photo_path2 = $upload_dir . $photo_name2;

if (!move_uploaded_file($_FILES['photo1']['tmp_name'], $photo_path1) || 
    !move_uploaded_file($_FILES['photo2']['tmp_name'], $photo_path2)) {
    $_SESSION['error'] = 'Failed to save the uploaded verification photos on the server.';
    header('Location: ../task_view.php?task_id=' . $task_id);
    exit();
}

// Save database path relative to web root (/boardnest/public/uploads/...)
$db_photo_path1 = '/boardnest/public/uploads/' . $photo_name1;
$db_photo_path2 = '/boardnest/public/uploads/' . $photo_name2;

// Process optional extra proof photos
if (isset($_FILES['extra_photos']) && is_array($_FILES['extra_photos']['name'])) {
    $extra_count = count($_FILES['extra_photos']['name']);
    $saved_extra_links = array();
    for ($i = 0; $i < $extra_count; $i++) {
        if ($_FILES['extra_photos']['error'][$i] === UPLOAD_ERR_OK) {
            $ext_name = strtolower(pathinfo($_FILES['extra_photos']['name'][$i], PATHINFO_EXTENSION));
            $extra_file_name = 'task_' . $task_id . '_extra_' . ($i + 3) . '_' . time() . '.' . $ext_name;
            $extra_dest = $upload_dir . $extra_file_name;
            $cat_title = isset($_POST['extra_photo_categories'][$i]) ? trim($_POST['extra_photo_categories'][$i]) : 'Additional Proof';
            if (move_uploaded_file($_FILES['extra_photos']['tmp_name'][$i], $extra_dest)) {
                $saved_extra_links[] = '📷 Additional Proof Photo ' . ($i + 3) . ' (' . $cat_title . '): /boardnest/public/uploads/' . $extra_file_name;
            }
        }
    }
    if (!empty($saved_extra_links)) {
        $agent_comments .= "\n\nAdditional Verification Photos Captured:\n" . implode("\n", $saved_extra_links);
    }
}

try {
    $pdo->beginTransaction();

    // 1. Insert Verification Report (Create operation)
    $stmtRep = $pdo->prepare("
        INSERT INTO verification_reports (
            task_id, structural_safety, electrical_safety, fire_exit, gps_match, 
            neighborhood_safety, furnishing_match, bathroom_match, wifi_match, finance_match, kitchen_food_match,
            photo_path_1, photo_path_2, agent_comments
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmtRep->execute(array(
        $task_id, $structural_safety, $electrical_safety, $fire_exit, $gps_match,
        $neighborhood_safety, $furnishing_match, $bathroom_match, $wifi_match, $finance_match, $kitchen_food_match,
        $db_photo_path1, $db_photo_path2, $agent_comments
    ));

    // 2. Update Agent Task status to completed (Update operation)
    $stmtTaskUpdate = $pdo->prepare("UPDATE agent_tasks SET status = 'completed', completed_at = CURRENT_TIMESTAMP WHERE task_id = ?");
    $stmtTaskUpdate->execute(array($task_id));

    // 3. Update property rooms status to awaiting_admin (Listing state machine update)
    $stmtRooms = $pdo->prepare("UPDATE rooms SET status = 'awaiting_admin' WHERE property_id = ?");
    $stmtRooms->execute(array($task['property_id']));

    // 4. Save Section 04 Area Profile & Observations if filled
    $transport = isset($_POST['transport_details']) ? trim($_POST['transport_details']) : '';
    $amenities = isset($_POST['amenities_details']) ? trim($_POST['amenities_details']) : '';
    $safety    = isset($_POST['safety_details'])    ? trim($_POST['safety_details'])    : '';

    if (!empty($transport) || !empty($amenities) || !empty($safety)) {
        $stmtArea = $pdo->prepare("
            INSERT INTO area_reports (agent_id, city, transport_details, amenities_details, safety_details, status, submitted_at)
            VALUES (?, ?, ?, ?, ?, 'pending', NOW())
        ");
        $stmtArea->execute(array($agent_id, $city, $transport, $amenities, $safety));
    }

    // Clear geofence session variable
    unset($_SESSION['geofence_passed_' . $task_id]);

    $pdo->commit();

    $_SESSION['success'] = 'Verification report submitted successfully! The listing status is now: Awaiting Admin Approval.';
    header('Location: ../dashboard.php?tab=history');
    exit();

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    // Clean up uploaded files in case of db rollback
    if (file_exists($photo_path1)) unlink($photo_path1);
    if (file_exists($photo_path2)) unlink($photo_path2);

    $_SESSION['error'] = 'Database transaction failed: ' . $e->getMessage();
    header('Location: ../task_view.php?task_id=' . $task_id);
    exit();
}
?>
