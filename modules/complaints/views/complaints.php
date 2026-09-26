<?php
require_once '../../../config/db.php';
require_once '../../../includes/session.php';

requireRole('admin');

startSession();

$success = $_SESSION['success'] ?? '';
$error = $_SESSION['error'] ?? '';

unset($_SESSION['success'], $_SESSION['error']);

$tab = $_GET['tab'] ?? 'new';

$allowedTabs = ['new', 'assigned', 'resolved'];

if (!in_array($tab, $allowedTabs, true)) {
    $tab = 'new';
}

/* Complaint counts */

/*$countNew = 0;
$countAssigned = 0;
$countResolved = 0;

$countStmt = $pdo->query("
    SELECT
        SUM(status = 'new') AS new_count,
        SUM(status IN ('assigned', 'under_investigation')) AS assigned_count,
        SUM(status IN ('upheld', 'dismissed', 'escalated')) AS resolved_count
    FROM complaints
");

$countData = $countStmt->fetch();

$countNew = (int) ($countData['new_count'] ?? 0);
$countAssigned = (int) ($countData['assigned_count'] ?? 0);
$countResolved = (int) ($countData['resolved_count'] ?? 0); */


/*
|--------------------------------------------------------------------------
| Complaint query
|--------------------------------------------------------------------------
|
| complaints:
|   id
|   listing_id
|   complainant_user_id
|   landlord_user_id
|   category
|   description
|   status
|   unverified_stay
|   submitted_at
|
| listings:
|   id
|   title
|
| users:
|   user_id
|   full_name
|
| complaint_investigations:
|   complaint_id
|   field_agent_user_id
|
*/

/*$statusCondition = '';

if ($tab === 'new') {
    $statusCondition = "c.status = 'new'";
} elseif ($tab === 'assigned') {
    $statusCondition = "c.status IN ('assigned', 'under_investigation')";
} elseif ($tab === 'resolved') {
    $statusCondition = "c.status IN ('upheld', 'dismissed', 'escalated')";
}

$sql = "
    SELECT
        c.id,
        c.listing_id,
        c.complainant_user_id,
        c.landlord_user_id,
        c.category,
        c.description,
        c.status,
        c.unverified_stay,
        c.submitted_at,

        complainant.full_name AS complainant_name,
        landlord.full_name AS landlord_name,

        l.title AS listing_title,

        ci.field_agent_user_id,
        agent.full_name AS field_agent_name

    FROM complaints c

    INNER JOIN users complainant
        ON complainant.user_id = c.complainant_user_id

    INNER JOIN users landlord
        ON landlord.user_id = c.landlord_user_id

    INNER JOIN listings l
        ON l.id = c.listing_id

    LEFT JOIN complaint_investigations ci
        ON ci.complaint_id = c.id

    LEFT JOIN users agent
        ON agent.user_id = ci.field_agent_user_id

    WHERE $statusCondition

    ORDER BY c.submitted_at DESC
";

$stmt = $pdo->query($sql);
$complaints = $stmt->fetchAll();*/


/* Active field agents */

/*$agentStmt = $pdo->query("
    SELECT
        fa.agent_id,
        u.user_id,
        u.full_name
    FROM field_agents fa

    INNER JOIN users u
        ON u.user_id = fa.user_id

    WHERE fa.is_active = 1
      AND u.role = 'field_agent'
      AND u.status = 'active'

    ORDER BY u.full_name ASC
");

$fieldAgents = $agentStmt->fetchAll();*/


/* Helper functions */

/*function complaintCategoryLabel(string $category): string
{
    return match ($category) {
        'safety' => 'Safety',
        'false_advertising' => 'False Advertising',
        'misconduct' => 'Misconduct',
        'other' => 'Other',
        default => ucfirst(str_replace('_', ' ', $category))
    };
}

function complaintCategoryClass(string $category): string
{
    return match ($category) {
        'safety' => 'admin-complaint-category--safety',
        'false_advertising' => 'admin-complaint-category--advertising',
        'misconduct' => 'admin-complaint-category--misconduct',
        default => 'admin-complaint-category--other'
    };
}

function complaintCategoryIcon(string $category): string
{
    return match ($category) {
        'safety' => '!',
        'false_advertising' => 'A',
        'misconduct' => 'M',
        default => '?'
    };
}

function getInitials(string $name): string
{
    $words = preg_split('/\s+/', trim($name));

    if (!$words || empty($words[0])) {
        return '?';
    }

    $initials = strtoupper(substr($words[0], 0, 1));

    if (count($words) > 1) {
        $initials .= strtoupper(substr($words[count($words) - 1], 0, 1));
    }

    return $initials;
}

function formatComplaintDate(string $date): string
{
    return date('M d, Y', strtotime($date));
}

function statusLabel(string $status): string
{
    return match ($status) {
        'new' => 'New',
        'assigned' => 'Assigned',
        'under_investigation' => 'Under Investigation',
        'upheld' => 'Upheld',
        'dismissed' => 'Dismissed',
        'escalated' => 'Escalated',
        default => ucfirst(str_replace('_', ' ', $status))
    };
}
*/
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Complaint Moderation</title>

    <!-- Shared project CSS -->
    <link rel="stylesheet" href="../../../public/assets/css/style.css">

    <!-- Admin-only CSS -->
    <link rel="stylesheet" href="../../../public/assets/css/admin.css">
</head>

<body>

    <div class="admin-layout">

        <!-- SIDEBAR -->

        <aside class="admin-sidebar">

            <div class="admin-sidebar-brand">
                <div class="admin-brand-icon">B</div>

                <div>
                    <h2 class="admin-brand-title">BoardNest</h2>
                    <span class="admin-brand-subtitle">Admin Portal</span>
                </div>
            </div>

            <nav class="admin-navigation">

                <a href="../../admin/views/dashboard.php"
                    class="admin-nav-item">
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

                <a href="Complaints.php" class="admin-nav-item admin-nav-item-active">
                    <span class="admin-nav-icon">⚖</span>
                    <span>Complaint Moderation</span>
                </a>

                <a href="../../approvals/views/field_agent_manage.php" class="admin-nav-item">
                    <span class="admin-nav-icon">♙</span>
                    <span>Field Agents</span>
                </a>

                <a href="../../admin/views/area_profiles.php" class="admin-nav-item">
                    <span class="admin-nav-icon">⌖</span>
                    <span>Area Profiles</span>
                </a>

                <a href="../../announcements/views/announcements.php" class="admin-nav-item">
                    <span class="admin-nav-icon">⚑</span>
                    <span>Send Announcement</span>
                </a>

            </nav>

            <div class="admin-sidebar-bottom">

                <a href="../../logout.php" class="admin-nav-item admin-signout">
                    <span class="admin-nav-icon">↪</span>
                    <span>Sign Out</span>
                </a>

            </div>

        </aside>


        <!-- MAIN AREA -->

        <main class="admin-main">

            <!-- TOP BAR -->

            <header class="admin-topbar">

                <div class="admin-page-heading">
                    <h1>Complaint Moderation</h1>
                    <p>Review and manage complaints submitted by students.</p>
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

            <!-- CONTENT 

        <section class="admin-content">

            <?php if ($success): ?>

                <div class="alert alert-success">
                    <?= htmlspecialchars($success) ?>
                </div>

            <?php endif; ?>


            <?php if ($error): ?>

                <div class="alert alert-error">
                    <?= htmlspecialchars($error) ?>
                </div>

            <?php endif; ?> -->


            <!-- PAGE HEADER -->

            <div class="admin-page-header">

                <div>
                    <h3>Complaint Queue</h3>
                </div>

            </div>


            <!-- TABS -->

            <div class="admin-complaint-tabs">

                <a
                    href="complaints.php?tab=new"
                    class="admin-complaint-tab <? //= $tab === 'new' ? 'admin-complaint-tab--active' : '' 
                                                ?>">
                    <span>New</span>

                    <span class="admin-complaint-tab__count">
                        <? //= $countNew 
                        ?>
                    </span>
                </a>


                <a
                    href="complaints.php?tab=assigned"
                    class="admin-complaint-tab <? //= $tab === 'assigned' ? 'admin-complaint-tab--active' : '' 
                                                ?>">
                    <span>Assigned</span>

                    <span class="admin-complaint-tab__count">
                        <? //= $countAssigned 
                        ?>
                    </span>
                </a>


                <a
                    href="complaints.php?tab=resolved"
                    class="admin-complaint-tab <? //= $tab === 'resolved' ? 'admin-complaint-tab--active' : '' 
                                                ?>">
                    <span>Resolved</span>

                    <span class="admin-complaint-tab__count">
                        <? //= $countResolved 
                        ?>
                    </span>
                </a>

            </div>


            <!-- COMPLAINT LIST -->

            <div class="admin-complaint-list">

                <?php if (empty($complaints)): ?>

                    <div class="admin-empty-state">

                        <div class="admin-empty-state__icon">
                            ✓
                        </div>

                        <h3>No complaints found</h3>

                        <p>
                            There are no complaints in this queue.
                        </p>

                    </div>

                <?php else: ?>

                    <?php foreach ($complaints as $complaint): ?>

                        <article class="admin-complaint-card">

                            <!-- CARD HEADER -->

                            <div class="admin-complaint-card__header">

                                <div class="admin-complainant">

                                    <div class="admin-complainant__avatar">
                                        <?= getInitials($complaint['complainant_name']) ?>
                                    </div>

                                    <div>

                                        <h3>
                                            <?= htmlspecialchars($complaint['complainant_name']) ?>
                                        </h3>

                                        <p>
                                            Submitted
                                            <?= htmlspecialchars(formatComplaintDate($complaint['submitted_at'])) ?>
                                        </p>

                                    </div>

                                </div>


                                <div class="admin-complaint-card__status-area">

                                    <span
                                        class="admin-complaint-category <?= complaintCategoryClass($complaint['category']) ?>">
                                        <span class="admin-complaint-category__icon">
                                            <?= complaintCategoryIcon($complaint['category']) ?>
                                        </span>

                                        <?= htmlspecialchars(
                                            complaintCategoryLabel($complaint['category'])
                                        ) ?>
                                    </span>


                                    <?php if ($tab !== 'new'): ?>

                                        <span class="admin-status-badge admin-status-badge--<?= htmlspecialchars($complaint['status']) ?>">
                                            <?= htmlspecialchars(statusLabel($complaint['status'])) ?>
                                        </span>

                                    <?php endif; ?>

                                </div>

                            </div>


                            <!-- CARD BODY -->

                            <div class="admin-complaint-card__body">

                                <div class="admin-complaint-card__parties">

                                    <div class="admin-complaint-detail">

                                        <span class="admin-complaint-detail__label">
                                            Landlord
                                        </span>

                                        <strong>
                                            <?= htmlspecialchars($complaint['landlord_name']) ?>
                                        </strong>

                                    </div>


                                    <div class="admin-complaint-detail">

                                        <span class="admin-complaint-detail__label">
                                            Listing
                                        </span>

                                        <strong>
                                            <?= htmlspecialchars($complaint['listing_title']) ?>
                                        </strong>

                                    </div>

                                </div>


                                <div class="admin-complaint-description">

                                    <span class="admin-complaint-detail__label">
                                        Complaint
                                    </span>

                                    <p>
                                        <?= nl2br(
                                            htmlspecialchars($complaint['description'])
                                        ) ?>
                                    </p>

                                </div>


                                <?php if ((int) $complaint['unverified_stay'] === 1): ?>

                                    <div class="admin-complaint-warning">
                                        <span>!</span>
                                        <span>
                                            Stay has not been verified.
                                        </span>
                                    </div>

                                <?php endif; ?>


                                <?php if (!empty($complaint['field_agent_name'])): ?>

                                    <div class="admin-complaint-assignment">

                                        <span class="admin-complaint-detail__label">
                                            Assigned Field Agent
                                        </span>

                                        <strong>
                                            <?= htmlspecialchars($complaint['field_agent_name']) ?>
                                        </strong>

                                    </div>

                                <?php endif; ?>

                            </div>


                            <!-- CARD FOOTER -->

                            <div class="admin-complaint-card__footer">

                                <span class="admin-complaint-reference">
                                    Complaint #<?= (int) $complaint['id'] ?>
                                </span>


                                <div class="admin-complaint-actions">

                                    <button
                                        type="button"
                                        class="btn btn-secondary admin-view-complaint"
                                        data-complaint-id="<?= (int) $complaint['id'] ?>"
                                        data-complainant="<?= htmlspecialchars($complaint['complainant_name'], ENT_QUOTES) ?>"
                                        data-landlord="<?= htmlspecialchars($complaint['landlord_name'], ENT_QUOTES) ?>"
                                        data-listing="<?= htmlspecialchars($complaint['listing_title'], ENT_QUOTES) ?>"
                                        data-category="<?= htmlspecialchars(complaintCategoryLabel($complaint['category']), ENT_QUOTES) ?>"
                                        data-description="<?= htmlspecialchars($complaint['description'], ENT_QUOTES) ?>">
                                        View Details
                                    </button>


                                    <?php if ($complaint['status'] === 'new'): ?>

                                        <button
                                            type="button"
                                            class="btn btn-primary admin-assign-complaint"
                                            data-complaint-id="<?= (int) $complaint['id'] ?>"
                                            data-description="<?= htmlspecialchars($complaint['description'], ENT_QUOTES) ?>">
                                            Assign to Agent
                                        </button>

                                    <?php endif; ?>

                                </div>

                            </div>

                        </article>

                    <?php endforeach; ?>

                <?php endif; ?>

            </div>

            </section>

        </main>

    </div>


    <!-- VIEW COMPLAINT MODAL -->

    <div
        class="admin-modal"
        id="complaintDetailsModal"
        aria-hidden="true">

        <div class="admin-modal__overlay" data-close-modal></div>

        <div class="admin-modal__content">

            <div class="admin-modal__header">

                <div>
                    <span class="admin-eyebrow">
                        COMPLAINT DETAILS
                    </span>

                    <h2>Complaint Information</h2>
                </div>

                <button
                    type="button"
                    class="admin-modal__close"
                    data-close-modal
                    aria-label="Close">
                    ×
                </button>

            </div>


            <div class="admin-modal__body">

                <div class="admin-modal-detail-grid">

                    <div>
                        <span>Complaint ID</span>
                        <strong id="detailsComplaintId">-</strong>
                    </div>

                    <div>
                        <span>Category</span>
                        <strong id="detailsCategory">-</strong>
                    </div>

                    <div>
                        <span>Complainant</span>
                        <strong id="detailsComplainant">-</strong>
                    </div>

                    <div>
                        <span>Landlord</span>
                        <strong id="detailsLandlord">-</strong>
                    </div>

                    <div class="admin-modal-detail-grid__full">
                        <span>Listing</span>
                        <strong id="detailsListing">-</strong>
                    </div>

                </div>


                <div class="admin-modal-description">

                    <span>Description</span>

                    <p id="detailsDescription">
                        -
                    </p>

                </div>

            </div>


            <div class="admin-modal__footer">

                <button
                    type="button"
                    class="btn btn-secondary"
                    data-close-modal>
                    Close
                </button>

            </div>

        </div>

    </div>


    <!-- ASSIGN COMPLAINT MODAL -->

    <div
        class="admin-modal"
        id="assignComplaintModal"
        aria-hidden="true">

        <div class="admin-modal__overlay" data-close-modal></div>

        <div class="admin-modal__content">

            <div class="admin-modal__header">

                <div>
                    <span class="admin-eyebrow">
                        COMPLAINT INVESTIGATION
                    </span>

                    <h2>Assign Complaint Investigation</h2>
                </div>

                <button
                    type="button"
                    class="admin-modal__close"
                    data-close-modal
                    aria-label="Close">
                    ×
                </button>

            </div>


            <div class="admin-modal__body">

                <input
                    type="hidden"
                    id="assignComplaintId">


                <div class="admin-form-group">

                    <label for="assignComplaintDescription">
                        Complaint
                    </label>

                    <textarea
                        id="assignComplaintDescription"
                        rows="5"
                        readonly></textarea>

                </div>


                <div class="admin-form-group">

                    <label for="fieldAgent">

                        Field Agent

                        <span class="admin-required">
                            *
                        </span>

                    </label>

                    <select
                        id="fieldAgent"
                        name="field_agent_user_id">

                        <option value="">
                            Select a field agent
                        </option>

                        <?php foreach ($fieldAgents as $agent): ?>

                            <option value="<?= (int) $agent['user_id'] ?>">
                                <?= htmlspecialchars($agent['full_name']) ?>
                            </option>

                        <?php endforeach; ?>

                    </select>

                </div>


                <div class="admin-form-group">

                    <label for="adminNotes">
                        Admin Notes
                    </label>

                    <textarea
                        id="adminNotes"
                        rows="4"
                        placeholder="Optional notes for the investigation..."></textarea>

                </div>


                <div class="admin-modal-notice">

                    <strong>Assignment action</strong>

                    <p>
                        The database assignment operation will be implemented
                        later. This screen currently only provides the interface.
                    </p>

                </div>

            </div>


            <div class="admin-modal__footer">

                <button
                    type="button"
                    class="btn btn-secondary"
                    data-close-modal>
                    Cancel
                </button>

                <button
                    type="button"
                    class="btn btn-primary"
                    id="confirmAssignmentButton">
                    Confirm Assignment
                </button>

            </div>

        </div>

    </div>


    <!-- Admin-only JavaScript -->
    <script src="../../../public/assets/js/admin.js"></script>

</body>

</html>