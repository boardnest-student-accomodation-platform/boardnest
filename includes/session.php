<?php
function requireRole($role) {
    if (session_id() === '') {
        session_start();
    }
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== $role) {
        // Absolute path (/boardnest/login.php) to Relative Path 
        header('Location: ../../login.php');
        exit();
    }
}

function startSession() {
    if (session_id() === '') {
        session_start();
    }
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
}
?>