<?php
// Dedicated Logout Handler for Field Agent Module
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
session_destroy();

// Redirect back to the beautiful Field Agent login page
header('Location: login.php');
exit();
?>
