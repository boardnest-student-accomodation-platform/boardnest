<?php
// Core server-side logic for update_task
// Path: src/field_agent/actions/update_task.php
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

// Handle complaint geofence unlock
if ($complaint_id > 0 && $geofence_override) {
    $_SESSION['geofence_passed_comp_' . $complaint_id] = true;
    $_SESSION['success'] = 'GPS Geofence validated. Investigation form unlocked.';
    header('Location: ../task_view.php?complaint_id=' . $complaint_id);
    exit();
}

// Fetch the task and verify the property is in the agent's city
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

// Verify city locking for new claims
if ($action_type === 'claim' && !$geofence_override && $task['city'] !== $city) {
    $_SESSION['error'] = 'You can only claim tasks within your assigned city (' . htmlspecialchars($city) . ').';
    header('Location: ../dashboard.php');
    exit();
}

try {
    $pdo->beginTransaction();

    if ($action_type === 'claim') {
        // Case A: Geofence override (unlocking checklist on site)
        if ($geofence_override) {
            // Verify current agent owns the task or it is unclaimed
            if ($task['agent_id'] !== null && intval($task['agent_id']) !== intval($agent_id)) {
                if ($pdo->inTransaction()) { $pdo->rollBack(); }
                $_SESSION['error'] = 'This task is claimed by another agent.';
                header('Location: ../dashboard.php');
                exit();
            }

            // Assign to agent and mark in_progress if not already
            $stmtUpdate = $pdo->prepare("UPDATE agent_tasks SET agent_id = ?, status = 'in_progress' WHERE task_id = ?");
            $stmtUpdate->execute(array($agent_id, $task_id));

            // Set room status to agent_on_site
            $stmtRooms = $pdo->prepare("UPDATE rooms SET status = 'agent_on_site' WHERE property_id = ?");
            $stmtRooms->execute(array($task['property_id']));

            $_SESSION['geofence_passed_' . $task_id] = true;
            $_SESSION['success'] = 'Geofence validated. You are now verified on-site.';
            $redirect = '../task_view.php?task_id=' . $task_id;

        } else {
            // Case B: Normal Claim Task
            // Verify it is not already claimed
            if ($task['agent_id'] !== null) {
                if ($pdo->inTransaction()) { $pdo->rollBack(); }
                $_SESSION['error'] = 'This task is already claimed by another agent.';
                header('Location: ../dashboard.php');
                exit();
            }

            // Update task status to in_progress and assign to agent
            $stmtUpdate = $pdo->prepare("UPDATE agent_tasks SET agent_id = ?, status = 'in_progress' WHERE task_id = ?");
            $stmtUpdate->execute(array($agent_id, $task_id));

            // Update property rooms status to under_verification
            $stmtRooms = $pdo->prepare("UPDATE rooms SET status = 'under_verification' WHERE property_id = ? AND status = 'pending'");
            $stmtRooms->execute(array($task['property_id']));

            $_SESSION['success'] = 'Task claimed successfully. It is now in your claimed queue.';
            $redirect = '../dashboard.php?tab=claimed';
        }

    } elseif ($action_type === 'withdraw') {
        // Withdraw from task (Delete claim and return to pool)
        // Verify current agent owns the task or agent_id match
        if ($task['agent_id'] !== null && intval($task['agent_id']) !== intval($agent_id)) {
            if ($pdo->inTransaction()) { $pdo->rollBack(); }
            $_SESSION['error'] = 'You do not own this task.';
            header('Location: ../dashboard.php?tab=claimed');
            exit();
        }

        // Reset task to pending and null agent_id
        $stmtUpdate = $pdo->prepare("UPDATE agent_tasks SET agent_id = NULL, status = 'pending' WHERE task_id = ?");
        $stmtUpdate->execute(array($task_id));

        // Revert rooms status back to pending
        $stmtRooms = $pdo->prepare("UPDATE rooms SET status = 'pending' WHERE property_id = ? AND status IN ('under_verification', 'agent_on_site')");
        $stmtRooms->execute(array($task['property_id']));

        // Clear geofence session flag
        unset($_SESSION['geofence_passed_' . $task_id]);

        $_SESSION['success'] = 'Withdrew from task successfully. It has been returned to the pending pool.';
        $redirect = '../dashboard.php?tab=claimed';

    } elseif ($action_type === 'suspend') {
        // Emergency Suspension
        // Verify current agent owns the task
        if (intval($task['agent_id']) !== intval($agent_id)) {
            $_SESSION['error'] = 'You do not own this task.';
            header('Location: ../dashboard.php');
            exit();
        }

        $reason = isset($_POST['reason']) ? trim($_POST['reason']) : '';
        if (empty($reason)) {
            $_SESSION['error'] = 'A suspension reason is mandatory for emergency suspension.';
            header('Location: ../task_view.php?task_id=' . $task_id);
            exit();
        }

        // Suspend the property rooms instantly
        $stmtRooms = $pdo->prepare("UPDATE rooms SET status = 'suspended' WHERE property_id = ?");
        $stmtRooms->execute(array($task['property_id']));

        // Complete the task
        $stmtUpdate = $pdo->prepare("UPDATE agent_tasks SET status = 'completed', completed_at = CURRENT_TIMESTAMP WHERE task_id = ?");
        $stmtUpdate->execute(array($task_id));

        // Insert a verification report capturing the emergency suspension
        $stmtRep = $pdo->prepare("
            INSERT INTO verification_reports (
                task_id, structural_safety, electrical_safety, fire_exit, gps_match, 
                neighborhood_safety, furnishing_match, bathroom_match, wifi_match, finance_match, 
                photo_path_1, photo_path_2, agent_comments
            ) VALUES (?, 0, 0, 0, 1, 1, 0, 0, 0, 0, 'suspended_no_image', 'suspended_no_image', ?)
        ");
        $stmtRep->execute(array($task_id, 'EMERGENCY SUSPENSION TRIGGERED. Reason: ' . $reason));

        unset($_SESSION['geofence_passed_' . $task_id]);

        $_SESSION['success'] = 'Property has been put under Emergency Suspension and hidden from public search.';
        $redirect = '../dashboard.php?tab=history';
    }

    $pdo->commit();
    header('Location: ' . $redirect);
    exit();

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    $_SESSION['error'] = 'Database transaction failed: ' . $e->getMessage();
    header('Location: ../dashboard.php');
    exit();
}
?>
