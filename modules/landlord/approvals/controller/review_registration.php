<?php

require_once '../../../includes/session.php';
requireRole('admin');

require_once '../../../config/db.php';


if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    header('Location: ../registration_approvals.php');
    exit();

}


$userId = filter_input(
    INPUT_POST,
    'user_id',
    FILTER_VALIDATE_INT
);

$decision = $_POST['decision'] ?? '';

$rejectionReason = trim(
    $_POST['rejection_reason'] ?? ''
);


// Validate user ID

if (!$userId) {

    $_SESSION['error'] = 'Invalid registration request.';

    header('Location: ../registration_approvals.php');
    exit();

}


// Validate decision

if (!in_array($decision, ['approved', 'rejected'], true)) {

    $_SESSION['error'] = 'Invalid approval decision.';

    header('Location: ../registration_approvals.php');
    exit();

}


// Rejection reason is required when rejecting

if ($decision === 'rejected' && $rejectionReason === '') {

    $_SESSION['error'] =
        'Please provide a reason when rejecting a registration.';

    header('Location: ../registration_approvals.php');
    exit();

}


// Admin performing the review

$reviewedBy = $_SESSION['user_id'];


try {

    $pdo->beginTransaction();


    /*
     * Make sure the applicant exists and is still pending.
     */

    $stmt = $pdo->prepare("
        SELECT user_id, full_name, role, status
        FROM users
        WHERE user_id = ?
          AND role IN ('student', 'landlord')
        FOR UPDATE
    ");

    $stmt->execute([$userId]);

    $user = $stmt->fetch();


    if (!$user) {

        throw new Exception(
            'Registration request could not be found.'
        );

    }


    if ($user['status'] !== 'pending') {

        throw new Exception(
            'This registration has already been reviewed.'
        );

    }


    /*
     * Update the user's account status.
     */

    $newStatus =
        ($decision === 'approved')
        ? 'active'
        : 'rejected';


    $stmt = $pdo->prepare("
        UPDATE users
        SET status = ?
        WHERE user_id = ?
    ");

    $stmt->execute([
        $newStatus,
        $userId
    ]);


    /*
     * Store the admin's decision.
     */

    $stmt = $pdo->prepare("
        INSERT INTO registration_approvals
        (
            user_id,
            reviewed_by,
            decision,
            rejection_reason
        )
        VALUES (?, ?, ?, ?)
    ");

    $stmt->execute([
        $userId,
        $reviewedBy,
        $decision,
        $decision === 'rejected'
            ? $rejectionReason
            : null
    ]);


    $pdo->commit();


    if ($decision === 'approved') {

        $_SESSION['success'] =
            $user['full_name'] .
            ' has been approved successfully.';

    } else {

        $_SESSION['success'] =
            $user['full_name'] .
            ' has been rejected.';

    }


}
catch (Exception $e) {

    if ($pdo->inTransaction()) {

        $pdo->rollBack();

    }

    $_SESSION['error'] =
        'Unable to process the registration: ' .
        $e->getMessage();

}


header('Location: ../registration_approvals.php');
exit();

?>