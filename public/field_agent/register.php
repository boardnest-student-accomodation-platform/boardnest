<?php
// ============================================================
// BoardNest — Field Agent Register (Orchestrator)
// public/field_agent/register.php
// ============================================================
if (session_id() === '') session_start();
require_once '../../config/db.php';

if (isset($_SESSION['user_id']) && isset($_SESSION['role']) && $_SESSION['role'] === 'field_agent') {
    header('Location: dashboard.php'); exit();
}

$error   = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name  = trim(isset($_POST['full_name'])   ? $_POST['full_name']   : '');
    $email      = trim(isset($_POST['email'])        ? $_POST['email']       : '');
    $password   = trim(isset($_POST['password'])     ? $_POST['password']    : '');
    $nic_number = trim(isset($_POST['nic_number'])   ? $_POST['nic_number']  : '');
    $mobile     = trim(isset($_POST['mobile'])       ? $_POST['mobile']      : '');
    $district   = trim(isset($_POST['district'])     ? $_POST['district']    : '');
    $city       = trim(isset($_POST['city'])         ? $_POST['city']        : '');
    $is_senior_applicant = !empty($_POST['is_senior_student_applicant']);

    $agree_terms = !empty($_POST['agree_terms']);

    if (empty($full_name) || empty($email) || empty($password) || empty($nic_number) || empty($mobile) || empty($district) || empty($city)) {
        $error = 'All fields are strictly required. Please select both your Operating District and City.';
    } elseif (strlen($password) < 8) {
        $error = 'Password must be at least 8 characters long.';
    } elseif (empty($_FILES['nic_front']['name']) || empty($_FILES['nic_back']['name'])) {
        $error = 'Both NIC Card Front and Back image uploads are strictly required.';
    } elseif (!$agree_terms) {
        $error = 'You must review and check the agreement declaration to submit your application.';
    } else {
        $uploaded_paths = array();
        try {
            $pdo->beginTransaction();

            $upload_dir = __DIR__ . '/../../public/uploads/field_agent_nic/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }

            // Check if email belongs to an existing user
            $stmtUserCheck = $pdo->prepare("SELECT user_id, email, role, created_at FROM users WHERE email = ?");
            $stmtUserCheck->execute(array($email));
            $existingUser = $stmtUserCheck->fetch();

            // Check if applicant is an existing student (by email or NIC)
            $stmtStudent = $pdo->prepare("
                SELECT s.*, u.user_id AS u_user_id, u.email AS u_email, u.role AS u_role, u.created_at AS u_created_at
                FROM students s
                INNER JOIN users u ON s.user_id = u.user_id
                WHERE u.email = ? OR s.nic_number = ?
            ");
            $stmtStudent->execute(array($email, $nic_number));
            $existingStudent = $stmtStudent->fetch();

            $recruit_mode = 'self_registered';
            $user_id = null;

            if ($existingStudent) {
                // Determine registration date and calculate tenure
                $reg_date = !empty($existingStudent['registered_date'])
                    ? $existingStudent['registered_date']
                    : date('Y-m-d', strtotime($existingStudent['u_created_at']));
                $reg_time = strtotime($reg_date);
                $tenure_years = (time() - $reg_time) / (365.25 * 86400);

                if ($tenure_years >= 2.0) {
                    // Qualified: 2+ years of registered studentship
                    $recruit_mode = 'senior_student';
                    $user_id = $existingStudent['u_user_id'];

                    // Update student record in students table
                    $stmtUpdStudent = $pdo->prepare("
                        UPDATE students 
                        SET is_senior_student = 1, is_field_agent = 1, registered_date = ?
                        WHERE student_id = ?
                    ");
                    $stmtUpdStudent->execute(array($reg_date, $existingStudent['student_id']));

                    // Update user credentials
                    $hash = password_hash($password, PASSWORD_DEFAULT);
                    $stmtUpdUser = $pdo->prepare("
                        UPDATE users 
                        SET full_name = ?, password_hash = ?, role = 'field_agent', status = 'pending' 
                        WHERE user_id = ?
                    ");
                    $stmtUpdUser->execute(array($full_name, $hash, $user_id));

                } else {
                    $tenure_formatted = number_format($tenure_years, 1);
                    if ($is_senior_applicant) {
                        throw new Exception('Senior Student Field Agent status requires at least 2 years of registered studentship. Your student account was registered on ' . date('M j, Y', $reg_time) . ' (Current tenure: ' . $tenure_formatted . ' years).');
                    } else {
                        throw new Exception('This account is registered as a Student. Students may only apply as Field Agents after completing at least 2 years of registered studentship.');
                    }
                }
            } elseif ($existingUser) {
                if ($existingUser['role'] === 'field_agent') {
                    throw new Exception('This email is already registered as a Field Agent. Please sign in.');
                } else {
                    throw new Exception('This email is already used by a ' . ucfirst($existingUser['role']) . ' account.');
                }
            } else {
                // New applicant without student history
                if ($is_senior_applicant) {
                    throw new Exception('Senior Student recruit mode requires an existing student registration with 2+ years of studentship. Please uncheck "Senior Student Applicant" to apply as a regular field agent.');
                }

                $hash = password_hash($password, PASSWORD_DEFAULT);
                $stmtUser = $pdo->prepare("INSERT INTO users (full_name, email, password_hash, role, status) VALUES (?, ?, ?, 'field_agent', 'pending')");
                $stmtUser->execute(array($full_name, $email, $hash));
                $user_id = $pdo->lastInsertId();
            }

            // Process NIC Front and Back Uploads
            $allowed_types = array('image/jpeg', 'image/png', 'image/webp');
            $ext_map = array('image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp');
            $finfo = new finfo(FILEINFO_MIME_TYPE);

            // 1. NIC Front
            $file_front = $_FILES['nic_front'];
            if ($file_front['error'] !== UPLOAD_ERR_OK || (int)$file_front['size'] > 5 * 1024 * 1024) {
                throw new Exception('NIC Front image must be uploaded and smaller than 5MB.');
            }
            $mime_front = $finfo->file($file_front['tmp_name']);
            if (!in_array($mime_front, $allowed_types, true)) {
                throw new Exception('NIC Front image must be a valid JPG, PNG, or WebP image.');
            }
            $name_front = 'nic_front_user_' . $user_id . '_' . bin2hex(random_bytes(8)) . '.' . $ext_map[$mime_front];
            $dest_front = $upload_dir . $name_front;
            if (!move_uploaded_file($file_front['tmp_name'], $dest_front)) {
                throw new Exception('Failed to save NIC Front image.');
            }
            $uploaded_paths[] = $dest_front;
            $db_nic_front = '/boardnest/public/uploads/field_agent_nic/' . $name_front;

            // 2. NIC Back
            $file_back = $_FILES['nic_back'];
            if ($file_back['error'] !== UPLOAD_ERR_OK || (int)$file_back['size'] > 5 * 1024 * 1024) {
                throw new Exception('NIC Back image must be uploaded and smaller than 5MB.');
            }
            $mime_back = $finfo->file($file_back['tmp_name']);
            if (!in_array($mime_back, $allowed_types, true)) {
                throw new Exception('NIC Back image must be a valid JPG, PNG, or WebP image.');
            }
            $name_back = 'nic_back_user_' . $user_id . '_' . bin2hex(random_bytes(8)) . '.' . $ext_map[$mime_back];
            $dest_back = $upload_dir . $name_back;
            if (!move_uploaded_file($file_back['tmp_name'], $dest_back)) {
                throw new Exception('Failed to save NIC Back image.');
            }
            $uploaded_paths[] = $dest_back;
            $db_nic_back = '/boardnest/public/uploads/field_agent_nic/' . $name_back;

            // Insert or Update field_agents record (auto-ensure assigned_district column exists)
            try {
                $colCheck = $pdo->query("SHOW COLUMNS FROM field_agents LIKE 'assigned_district'")->fetch();
                if (!$colCheck) {
                    $pdo->exec("ALTER TABLE field_agents ADD COLUMN assigned_district VARCHAR(100) NULL AFTER mobile");
                }
            } catch (Exception $eCol) {
                // Ignore if already altered or restricted
            }

            $stmtAgent = $pdo->prepare("
                INSERT INTO field_agents (user_id, nic_number, nic_front, nic_back, mobile, assigned_district, assigned_city, is_active, recruit_mode) 
                VALUES (?, ?, ?, ?, ?, ?, ?, 0, ?)
                ON DUPLICATE KEY UPDATE 
                    nic_number = VALUES(nic_number),
                    nic_front = VALUES(nic_front),
                    nic_back = VALUES(nic_back),
                    mobile = VALUES(mobile),
                    assigned_district = VALUES(assigned_district),
                    assigned_city = VALUES(assigned_city),
                    recruit_mode = VALUES(recruit_mode)
            ");
            $stmtAgent->execute(array($user_id, $nic_number, $db_nic_front, $db_nic_back, $mobile, $district, $city, $recruit_mode));

            $pdo->commit();

            if ($recruit_mode === 'senior_student') {
                $_SESSION['success'] = 'Application submitted successfully as a Senior Student Field Agent (2+ years studentship verified)! Your application is pending admin approval.';
            } else {
                $_SESSION['success'] = 'Application submitted successfully with NIC verification! Your account is pending admin approval.';
            }
            header('Location: login.php'); exit();

        } catch (Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            foreach ($uploaded_paths as $up) {
                if (is_file($up)) unlink($up);
            }
            $error = $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BoardNest — Apply to be a Field Agent</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&family=Outfit:wght@800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/field_agent.css">

</head>
<body class="auth-page-body">
    <?php require __DIR__ . '/partials/auth_register_form.php'; ?>
    <script src="../assets/js/field_agent.js"></script>
</body>
</html>
