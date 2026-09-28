<?php
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../modules/students/services/dashboard.php';

requireRole('student');
$dashboard = getStudentDashboardData($pdo, (int) $_SESSION['user_id']);
if (!$dashboard) {
    session_unset();
    session_destroy();
    header('Location: ../../login.php?account_status=profile_missing');
    exit();
}

$profile = $dashboard['profile'];
if ($profile['account_status'] !== 'active') {
    $status = $profile['account_status'];
    session_unset();
    session_destroy();
    header('Location: ../../login.php?account_status=' . urlencode($status));
    exit();
}

$counts = $dashboard['counts'];
$bookings = $dashboard['bookings'];
$savedListings = $dashboard['saved_listings'];
$dashboardNotificationCount = $counts['notifications'];
$dashboardHasProfileDrawer = true;
$studentActivePage = 'overview';
$isVerified = $profile['verf_tier'] === 'tier2';
$nameParts = explode(' ', trim($profile['full_name']));
$firstName = $nameParts[0] ?: 'Student';
$hour = (int) date('G');
$greeting = $hour < 12 ? 'Good morning' : ($hour < 17 ? 'Good afternoon' : 'Good evening');
$deadline = $profile['verf_deadline'] ? new DateTime($profile['verf_deadline']) : null;
$today = new DateTime('today');
$daysUntilDeadline = $deadline ? (int) $today->diff($deadline)->format('%r%a') : null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard | BoardNest</title>
    <meta name="description" content="Manage your BoardNest student account, bookings, and saved boarding places.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/student-dashboard.css">
</head>
<body class="student-dashboard-page">
    <?php require __DIR__ . '/partials/dashboard_header.php'; ?>
    <main class="student-dashboard-shell">
        <div class="student-dashboard-columns">
            <div class="student-dashboard-main">
                <section class="student-dashboard-welcome">
                    <div><h1><?= htmlspecialchars($greeting . ', ' . $firstName) ?></h1><p>Here is what is happening with your boarding search.</p></div>
                    <span class="student-tier-badge <?= $isVerified ? 'is-verified' : 'is-pending' ?>"><?= $isVerified ? '&#10003; Verified Student (Tier 2)' : '&#9203; Pending Verification (Tier 1)' ?></span>
                </section>

                <section id="notifications" class="student-verification-banner <?= $isVerified ? 'is-verified' : 'is-pending' ?>">
                    <span class="student-verification-icon" aria-hidden="true"><?= $isVerified ? '&#10003;' : '!' ?></span>
                    <div>
                        <strong><?= $isVerified ? 'Tier 2 Verified Account' : 'Student verification pending' ?></strong>
                        <?php if ($isVerified): ?>
                            <p>Your student identity has been confirmed and your verified badge is visible to landlords.</p>
                        <?php elseif ($deadline): ?>
                            <p>Your student verification is pending. Please ensure your documents are valid. Your account will be suspended on <?= htmlspecialchars($deadline->format('j F Y')) ?> if not verified.</p>
                            <?php if ($daysUntilDeadline !== null && $daysUntilDeadline <= 7): ?><small><?= max(0, $daysUntilDeadline) ?> day<?= $daysUntilDeadline === 1 ? '' : 's' ?> remaining</small><?php endif; ?>
                        <?php else: ?>
                            <p>You can browse and submit booking requests while Admin reviews your documents. Landlords will see a Pending Verification badge.</p>
                        <?php endif; ?>
                    </div>
                </section>

                <nav class="student-quick-actions" aria-label="Quick actions">
                    <a class="is-primary" href="search.php"><span aria-hidden="true">&#10148;</span><div><strong>Browse Listings</strong><small>Find nearby annexes</small></div></a>
                    <a href="#bookings"><span aria-hidden="true">&#9638;</span><div><strong>My Bookings</strong><small><?= $counts['bookings'] ?> active request<?= $counts['bookings'] === 1 ? '' : 's' ?></small></div></a>
                    <a href="#student-profile" data-profile-toggle aria-controls="student-profile" aria-expanded="false"><span aria-hidden="true">&#9783;</span><div><strong>My Profile</strong><small>Review student details</small></div></a>
                </nav>

                <section class="student-stat-grid" aria-label="Account activity">
                    <a href="#bookings"><span>Bookings</span><strong><?= $counts['bookings'] ?></strong><small>Pending / active</small></a>
                    <a href="saved.php"><span>Saved</span><strong><?= $counts['saved'] ?></strong><small>Bookmarked rooms</small></a>
                    <a id="complaints" href="#complaints"><span>Complaints</span><strong><?= $counts['complaints'] ?></strong><small>Submitted tickets</small></a>
                    <a id="reviews" href="#reviews"><span>Reviews</span><strong><?= $counts['reviews'] ?></strong><small>Submitted on hosts</small></a>
                </section>

                <section id="bookings" class="student-dashboard-panel">
                    <div class="student-panel-heading"><h2>Recent Booking Requests <small><?= $counts['bookings'] ?> active</small></h2><a href="#bookings">View All Bookings &#8594;</a></div>
                    <?php if ($bookings): ?>
                        <div class="student-booking-list">
                            <?php foreach ($bookings as $booking): ?>
                                <?php [$bookingLabel, $bookingClass] = studentDashboardBookingState($booking['status']); ?>
                                <article class="student-booking-row">
                                    <span class="student-booking-icon" aria-hidden="true">&#9638;</span>
                                    <div><strong><?= htmlspecialchars($booking['title']) ?></strong><small><?= htmlspecialchars($booking['city']) ?><?= $booking['move_in'] ? ' - Move-in: ' . htmlspecialchars(date('j M Y', strtotime($booking['move_in']))) : ' - Current request' ?></small></div>
                                    <span class="student-booking-status is-<?= htmlspecialchars($bookingClass) ?>"><?= htmlspecialchars($bookingLabel) ?></span>
                                    <a href="listing.php?id=<?= (int) $booking['listing_id'] ?>">View</a>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="student-dashboard-empty"><strong>No booking requests yet</strong><p>Browse available rooms and submit a request when you find the right place.</p><a href="search.php">Browse Listings</a></div>
                    <?php endif; ?>
                </section>

                <section id="saved" class="student-dashboard-panel student-saved-panel">
                    <div class="student-panel-heading"><h2>Recently Saved Listings <small><?= $counts['saved'] ?> saved</small></h2><a href="saved.php">View All Saved &#8594;</a></div>
                    <?php if ($savedListings): ?>
                        <div class="student-saved-grid">
                            <?php foreach ($savedListings as $listing): ?>
                                <article class="student-saved-card">
                                    <a class="student-saved-image" href="listing.php?id=<?= (int) $listing['listing_id'] ?>"><img src="<?= htmlspecialchars($listing['image_url']) ?>" alt="<?= htmlspecialchars($listing['title']) ?>"><span><?= htmlspecialchars($listing['city']) ?></span></a>
                                    <div><h3><?= htmlspecialchars($listing['title']) ?></h3><p><?= htmlspecialchars($listing['address']) ?></p><strong>LKR <?= number_format((float) $listing['monthly_rent'], 0) ?> <small>/mo</small></strong><a href="listing.php?id=<?= (int) $listing['listing_id'] ?>">View Details</a></div>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="student-dashboard-empty"><strong>No saved listings yet</strong><p>Save boarding places while browsing to compare them here.</p><a href="search.php">Explore Listings</a></div>
                    <?php endif; ?>
                </section>
            </div>

            <aside id="student-profile" class="student-profile-card student-profile-drawer" role="dialog" aria-modal="true" aria-label="Student profile" aria-hidden="true">
                <button class="student-profile-close" type="button" aria-label="Close profile" title="Close profile" data-profile-close>&times;</button>
                <div class="student-profile-heading"><span class="student-profile-avatar"><?= htmlspecialchars(studentDashboardInitials($profile['full_name'])) ?></span><div><h2><?= htmlspecialchars($profile['full_name']) ?></h2><span class="student-tier-badge <?= $isVerified ? 'is-verified' : 'is-pending' ?>"><?= $isVerified ? '&#10003; Verified Student (Tier 2)' : '&#9203; Pending Verification' ?></span></div></div>
                <dl>
                    <div><dt>Email</dt><dd><?= htmlspecialchars($profile['email']) ?></dd></div>
                    <div><dt>University</dt><dd><?= htmlspecialchars($profile['university'] ?: 'Not provided') ?></dd></div>
                    <div><dt>Academic Year</dt><dd><?= htmlspecialchars($profile['academic_year'] ?: 'Not provided') ?></dd></div>
                    <div><dt>Mobile</dt><dd><?= htmlspecialchars($profile['mobile'] ?: 'Not provided') ?></dd></div>
                    <div><dt>National ID</dt><dd><?= htmlspecialchars($profile['nic_number'] ?: 'Not provided') ?></dd></div>
                </dl>
                <a class="student-profile-edit" href="profile.php#change-password">Change Password</a>
            </aside>
            <button class="student-profile-backdrop" type="button" aria-label="Close profile" data-profile-close hidden></button>
        </div>
    </main>
    <footer class="student-dashboard-footer"><div><strong>BoardNest</strong><p>&copy; <?= date('Y') ?> BoardNest. All rights reserved. Built for secure campus dwelling.</p></div><nav><a href="#">Privacy</a><a href="#">Terms</a><a href="#">Support</a></nav></footer>
    <script src="../assets/js/student-dashboard.js"></script>
</body>
</html>
