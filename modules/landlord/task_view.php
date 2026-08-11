<?php
require_once __DIR__ . '/../../includes/session.php';
startSession();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../login.php');
    exit();
}

require_once __DIR__ . '/../../config/db.php';

$user_id = $_SESSION['user_id'];
$role    = $_SESSION['role'];
$error   = '';
$success = '';

// Status Update Logic
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $task_id    = $_POST['task_id'] ?? '';
    $new_status = $_POST['status'] ?? '';

    if (!empty($task_id) && !empty($new_status)) {
        try {
            $updateStmt = $pdo->prepare("UPDATE tasks SET status = ? WHERE task_id = ?");
            $updateStmt->execute([$new_status, $task_id]);
            $success = "Task status updated successfully!";
        } catch (PDOException $e) {
            $error = "Failed to update status: " . $e->getMessage();
        }
    }
}

// User Tasks Fetch 
try {
    if ($role === 'admin') {
        $stmt = $pdo->query("SELECT t.*, p.title AS property_title, u.full_name AS assigned_to_name 
                             FROM tasks t 
                             LEFT JOIN properties p ON t.property_id = p.property_id 
                             LEFT JOIN users u ON t.assigned_to = u.user_id 
                             ORDER BY t.created_at DESC");
    } else {
        $stmt = $pdo->prepare("SELECT t.*, p.title AS property_title 
                               FROM tasks t 
                               LEFT JOIN properties p ON t.property_id = p.property_id 
                               WHERE t.assigned_to = ? OR t.created_by = ? 
                               ORDER BY t.created_at DESC");
        $stmt->execute([$user_id, $user_id]);
    }
    $tasks = $stmt->fetchAll();
} catch (PDOException $e) {
    $tasks = [];
    $error = "Database Error: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Task View — BoardNest</title>
    <link rel="stylesheet" href="../../public/assets/css/style.css">
    <style>
        .container {
            max-width: 900px;
            margin: 40px auto;
            padding: 20px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.05);
            font-family: Arial, sans-serif;
        }
        .header-action { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .btn-back { text-decoration: none; color: #007bff; font-weight: bold; }
        .task-table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        .task-table th, .task-table td { padding: 12px; border: 1px solid #ddd; text-align: left; }
        .task-table th { background-color: #f8f9fa; }
        .badge { padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: bold; }
        .badge-pending { background: #fff3cd; color: #856404; }
        .badge-progress { background: #cce5ff; color: #004085; }
        .badge-completed { background: #d4edda; color: #155724; }
        .badge-high { color: #dc3545; font-weight: bold; }
        .msg-error { background: #f8d7da; color: #721c24; padding: 10px; border-radius: 4px; margin-bottom: 15px; }
        .msg-success { background: #d4edda; color: #155724; padding: 10px; border-radius: 4px; margin-bottom: 15px; }
    </style>
</head>
<body>

<div class="container">
    <div class="header-action">
        <h2>Assigned Tasks</h2>
        <a href="dashboard.php" class="btn-back">← Back to Dashboard</a>
    </div>

    <?php if ($error): ?>
        <div class="msg-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="msg-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <?php if (empty($tasks)): ?>
        <p style="color: #666;">No tasks assigned at the moment.</p>
    <?php else: ?>
        <table class="task-table">
            <thead>
                <tr>
                    <th>Task ID</th>
                    <th>Property</th>
                    <th>Task Description</th>
                    <th>Priority</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($tasks as $task): ?>
                    <tr>
                        <td>#<?= $task['task_id'] ?></td>
                        <td><?= htmlspecialchars($task['property_title'] ?? 'N/A') ?></td>
                        <td><?= htmlspecialchars($task['description'] ?? '') ?></td>
                        <td>
                            <span class="<?= ($task['priority'] ?? '') === 'high' ? 'badge-high' : '' ?>">
                                <?= ucfirst($task['priority'] ?? 'Normal') ?>
                            </span>
                        </td>
                        <td>
                            <?php 
                                $status = $task['status'] ?? 'pending';
                                $badgeClass = 'badge-pending';
                                if ($status === 'in_progress') $badgeClass = 'badge-progress';
                                if ($status === 'completed') $badgeClass = 'badge-completed';
                            ?>
                            <span class="badge <?= $badgeClass ?>"><?= ucfirst(str_replace('_', ' ', $status)) ?></span>
                        </td>
                        <td>
                            <form method="POST" action="" style="display: flex; gap: 5px;">
                                <input type="hidden" name="task_id" value="<?= $task['task_id'] ?>">
                                <select name="status" style="padding: 4px;">
                                    <option value="pending" <?= $status === 'pending' ? 'selected' : '' ?>>Pending</option>
                                    <option value="in_progress" <?= $status === 'in_progress' ? 'selected' : '' ?>>In Progress</option>
                                    <option value="completed" <?= $status === 'completed' ? 'selected' : '' ?>>Completed</option>
                                </select>
                                <button type="submit" name="update_status" style="padding: 4px 8px; background: #28a745; color: #fff; border: none; border-radius: 3px; cursor: pointer;">Save</button>
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