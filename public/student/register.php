<?php
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../modules/students/services/register_student.php';

startSession();

if (isset($_SESSION['user_id']) && ($_SESSION['role'] ?? '') === 'student') {
    header('Location: dashboard.php');
    exit();
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$universities = studentRegistrationUniversities();
$academicYears = studentRegistrationAcademicYears();
$errors = [];
$values = [
    'full_name' => '',
    'mobile' => '',
    'nic_number' => '',
    'university' => '',
    'academic_year' => 'year_1',
    'email' => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $submittedToken = (string) ($_POST['csrf_token'] ?? '');
    if (!hash_equals($_SESSION['csrf_token'], $submittedToken)) {
        $errors[] = 'Your form session expired. Refresh the page and try again.';
        $values = array_merge($values, array_intersect_key($_POST, $values));
    } else {
        $result = registerStudent(
            $pdo,
            $_POST,
            $_FILES,
            __DIR__ . '/../../storage/student-verification'
        );
        $errors = $result['errors'];
        $values = array_merge($values, $result['values']);

        if ($result['success']) {
            $_SESSION['student_registration_complete'] = true;
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            header('Location: register.php?submitted=1');
            exit();
        }
    }
}

$submitted = isset($_GET['submitted']) && $_GET['submitted'] === '1'
    && !empty($_SESSION['student_registration_complete']);
unset($_SESSION['student_registration_complete']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Registration | BoardNest</title>
    <meta name="description" content="Create a verified BoardNest student account.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/student-register.css">
</head>
<body class="student-register-page">
    <header class="registration-header">
        <a class="registration-brand" href="../../index.html">BoardNest</a>
        <nav class="registration-nav" aria-label="Primary navigation">
            <a href="search.php">Browse</a>
            <a href="../../index.html#verification-heading">How It Works</a>
        </nav>
        <div class="registration-account-actions">
            <a class="registration-login" href="../../login.php">Log In</a>
            <a class="registration-signup" href="register.php" aria-current="page">Sign Up</a>
            <a class="registration-account-icon" href="../../login.php" aria-label="Account" title="Account">U</a>
        </div>
    </header>

    <main>
        <section class="registration-progress" aria-label="Registration progress">
            <div class="registration-progress-current"><span>01</span><strong>Student Profile &amp; Verification</strong></div>
            <div class="registration-progress-next"><span>Document Audit</span><i></i><span>Instant Access</span></div>
        </section>

        <section class="registration-intro">
            <span class="registration-accent" aria-hidden="true"></span>
            <h1>Join BoardNest as a Student</h1>
            <p>Create your verified student account to find safe, approved boarding houses, self-catering annexes, and study spaces near your Sri Lankan campus.</p>
        </section>

        <?php if ($submitted): ?>
            <section class="registration-success" role="status">
                <span aria-hidden="true">&#10003;</span>
                <div><strong>Application submitted for verification</strong><p>You can sign in now with Tier 1 access while an administrator reviews your documents.</p></div>
            </section>
        <?php endif; ?>

        <div class="registration-layout">
            <div class="registration-primary">
                <section class="verification-notice">
                    <span class="verification-notice-icon" aria-hidden="true">&#10003;</span>
                    <div><h2>Student Verification Notice <small>Protected</small></h2><p>Your information is encrypted, used solely to verify students and hosts, and never shared with third parties.</p></div>
                </section>

                <?php if ($errors): ?>
                    <div class="registration-errors" role="alert">
                        <strong>Please check your application.</strong>
                        <ul><?php foreach ($errors as $error): ?><li><?= htmlspecialchars($error) ?></li><?php endforeach; ?></ul>
                    </div>
                <?php endif; ?>

                <form class="student-registration-form" method="post" enctype="multipart/form-data" novalidate>
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

                    <fieldset class="registration-form-section">
                        <legend>Identity &amp; Contact</legend>
                        <p>Please provide your official legal identity as registered with your educational institution.</p>
                        <div class="registration-field-grid">
                            <label class="registration-field"><span>Full Legal Name <b>*</b></span><input type="text" name="full_name" value="<?= htmlspecialchars($values['full_name']) ?>" placeholder="e.g., Kavindu Lakshan Perera" maxlength="100" autocomplete="name" required></label>
                            <label class="registration-field"><span>Mobile Phone Number <b>*</b></span><input type="tel" name="mobile" value="<?= htmlspecialchars($values['mobile']) ?>" placeholder="+94 77 123 4567" maxlength="18" autocomplete="tel" required></label>
                            <label class="registration-field registration-field--wide"><span>National Identity Card (NIC) <b>*</b></span><input type="text" name="nic_number" value="<?= htmlspecialchars($values['nic_number']) ?>" placeholder="e.g., 200012345678 or 991234567V" maxlength="20" required></label>
                        </div>
                    </fieldset>

                    <fieldset class="registration-form-section">
                        <legend>University &amp; Academic Affiliation</legend>
                        <p>This allows us to prioritize rooms within walking distance of your lecture halls.</p>
                        <label class="registration-field registration-field--wide"><span>University / Higher Education Institute <b>*</b></span><select name="university" required><option value="">Select your University or Institute</option><?php foreach ($universities as $key => $name): ?><option value="<?= htmlspecialchars($key) ?>" <?= $values['university'] === $key ? 'selected' : '' ?>><?= htmlspecialchars($name) ?></option><?php endforeach; ?></select></label>
                        <div class="registration-year-heading"><span>Current Year of Study <b>*</b></span><small>Helps find suitable housemates</small></div>
                        <div class="registration-year-options" data-year-options>
                            <?php foreach ($academicYears as $key => $label): ?>
                                <label><input type="radio" name="academic_year" value="<?= htmlspecialchars($key) ?>" <?= $values['academic_year'] === $key ? 'checked' : '' ?> required><span><?= htmlspecialchars($label) ?></span></label>
                            <?php endforeach; ?>
                        </div>
                    </fieldset>

                    <fieldset class="registration-form-section">
                        <legend>Account Credentials</legend>
                        <p>A student email (.ac.lk or .edu) provides expedited instant verification.</p>
                        <div class="registration-field-grid">
                            <label class="registration-field"><span>Email Address <b>*</b></span><input type="email" name="email" value="<?= htmlspecialchars($values['email']) ?>" placeholder="kavindu@uom.lk or personal email" maxlength="100" autocomplete="email" required></label>
                            <label class="registration-field"><span>Create Password <b>*</b></span><span class="registration-password-control"><input id="studentPassword" type="password" name="password" placeholder="At least 8 characters" minlength="8" autocomplete="new-password" required><button type="button" data-password-toggle aria-label="Show password">Show</button></span><span class="registration-strength"><i></i><i></i><i></i><i></i><small data-password-strength>Enter 8+ characters</small></span></label>
                        </div>
                    </fieldset>

                    <section class="registration-form-section registration-upload-section">
                        <div class="registration-proof-heading"><div><h2>Proof of Studentship (Required)</h2><p>Upload a clear photo or PDF of your Student ID card, university admission letter, or current semester academic record.</p></div><span>&#10003; Safety requirement</span></div>
                        <label class="registration-upload" data-upload-area>
                            <input type="file" name="student_proof" accept=".pdf,.jpg,.jpeg,.png,application/pdf,image/jpeg,image/png" required data-file-input>
                            <span class="registration-upload-icon" aria-hidden="true">&#8679;</span>
                            <strong>Drag and drop your file here, or <u>browse files</u></strong>
                            <small>PDF, JPG, or PNG. Maximum file size 5 MB.</small>
                        </label>
                        <div class="registration-file-preview" data-file-preview hidden><span class="registration-file-type">ID</span><div><strong data-file-name>No file selected</strong><small data-file-size></small></div><button type="button" data-file-remove aria-label="Remove selected file">&times;</button></div>
                    </section>

                    <label class="registration-consent"><input type="checkbox" name="consent" value="1" required><span>I confirm that all details and submitted documents are authentic and belong to me. I agree to the <a href="#">BoardNest Terms of Service</a>, <a href="#">Privacy Standards</a>, and <a href="#">Community Safety Guidelines</a>.</span></label>

                    <button class="registration-submit" type="submit"><span aria-hidden="true">&#10003;</span> Complete Registration &amp; Submit for Verification <span aria-hidden="true">&#8594;</span></button>
                    <p class="registration-existing-account">Already have a BoardNest account? <a href="../../login.php">Log in here</a></p>
                </form>
            </div>

            <aside class="registration-aside">
                <section class="verification-benefits">
                    <h2>Why Verification Matters</h2>
                    <ol><li><span>1</span><div><strong>Fast-Track Approvals</strong></div></li><li><span>2</span><div><strong>Direct Landlord Contact</strong><p>Zero broker commissions</p></div></li><li><span>3</span><div><strong>Field-Inspected Properties</strong><p>Every listed boarding house has been physically inspected for basic student safety, locks, and water utilities.</p></div></li></ol>
                </section>
                <blockquote class="registration-testimonial">
                    <div class="registration-testimonial-person"><span>SW</span><div><strong>Sanduni Wickramasinghe</strong><small>Faculty of Engineering, UoM</small></div></div>
                    <p>“Getting verified took literally 45 minutes with my Moratuwa student card. I found an annex in Katubedda with no broker hassle, right on the bus route.”</p>
                    <footer><span>&#10003; Verified Resident</span><strong>Katubedda, Moratuwa</strong></footer>
                </blockquote>
            </aside>
        </div>
    </main>

    <footer class="registration-footer"><strong>BoardNest</strong><nav><a href="#">Privacy Policy</a><a href="#">Terms of Service</a><a href="#">Support</a><a href="#">FAQ</a></nav><p>Your information is encrypted, used solely to verify students and hosts, and never shared with third parties.</p></footer>
    <script src="../assets/js/student-register.js"></script>
</body>
</html>
