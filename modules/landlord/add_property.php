<?php
require_once __DIR__ . '/../../includes/session.php';
startSession();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'landlord') {
    header('Location: ../../login.php');
    exit();
}

require_once __DIR__ . '/../../config/db.php';

$landlord_id = $_SESSION['user_id'];
$error = '';
$success = '';

// Landlord has Property check (1-Property Free Limit)
$checkStmt = $pdo->prepare("SELECT COUNT(*) FROM properties WHERE landlord_id = ?");
$checkStmt->execute([$landlord_id]);
$propertyCount = $checkStmt->fetchColumn();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($propertyCount >= 1) {
        $error = "You have reached your Free Plan limit. Please upgrade to Pro to add more properties.";
    } else {
        $title       = trim($_POST['title'] ?? '');
        $rent_amount = trim($_POST['rent_amount'] ?? '');
        $address     = trim($_POST['address'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $map_link    = trim($_POST['map_link'] ?? '');
        $latitude    = trim($_POST['latitude'] ?? '');
        $longitude   = trim($_POST['longitude'] ?? '');

        if (empty($title) || empty($rent_amount) || empty($address)) {
            $error = "Please fill in all required fields.";
        } else {
            // Photos Multiple Upload Logic (Max 5 Limit)
            $uploaded_images = [];
            $upload_dir = __DIR__ . '/../../public/uploads/properties/';

            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }

            if (!empty($_FILES['images']['name'][0])) {
                $total_files = count($_FILES['images']['name']);
                
                // picture up to 5 check
                if ($total_files > 5) {
                    $error = "You can upload a maximum of 5 images only.";
                } else {
                    for ($i = 0; $i < $total_files; $i++) {
                        $tmp_name = $_FILES['images']['tmp_name'][$i];
                        $file_name = $_FILES['images']['name'][$i];
                        $file_ext  = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
                        $allowed_exts = ['jpg', 'jpeg', 'png', 'webp'];

                        if (in_array($file_ext, $allowed_exts)) {
                            $new_file_name = uniqid('prop_') . '_' . $i . '.' . $file_ext;
                            $target_path   = $upload_dir . $new_file_name;

                            if (move_uploaded_file($tmp_name, $target_path)) {
                                $uploaded_images[] = $new_file_name;
                            }
                        }
                    }
                }
            }

            if (empty($error)) {
                try {
                    $images_json = !empty($uploaded_images) ? json_encode($uploaded_images) : null;

                    $sql = "INSERT INTO properties (landlord_id, title, rent_amount, address, description, map_link, latitude, longitude, images) 
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
                    
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([$landlord_id, $title, $rent_amount, $address, $description, $map_link, $latitude, $longitude, $images_json]);

                    $success = "Property added successfully!";
                    $propertyCount = 1;
                } catch (PDOException $e) {
                    $error = "Database Error: " . $e->getMessage();
                }
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
    <link rel="stylesheet" href="../../public/assets/css/style.css">
    <style>
        .form-card {
            max-width: 540px;
            margin: 40px auto;
            padding: 25px;
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-family: Arial, sans-serif;
            box-shadow: 0 4px 10px rgba(0,0,0,0.05);
        }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: bold; }
        .form-group input, .form-group textarea { width: 100%; padding: 10px; box-sizing: border-box; border: 1px solid #ccc; border-radius: 4px; }
        .row { display: flex; gap: 10px; }
        .row .form-group { flex: 1; }
        .btn-submit { width: 100%; padding: 12px; background: #28a745; color: #fff; border: none; border-radius: 4px; font-weight: bold; font-size: 16px; cursor: pointer; }
        .msg-error { background: #f8d7da; color: #721c24; padding: 10px; border-radius: 4px; margin-bottom: 15px; word-break: break-all; }
        .msg-success { background: #d4edda; color: #155724; padding: 10px; border-radius: 4px; margin-bottom: 15px; }
        
        .pro-upgrade-card {
            background: #fff8e1;
            border: 2px dashed #ffc107;
            padding: 25px;
            border-radius: 8px;
            text-align: center;
        }
        .pro-badge {
            background: #ffc107;
            color: #000;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: bold;
            display: inline-block;
            margin-bottom: 10px;
        }
        .btn-upgrade {
            display: inline-block;
            margin-top: 15px;
            padding: 10px 20px;
            background: #007bff;
            color: #fff;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
        }
        #file_list ul { margin: 5px 0 0 0; padding-left: 20px; color: #333; font-weight: normal; }
    </style>
</head>
<body>

<div class="form-card">
    <h2>Add New Property</h2>

    <?php if (!empty($error)): ?>
        <div class="msg-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <?php if (!empty($success)): ?>
        <div class="msg-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <?php if ($propertyCount >= 1 && empty($error)): ?>
        <div class="pro-upgrade-card">
            <span class="pro-badge">FREE PLAN LIMIT REACHED</span>
            <h3 style="margin: 10px 0 5px 0;">Want to add more properties?</h3>
            <p style="color: #666; font-size: 14px; margin-bottom: 15px;">
                You are currently on the Free Plan which allows only <strong>1 property</strong>. Upgrade to <strong>BoardNest Pro</strong> to publish unlimited properties!
            </p>
            <a href="upgrade.php" class="btn-upgrade">🚀 Get BoardNest Pro</a>
            <br><br>
            <a href="dashboard.php" style="color: #555; font-size: 14px;">Back to Dashboard</a>
        </div>
    <?php else: ?>
        <form method="POST" action="" enctype="multipart/form-data">
            <div class="form-group">
                <label>Property Title *</label>
                <input type="text" name="title" required placeholder="e.g. Single Room near UCSC">
            </div>

            <div class="form-group">
                <label>Monthly Rent (LKR) *</label>
                <input type="number" name="rent_amount" required placeholder="e.g. 15000">
            </div>

            <div class="form-group">
                <label>Address *</label>
                <input type="text" name="address" required placeholder="e.g. No. 12, Reid Avenue, Colombo 07">
            </div>

            <div class="form-group">
                <label>Google Maps Link</label>
                <input type="url" name="map_link" placeholder="e.g. https://maps.google.com/?q=...">
            </div>

            <div class="row">
                <div class="form-group">
                    <label>Latitude</label>
                    <input type="text" name="latitude" placeholder="e.g. 6.9022">
                </div>
                <div class="form-group">
                    <label>Longitude</label>
                    <input type="text" name="longitude" placeholder="e.g. 79.8612">
                </div>
            </div>

            <div class="form-group">
                <label>Description</label>
                <textarea name="description" rows="3" placeholder="Enter details about facilities, rules, etc."></textarea>
            </div>

            <div class="form-group">
                <label>Property Photos (Maximum 5 Images)</label>
                <input type="file" name="images[]" id="property_images" multiple accept="image/*" onchange="previewFiles()">
                
                <div id="file_list" style="margin-top: 10px; font-size: 13px;"></div>
            </div>

            <button type="submit" class="btn-submit">Add Property</button>
        </form>
    <?php endif; ?>
</div>

<script>
function previewFiles() {
    const input = document.getElementById('property_images');
    const output = document.getElementById('file_list');
    
    if (input.files.length > 5) {
        output.style.color = '#721c24';
        output.innerHTML = '⚠️ <strong>Warning:</strong> You can only select up to 5 photos!';
        input.value = ''; // up o 5 cancel
    } else if (input.files.length > 0) {
        output.style.color = '#28a745';
        let names = '<strong>Selected ' + input.files.length + ' photo(s):</strong><ul>';
        for (let i = 0; i < input.files.length; i++) {
            names += '<li>' + input.files[i].name + '</li>';
        }
        names += '</ul>';
        output.innerHTML = names;
    } else {
        output.innerHTML = '';
    }
}
</script>

</body>
</html>