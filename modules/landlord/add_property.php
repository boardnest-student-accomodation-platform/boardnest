<?php
require_once __DIR__ . '/../../includes/session.php';
startSession();

if (!isset($_SESSION['user_id'], $_SESSION['role']) || $_SESSION['role'] !== 'landlord') {
    header('Location: ../../login.php');
    exit;
}

require_once __DIR__ . '/../../config/db.php';

$user_id = (int) $_SESSION['user_id'];
$error = '';
$success = '';
$landlord_id = null;
$is_pro = false;
$property_count = 0;

try {
    $lStmt = $pdo->prepare('SELECT landlord_id, subsc_tier, subsc_expires FROM landlords WHERE user_id = ? LIMIT 1');
    $lStmt->execute([$user_id]);
    $landlord = $lStmt->fetch(PDO::FETCH_ASSOC);
    if (!$landlord) {
        http_response_code(403);
        exit('Landlord profile not found.');
    }

    $landlord_id = (int) $landlord['landlord_id'];
    $is_pro = strtolower((string)($landlord['subsc_tier'] ?? 'standard')) === 'pro'
        && (empty($landlord['subsc_expires']) || $landlord['subsc_expires'] >= date('Y-m-d'));

    $countStmt = $pdo->prepare('SELECT COUNT(*) FROM properties WHERE landlord_id = ?');
    $countStmt->execute([$landlord_id]);
    $property_count = (int) $countStmt->fetchColumn();
} catch (PDOException $e) {
    error_log('Property limit check error: ' . $e->getMessage());
    http_response_code(500);
    exit('Unable to load your property account. Please try again later.');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!$is_pro && $property_count >= 1) {
        $error = 'Your Standard plan allows only one property. Upgrade to Pro to add more properties.';
    }

    $title = trim($_POST['title'] ?? '');
    $city_id = filter_input(INPUT_POST, 'city_id', FILTER_VALIDATE_INT) ?: 1;
    $address = trim($_POST['address'] ?? '');
    $latitude = trim($_POST['latitude'] ?? '');
    $longitude = trim($_POST['longitude'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $maps_url = trim($_POST['maps_url'] ?? '');

    if (!$error && ($title === '' || $address === '')) {
        $error = 'Property Title and Address are required.';
    } elseif (!$error && $latitude !== '' && (!is_numeric($latitude) || (float)$latitude < -90 || (float)$latitude > 90)) {
        $error = 'Latitude must be a valid number between -90 and 90.';
    } elseif (!$error && $longitude !== '' && (!is_numeric($longitude) || (float)$longitude < -180 || (float)$longitude > 180)) {
        $error = 'Longitude must be a valid number between -180 and 180.';
    } elseif (!$error && $maps_url !== '' && !filter_var($maps_url, FILTER_VALIDATE_URL)) {
        $error = 'Please enter a valid Maps URL.';
    }

    $uploaded_photos = [];
    $upload_dir = __DIR__ . '/../../public/uploads/properties/';
    if (!$error && !empty($_FILES['photos']['name'][0])) {
        $allowed_types = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
        $max_file_size = 5 * 1024 * 1024;
        $file_count = count($_FILES['photos']['name']);
        if ($file_count > 5) {
            $error = 'You can only upload a maximum of 5 images.';
        } elseif (!is_dir($upload_dir) && !mkdir($upload_dir, 0755, true) && !is_dir($upload_dir)) {
            $error = 'Image upload directory is unavailable.';
        } else {
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            for ($i = 0; $i < $file_count; $i++) {
                $upload_error = $_FILES['photos']['error'][$i] ?? UPLOAD_ERR_NO_FILE;
                if ($upload_error !== UPLOAD_ERR_OK) {
                    $error = 'One of the images could not be uploaded.';
                    break;
                }
                $tmp_name = $_FILES['photos']['tmp_name'][$i];
                $file_size = (int) $_FILES['photos']['size'][$i];
                $mime_type = $finfo->file($tmp_name);
                if ($file_size <= 0 || $file_size > $max_file_size) {
                    $error = 'Each image must be between 1 byte and 5MB.';
                    break;
                }
                if (!isset($allowed_types[$mime_type]) || !is_uploaded_file($tmp_name)) {
                    $error = 'Only JPG, PNG, and WebP images are allowed.';
                    break;
                }
                $filename = bin2hex(random_bytes(16)) . '.' . $allowed_types[$mime_type];
                if (!move_uploaded_file($tmp_name, $upload_dir . $filename)) {
                    $error = 'An image could not be saved. Please try again.';
                    break;
                }
                $uploaded_photos[] = $filename;
            }
        }
    }

    if (!$error) {
        try {
            // Re-check inside a transaction so the limit is enforced at the point of insertion.
            $pdo->beginTransaction();
            $planStmt = $pdo->prepare('SELECT subsc_tier, subsc_expires FROM landlords WHERE landlord_id = ? FOR UPDATE');
            $planStmt->execute([$landlord_id]);
            $currentPlan = $planStmt->fetch(PDO::FETCH_ASSOC);
            $currentlyPro = $currentPlan
                && strtolower((string)($currentPlan['subsc_tier'] ?? 'standard')) === 'pro'
                && (empty($currentPlan['subsc_expires']) || $currentPlan['subsc_expires'] >= date('Y-m-d'));
            $countStmt = $pdo->prepare('SELECT COUNT(*) FROM properties WHERE landlord_id = ?');
            $countStmt->execute([$landlord_id]);
            $currentCount = (int) $countStmt->fetchColumn();

            if (!$currentlyPro && $currentCount >= 1) {
                $pdo->rollBack();
                $error = 'Your Standard plan allows only one property. Upgrade to Pro to add more properties.';
            } else {
                $images_json = $uploaded_photos ? json_encode($uploaded_photos, JSON_THROW_ON_ERROR) : null;
                $stmt = $pdo->prepare("INSERT INTO properties (landlord_id, title, city_id, address, maps_url, latitude, longitude, description, status, images) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'available', ?)");
                $stmt->execute([
                    $landlord_id, $title, $city_id, $address, $maps_url,
                    $latitude !== '' ? (float)$latitude : null,
                    $longitude !== '' ? (float)$longitude : null,
                    $description, $images_json
                ]);
                $pdo->commit();
                $success = 'Property submitted successfully!';
                $property_count = $currentCount + 1;
            }
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            foreach ($uploaded_photos as $photo) {
                $path = $upload_dir . $photo;
                if (is_file($path)) @unlink($path);
            }
            error_log('Add property error: ' . $e->getMessage());
            $error = 'An internal error occurred while saving the property.';
        }
    }
    if ($error && $uploaded_photos) {
        foreach ($uploaded_photos as $photo) {
            $path = $upload_dir . $photo;
            if (is_file($path)) @unlink($path);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Property — BoardNest</title>
    <link rel="stylesheet" href="../../public/assets/css/landlord.css?v=2">
</head>
<body>
    <?php include __DIR__ . '/nav.php'; ?>
<div class="form-card">
    <div class="page-header">
        <h2>Add New Property</h2>
    </div>

    <p>Plan: <strong><?= $is_pro ? 'Pro' : 'Standard' ?></strong> | Properties: <strong><?= (int)$property_count ?></strong><?= $is_pro ? '' : ' / 1' ?></p>

    <?php if ($error): ?>
        <div class="alert-error" role="alert">
            <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
            <?php if (!$is_pro && $property_count >= 1): ?>
                <p><a href="upgrade.php">Upgrade to Pro</a></p>
            <?php endif; ?>
        </div>
    <?php endif; ?>
    <?php if ($success): ?>
        <div class="alert-success" role="status"><?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>

    <?php if ($is_pro || $property_count < 1): ?>
    <form method="POST" action="" enctype="multipart/form-data">
        <div class="form-group">
            <label for="title">Property Title *</label>
            <input id="title" type="text" name="title" required maxlength="255" placeholder="e.g. Colombo Homestay">
        </div>
        <div class="form-group">
            <label for="address">Address *</label>
            <textarea id="address" name="address" rows="2" required placeholder="Full physical address"></textarea>
        </div>
        <div class="form-group">
            <label for="maps_url">Maps URL</label>
            <input id="maps_url" type="url" name="maps_url" placeholder="Google Maps link">
        </div>
        <div class="form-row">
            <div class="form-group">
                <label for="latitude">Latitude</label>
                <input id="latitude" type="number" step="any" min="-90" max="90" name="latitude" placeholder="e.g. 6.9271">
            </div>
            <div class="form-group">
                <label for="longitude">Longitude</label>
                <input id="longitude" type="number" step="any" min="-180" max="180" name="longitude" placeholder="e.g. 79.8612">
            </div>
        </div>
        <div class="form-group">
            <label for="description">Description</label>
            <textarea id="description" name="description" rows="3" placeholder="Facilities, nearby places, etc."></textarea>
        </div>
        <div class="form-group">
            <label for="photos">Property Images (Max 5, 5MB each)</label>
            <input id="photos" type="file" name="photos[]" multiple accept="image/jpeg,image/png,image/webp">
        </div>
        <button type="submit" class="btn btn-primary btn-block">Save Property</button>
    </form>
    <?php elseif (!$is_pro): ?>
        <p>Your Standard plan allows one property. Upgrade to Pro to register more.</p>
        <a class="btn btn-primary" href="upgrade.php">Upgrade to Pro</a>
    <?php endif; ?>
</div>
</body>
</html>
