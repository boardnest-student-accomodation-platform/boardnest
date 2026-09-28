<?php
$host = getenv('BOARDNEST_DB_HOST') ?: 'localhost';
$dbname = getenv('BOARDNEST_DB_NAME') ?: 'boardnest';
$username = getenv('BOARDNEST_DB_USER') ?: 'root';
$password = getenv('BOARDNEST_DB_PASSWORD');
if ($password === false) {
    $password = '';
}

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $username,
        $password
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>
