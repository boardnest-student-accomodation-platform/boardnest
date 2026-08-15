<?php
function requireRole($role) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== $role) {
        // Absolute path (/boardnest/login.php) to Relative Path 
        header('Location: ../../login.php');
        exit();
    }
}

function startSession() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}
?>