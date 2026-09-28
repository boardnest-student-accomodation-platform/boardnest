<?php
$dashboardNotificationCount = (int) ($dashboardNotificationCount ?? 0);
$dashboardHasProfileDrawer = (bool) ($dashboardHasProfileDrawer ?? false);
$studentActivePage = (string) ($studentActivePage ?? '');
?>
<header class="student-dashboard-header">
    <a class="student-dashboard-brand" href="dashboard.php">BoardNest</a>
    <button class="student-dashboard-menu" type="button" aria-label="Open dashboard navigation" aria-expanded="false" data-dashboard-menu>&#9776;</button>
    <nav class="student-dashboard-nav" aria-label="Student dashboard" data-dashboard-nav>
        <a href="search.php">Browse</a>
        <a href="dashboard.php#bookings">My Bookings</a>
        <a class="<?= $studentActivePage === 'saved' ? 'is-active' : '' ?>" href="saved.php">Saved</a>
        <a class="<?= $studentActivePage === 'notifications' ? 'is-active' : '' ?>" href="notifications.php">Notifications<?php if ($dashboardNotificationCount > 0): ?><b class="student-nav-count"><?= $dashboardNotificationCount ?></b><?php endif; ?></a>
        <a class="<?= $studentActivePage === 'complaints' ? 'is-active' : '' ?>" href="complaints.php">Complaints</a>
        <?php if ($dashboardHasProfileDrawer): ?>
            <a href="#student-profile" data-profile-toggle aria-controls="student-profile" aria-expanded="false">Profile</a>
        <?php else: ?>
            <a class="<?= $studentActivePage === 'profile' ? 'is-active' : '' ?>" href="profile.php">Profile</a>
        <?php endif; ?>
    </nav>
    <div class="student-dashboard-header-actions">
        <a class="student-dashboard-bell" href="notifications.php" aria-label="Notifications" title="Notifications"><span aria-hidden="true">&#9906;</span><?php if ($dashboardNotificationCount > 0): ?><i aria-hidden="true"><?= $dashboardNotificationCount ?></i><?php endif; ?></a>
        <a class="student-dashboard-logout" href="../../logout.php">Logout</a>
        <span class="student-dashboard-user-name"><?= htmlspecialchars($_SESSION['full_name'] ?? 'Student') ?></span>
        <?php if ($dashboardHasProfileDrawer): ?>
            <a class="student-dashboard-account" href="#student-profile" aria-label="Profile" title="Profile" data-profile-toggle aria-controls="student-profile" aria-expanded="false"><?= htmlspecialchars(strtoupper(substr($_SESSION['full_name'] ?? 'S', 0, 1))) ?></a>
        <?php else: ?>
            <a class="student-dashboard-account" href="profile.php" aria-label="Profile" title="Profile"><?= htmlspecialchars(strtoupper(substr($_SESSION['full_name'] ?? 'S', 0, 1))) ?></a>
        <?php endif; ?>
    </div>
</header>
