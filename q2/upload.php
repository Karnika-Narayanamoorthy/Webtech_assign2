<?php
declare(strict_types=1);

session_start();

require __DIR__ . '/db.php';
require __DIR__ . '/helpers.php';

// Allow POST requests only
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('POST required.');
}

// Verify CSRF token
verify_csrf();

// Get student ID and file category
$studentId = filter_input(INPUT_POST, 'student_id', FILTER_VALIDATE_INT);

$category = (string) ($_POST['file_category'] ?? 'other');

$allowedCategories = [
    'profile_image',
    'id_proof',
    'certificate',
    'other'
];

if (
    !$studentId ||
    !in_array($category, $allowedCategories, true)
) {
    http_response_code(400);
    exit('Invalid student or file category.');
}

// Check whether a file was selected
if (
    !isset($_FILES['upload']) ||
    !is_array($_FILES['upload'])
) {
    redirect(
        'view.php?id=' . $studentId .
        '&message=' . urlencode('Please select a file.')
    );
}

$file = $_FILES['upload'];

// Check upload error
if ($file['error'] !== UPLOAD_ERR_OK) {
    redirect(
        'view.php?id=' . $studentId .
        '&message=' . urlencode('File upload failed. Please try again.')
    );
}

// Check file size: maximum 5 MB
$maxBytes = 5 * 1024 * 1024;

if (
    $file['size'] < 1 ||
    $file['size'] > $maxBytes
) {
    redirect(
        'view.php?id=' . $studentId .
        '&message=' . urlencode('File must be between 1 byte and 5 MB.')
    );
}

// Verify uploaded temporary file
if (!is_uploaded_file($file['tmp_name'])) {
    redirect(
        'view.php?id=' . $studentId .
        '&message=' . urlencode('Invalid uploaded file.')
    );
}

// Get original filename and extension
$originalName = basename((string) $file['name']);

$extension = strtolower(
    pathinfo($originalName, PATHINFO_EXTENSION)
);

// Allowed extensions and MIME types
$allowedExtensions = [
    'jpg'  => 'image/jpeg',
    'jpeg' => 'image/jpeg',
    'png'  => 'image/png',
    'pdf'  => 'application/pdf'
];

// Validate file extension
if (!isset($allowedExtensions[$extension])) {
    redirect(
        'view.php?id=' . $studentId .
        '&message=' . urlencode('Only JPG, PNG and PDF files are accepted.')
    );
}

// Detect actual file type
$finfo = new finfo(FILEINFO_MIME_TYPE);

$mime = $finfo->file($file['tmp_name']);

if (
    $mime === false ||
    $mime !== $allowedExtensions[$extension]
) {
    redirect(
        'view.php?id=' . $studentId .
        '&message=' . urlencode('File content does not match its extension.')
    );
}

// Validate profile image
if (
    $category === 'profile_image' &&
    !in_array($mime, ['image/jpeg', 'image/png'], true)
) {
    redirect(
        'view.php?id=' . $studentId .
        '&message=' . urlencode('A profile image must be JPG or PNG.')
    );
}

// Create upload directory
$uploadDirectory = __DIR__ . '/uploads';

if (
    !is_dir($uploadDirectory) &&
    !mkdir($uploadDirectory, 0750, true) &&
    !is_dir($uploadDirectory)
) {
    http_response_code(500);
    exit('Could not create upload directory.');
}

// Generate a unique filename to prevent overwriting
try {
    do {
        $storedName = bin2hex(random_bytes(16)) . '.' . $extension;
        $absolutePath = $uploadDirectory . '/' . $storedName;
    } while (file_exists($absolutePath));
} catch (Throwable $exception) {
    http_response_code(500);
    exit('Could not generate a safe filename.');
}

// Relative path stored in database
$relativePath = 'uploads/' . $storedName;

// Move uploaded file
if (!move_uploaded_file($file['tmp_name'], $absolutePath)) {
    http_response_code(500);
    exit('Upload could not be saved.');
}

// Save file metadata in MySQL
try {

    // Check whether the student exists
    $checkStudent = $pdo->prepare(
        'SELECT id FROM students WHERE id = ?'
    );

    $checkStudent->execute([$studentId]);

    if (!$checkStudent->fetch()) {
        throw new RuntimeException(
            'Student record not found.'
        );
    }

    // Insert file metadata
    $insert = $pdo->prepare(
        'INSERT INTO student_files
        (
            student_id,
            original_name,
            stored_name,
            file_path,
            mime_type,
            file_category,
            file_size
        )
        VALUES (?, ?, ?, ?, ?, ?, ?)'
    );

    $insert->execute([
        $studentId,
        $originalName,
        $storedName,
        $relativePath,
        $mime,
        $category,
        (int) $file['size']
    ]);

} catch (Throwable $exception) {

    if (isset($absolutePath) && is_file($absolutePath)) {
        unlink($absolutePath);
    }

    http_response_code(500);

    exit(
        'Actual error: ' .
        htmlspecialchars(
            $exception->getMessage(),
            ENT_QUOTES,
            'UTF-8'
        )
    );
}

// Redirect after successful upload
redirect(
    'view.php?id=' . $studentId .
    '&message=' . urlencode('File uploaded successfully.')
);