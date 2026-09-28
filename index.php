<?php

require_once __DIR__ . '/includes/session.php';
startSession();

// If not logged in, send to landing page
if (empty($_SESSION['user_id'])) {
    header('Location: /boardnest/index.html');
    exit();
}

// If logged in, send to the right dashboard based on role
switch ($_SESSION['role'] ?? '') {
    case 'student':
        header('Location: /boardnest/public/student/dashboard.php');
        break;

    case 'landlord':
        header('Location: /boardnest/modules/landlord/dashboard.php');
        break;

    case 'field_agent':
        header('Location: /boardnest/public/field_agent/dashboard.php');
        break;

    case 'admin':
        header('Location: /boardnest/modules/admin/views/dashboard.php');
        break;

    default:
        header('Location: /boardnest/login.php');
        break;
}

exit();
