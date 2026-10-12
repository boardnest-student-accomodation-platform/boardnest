<?php

require_once '../../../includes/session.php';
requireRole('admin');

require_once '../../../config/db.php';


/* Only POST requests are allowed*/

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    header('Location: ../area_profiles.php');
    exit();
}


/* Get Form Data*/

$cityId = filter_input(
    INPUT_POST,
    'city_id',
    FILTER_VALIDATE_INT
);

$safetyClassification =
    trim($_POST['safety_classification'] ?? '');

$transportOptions =
    $_POST['transport_options'] ?? [];

$amenities =
    $_POST['amenities'] ?? [];

$description =
    trim($_POST['description'] ?? '');


/*Validation*/

if (!$cityId) {

    $_SESSION['error'] =
        'Please select an area.';

    header('Location: ../area_profiles.php');
    exit();
}


$allowedSafetyValues = [
    'Standard',
    'Caution Advised',
    'Under Review'
];


if (!in_array(
    $safetyClassification,
    $allowedSafetyValues,
    true
)) {

    $_SESSION['error'] =
        'Invalid safety classification.';

    header('Location: ../area_profiles.php');
    exit();
}


/*
|--------------------------------------------------------------------------
| Clean Transport Values
|--------------------------------------------------------------------------
*/

$transportOptions = array_map(
    'trim',
    $transportOptions
);

$transportOptions = array_filter(
    $transportOptions,
    function ($value) {
        return $value !== '';
    }
);


/*
|--------------------------------------------------------------------------
| Clean Amenity Values
|--------------------------------------------------------------------------
*/

$amenities = array_map(
    'trim',
    $amenities
);

$amenities = array_filter(
    $amenities,
    function ($value) {
        return $value !== '';
    }
);


/*
|--------------------------------------------------------------------------
| Convert Arrays to Database Strings
|--------------------------------------------------------------------------
*/

$transportString =
    implode(', ', $transportOptions);

$amenitiesString =
    implode(', ', $amenities);


/*
|--------------------------------------------------------------------------
| Check Whether City Exists
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT id
    FROM cities
    WHERE id = ?
");

$stmt->execute([$cityId]);

$cityExists = $stmt->fetchColumn();


if (!$cityExists) {

    $_SESSION['error'] =
        'The selected city does not exist.';

    header('Location: ../area_profiles.php');
    exit();
}


/*
|--------------------------------------------------------------------------
| Check Existing Profile
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT area_profile_id
    FROM area_profiles
    WHERE city_id = ?
");

$stmt->execute([$cityId]);

$existingProfileId =
    $stmt->fetchColumn();


/*
|--------------------------------------------------------------------------
| Update Existing Profile
|--------------------------------------------------------------------------
*/

if ($existingProfileId) {

    $stmt = $pdo->prepare("
        UPDATE area_profiles

        SET
            safety_classification = ?,
            transport_options = ?,
            amenities = ?,
            description = ?

        WHERE city_id = ?
    ");

    $stmt->execute([
        $safetyClassification,
        $transportString,
        $amenitiesString,
        $description,
        $cityId
    ]);

    $_SESSION['success'] =
        'Area profile updated successfully.';
}


/*
|--------------------------------------------------------------------------
| Create New Profile
|--------------------------------------------------------------------------
*/ else {

    $stmt = $pdo->prepare("
        INSERT INTO area_profiles (
            city_id,
            safety_classification,
            transport_options,
            amenities,
            description
        )

        VALUES (?, ?, ?, ?, ?)
    ");

    $stmt->execute([
        $cityId,
        $safetyClassification,
        $transportString,
        $amenitiesString,
        $description
    ]);

    $_SESSION['success'] =
        'Area profile created successfully.';
}


/*
|--------------------------------------------------------------------------
| Redirect
|--------------------------------------------------------------------------
*/

header('Location: ../area_profiles.php');
exit();
