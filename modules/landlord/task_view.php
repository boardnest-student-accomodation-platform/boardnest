<?php
require_once __DIR__ . '/../../includes/session.php';
startSession();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../login.php');
    exit();
}

require_once __DIR__ . '/../../config/db.php';

$user_id = $_SESSION['user_id'];
$error   = '';
$success = '';

// Status Update with IDOR Fix
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $task_id    = filter_input(INPUT_POST, 'task_id', FILTER_VALIDATE_INT);
    $new_status = trim($_POST['status'] ?? '');

    $allowed_statuses = ['pending', 'in_progress', 'completed'];

    if ($task_id && in_array($new_status, $allowed_statuses, true)) {
        try {
            // Check ownership/assignment securely
            $updateStmt = $pdo->prepare("UPDATE tasks SET status = ? WHERE task_id = ? AND (assigned_to = ? OR created_by = ?)");
            $updateStmt->execute([$new_status, $task_id, $user_id, $user_id]);

            if ($updateStmt->rowCount() > 0) {
                $success = "Task status updated!";
            } else {
                $error = "Permission denied or task not found.";
            }
        } catch (PDOException $e) {
            error_log("Task update error: " . $e->getMessage());
            $error = "Failed to update task.";
        }
    }
}

// Fetch Tasks
try {
    $stmt = $pdo->prepare("
        SELECT t.*, p.title AS property_title 
        FROM tasks t 
        LEFT JOIN properties p ON t.property_id = p.property_id 
        WHERE t.assigned_to = ? OR t.created_by = ? 
        ORDER BY t.created_at DESC
    ");
    $stmt->execute([$user_id, $user_id]);
    $tasks = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("Task fetch error: " . $e->getMessage());
    $tasks = [];
    $error = "Could not load tasks.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Task View — BoardNest</title>
    <link rel="stylesheet" href="../../public/assets/css/landlord.css">
</head>
<body>

<div class="landlord-container">
    <div class="page-header">
        <h2>My Tasks</h2>
        <a href="dashboard.php" class="btn-back">← Back to Dashboard</a>
    </div>

    <?php if ($error): ?>
        <div class="alert-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
        <div class="alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <?php if (empty($tasks)): ?>
        <p>No tasks assigned.</p>
    <?php else: ?>
        <table class="custom-table">
            <thead>
                <tr>
                    <th>Task ID</th>
                    <th>Property</th>
                    <th>Description</th>
                    <th>Priority</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($tasks as $task): ?>
                    <tr>
                        <td>#<?= htmlspecialchars($task['task_id']) ?></td>
                        <td><?= htmlspecialchars($task['property_title'] ?? 'General') ?></td>
                        <td><?= htmlspecialchars($task['description']) ?></td>
                        <td><?= htmlspecialchars(ucfirst($task['priority'] ?? 'medium')) ?></td>
                        <td>
                            <?php $curr = $task['status'] ?? 'pending'; ?>
                            <span class="badge badge-<?= htmlspecialchars($curr) ?>">
                                <?= htmlspecialchars(ucfirst(str_replace('_', ' ', $curr))) ?>
                            </span>
                        </td>
                        <td>
                            <form method="POST" action="" class="action-buttons">
                                <input type="hidden" name="task_id" value="<?= htmlspecialchars($task['task_id']) ?>">
                                <select name="status">
                                    <option value="pending" <?= $curr === 'pending' ? 'selected' : '' ?>>Pending</option>
                                    <option value="in_progress" <?= $curr === 'in_progress' ? 'selected' : '' ?>>In Progress</option>
                                    <option value="completed" <?= $curr === 'completed' ? 'selected' : '' ?>>Completed</option>
                                </select>
                                <button type="submit" name="update_status" class="btn btn-success">Update</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

</body>
</html>