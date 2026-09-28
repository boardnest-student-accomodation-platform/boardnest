<?php

function registerStudent(PDO $pdo, array $input, array $files, string $uploadDirectory): array
{
    $values = [
        'full_name' => trim((string) ($input['full_name'] ?? '')),
        'mobile' => trim((string) ($input['mobile'] ?? '')),
        'nic_number' => strtoupper(trim((string) ($input['nic_number'] ?? ''))),
        'university' => trim((string) ($input['university'] ?? '')),
        'academic_year' => trim((string) ($input['academic_year'] ?? '')),
        'email' => strtolower(trim((string) ($input['email'] ?? ''))),
    ];

    $password = (string) ($input['password'] ?? '');
    $errors = [];
    $universities = studentRegistrationUniversities();
    $academicYears = studentRegistrationAcademicYears();

    if ($values['full_name'] === '' || mb_strlen($values['full_name']) < 3 || mb_strlen($values['full_name']) > 100) {
        $errors[] = 'Enter your full legal name.';
    }

    $normalisedMobile = preg_replace('/[\s()-]+/', '', $values['mobile']);
    if (!preg_match('/^(?:\+94|0)?7\d{8}$/', $normalisedMobile)) {
        $errors[] = 'Enter a valid Sri Lankan mobile number.';
    }

    $normalisedNic = preg_replace('/\s+/', '', $values['nic_number']);
    if (!preg_match('/^(?:\d{9}[VX]|\d{12})$/', $normalisedNic)) {
        $errors[] = 'Enter a valid Sri Lankan NIC number.';
    }

    if (!isset($universities[$values['university']])) {
        $errors[] = 'Select your university or higher education institute.';
    }

    if (!isset($academicYears[$values['academic_year']])) {
        $errors[] = 'Select your current year of study.';
    }

    if (!filter_var($values['email'], FILTER_VALIDATE_EMAIL) || strlen($values['email']) > 100) {
        $errors[] = 'Enter a valid email address.';
    }

    if (strlen($password) < 8) {
        $errors[] = 'Create a password with at least 8 characters.';
    }

    if (!isset($input['consent'])) {
        $errors[] = 'Confirm that your details and document are authentic.';
    }

    $proof = $files['student_proof'] ?? null;
    if (!$proof || ($proof['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        $errors[] = 'Upload your proof of studentship.';
    } elseif (($proof['size'] ?? 0) > 5 * 1024 * 1024) {
        $errors[] = 'The proof document must be 5 MB or smaller.';
    }

    $extension = '';
    if ($proof && ($proof['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK) {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = $finfo ? finfo_file($finfo, $proof['tmp_name']) : '';
        if ($finfo) {
            finfo_close($finfo);
        }

        $allowedTypes = [
            'application/pdf' => 'pdf',
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
        ];

        if (!isset($allowedTypes[$mime])) {
            $errors[] = 'Upload a PDF, JPG, or PNG document.';
        } else {
            $extension = $allowedTypes[$mime];
        }
    }

    if ($errors) {
        return ['success' => false, 'errors' => array_values(array_unique($errors)), 'values' => $values];
    }

    $emailCheck = $pdo->prepare('SELECT user_id FROM users WHERE email = ? LIMIT 1');
    $emailCheck->execute([$values['email']]);
    if ($emailCheck->fetch()) {
        return ['success' => false, 'errors' => ['An account already exists for this email address.'], 'values' => $values];
    }

    $nicCheck = $pdo->prepare('SELECT student_id FROM students WHERE nic_number = ? LIMIT 1');
    $nicCheck->execute([$normalisedNic]);
    if ($nicCheck->fetch()) {
        return ['success' => false, 'errors' => ['A student application already exists for this NIC number.'], 'values' => $values];
    }

    $storedFile = null;

    try {
        $pdo->beginTransaction();

        $userStatement = $pdo->prepare(
            "INSERT INTO users (full_name, email, password_hash, role, status) VALUES (?, ?, ?, 'student', 'active')"
        );
        $userStatement->execute([
            $values['full_name'],
            $values['email'],
            password_hash($password, PASSWORD_DEFAULT),
        ]);

        $userId = (int) $pdo->lastInsertId();
        $studentStatement = $pdo->prepare(
            "INSERT INTO students (user_id, nic_number, mobile, university, academic_year, verf_tier) VALUES (?, ?, ?, ?, ?, 'tier1')"
        );
        $studentStatement->execute([
            $userId,
            $normalisedNic,
            $normalisedMobile,
            $universities[$values['university']],
            $academicYears[$values['academic_year']],
        ]);

        if (!is_dir($uploadDirectory) && !mkdir($uploadDirectory, 0775, true) && !is_dir($uploadDirectory)) {
            throw new RuntimeException('The verification upload directory could not be created.');
        }

        $storedFile = rtrim($uploadDirectory, DIRECTORY_SEPARATOR)
            . DIRECTORY_SEPARATOR
            . 'student-' . $userId . '-' . bin2hex(random_bytes(6)) . '.' . $extension;

        if (!move_uploaded_file($proof['tmp_name'], $storedFile)) {
            throw new RuntimeException('The proof document could not be stored.');
        }

        $pdo->commit();
        return ['success' => true, 'errors' => [], 'values' => $values];
    } catch (Throwable $exception) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        if ($storedFile && is_file($storedFile)) {
            unlink($storedFile);
        }

        return [
            'success' => false,
            'errors' => ['We could not submit your application. Please try again.'],
            'values' => $values,
        ];
    }
}

function studentRegistrationUniversities(): array
{
    return [
        'uom' => 'University of Moratuwa',
        'uoc' => 'University of Colombo',
        'sliit' => 'SLIIT Malabe',
        'nsbm' => 'NSBM Green University',
        'other' => 'Other Higher Education Institute',
    ];
}

function studentRegistrationAcademicYears(): array
{
    return [
        'year_1' => '1st Year / Fresher',
        'year_2' => '2nd Year',
        'year_3' => '3rd Year',
        'year_4' => '4th / Final Year',
        'postgraduate' => 'Postgraduate',
    ];
}
