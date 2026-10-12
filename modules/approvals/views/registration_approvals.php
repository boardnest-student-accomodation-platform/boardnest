<?php

require_once '../../../includes/session.php';
requireRole('admin');

require_once '../../../config/db.php';

// Flash messages
$success = $_SESSION['success'] ?? '';
$error = $_SESSION['error'] ?? '';

unset($_SESSION['success']);
unset($_SESSION['error']);


// Get pending student and landlord registrations
$stmt = $pdo->query("
    SELECT
        user_id,
        full_name,
        email,
        role,
        created_at
    FROM users
    WHERE status = 'pending'
      AND role IN ('student', 'landlord')
    ORDER BY created_at ASC
");

$pendingRegistrations = $stmt->fetchAll();


// Get recently reviewed registrations
$stmt = $pdo->query("
    SELECT
        ra.registration_approvals_id,
        ra.user_id,
        ra.decision,
        ra.rejection_reason,
        ra.reviewed_at,
        u.full_name,
        u.email,
        u.role,
        admin_user.full_name AS reviewer_name
    FROM registration_approvals ra

    INNER JOIN users u
        ON ra.user_id = u.user_id

    INNER JOIN users admin_user
        ON ra.reviewed_by = admin_user.user_id

    ORDER BY ra.reviewed_at DESC
    LIMIT 10
");

$reviewedRegistrations = $stmt->fetchAll();

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Registration Approvals</title>

    <link rel="stylesheet" href="../../../public/assets/css/style.css">
    <link rel="stylesheet" href="../../../public/assets/css/admin.css">

</head>

<body>

    <div class="dashboard">

        <!-- Sidebar -->

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

                <a href="registration_approvals.php" class="admin-nav-item admin-nav-item-active">
                    <span class="admin-nav-icon">✓</span>
                    <span>Registration Approvals</span>
                </a>

                <a href="listings_decisions.php" class="admin-nav-item">
                    <span class="admin-nav-icon">▤</span>
                    <span>Listings</span>
                </a>

                <a href="../../complaints/views/complaints.php" class="admin-nav-item">
                    <span class="admin-nav-icon">⚖</span>
                    <span>Complaint Moderation</span>
                </a>

                <a href="field_agent_manage.php" class="admin-nav-item">
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

                <a href="../../../logout.php" class="admin-nav-item admin-signout">
                    <span class="admin-nav-icon">↪</span>
                    <span>Sign Out</span>
                </a>

            </div>

        </aside>


        <!-- Main Content -->

        <main class="admin-main">

            <header class="admin-topbar">

                <div class="admin-page-heading">

                    <h1>
                        Registration Approvals
                    </h1>

                </div>


                <div class="admin-topbar-actions">

                    <div class="admin-profile">

                        <div class="admin-profile-avatar">
                            A
                        </div>

                        <div class="admin-profile-details">

                            <span class="admin-profile-name">
                                Admin
                            </span>

                            <span class="admin-profile-role">
                                Administrator
                            </span>

                        </div>

                    </div>

                </div>

            </header>


            <!-- Flash Messages -->

            <?php if ($success): ?>

                <div class="alert alert-success">
                    <?= htmlspecialchars($success) ?>
                </div>

            <?php endif; ?>


            <?php if ($error): ?>

                <div class="alert alert-error">
                    <?= htmlspecialchars($error) ?>
                </div>

            <?php endif; ?>


            <!-- Pending Registrations -->

            <section class="card">

                <div class="section-header">

                    <div>
                        <h2>Pending Registrations</h2>

                        <p>
                            Registrations waiting for admin approval.
                        </p>
                    </div>

                    <span class="badge">
                        <?= count($pendingRegistrations) ?> Pending
                    </span>

                </div>


                <?php if (empty($pendingRegistrations)): ?>

                    <div class="empty-state">

                        <h3>No pending registrations</h3>

                        <p>
                            There are currently no student or landlord registrations
                            waiting for approval.
                        </p>

                    </div>

                <?php else: ?>

                    <div class="table-wrapper">

                        <table>

                            <thead>

                                <tr>

                                    <th>Name</th>

                                    <th>Email</th>

                                    <th>Role</th>

                                    <th>Registered</th>

                                    <th>Action</th>

                                </tr>

                            </thead>

                            <tbody>

                                <?php foreach ($pendingRegistrations as $registration): ?>

                                    <tr>

                                        <td>
                                            <?= htmlspecialchars($registration['full_name']) ?>
                                        </td>

                                        <td>
                                            <?= htmlspecialchars($registration['email']) ?>
                                        </td>

                                        <td>

                                            <span class="badge">

                                                <?= htmlspecialchars(
                                                    ucfirst($registration['role'])
                                                ) ?>

                                            </span>

                                        </td>

                                        <td>

                                            <?= htmlspecialchars(
                                                date(
                                                    'd M Y, h:i A',
                                                    strtotime($registration['created_at'])
                                                )
                                            ) ?>

                                        </td>

                                        <td>

                                            <button
                                                type="button"
                                                class="btn btn-primary"
                                                onclick="openReviewModal(
                                            <?= (int)$registration['user_id'] ?>,
                                            '<?= htmlspecialchars(
                                                    $registration['full_name'],
                                                    ENT_QUOTES
                                                ) ?>'
                                        )">
                                                Review
                                            </button>

                                        </td>

                                    </tr>

                                <?php endforeach; ?>

                            </tbody>

                        </table>

                    </div>

                <?php endif; ?>

            </section>


            <!-- Recently Reviewed -->

            <section class="card" style="margin-top: 30px;">

                <div class="section-header">

                    <div>
                        <h3>Recently Reviewed</h3>
                    </div>

                </div>


                <?php if (empty($reviewedRegistrations)): ?>

                    <div class=" empty-state">

                        <p>
                            No registration decisions have been recorded yet.
                        </p>

                    </div>

                <?php else: ?>

                    <div class="table-wrapper">

                        <table>

                            <thead>

                                <tr>

                                    <th>Name</th>

                                    <th>Role</th>

                                    <th>Decision</th>

                                    <th>Reviewed By</th>

                                    <th>Date</th>

                                </tr>

                            </thead>

                            <tbody>

                                <?php foreach ($reviewedRegistrations as $registration): ?>

                                    <tr>

                                        <td>

                                            <strong>
                                                <?= htmlspecialchars(
                                                    $registration['full_name']
                                                ) ?>
                                            </strong>

                                            <br>

                                            <small>
                                                <?= htmlspecialchars(
                                                    $registration['email']
                                                ) ?>
                                            </small>

                                        </td>


                                        <td>

                                            <?= htmlspecialchars(
                                                ucfirst($registration['role'])
                                            ) ?>

                                        </td>


                                        <td>

                                            <?php if (
                                                $registration['decision'] === 'approved'
                                            ): ?>

                                                <span class="badge badge-success">
                                                    Approved
                                                </span>

                                            <?php else: ?>

                                                <span class="badge badge-error">
                                                    Rejected
                                                </span>

                                            <?php endif; ?>

                                        </td>


                                        <td>

                                            <?= htmlspecialchars(
                                                $registration['reviewer_name']
                                            ) ?>

                                        </td>


                                        <td>

                                            <?= htmlspecialchars(
                                                date(
                                                    'd M Y, h:i A',
                                                    strtotime(
                                                        $registration['reviewed_at']
                                                    )
                                                )
                                            ) ?>

                                        </td>

                                    </tr>

                                <?php endforeach; ?>

                            </tbody>

                        </table>

                    </div>

                <?php endif; ?>

            </section>

        </main>

    </div>


    <!-- Review Modal -->

    <div
        id="reviewModal"
        class="modal"
        style="display: none;">

        <div class="modal__content">

            <div class="modal__header">

                <h2>Review Registration</h2>

                <button
                    type="button"
                    onclick="closeReviewModal()">
                    ×
                </button>

            </div>


            <p>
                Review registration request from
                <strong id="applicantName"></strong>.
            </p>


            <form
                method="POST"
                action="../controller/review_registration.php">

                <input
                    type="hidden"
                    name="user_id"
                    id="reviewUserId">


                <div class="form-group">

                    <label for="decision">
                        Decision
                    </label>

                    <select
                        name="decision"
                        id="decision"
                        required
                        onchange="toggleRejectionReason()">

                        <option value="">
                            Select decision
                        </option>

                        <option value="approved">
                            Approve
                        </option>

                        <option value="rejected">
                            Reject
                        </option>

                    </select>

                </div>


                <div
                    class="form-group"
                    id="rejectionReasonGroup"
                    style="display: none;">

                    <label for="rejection_reason">
                        Rejection Reason
                    </label>

                    <textarea
                        name="rejection_reason"
                        id="rejection_reason"
                        rows="4"
                        placeholder="Enter the reason for rejecting this registration..."></textarea>

                </div>


                <div class="modal__actions">

                    <button
                        type="button"
                        class="btn"
                        onclick="closeReviewModal()">
                        Cancel
                    </button>

                    <button
                        type="submit"
                        class="btn btn-primary">
                        Submit Decision
                    </button>

                </div>

            </form>

        </div>

    </div>


    <script>
        function openReviewModal(userId, name) {
            document.getElementById('reviewUserId').value = userId;

            document.getElementById('applicantName').textContent = name;

            document.getElementById('reviewModal').style.display = 'flex';

            document.getElementById('decision').value = '';

            document.getElementById('rejectionReasonGroup').style.display = 'none';

            document.getElementById('rejection_reason').value = '';
        }


        function closeReviewModal() {
            document.getElementById('reviewModal').style.display = 'none';
        }


        function toggleRejectionReason() {
            const decision =
                document.getElementById('decision').value;

            const reasonGroup =
                document.getElementById('rejectionReasonGroup');

            const reason =
                document.getElementById('rejection_reason');

            if (decision === 'rejected') {
                reasonGroup.style.display = 'block';

                reason.required = true;
            } else {
                reasonGroup.style.display = 'none';

                reason.required = false;

                reason.value = '';
            }
        }
    </script>

</body>

</html>
