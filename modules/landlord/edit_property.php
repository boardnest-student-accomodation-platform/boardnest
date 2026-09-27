
<?php
require_once __DIR__ . '/../../includes/session.php';
startSession();

requireRole('landlord');

require_once __DIR__ . '/../../config/db.php';

// Get user ID from session
$user_id = $_SESSION['user_id'];

// Get the actual landlord ID
$stmt = $pdo->prepare(
    "SELECT landlord_id FROM landlords WHERE user_id = ?"
);
$stmt->execute([$user_id]);
$landlord_id = $stmt->fetchColumn();

if (!$landlord_id) {
    die("Landlord profile not found.");
}

// Get property ID
$property_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$property_id || $property_id <= 0) {
    header("Location: dashboard.php");
    exit();
}

// Generate CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$csrf_token = $_SESSION['csrf_token'];
$message = '';

// Fetch property and room details
$stmt = $pdo->prepare("
    SELECT
        p.*,
        r.room_type,
        r.price,
        r.slot_capacity
    FROM properties p
    LEFT JOIN rooms r
        ON p.property_id = r.property_id
    WHERE p.property_id = ?
      AND p.landlord_id = ?
    LIMIT 1
");

$stmt->execute([$property_id, $landlord_id]);
$data = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$data) {
    http_response_code(404);
    die("Property not found or unauthorized access.");
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Verify CSRF token
    $submitted_token = $_POST['csrf_token'] ?? '';

    if (!hash_equals($csrf_token, $submitted_token)) {
        $message = "Invalid request. Please refresh and try again.";
    } else {

        $address = trim($_POST['address'] ?? '');
        $city_id = filter_input(INPUT_POST, 'city_id', FILTER_VALIDATE_INT);
        $maps_url = trim($_POST['maps_url'] ?? '');
        $latitude = trim($_POST['latitude'] ?? '');
        $longitude = trim($_POST['longitude'] ?? '');
        $room_type = $_POST['room_type'] ?? '';
        $price = filter_input(INPUT_POST, 'price', FILTER_VALIDATE_FLOAT);
        $slot_capacity = filter_input(
            INPUT_POST,
            'slot_capacity',
            FILTER_VALIDATE_INT
        );

        // Validate inputs
        $errors = [];

        if ($address === '') {
            $errors[] = "Address is required.";
        }

        if (!$city_id || $city_id <= 0) {
            $errors[] = "Please enter a valid city ID.";
        }

        if (!in_array($room_type, ['single', 'shared'], true)) {
            $errors[] = "Please select a valid room type.";
        }

        if ($price === false || $price <= 0) {
            $errors[] = "Please enter a valid monthly rent.";
        }

        if ($slot_capacity === false || $slot_capacity <= 0) {
            $errors[] = "Please enter a valid slot capacity.";
        }

        if ($maps_url !== '' && !filter_var($maps_url, FILTER_VALIDATE_URL)) {
            $errors[] = "Please enter a valid Google Maps URL.";
        }

        if (
            ($latitude !== '' && !is_numeric($latitude)) ||
            ($longitude !== '' && !is_numeric($longitude))
        ) {
            $errors[] = "Latitude and longitude must be numeric.";
        }

        if (
            $latitude !== '' &&
            ((float)$latitude < -90 || (float)$latitude > 90)
        ) {
            $errors[] = "Latitude must be between -90 and 90.";
        }

        if (
            $longitude !== '' &&
            ((float)$longitude < -180 || (float)$longitude > 180)
        ) {
            $errors[] = "Longitude must be between -180 and 180.";
        }

        if (empty($errors)) {
            try {
                $pdo->beginTransaction();

                // Update property
                $stmt1 = $pdo->prepare("
                    UPDATE properties
                    SET city_id = ?,
                        address = ?,
                        maps_url = ?,
                        latitude = ?,
                        longitude = ?
                    WHERE property_id = ?
                      AND landlord_id = ?
                ");

                $stmt1->execute([
                    $city_id,
                    $address,
                    $maps_url !== '' ? $maps_url : null,
                    $latitude !== '' ? $latitude : null,
                    $longitude !== '' ? $longitude : null,
                    $property_id,
                    $landlord_id
                ]);

                // Update existing room
                $stmt2 = $pdo->prepare("
                    UPDATE rooms
                    SET room_type = ?,
                        price = ?,
                        slot_capacity = ?
                    WHERE property_id = ?
                ");

                $stmt2->execute([
                    $room_type,
                    $price,
                    $slot_capacity,
                    $property_id
                ]);

                $pdo->commit();

                header("Location: dashboard.php?status=updated");
                exit();

            } catch (PDOException $e) {
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }

                error_log("Property update error: " . $e->getMessage());
                $message = "An error occurred while updating the property.";
            }
        } else {
            $message = implode(' ', $errors);
        }

        // Preserve submitted values if validation fails
        if (!empty($errors)) {
            $data['address'] = $address;
            $data['city_id'] = $city_id ?: '';
            $data['maps_url'] = $maps_url;
            $data['latitude'] = $latitude;
            $data['longitude'] = $longitude;
            $data['room_type'] = $room_type;
            $data['price'] = $_POST['price'] ?? '';
            $data['slot_capacity'] = $_POST['slot_capacity'] ?? '';
        }
    }
}

// HTML escaping helper
function e($value) {
    return htmlspecialchars(
        (string)($value ?? ''),
        ENT_QUOTES,
        'UTF-8'
    );
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Property - BoardNest</title>
    <link rel="stylesheet" href="../../public/assets/css/landlord.css?v=2">
</head>
<body>

<?php include __DIR__ . '/nav.php'; ?>

<div style="max-width:600px; margin:40px auto; padding:20px; border:1px solid #ccc; border-radius:8px;">

    <h2>Edit Property Details</h2>

    <?php if ($message !== ''): ?>
        <div style="color:red; margin-bottom:15px;" role="alert">
            <?= e($message) ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="edit_property.php?id=<?= (int)$property_id ?>">

        <input type="hidden" name="csrf_token" value="<?= e($csrf_token) ?>">

        <label for="city_id">City ID *</label>
        <input
            type="number"
            id="city_id"
            name="city_id"
            value="<?= e($data['city_id']) ?>"
            required
            min="1"
            style="width:100%; margin-bottom:10px; padding:8px;"
        >

        <label for="address">Full Address *</label>
        <textarea
            id="address"
            name="address"
            required
            style="width:100%; margin-bottom:10px; padding:8px;"
        ><?= e($data['address']) ?></textarea>

        <label for="maps_url">Google Maps URL</label>
        <input
            type="url"
            id="maps_url"
            name="maps_url"
            value="<?= e($data['maps_url']) ?>"
            style="width:100%; margin-bottom:10px; padding:8px;"
        >

        <div style="display:flex; gap:10px;">
            <div style="flex:1;">
                <label for="latitude">Latitude</label>
                <input
                    type="text"
                    id="latitude"
                    name="latitude"
                    value="<?= e($data['latitude']) ?>"
                    style="width:100%; padding:8px;"
                >
            </div>

            <div style="flex:1;">
                <label for="longitude">Longitude</label>
                <input
                    type="text"
                    id="longitude"
                    name="longitude"
                    value="<?= e($data['longitude']) ?>"
                    style="width:100%; padding:8px;"
                >
            </div>
        </div>

        <h3 style="margin-top:15px;">Room Details</h3>

        <label for="room_type">Room Type</label>
        <select
            id="room_type"
            name="room_type"
            required
            style="width:100%; margin-bottom:10px; padding:8px;"
        >
            <option value="single" <?= ($data['room_type'] ?? '') === 'single' ? 'selected' : '' ?>>
                Single Room
            </option>
            <option value="shared" <?= ($data['room_type'] ?? '') === 'shared' ? 'selected' : '' ?>>
                Shared Room
            </option>
        </select>

        <label for="price">Monthly Rent (LKR)</label>
        <input
            type="number"
            id="price"
            name="price"
            value="<?= e($data['price']) ?>"
            required
            min="0.01"
            step="0.01"
            style="width:100%; margin-bottom:10px; padding:8px;"
        >

        <label for="slot_capacity">Slot Capacity</label>
        <input
            type="number"
            id="slot_capacity"
            name="slot_capacity"
            value="<?= e($data['slot_capacity']) ?>"
            required
            min="1"
            style="width:100%; margin-bottom:15px; padding:8px;"
        >

        <button
            type="submit"
            style="background:#884513; color:#fff; padding:10px 20px; border:none; border-radius:4px; cursor:pointer;"
        >
            Update Property
        </button>

        <a
            href="dashboard.php"
            style="margin-left:10px; text-decoration:none; color:#666;"
        >
            Cancel
        </a>
    </form>
</div>

</body>
</html>