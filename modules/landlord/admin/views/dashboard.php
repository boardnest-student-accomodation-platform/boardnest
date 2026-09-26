<?php
require_once '../../../includes/session.php';
requireRole('admin');

require_once '../../../config/db.php';

/* Dashboard Counts */

// Total students
$stmt = $pdo->query("
    SELECT COUNT(*) 
    FROM users 
    WHERE role = 'student'
");
$totalStudents = $stmt->fetchColumn();

// Total landlords
$stmt = $pdo->query("
    SELECT COUNT(*) 
    FROM users 
    WHERE role = 'landlord'
");
$totalLandlords = $stmt->fetchColumn();

// Total field agents
$stmt = $pdo->query("
    SELECT COUNT(*) 
    FROM users 
    WHERE role = 'field_agent'
");
$totalFieldAgents = $stmt->fetchColumn();

// Pending registrations
$stmt = $pdo->query("
    SELECT COUNT(*) 
    FROM users 
    WHERE status = 'pending'
");
$pendingRegistrations = $stmt->fetchColumn();

// Listings currently live
$stmt = $pdo->query("
    SELECT COUNT(*)
    FROM listings
    WHERE status = 'live'
");
$listingsLive = $stmt->fetchColumn();

// Listings waiting for Admin approval
$stmt = $pdo->query("
    SELECT COUNT(*)
    FROM listings
    WHERE status = 'awaiting_approval'
");
$listingsPending = $stmt->fetchColumn();

// Complaints submitted this month
/*$stmt = $pdo->query("
    SELECT COUNT(*)
    FROM complaints
    WHERE submitted_at >= DATE_FORMAT(CURRENT_DATE, '%Y-%m-01')
      AND submitted_at < DATE_ADD(
            DATE_FORMAT(CURRENT_DATE, '%Y-%m-01'),
            INTERVAL 1 MONTH
          )
");
$complaintsThisMonth = $stmt->fetchColumn();*/

/* Pending Registration List */

$stmt = $pdo->query("
    SELECT 
        user_id,
        full_name,
        role,
        created_at
    FROM users
    WHERE status = 'pending'
    ORDER BY created_at DESC
    LIMIT 5
");

$pendingUsers = $stmt->fetchAll();

/* Listing Approval Queue */

/* $stmt = $pdo->query("
    SELECT
        l.id AS listing_id,
        l.title,
        l.status,
        l.submitted_at,
        p.address,
        c.name AS city_name
    FROM listings l
    INNER JOIN rooms r
        ON l.room_id = r.id
    INNER JOIN properties p
        ON r.property_id = p.id
    INNER JOIN cities c
        ON p.city_id = c.id
    WHERE l.status = 'awaiting_approval'
    ORDER BY l.submitted_at DESC
    LIMIT 5
");

$pendingListings = $stmt->fetchAll();*/

/* Complaint Queue */

/*$stmt = $pdo->query("
    SELECT
        c.id AS complaint_id,
        c.category,
        c.status,
        c.submitted_at,
        l.title AS listing_title,
        u.full_name AS complainant_name
    FROM complaints c
    INNER JOIN listings l
        ON c.listing_id = l.id
    INNER JOIN users u
        ON c.complainant_user_id = u.user_id
    WHERE c.status IN (
        'new',
        'assigned',
        'under_investigation',
        'escalated'
    )
    ORDER BY c.submitted_at DESC
    LIMIT 5
");

$complaintQueue = $stmt->fetchAll();*/
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Admin Dashboard</title>

    <!-- Shared stylesheet -->
    <link rel="stylesheet" href="../../../public/assets/css/style.css">

    <!-- Admin-specific stylesheet -->
    <link rel="stylesheet" href="../../../public/assets/css/admin.css">
</head>

<body class="admin-body">

    <div class="admin-layout">

        <!-- ADMIN SIDEBAR -->
        <aside class="admin-sidebar">

            <div class="admin-sidebar-brand">
                <div class="admin-brand-icon">B</div>

                <div>
                    <h2 class="admin-brand-title">BoardNest</h2>
                    <span class="admin-brand-subtitle">Admin Portal</span>
                </div>
            </div>

            <nav class="admin-navigation">

                <a href="dashboard.php"
                    class="admin-nav-item admin-nav-item-active">
                    <span class="admin-nav-icon">⌂</span>
                    <span>Dashboard</span>
                </a>

                <a href="../../approvals/views/registration_approvals.php" class="admin-nav-item">
                    <span class="admin-nav-icon">✓</span>
                    <span>Registration Approvals</span>
                </a>

                <a href="../../approvals/views/listings_decisions.php" class="admin-nav-item">
                    <span class="admin-nav-icon">▤</span>
                    <span>Listings</span>
                </a>

                <a href="../../complaints/views/complaints.php" class="admin-nav-item">
                    <span class="admin-nav-icon">⚖</span>
                    <span>Complaint Moderation</span>
                </a>

                <a href="../../approvals/views/field_agent_manage.php" class="admin-nav-item">
                    <span class="admin-nav-icon">♙</span>
                    <span>Field Agents</span>
                </a>

                <a href="area_profiles.php" class="admin-nav-item">
                    <span class="admin-nav-icon">⌖</span>
                    <span>Area Profiles</span>
                </a>

                <a href="../../announcements/views/announcements.php" class="admin-nav-item">
                    <span class="admin-nav-icon">⚑</span>
                    <span>Send Announcement</span>
                </a>

            </nav>

            <div class="admin-sidebar-bottom">

                <a href="../../../logout.php" class="admin-nav-item admin-signout">
                    <span class="admin-nav-icon">↪</span>
                    <span>Sign Out</span>
                </a>

            </div>

        </aside>


        <!-- MAIN CONTENT -->
        <main class="admin-main">

            <!-- TOP BAR -->
            <header class="admin-topbar">

                <div class="admin-page-heading">
                    <h1>Dashboard</h1>
                    <p>Overview of BoardNest platform activity.</p>
                </div>

                <div class="admin-topbar-actions">

                    <div class="admin-profile">

                        <div class="admin-profile-avatar">
                            A
                        </div>

                        <div class="admin-profile-details">
                            <span class="admin-profile-name">Admin</span>
                            <span class="admin-profile-role">Administrator</span>
                        </div>

                    </div>

                </div>

            </header>


            <!-- SUMMARY CARDS -->
            <section class="admin-summary-grid">

                <article class="admin-summary-card">

                    <div class="admin-summary-icon admin-summary-icon-student">
                        S
                    </div>

                    <div class="admin-summary-content">
                        <span class="admin-summary-label">
                            Total Students
                        </span>

                        <strong class="admin-summary-value">
                            <?= htmlspecialchars($totalStudents) ?>
                        </strong>
                    </div>

                </article>


                <article class="admin-summary-card">

                    <div class="admin-summary-icon admin-summary-icon-landlord">
                        L
                    </div>

                    <div class="admin-summary-content">
                        <span class="admin-summary-label">
                            Total Landlords
                        </span>

                        <strong class="admin-summary-value">
                            <?= htmlspecialchars($totalLandlords) ?>
                        </strong>
                    </div>

                </article>


                <article class="admin-summary-card">

                    <div class="admin-summary-icon admin-summary-icon-agent">
                        F
                    </div>

                    <div class="admin-summary-content">
                        <span class="admin-summary-label">
                            Total Field Agents
                        </span>

                        <strong class="admin-summary-value">
                            <?= htmlspecialchars($totalFieldAgents) ?>
                        </strong>
                    </div>

                </article>


                <article class="admin-summary-card">

                    <div class="admin-summary-icon admin-summary-icon-live">
                        ✓
                    </div>

                    <div class="admin-summary-content">
                        <span class="admin-summary-label">
                            Listings Live
                        </span>

                        <strong class="admin-summary-value">
                            <?/*= htmlspecialchars($listingsLive)*/ ?>
                        </strong>
                    </div>

                </article>


                <article class="admin-summary-card">

                    <div class="admin-summary-icon admin-summary-icon-pending">
                        P
                    </div>

                    <div class="admin-summary-content">
                        <span class="admin-summary-label">
                            Listings Pending Approval
                        </span>

                        <strong class="admin-summary-value">
                            <?/*= htmlspecialchars($listingsPending) */ ?>
                        </strong>
                    </div>

                </article>


                <article class="admin-summary-card">

                    <div class="admin-summary-icon admin-summary-icon-complaint">
                        !
                    </div>

                    <div class="admin-summary-content">
                        <span class="admin-summary-label">
                            Complaints This Month
                        </span>

                        <strong class="admin-summary-value">
                            <?/*= htmlspecialchars($complaintsThisMonth) */ ?>
                        </strong>
                    </div>

                </article>

            </section>


            <!-- DASHBOARD QUEUES -->
            <section class="admin-queue-grid">


                <!-- PENDING REGISTRATIONS -->
                <article class="admin-queue-panel">

                    <div class="admin-queue-header">

                        <div>
                            <h2>Pending Registrations</h2>
                            <p>Users waiting for approval.</p>
                        </div>

                        <button
                            type="button"
                            class="admin-text-button"
                            data-admin-action="registrations">
                            View All
                        </button>

                    </div>


                    <div class="admin-table-wrapper">

                        <table class="admin-table">

                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Role</th>
                                    <th>Submitted</th>
                                    <th>Action</th>
                                </tr>
                            </thead>

                            <tbody>
                                <?php if (empty($pendingUsers)): ?>

                                    <tr>
                                        <td colspan="4">No pending registrations.</td>
                                    </tr>

                                <?php else: ?>

                                    <?php foreach ($pendingUsers as $user): ?>

                                        <tr>
                                            <td>
                                                <?= htmlspecialchars($user['full_name']) ?>
                                            </td>

                                            <td>
                                                <?= htmlspecialchars(ucwords(str_replace('_', ' ', $user['role']))) ?>
                                            </td>

                                            <td>
                                                <?= htmlspecialchars(date('M d, Y', strtotime($user['created_at']))) ?>
                                            </td>

                                            <td>
                                                <a
                                                    href="#"
                                                    class="admin-action-button"
                                                    data-admin-review="<?= htmlspecialchars($user['user_id']) ?>">
                                                    Review
                                                </a>
                                            </td>
                                        </tr>

                                    <?php endforeach; ?>

                                <?php endif; ?>
                            </tbody>

                        </table>

                    </div>

                </article>


                <!-- LISTING APPROVALS -->

                <article class="admin-queue-panel">

                    <div class="admin-queue-header">

                        <div>
                            <h2>Listing Approvals</h2>
                            <p>Listings waiting for Admin approval.</p>
                        </div>

                        <a
                            href="listings_decisions.php"
                            class="admin-text-button">
                            View All
                        </a>

                    </div>


                    <div class="admin-table-wrapper">

                        <table class="admin-table">

                            <thead>

                                <tr>
                                    <th>Property</th>
                                    <th>Area</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>

                            </thead>


                            <tbody>

                                <?php if (empty($pendingListings)): ?>

                                    <tr>

                                        <td colspan="4">
                                            No listings are currently waiting for approval.
                                        </td>

                                    </tr>

                                <?php else: ?>

                                    <?php foreach ($pendingListings as $listing): ?>

                                        <tr>

                                            <td>
                                                <?= htmlspecialchars($listing['title']) ?>
                                            </td>


                                            <td>
                                                <?= htmlspecialchars($listing['city_name']) ?>
                                            </td>


                                            <td>

                                                <span class="badge badge--pending">
                                                    Pending Approval
                                                </span>

                                            </td>


                                            <td>

                                                <a
                                                    href="listings_decisions.php?id=<?= htmlspecialchars($listing['listing_id']) ?>"
                                                    class="admin-action-button">
                                                    Review
                                                </a>

                                            </td>

                                        </tr>

                                    <?php endforeach; ?>

                                <?php endif; ?>

                            </tbody>

                        </table>

                    </div>

                </article>


                <!-- COMPLAINT QUEUE -->
                <article class="admin-queue-panel">

                    <div class="admin-queue-header">

                        <div>
                            <h2>Complaint Queue</h2>
                            <p>Complaints requiring attention.</p>
                        </div>

                        <a
                            href="Complaints.php"
                            class="admin-text-button">
                            View All
                        </a>

                    </div>


                    <div class="admin-table-wrapper">

                        <table class="admin-table">

                            <thead>
                                <tr>
                                    <th>Property / User</th>
                                    <th>Type</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>

                            <tbody>

                                <?php if (empty($complaintQueue)): ?>

                                    <tr>

                                        <td colspan="4">
                                            No complaints currently require attention.
                                        </td>

                                    </tr>

                                <?php else: ?>

                                    <?php foreach ($complaintQueue as $complaint): ?>

                                        <?php
                                        $complaintStatus = $complaint['status'];

                                        switch ($complaintStatus) {

                                            case 'new':
                                                $statusLabel = 'New';
                                                $statusClass = 'badge--pending';
                                                break;

                                            case 'assigned':
                                                $statusLabel = 'Assigned';
                                                $statusClass = 'badge--outline';
                                                break;

                                            case 'under_investigation':
                                                $statusLabel = 'Investigating';
                                                $statusClass = 'badge--outline';
                                                break;

                                            case 'escalated':
                                                $statusLabel = 'Escalated';
                                                $statusClass = 'badge--error';
                                                break;

                                            default:
                                                $statusLabel = ucfirst(
                                                    str_replace('_', ' ', $complaintStatus)
                                                );
                                                $statusClass = 'badge--muted';
                                        }
                                        ?>

                                        <tr>

                                            <td>

                                                <?php if (!empty($complaint['listing_title'])): ?>

                                                    <?= htmlspecialchars($complaint['listing_title']) ?>

                                                <?php else: ?>

                                                    <?= htmlspecialchars($complaint['complainant_name']) ?>

                                                <?php endif; ?>

                                            </td>


                                            <td>
                                                <?= htmlspecialchars(
                                                    ucwords(
                                                        str_replace(
                                                            '_',
                                                            ' ',
                                                            $complaint['category']
                                                        )
                                                    )
                                                ) ?>
                                            </td>


                                            <td>

                                                <span class="badge <?= htmlspecialchars($statusClass) ?>">
                                                    <?= htmlspecialchars($statusLabel) ?>
                                                </span>

                                            </td>


                                            <td>

                                                <a
                                                    href="Complaints.php?id=<?= htmlspecialchars($complaint['complaint_id']) ?>"
                                                    class="admin-action-button">
                                                    Review
                                                </a>

                                            </td>

                                        </tr>

                                    <?php endforeach; ?>

                                <?php endif; ?>

                            </tbody>

                        </table>

                    </div>

                </article>

            </section>

        </main>

    </div>


    <script src="../../../public/assets/js/admin.js"></script>

</body>

</html>