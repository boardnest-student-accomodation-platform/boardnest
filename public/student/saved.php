<?php
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../modules/students/services/portal.php';
requireRole('student');
$profile = studentPortalProfile($pdo, (int) $_SESSION['user_id']);
if (!$profile) { header('Location: ../../login.php?account_status=profile_missing'); exit(); }
$savedListings = studentPortalSavedListings($pdo, (int) $profile['student_id']);
$dashboardNotificationCount = studentPortalUnreadCount($pdo, (int) $_SESSION['user_id']);
$studentActivePage = 'saved';
$csrfToken = studentPortalEnsureCsrf();
$success = $_SESSION['flash']['success'] ?? '';
$error = $_SESSION['flash']['error'] ?? '';
unset($_SESSION['flash']['success'], $_SESSION['flash']['error']);
?>
<!DOCTYPE html><html lang="en"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Saved Listings | BoardNest</title>
<link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin><link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/student-dashboard.css"><link rel="stylesheet" href="../assets/css/student-portal.css">
</head><body class="student-dashboard-page student-portal-page">
<?php require __DIR__ . '/partials/dashboard_header.php'; ?>
<main class="student-portal-shell">
    <header class="student-portal-title"><div><span>Your Shortlist</span><h1>Saved Listings</h1><p>Review and manage boarding places you bookmarked while browsing.</p></div></header>
    <?php if ($success): ?><div class="student-portal-alert is-success" role="status"><?= htmlspecialchars($success) ?></div><?php endif; ?>
    <?php if ($error): ?><div class="student-portal-alert is-error" role="alert"><?= htmlspecialchars($error) ?></div><?php endif; ?>
    <?php if ($savedListings): ?><section class="student-saved-page-grid">
        <?php foreach ($savedListings as $listing): ?><article class="student-saved-page-card">
            <a class="student-saved-page-image" href="listing.php?id=<?= (int) $listing['listing_id'] ?>"><img src="<?= htmlspecialchars($listing['image_url']) ?>" alt="<?= htmlspecialchars($listing['title']) ?>"><span><?= htmlspecialchars($listing['city']) ?></span></a>
            <div class="student-saved-page-copy"><h2><?= htmlspecialchars($listing['title']) ?></h2><p><?= htmlspecialchars($listing['address']) ?></p><p><?= htmlspecialchars(mb_strimwidth((string) $listing['description'], 0, 120, '...')) ?></p><strong>LKR <?= number_format((float) $listing['monthly_rent'], 0) ?> <small>/month</small></strong><div><a class="student-portal-secondary" href="listing.php?id=<?= (int) $listing['listing_id'] ?>">View Details</a><form action="actions/remove_saved.php" method="post" data-remove-saved-form data-listing-title="<?= htmlspecialchars($listing['title']) ?>"><input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>"><input type="hidden" name="listing_id" value="<?= (int) $listing['listing_id'] ?>"><button class="student-remove-saved" type="submit" aria-label="Remove <?= htmlspecialchars($listing['title']) ?> from saved listings" title="Remove saved listing"><svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"></path><path d="M8 6V4h8v2"></path><path d="M19 6l-1 14H6L5 6"></path><path d="M10 11v5"></path><path d="M14 11v5"></path></svg><span>Remove</span></button></form></div></div>
        </article><?php endforeach; ?>
    </section><?php else: ?><section class="student-portal-panel student-portal-empty"><strong>You have no saved listings.</strong><p>Save a boarding place from Browse and it will appear here.</p><a href="search.php">Browse Listings</a></section><?php endif; ?>
</main>
<dialog class="student-confirm-dialog" id="removeSavedDialog" aria-labelledby="removeSavedTitle" aria-describedby="removeSavedMessage">
    <div class="student-confirm-dialog__panel">
        <div class="student-confirm-dialog__icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"></path><path d="M8 6V4h8v2"></path><path d="M19 6l-1 14H6L5 6"></path><path d="M10 11v5"></path><path d="M14 11v5"></path></svg></div>
        <div class="student-confirm-dialog__copy"><span>Saved Listing</span><h2 id="removeSavedTitle">Remove this listing?</h2><p id="removeSavedMessage">Are you sure you want to remove <strong data-remove-listing-name></strong> from your saved listings?</p></div>
        <div class="student-confirm-dialog__actions"><button class="student-confirm-dialog__cancel" type="button" data-remove-cancel>Keep Saved</button><button class="student-confirm-dialog__confirm" type="button" data-remove-confirm><svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"></path><path d="M8 6V4h8v2"></path><path d="M19 6l-1 14H6L5 6"></path></svg>Remove Listing</button></div>
    </div>
</dialog>
<footer class="student-dashboard-footer"><div><strong>BoardNest</strong><p>&copy; <?= date('Y') ?> BoardNest. Build a shortlist that works for you.</p></div><nav><a href="#">Privacy</a><a href="#">Terms</a><a href="#">Support</a></nav></footer><script src="../assets/js/student-dashboard.js"></script><script src="../assets/js/student-saved.js"></script></body></html>
