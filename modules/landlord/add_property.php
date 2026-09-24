<?php
require_once __DIR__ . '/../../includes/session.php';
startSession();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'landlord') {
    header('Location: ../../login.php');
    exit();
}

require_once __DIR__ . '/../../config/db.php';

$user_id = $_SESSION['user_id'];
$error   = '';
$success = '';

// Get landlord_id
$lStmt = $pdo->prepare("SELECT landlord_id FROM landlords WHERE user_id = ?");
$lStmt->execute([$user_id]);
$landlord = $lStmt->fetch();
$landlord_id = $landlord['landlord_id'] ?? null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title       = trim($_POST['title'] ?? '');
    $city_id     = filter_input(INPUT_POST, 'city_id', FILTER_VALIDATE_INT) ?: 1;
    $address     = trim($_POST['address'] ?? '');
    $latitude    = trim($_POST['latitude'] ?? '');
    $longitude   = trim($_POST['longitude'] ?? '');
    $rent_amount = trim($_POST['rent_amount'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $maps_url    = trim($_POST['maps_url'] ?? '');

    if (empty($title) || empty($address)) {
        $error = "Property Title and Address are required.";
    } elseif (!empty($latitude) && !is_numeric($latitude)) {
        $error = "Latitude must be a numeric value.";
    } elseif (!empty($longitude) && !is_numeric($longitude)) {
        $error = "Longitude must be a numeric value.";
    } elseif (!empty($rent_amount) && !is_numeric($rent_amount)) {
        $error = "Rent amount must be numeric.";
    } else {
        $uploaded_photos = [];
        $upload_dir = __DIR__ . '/../../public/uploads/properties/';

        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }

        // Image validation & upload (Max 5 photos, 5MB each)
        if (!empty($_FILES['photos']['name'][0])) {
            $allowed_types = ['image/jpeg', 'image/png', 'image/webp'];
            $max_file_size = 5 * 1024 * 1024;
            $file_count    = count($_FILES['photos']['name']);

            if ($file_count > 5) {
                $error = "You can only upload a maximum of 5 images.";
            } else {
                for ($i = 0; $i < $file_count; $i++) {
                    if ($_FILES['photos']['error'][$i] === UPLOAD_ERR_OK) {
                        $tmp_name  = $_FILES['photos']['tmp_name'][$i];
                        $file_size = $_FILES['photos']['size'][$i];
                        $mime_type = mime_content_type($tmp_name);

                        if ($file_size > $max_file_size) {
                            $error = "Each image must not exceed 5MB.";
                            break;
                        }

                        if (!in_array($mime_type, $allowed_types, true)) {
                            $error = "Only JPG, PNG, and WebP formats are allowed.";
                            break;
                        }

                        $ext = pathinfo($_FILES['photos']['name'][$i], PATHINFO_EXTENSION);
                        $filename = uniqid('prop_', true) . '.' . $ext;
                        if (move_uploaded_file($tmp_name, $upload_dir . $filename)) {
                            $uploaded_photos[] = $filename;
                        }
                    }
                }
            }
        }

        if (empty($error) && $landlord_id) {
            try {
                $images_json = !empty($uploaded_photos) ? json_encode($uploaded_photos) : null;
                $lat_val     = !empty($latitude) ? $latitude : null;
                $lng_val     = !empty($longitude) ? $longitude : null;
                $rent_val    = !empty($rent_amount) ? (float)$rent_amount : null;

                $stmt = $pdo->prepare("
                    INSERT INTO properties (landlord_id, title, city_id, address, maps_url, latitude, longitude, description, status, rent_amount, images) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'available', ?, ?)
                ");
                $stmt->execute([
                    $landlord_id,
                    $title,
                    $city_id,
                    $address,
                    $maps_url,
                    $lat_val,
                    $lng_val,
                    $description,
                    $rent_val,
                    $images_json
                ]);

                $success = "Property submitted successfully!";
            } catch (PDOException $e) {
                error_log("Add property error: " . $e->getMessage());
                $error = "An internal error occurred while saving the property.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Property — BoardNest</title>
    <link rel="stylesheet" href="../../public/assets/css/landlord.css">
</head>
<body>

<div class="form-card">
    <div class="page-header">
        <h2>Add New Property</h2>
        <a href="dashboard.php" class="btn-back">← Dashboard</a>
    </div>

    <?php if ($error): ?>
        <div class="alert-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <form method="POST" action="" enctype="multipart/form-data">
        <div class="form-group">
            <label>Property Title *</label>
            <input type="text" name="title" required placeholder="e.g. Colombo Homestay">
        </div>

        <div class="form-group">
            <label>Address *</label>
            <textarea name="address" rows="2" required placeholder="Full physical address"></textarea>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label>Rent Amount (LKR)</label>
                <input type="number" step="0.01" name="rent_amount" placeholder="e.g. 25000.00">
            </div>
            <div class="form-group">
                <label>Maps URL</label>
                <input type="url" name="maps_url" placeholder="Google Maps link">
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label>Latitude (Numeric)</label>
                <input type="text" name="latitude" placeholder="e.g. 6.9271">
            </div>
            <div class="form-group">
                <label>Longitude (Numeric)</label>
                <input type="text" name="longitude" placeholder="e.g. 79.8612">
            </div>
        </div>

        <div class="form-group">
            <label>Description</label>
            <textarea name="description" rows="3" placeholder="Facilities, nearby places, etc."></textarea>
        </div>

        <div class="form-group">
            <label>Property Images (Max 5, 5MB each)</label>
            <input type="file" name="photos[]" multiple accept="image/jpeg,image/png,image/webp">
        </div>

        <button type="submit" class="btn btn-primary btn-block">Save Property</button>
    </form>
</div>

</body>
</html>