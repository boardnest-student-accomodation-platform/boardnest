<?php
function requireRole($role) {
    if (session_id() === '') {
        session_start();
    }
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== $role) {
        // A module may be nested at different depths under public/ or modules/.
        // Derive the project URL prefix from the executing script (not this include).
        $scriptPath = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
        $prefix = '';
        if (preg_match('~^(.*?)/(?:public|modules)/~', $scriptPath, $match)) {
            $prefix = $match[1];
        }
        header('Location: ' . $prefix . '/login.php');
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