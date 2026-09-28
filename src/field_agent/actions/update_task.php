<?php
// Core server-side logic for field-agent task state changes.
require_once __DIR__ . '/../../../config/db.php';

function fieldAgentDistanceMetres($lat1, $lng1, $lat2, $lng2)
{
    $earthRadius = 6371000;
    $latDelta = deg2rad($lat2 - $lat1);
    $lngDelta = deg2rad($lng2 - $lng1);
    $a = sin($latDelta / 2) * sin($latDelta / 2)
        + cos(deg2rad($lat1)) * cos(deg2rad($lat2))
        * sin($lngDelta / 2) * sin($lngDelta / 2);

    return $earthRadius * 2 * atan2(sqrt($a), sqrt(1 - $a));
}

function fieldAgentRedirectWithError($message, $location)
{
    $_SESSION['error'] = $message;
    header('Location: ' . $location);
    exit();
}

$stmt = $pdo->prepare("
    SELECT f.agent_id, f.assigned_city
    FROM field_agents f
    INNER JOIN users u ON u.user_id = f.user_id
    WHERE f.user_id = ? AND f.is_active = 1 AND u.status = 'active'
");
$stmt->execute(array($_SESSION['user_id']));
$agent = $stmt->fetch();

if (!$agent) {
    fieldAgentRedirectWithError('Your field agent account is not active.', '../dashboard.php');
}

$agent_id = (int)$agent['agent_id'];
$city = $agent['assigned_city'];

if ($action_type === 'verify_geofence') {
    $verified_lat = filter_input(INPUT_POST, 'verified_lat', FILTER_VALIDATE_FLOAT);
    $verified_lng = filter_input(INPUT_POST, 'verified_lng', FILTER_VALIDATE_FLOAT);

    if ($verified_lat === false || $verified_lat === null || $verified_lng === false || $verified_lng === null
        || $verified_lat < -90 || $verified_lat > 90 || $verified_lng < -180 || $verified_lng > 180) {
        fieldAgentRedirectWithError('Valid device coordinates are required.', '../dashboard.php');
    }

    if ($complaint_id > 0) {
        $targetStatement = $pdo->prepare("
            SELECT p.latitude, p.longitude
            FROM complaints c
            INNER JOIN complaint_investigations ci ON ci.complaint_id = c.complaint_id
            INNER JOIN listings l ON l.listing_id = c.listing_id
            INNER JOIN properties p ON p.property_id = l.property_id
            WHERE c.complaint_id = ?
              AND ci.field_agent_user_id = ?
              AND c.status IN ('assigned', 'under_investigation')
        ");
        $targetStatement->execute(array($complaint_id, $_SESSION['user_id']));
        $target = $targetStatement->fetch();
        $redirect = '../task_view.php?complaint_id=' . $complaint_id;
        $session_key = 'geofence_passed_comp_' . $complaint_id;
    } else {
        $targetStatement = $pdo->prepare("
            SELECT p.latitude, p.longitude
            FROM agent_tasks t
            INNER JOIN properties p ON p.property_id = t.property_id
            WHERE t.task_id = ? AND t.agent_id = ? AND t.status = 'in_progress'
        ");
        $targetStatement->execute(array($task_id, $agent_id));
        $target = $targetStatement->fetch();
        $redirect = '../task_view.php?task_id=' . $task_id;
        $session_key = 'geofence_passed_' . $task_id;
    }

    if (!$target || $target['latitude'] === null || $target['longitude'] === null) {
        fieldAgentRedirectWithError('The assigned property does not have valid GPS coordinates.', $redirect);
    }

    $distance = fieldAgentDistanceMetres(
        (float)$verified_lat,
        (float)$verified_lng,
        (float)$target['latitude'],
        (float)$target['longitude']
    );

    if ($distance > 100) {
        fieldAgentRedirectWithError(
            'GPS verification failed. You are ' . number_format($distance, 0) . ' metres from the property.',
            $redirect
        );
    }

    $_SESSION[$session_key] = array(
        'coords' => array('lat' => (float)$verified_lat, 'lng' => (float)$verified_lng),
        'distance_metres' => $distance,
        'verified_at' => time(),
        'expires' => time() + 3600
    );

    if ($complaint_id > 0) {
        $statusStatement = $pdo->prepare("
            UPDATE complaints SET status = 'under_investigation'
            WHERE complaint_id = ? AND status = 'assigned'
        ");
        $statusStatement->execute(array($complaint_id));
    }

    $_SESSION['success'] = 'GPS location verified. The on-site form is now unlocked.';
    header('Location: ' . $redirect);
    exit();
}

if ($task_id < 1) {
    fieldAgentRedirectWithError('A valid task is required.', '../dashboard.php');
}

try {
    $pdo->beginTransaction();

    $stmtTask = $pdo->prepare("
        SELECT t.*, p.city, p.property_id
        FROM agent_tasks t
        INNER JOIN properties p ON t.property_id = p.property_id
        WHERE t.task_id = ?
        FOR UPDATE
    ");
    $stmtTask->execute(array($task_id));
    $task = $stmtTask->fetch();

    if (!$task) {
        throw new Exception('Task not found.');
    }

    if ($action_type === 'claim') {
        if ($task['city'] !== $city) {
            throw new Exception('You can only claim tasks within your assigned city (' . $city . ').');
        }
        if ($task['agent_id'] !== null || $task['status'] !== 'pending') {
            throw new Exception('This task has already been claimed.');
        }

        $stmtUpdate = $pdo->prepare("
            UPDATE agent_tasks
            SET agent_id = ?, status = 'in_progress'
            WHERE task_id = ? AND agent_id IS NULL AND status = 'pending'
        ");
        $stmtUpdate->execute(array($agent_id, $task_id));
        if ($stmtUpdate->rowCount() !== 1) {
            throw new Exception('This task was claimed by another agent.');
        }

        $stmtRooms = $pdo->prepare("
            UPDATE rooms SET status = 'under_verification'
            WHERE property_id = ? AND status = 'pending'
        ");
        $stmtRooms->execute(array($task['property_id']));
        $_SESSION['success'] = 'Task claimed successfully. It is now in your claimed queue.';
        $redirect = '../dashboard.php?tab=claimed';
    } elseif ($action_type === 'withdraw') {
        if ((int)$task['agent_id'] !== $agent_id || $task['status'] !== 'in_progress') {
            throw new Exception('You do not own an active claim for this task.');
        }

        $stmtUpdate = $pdo->prepare("
            UPDATE agent_tasks SET agent_id = NULL, status = 'pending'
            WHERE task_id = ? AND agent_id = ? AND status = 'in_progress'
        ");
        $stmtUpdate->execute(array($task_id, $agent_id));
        $stmtRooms = $pdo->prepare("
            UPDATE rooms SET status = 'pending'
            WHERE property_id = ? AND status IN ('under_verification', 'agent_on_site')
        ");
        $stmtRooms->execute(array($task['property_id']));
        unset($_SESSION['geofence_passed_' . $task_id]);
        $_SESSION['success'] = 'The task has been returned to the pending pool.';
        $redirect = '../dashboard.php?tab=claimed';
    } else {
        if ((int)$task['agent_id'] !== $agent_id || $task['status'] !== 'in_progress') {
            throw new Exception('You do not own this active task.');
        }

        $geo_key = 'geofence_passed_' . $task_id;
        $geo_data = isset($_SESSION[$geo_key]) ? $_SESSION[$geo_key] : null;
        if (!is_array($geo_data) || !isset($geo_data['expires']) || time() > (int)$geo_data['expires']) {
            unset($_SESSION[$geo_key]);
            throw new Exception('Verify your on-site GPS location before using emergency suspension.');
        }

        $reason = isset($_POST['reason']) ? trim($_POST['reason']) : '';
        if ($reason === '') {
            throw new Exception('A suspension reason is mandatory.');
        }

        $stmtRooms = $pdo->prepare("UPDATE rooms SET status = 'suspended' WHERE property_id = ?");
        $stmtRooms->execute(array($task['property_id']));
        $stmtListings = $pdo->prepare("UPDATE listings SET status = 'suspended' WHERE property_id = ?");
        $stmtListings->execute(array($task['property_id']));
        $stmtUpdate = $pdo->prepare("
            UPDATE agent_tasks SET status = 'completed', completed_at = CURRENT_TIMESTAMP
            WHERE task_id = ? AND agent_id = ?
        ");
        $stmtUpdate->execute(array($task_id, $agent_id));

        $stmtReport = $pdo->prepare("
            INSERT INTO verification_reports (
                task_id, field_agent_user_id, structural_safety, electrical_safety,
                fire_exit, gps_match, neighborhood_safety, furnishing_match,
                bathroom_match, kitchen_food_match, wifi_match, finance_match,
                photo_path_1, photo_path_2, agent_comments
            ) VALUES (?, ?, 0, 0, 0, 1, 1, 0, 0, 0, 0, 0, 'suspended_no_image', 'suspended_no_image', ?)
        ");
        $stmtReport->execute(array(
            $task_id,
            $_SESSION['user_id'],
            'EMERGENCY SUSPENSION TRIGGERED. Reason: ' . $reason
        ));
        unset($_SESSION[$geo_key]);
        $_SESSION['success'] = 'The property has been suspended and hidden from student search.';
        $redirect = '../dashboard.php?tab=history';
    }

    $pdo->commit();
    header('Location: ' . $redirect);
    exit();
} catch (Exception $exception) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    fieldAgentRedirectWithError($exception->getMessage(), '../dashboard.php');
}
?>
