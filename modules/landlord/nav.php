<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>

<nav class="landlord-navbar">
    <div class="navbar-brand">
        <a href="dashboard.php">BoardNest</a>
    </div>

    <div class="navbar-links">
        <a href="dashboard.php"
           class="<?= $current_page === 'dashboard.php' ? 'active' : '' ?>">
            Dashboard
        </a>

        <a href="add_property.php"
           class="<?= $current_page === 'add_property.php' ? 'active' : '' ?>">
            Add Property
        </a>

        <a href="task_view.php"
           class="<?= $current_page === 'task_view.php' ? 'active' : '' ?>">
            Tasks
        </a>

        <a href="../../logout.php" class="navbar-logout">
            Logout
        </a>
    </div>
</nav>