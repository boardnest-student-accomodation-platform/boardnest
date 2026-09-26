<?php
// Reset admin password to a known default and activate account
require_once __DIR__ . '/../../config/db.php'; // adjust path to config

$newPassword = 'admin123'; // default password you can change
$newHash = password_hash($newPassword, PASSWORD_DEFAULT);

$stmt = $pdo->prepare('UPDATE users SET password_hash = ?, status = ? WHERE role = ?');
$stmt->execute([$newHash, 'active', 'admin']);

echo "Admin password reset to '{$newPassword}' and status set to active.";
