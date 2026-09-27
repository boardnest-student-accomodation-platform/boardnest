<?php

require_once '../../../includes/session.php';
requireRole('admin');

require_once '../../../config/db.php';


/*
 * Only allow POST requests.
 */

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    header('Location: ../views/listings_decisions.php');
    exit();
}


/*
 * Get submitted values.
 */

$listingId = filter_input(
    INPUT_POST,
    'listing_id',
    FILTER_VALIDATE_INT
);

$decision = $_POST['decision'] ?? '';

$rejectionReason = trim(
    $_POST['rejection_reason'] ?? ''
);


/*
 * Validate listing ID.
 */

if (!$listingId) {

    $_SESSION['error'] =
        'Invalid listing request.';

    header('Location: ../views/listings_decisions.php');
    exit();
}


/*
 * Validate decision.
 */

$validDecisions = [
    'approved',
    'rejected',
    'reverification_requested'
];


if (!in_array($decision, $validDecisions, true)) {

    $_SESSION['error'] =
        'Invalid listing decision.';

    header('Location: ../views/listings_decisions.php');
    exit();
}


/*
 * Rejection/reverification reason is required.
 */

if (
    (
        $decision === 'rejected' ||
        $decision === 'reverification_requested'
    )
    &&
    $rejectionReason === ''
) {

    $_SESSION['error'] =
        'Please provide a reason for this decision.';

    header('Location: ../views/listings_decisions.php');
    exit();
}


/*
 * Current admin.
 */

$adminUserId = $_SESSION['user_id'];


try {

    $pdo->beginTransaction();


    /*
     * Find the listing and lock the row
     * while the decision is being processed.
     */

    $stmt = $pdo->prepare("
        SELECT
            listing_id,
            title,
            status

        FROM listings

        WHERE listing_id = ?

        FOR UPDATE
    ");

    $stmt->execute([
        $listingId
    ]);

    $listing = $stmt->fetch();


    /*
     * Listing does not exist.
     */

    if (!$listing) {

        throw new Exception(
            'Listing could not be found.'
        );
    }


    /*
     * Only pending listings can be reviewed.
     */

    if ($listing['status'] !== 'pending') {

        throw new Exception(
            'This listing has already been reviewed.'
        );
    }


    /*
     * Determine the new listing status.
     */

    switch ($decision) {

        case 'approved':

            $newStatus = 'live';

            break;


        case 'rejected':

            $newStatus = 'rejected';

            break;


        case 'reverification_requested':

            $newStatus = 'verification_pending';

            break;


        default:

            throw new Exception(
                'Invalid decision.'
            );
    }


    /*
     * Update listing status.
     */

    $stmt = $pdo->prepare("
        UPDATE listings

        SET status = ?

        WHERE listing_id = ?
    ");

    $stmt->execute([
        $newStatus,
        $listingId
    ]);


    /*
     * Store admin decision.
     */

    $stmt = $pdo->prepare("
        INSERT INTO listing_decisions
        (
            listing_id,
            admin_user_id,
            decision,
            rejection_reason
        )

        VALUES (?, ?, ?, ?)
    ");

    $stmt->execute([
        $listingId,
        $adminUserId,
        $decision,
        (
            $decision === 'approved'
            ? null
            : $rejectionReason
        )
    ]);


    /*
     * Everything succeeded.
     */

    $pdo->commit();


    if ($decision === 'approved') {

        $_SESSION['success'] =
            $listing['title'] .
            ' has been approved and is now live.';
    } elseif ($decision === 'rejected') {

        $_SESSION['success'] =
            $listing['title'] .
            ' has been rejected.';
    } else {

        $_SESSION['success'] =
            $listing['title'] .
            ' has been sent for reverification.';
    }
} catch (Exception $e) {


    /*
     * Undo database changes if something failed.
     */

    if ($pdo->inTransaction()) {

        $pdo->rollBack();
    }


    $_SESSION['error'] =
        'Unable to process the listing: ' .
        $e->getMessage();
}


/*
 * Redirect after POST.
 */

header('Location: ../views/listings_decisions.php');

exit();
