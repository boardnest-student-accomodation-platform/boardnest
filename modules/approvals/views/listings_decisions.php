<?php

require_once '../../../includes/session.php';
requireRole('admin');

require_once '../../../config/db.php';


// Flash messages
$success = $_SESSION['success'] ?? '';
$error = $_SESSION['error'] ?? '';

unset($_SESSION['success']);
unset($_SESSION['error']);


// Get listings waiting for admin decision
$stmt = $pdo->query("
    SELECT
        l.listing_id,
        l.title,
        l.description,
        l.address,
        l.city,
        l.monthly_rent,
        l.status,
        l.created_at,

        u.user_id AS landlord_user_id,
        u.full_name AS landlord_name,
        u.email AS landlord_email

    FROM listings l

    INNER JOIN landlords ld
        ON l.landlord_id = ld.landlord_id

    INNER JOIN users u
        ON ld.user_id = u.user_id

    WHERE l.status = 'pending'

    ORDER BY l.created_at ASC
");

$pendingListings = $stmt->fetchAll();


// Recently reviewed listings
$stmt = $pdo->query("
    SELECT
        d.listing_decisions_id,
        d.listing_id,
        d.decision,
        d.rejection_reason,
        d.decided_at,

        l.title,

        landlord_user.full_name AS landlord_name,

        admin_user.full_name AS admin_name

    FROM listing_decisions d

    INNER JOIN listings l
        ON d.listing_id = l.listing_id

    INNER JOIN landlords ld
        ON l.landlord_id = ld.landlord_id

    INNER JOIN users landlord_user
        ON ld.user_id = landlord_user.user_id

    INNER JOIN users admin_user
        ON d.admin_user_id = admin_user.user_id

    ORDER BY d.decided_at DESC

    LIMIT 10
");

$reviewedListings = $stmt->fetchAll();

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Listing Decisions</title>

    <link rel="stylesheet" href="../../../public/assets/css/style.css">

    <link rel="stylesheet" href="../../../public/assets/css/admin.css">

</head>


<body>

    <div class="dashboard">


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

                <a href="registration_approvals.php" class="admin-nav-item">
                    <span class="admin-nav-icon">✓</span>
                    <span>Registration Approvals</span>
                </a>

                <a href="listings_decisions.php" class="admin-nav-item admin-nav-item-active">
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



        <!-- MAIN CONTENT -->

        <main class="dashboard__content">


            <!-- PAGE HEADER -->

            <div class="section-header">

                <div>

                    <h1>Listing Decisions</h1>

                    <p>
                        Review listings submitted by landlords
                        before they become visible to students.
                    </p>

                </div>

            </div>



            <!-- FLASH SUCCESS -->

            <?php if ($success): ?>

                <div class="alert alert-success">

                    <?= htmlspecialchars($success) ?>

                </div>

            <?php endif; ?>



            <!-- FLASH ERROR -->

            <?php if ($error): ?>

                <div class="alert alert-error">

                    <?= htmlspecialchars($error) ?>

                </div>

            <?php endif; ?>



            <!-- PENDING LISTINGS -->

            <section class="card">

                <div class="section-header">

                    <div>

                        <h3>Pending Listings</h3>

                        <p>
                            Listings waiting for an administrator's decision.
                        </p>

                    </div>


                    <span class="badge">

                        <?= count($pendingListings) ?> Pending

                    </span>

                </div>



                <?php if (empty($pendingListings)): ?>

                    <div class="empty-state">

                        <h3>No pending listings</h3>

                        <p>
                            There are currently no listings waiting
                            for admin review.
                        </p>

                    </div>

                <?php else: ?>


                    <div class="table-wrapper">

                        <table>

                            <thead>

                                <tr>

                                    <th>Listing</th>

                                    <th>Landlord</th>

                                    <th>Location</th>

                                    <th>Monthly Rent</th>

                                    <th>Submitted</th>

                                    <th>Action</th>

                                </tr>

                            </thead>


                            <tbody>


                                <?php foreach ($pendingListings as $listing): ?>

                                    <tr>


                                        <!-- LISTING -->

                                        <td>

                                            <strong>

                                                <?= htmlspecialchars(
                                                    $listing['title']
                                                ) ?>

                                            </strong>

                                            <br>

                                            <small>

                                                Listing #<?= (int)$listing['listing_id'] ?>

                                            </small>

                                        </td>



                                        <!-- LANDLORD -->

                                        <td>

                                            <?= htmlspecialchars(
                                                $listing['landlord_name']
                                            ) ?>

                                            <br>

                                            <small>

                                                <?= htmlspecialchars(
                                                    $listing['landlord_email']
                                                ) ?>

                                            </small>

                                        </td>



                                        <!-- LOCATION -->

                                        <td>

                                            <?= htmlspecialchars(
                                                $listing['city']
                                            ) ?>

                                            <br>

                                            <small>

                                                <?= htmlspecialchars(
                                                    $listing['address']
                                                ) ?>

                                            </small>

                                        </td>



                                        <!-- RENT -->

                                        <td>

                                            Rs.
                                            <?= number_format(
                                                (float)$listing['monthly_rent'],
                                                2
                                            ) ?>

                                        </td>



                                        <!-- DATE -->

                                        <td>

                                            <?= htmlspecialchars(
                                                date(
                                                    'd M Y, h:i A',
                                                    strtotime(
                                                        $listing['created_at']
                                                    )
                                                )
                                            ) ?>

                                        </td>



                                        <!-- ACTION -->

                                        <td>

                                            <button
                                                type="button"
                                                class="btn btn-primary"
                                                onclick='openListingModal(
                                            <?= (int)$listing['listing_id'] ?>,
                                            <?= json_encode($listing['title']) ?>,
                                            <?= json_encode($listing['landlord_name']) ?>,
                                            <?= json_encode($listing['city']) ?>,
                                            <?= json_encode($listing['address']) ?>,
                                            <?= json_encode($listing['description']) ?>,
                                            <?= json_encode($listing['monthly_rent']) ?>
                                        )'>
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



            <!-- REVIEW HISTORY -->

            <section
                class="card"
                style="margin-top: 30px;">

                <div class="section-header">

                    <div>

                        <h3>Recently Reviewed</h3>

                    </div>

                </div>



                <?php if (empty($reviewedListings)): ?>

                    <div class="empty-state">

                        <p>
                            No listing decisions have been recorded yet.
                        </p>

                    </div>

                <?php else: ?>


                    <div class="table-wrapper">

                        <table>

                            <thead>

                                <tr>

                                    <th>Listing</th>

                                    <th>Landlord</th>

                                    <th>Decision</th>

                                    <th>Reviewed By</th>

                                    <th>Date</th>

                                </tr>

                            </thead>


                            <tbody>


                                <?php foreach ($reviewedListings as $listing): ?>

                                    <tr>


                                        <td>

                                            <strong>

                                                <?= htmlspecialchars(
                                                    $listing['title']
                                                ) ?>

                                            </strong>

                                            <br>

                                            <small>

                                                Listing #<?= (int)$listing['listing_id'] ?>

                                            </small>

                                        </td>



                                        <td>

                                            <?= htmlspecialchars(
                                                $listing['landlord_name']
                                            ) ?>

                                        </td>



                                        <td>


                                            <?php if (
                                                $listing['decision'] === 'approved'
                                            ): ?>

                                                <span class="badge badge-success">

                                                    Approved

                                                </span>


                                            <?php elseif (
                                                $listing['decision'] === 'rejected'
                                            ): ?>

                                                <span class="badge badge-error">

                                                    Rejected

                                                </span>


                                            <?php else: ?>

                                                <span class="badge">

                                                    Reverification Requested

                                                </span>

                                            <?php endif; ?>


                                        </td>



                                        <td>

                                            <?= htmlspecialchars(
                                                $listing['admin_name']
                                            ) ?>

                                        </td>



                                        <td>

                                            <?= htmlspecialchars(
                                                date(
                                                    'd M Y, h:i A',
                                                    strtotime(
                                                        $listing['decided_at']
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



    <!-- LISTING REVIEW MODAL -->

    <div
        id="listingReviewModal"
        class="modal"
        style="display: none;">


        <div class="modal__content">


            <div class="modal__header">

                <h2>Review Listing</h2>

                <button
                    type="button"
                    onclick="closeListingModal()">
                    ×
                </button>

            </div>



            <div class="admin-listing-details">


                <h3 id="modalListingTitle"></h3>


                <p>

                    <strong>Landlord:</strong>

                    <span id="modalLandlord"></span>

                </p>


                <p>

                    <strong>Location:</strong>

                    <span id="modalLocation"></span>

                </p>


                <p>

                    <strong>Address:</strong>

                    <span id="modalAddress"></span>

                </p>


                <p>

                    <strong>Monthly Rent:</strong>

                    Rs. <span id="modalRent"></span>

                </p>


                <p>

                    <strong>Description:</strong>

                </p>


                <p id="modalDescription"></p>


            </div>



            <form
                method="POST"
                action="../controller/review_listing.php">


                <input
                    type="hidden"
                    name="listing_id"
                    id="reviewListingId">



                <div class="form-group">

                    <label for="decision">

                        Decision

                    </label>


                    <select
                        name="decision"
                        id="decision"
                        required
                        onchange="toggleListingReason()">

                        <option value="">

                            Select decision

                        </option>


                        <option value="approved">

                            Approve Listing

                        </option>


                        <option value="rejected">

                            Reject Listing

                        </option>


                        <option value="reverification_requested">

                            Request Reverification

                        </option>

                    </select>

                </div>



                <!-- REJECTION / REVERIFICATION REASON -->

                <div
                    class="form-group"
                    id="listingReasonGroup"
                    style="display: none;">

                    <label for="rejection_reason">

                        Reason

                    </label>


                    <textarea
                        name="rejection_reason"
                        id="rejection_reason"
                        rows="4"
                        placeholder="Enter the reason..."></textarea>

                </div>



                <div class="modal__actions">


                    <button
                        type="button"
                        class="btn"
                        onclick="closeListingModal()">

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
        function openListingModal(
            listingId,
            title,
            landlord,
            city,
            address,
            description,
            rent
        ) {

            document.getElementById(
                'reviewListingId'
            ).value = listingId;


            document.getElementById(
                'modalListingTitle'
            ).textContent = title;


            document.getElementById(
                'modalLandlord'
            ).textContent = landlord;


            document.getElementById(
                'modalLocation'
            ).textContent = city;


            document.getElementById(
                'modalAddress'
            ).textContent = address;


            document.getElementById(
                'modalDescription'
            ).textContent = description;


            document.getElementById(
                'modalRent'
            ).textContent = Number(rent).toLocaleString(
                'en-LK', {
                    minimumFractionDigits: 2
                }
            );


            document.getElementById(
                'decision'
            ).value = '';


            document.getElementById(
                'listingReasonGroup'
            ).style.display = 'none';


            document.getElementById(
                'rejection_reason'
            ).value = '';


            document.getElementById(
                'listingReviewModal'
            ).style.display = 'flex';

        }



        function closeListingModal() {

            document.getElementById(
                'listingReviewModal'
            ).style.display = 'none';

        }



        function toggleListingReason() {

            const decision =
                document.getElementById('decision').value;


            const reasonGroup =
                document.getElementById('listingReasonGroup');


            const reason =
                document.getElementById('rejection_reason');


            if (
                decision === 'rejected' ||
                decision === 'reverification_requested'
            ) {

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
