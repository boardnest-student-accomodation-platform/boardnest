<?php

require_once __DIR__ . '/src/bootstrap.php';

// If not logged in, send to landing page
if (!Auth::isLoggedIn()) {
    header('Location: /boardnest/index.html');
    exit();
}

// If logged in, send to the right dashboard based on role
switch (Auth::user()['role']) {
    case 'student':
        header('Location: /boardnest/public/student/dashboard.php');
        break;

    case 'landlord':
        header('Location: /boardnest/public/landlord/dashboard.php');
        break;

    case 'field_agent':
        header('Location: /boardnest/public/field_agent/dashboard.php');
        break;

    case 'admin':
        header('Location: /boardnest/public/admin/dashboard.php');
        break;

    default:
        header('Location: /boardnest/login.php');
        break;
}

exit();
