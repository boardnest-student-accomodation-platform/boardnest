<?php
// ============================================================
// BoardNest — Field Agent Task View (Orchestrator)
// public/field_agent/task_view.php
// All logic in src/field_agent/ | Partials in partials/
// ============================================================
require_once dirname(__FILE__) . '/../../src/field_agent/task_view.php';

// Shorthand for component paths
define('PARTIALS', dirname(__FILE__) . '/partials/');
define('MODALS',   dirname(__FILE__) . '/../../src/field_agent/components/');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BoardNest — Task Audit Details</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Outfit:wght@800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/field_agent.css">

</head>
<body>
    <?php require PARTIALS . 'global_header.php'; ?>

    <div class="main-container">

        <?php if ($success_msg): ?>
            <div class="fa-alert fa-alert-success">
                ✅ <?php echo htmlspecialchars($success_msg); ?>
            </div>
        <?php endif; ?>
        <?php if ($error_msg): ?>
            <div class="fa-alert fa-alert-danger">
                ⚠️ <?php echo htmlspecialchars($error_msg); ?>
            </div>
        <?php endif; ?>

        <?php if ($complaint_id > 0): ?>
            <?php 
            $show_complaint_part = 'header';
            require PARTIALS . 'complaint_view.php'; 

            // Extract condition to avoid linter parsing issues with nested arrays in alternative syntax
            $is_complaint_resolved = isset($complaint['status']) && in_array($complaint['status'], array('resolved', 'upheld', 'dismissed', 'escalated'));
            ?>

            <?php if ($is_complaint_resolved): ?>
                <?php 
                $show_complaint_part = 'form';
                require PARTIALS . 'complaint_view.php'; 
                ?>
            <?php else: ?>
                <?php require PARTIALS . 'task_gps_geofence.php'; ?>

                <?php if (isset($_SESSION['geofence_passed_comp_' . $complaint_id])): ?>
                    <?php 
                    $show_complaint_part = 'form';
                    require PARTIALS . 'complaint_view.php'; 
                    ?>
                <?php endif; ?>
            <?php endif; ?>

        <?php else: ?>
            <?php require PARTIALS . 'task_checklist_item.php'; // loads renderChecklistItem() ?>

            <div class="audit-two-column-layout">
                <!-- LEFT: Sticky property context -->
                <div class="left-property-sidebar">
                    <?php require PARTIALS . 'task_property_sidebar.php'; ?>
                </div>

                <!-- RIGHT: GPS gate + Audit form -->
                <div>
                    <?php if (isset($task['status']) && $task['status'] === 'completed'): ?>
                        <?php if ($report): ?>
                            <?php require PARTIALS . 'task_audit_form.php'; ?>
                        <?php else: ?>
                            <div class="fa-alert fa-alert-danger">Error: Completed task is missing its verification report.</div>
                        <?php endif; ?>
                    <?php else: ?>
                        <?php require PARTIALS . 'task_gps_geofence.php'; ?>

                        <?php if (isset($_SESSION['geofence_passed_' . $task_id])): ?>
                            <?php require PARTIALS . 'task_audit_form.php'; ?>
                        <?php endif; ?>

                        <?php require PARTIALS . 'task_emergency_suspension.php'; ?>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>

    </div>

    <!-- Modals -->
    <?php require dirname(__FILE__) . '/../../modules/verification/views/components/live_camera_modal.php'; ?>
    <?php require MODALS . 'agent_guide_modal.php'; ?>

    <script src="../assets/js/field_agent.js?v=5"></script>
</body>
</html>
