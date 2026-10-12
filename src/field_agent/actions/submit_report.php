<?php
// Core server-side logic for submit_report
// Path: src/field_agent/actions/submit_report.php
require_once __DIR__ . '/../../../config/db.php';

// Fetch agent details
$stmt = $pdo->prepare("
    SELECT f.agent_id, f.assigned_city
    FROM field_agents f
    INNER JOIN users u ON u.user_id = f.user_id
    WHERE f.user_id = ? AND f.is_active = 1 AND u.status = 'active'
");
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

// Server enforcement of Geofence Authorization
if (!isset($_SESSION['geofence_passed_' . $task_id])) {
    $_SESSION['error'] = 'GPS Geofence authorization is missing. Please verify your location first.';
    header('Location: ../task_view.php?task_id=' . $task_id);
    exit();
}
$geo_data = $_SESSION['geofence_passed_' . $task_id];
if (!is_array($geo_data) || !isset($geo_data['expires']) || time() > $geo_data['expires']) {
    unset($_SESSION['geofence_passed_' . $task_id]);
    $_SESSION['error'] = 'GPS Geofence authorization has expired (1 hour limit). Please verify your location again.';
    header('Location: ../task_view.php?task_id=' . $task_id);
    exit();
}

$audit_items = array(
    'structural_safety' => 'Structural Safety',
    'electrical_safety' => 'Electrical Wiring',
    'fire_exit' => 'Fire Exit pathways',
    'furnishing_match' => 'Furnishing details match',
    'bathroom_match' => 'Bathroom Access type match',
    'wifi_match' => 'Wi-Fi Availability match',
    'finance_match' => 'Price & Deposit match',
    'kitchen_food_match' => 'Kitchen & Food Access match'
);

$audit_values = array();

foreach ($audit_items as $field => $label) {
    if (!isset($_POST[$field]) || ($_POST[$field] !== '1' && $_POST[$field] !== '0')) {
        $_SESSION['error'] = 'You must complete the verification check for: ' . $label;
        header('Location: ../task_view.php?task_id=' . $task_id);
        exit();
    }
    
    $val = $_POST[$field];
    if ($val === '0') {
        $reason_field = $field . '_reason';
        if (!isset($_POST[$reason_field]) || trim($_POST[$reason_field]) === '') {
            $_SESSION['error'] = 'You must provide a discrepancy note for: ' . $label;
            header('Location: ../task_view.php?task_id=' . $task_id);
            exit();
        }
    }
    $audit_values[$field] = (int)$val;
}

$structural_safety = $audit_values['structural_safety'];
$electrical_safety = $audit_values['electrical_safety'];
$fire_exit = $audit_values['fire_exit'];
$gps_match = 1;
$furnishing_match = $audit_values['furnishing_match'];
$bathroom_match = $audit_values['bathroom_match'];
$wifi_match = $audit_values['wifi_match'];
$finance_match = $audit_values['finance_match'];
$kitchen_food_match = $audit_values['kitchen_food_match'];

$neighborhood_safety = isset($_POST['neighborhood_safety']) ? intval($_POST['neighborhood_safety']) : 0;
$agent_comments = isset($_POST['agent_comments']) ? trim($_POST['agent_comments']) : '';

// Validation
if ($neighborhood_safety < 1 || $neighborhood_safety > 5 || empty($agent_comments)) {
    $_SESSION['error'] = 'Neighborhood safety and inspection remarks are mandatory.';
    header('Location: ../task_view.php?task_id=' . $task_id);
    exit();
}

function saveFieldAgentImage($file, $prefix, $upload_dir, &$uploaded_paths)
{
    if (!is_array($file) || !isset($file['error'], $file['tmp_name'], $file['size']) || $file['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('Every required verification photo must upload successfully.');
    }
    if ((int)$file['size'] < 1 || (int)$file['size'] > 5 * 1024 * 1024) {
        throw new Exception('Each verification photo must be 5MB or smaller.');
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($file['tmp_name']);
    $extensions = array('image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp');
    if (!isset($extensions[$mime]) || @getimagesize($file['tmp_name']) === false) {
        throw new Exception('Verification uploads must be valid JPG, PNG, or WebP images.');
    }

    $filename = $prefix . '_' . bin2hex(random_bytes(12)) . '.' . $extensions[$mime];
    $destination = $upload_dir . $filename;
    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        throw new Exception('The verification photo could not be stored.');
    }
    $uploaded_paths[] = $destination;
    return $filename;
}

$upload_dir = __DIR__ . '/../../../public/uploads/verification/';
$uploaded_paths = array();
if (!is_dir($upload_dir) && !mkdir($upload_dir, 0755, true)) {
    $_SESSION['error'] = 'The verification upload directory is unavailable.';
    header('Location: ../task_view.php?task_id=' . $task_id);
    exit();
}

try {
    $photo1 = isset($_FILES['photo1']) ? $_FILES['photo1'] : array();
    $photo2 = isset($_FILES['photo2']) ? $_FILES['photo2'] : array();
    $photo_name1 = saveFieldAgentImage($photo1, 'task_' . $task_id . '_exterior', $upload_dir, $uploaded_paths);
    $photo_name2 = saveFieldAgentImage($photo2, 'task_' . $task_id . '_interior', $upload_dir, $uploaded_paths);
    $db_photo_path1 = '/boardnest/public/uploads/verification/' . $photo_name1;
    $db_photo_path2 = '/boardnest/public/uploads/verification/' . $photo_name2;

    if (isset($_FILES['extra_photos']['name']) && is_array($_FILES['extra_photos']['name'])) {
        $extra_count = min(count($_FILES['extra_photos']['name']), 6);
        $saved_extra_links = array();
        for ($i = 0; $i < $extra_count; $i++) {
            if ($_FILES['extra_photos']['error'][$i] === UPLOAD_ERR_NO_FILE) {
                continue;
            }
            $extra_file = array(
                'name' => $_FILES['extra_photos']['name'][$i],
                'tmp_name' => $_FILES['extra_photos']['tmp_name'][$i],
                'size' => $_FILES['extra_photos']['size'][$i],
                'error' => $_FILES['extra_photos']['error'][$i]
            );
            $extra_name = saveFieldAgentImage($extra_file, 'task_' . $task_id . '_proof', $upload_dir, $uploaded_paths);
            $category = isset($_POST['extra_photo_categories'][$i])
                ? trim($_POST['extra_photo_categories'][$i])
                : 'Additional Proof';
            $saved_extra_links[] = 'Additional Proof (' . $category . '): /boardnest/public/uploads/verification/' . $extra_name;
        }
        if (!empty($saved_extra_links)) {
            $agent_comments .= "\n\nAdditional Verification Photos:\n" . implode("\n", $saved_extra_links);
        }
    }
} catch (Exception $upload_exception) {
    foreach ($uploaded_paths as $uploaded_path) {
        if (is_file($uploaded_path)) {
            unlink($uploaded_path);
        }
    }
    $_SESSION['error'] = $upload_exception->getMessage();
    header('Location: ../task_view.php?task_id=' . $task_id);
    exit();
}

try {
    $pdo->beginTransaction();

    $transport = isset($_POST['transport_details']) ? trim($_POST['transport_details']) : '';
    $amenities = isset($_POST['amenities_details']) ? trim($_POST['amenities_details']) : '';
    $safety    = isset($_POST['safety_details'])    ? trim($_POST['safety_details'])    : '';

    // 1. Insert Verification Report (Create operation)
    $stmtRep = $pdo->prepare("
        INSERT INTO verification_reports (
            task_id, field_agent_user_id, structural_safety, electrical_safety, fire_exit, gps_match, 
            neighborhood_safety, furnishing_match, bathroom_match, wifi_match, finance_match, kitchen_food_match,
            transport_details, amenities_details, safety_details,
            photo_path_1, photo_path_2, agent_comments
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmtRep->execute(array(
        $task_id, $_SESSION['user_id'], $structural_safety, $electrical_safety, $fire_exit, $gps_match,
        $neighborhood_safety, $furnishing_match, $bathroom_match, $wifi_match, $finance_match, $kitchen_food_match,
        $transport, $amenities, $safety,
        $db_photo_path1, $db_photo_path2, $agent_comments
    ));

    // 2. Update Agent Task status to completed (Update operation)
    $stmtTaskUpdate = $pdo->prepare("UPDATE agent_tasks SET status = 'completed', completed_at = CURRENT_TIMESTAMP WHERE task_id = ? AND agent_id = ? AND status = 'in_progress'");
    $stmtTaskUpdate->execute(array($task_id, $agent_id));
    if ($stmtTaskUpdate->rowCount() !== 1) {
        throw new Exception('The task is no longer available for completion.');
    }

    // 3. Update property rooms status to awaiting_admin (Listing state machine update)
    $stmtRooms = $pdo->prepare("UPDATE rooms SET status = 'awaiting_admin' WHERE property_id = ?");
    $stmtRooms->execute(array($task['property_id']));

    $stmtListings = $pdo->prepare("UPDATE listings SET status = 'awaiting_approval' WHERE property_id = ?");
    $stmtListings->execute(array($task['property_id']));

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
    foreach ($uploaded_paths as $uploaded_path) {
        if (is_file($uploaded_path)) {
            unlink($uploaded_path);
        }
    }

    $_SESSION['error'] = 'Database transaction failed: ' . $e->getMessage();
    header('Location: ../task_view.php?task_id=' . $task_id);
    exit();
}
?>
