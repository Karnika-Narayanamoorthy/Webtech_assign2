<?php
declare(strict_types=1);
session_start();
require __DIR__ . '/db.php';
require __DIR__ . '/helpers.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); exit('POST required.'); }
verify_csrf();
$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
if (!$id) { http_response_code(400); exit('Invalid student ID.'); }

// Delete stored bytes first; FK cascade deletes metadata. Keep paths constrained to our upload folder.
$stmt = $pdo->prepare('SELECT stored_name FROM student_files WHERE student_id = ?');
$stmt->execute([$id]);
$uploadDir = __DIR__ . '/uploads/';
foreach ($stmt->fetchAll(PDO::FETCH_COLUMN) as $storedName) {
    if (is_string($storedName) && basename($storedName) === $storedName && is_file($uploadDir . $storedName)) {
        unlink($uploadDir . $storedName);
    }
}
$delete = $pdo->prepare('DELETE FROM students WHERE id = ?');
$delete->execute([$id]);
redirect('index.php?message=' . urlencode('Student deleted if the record existed.'));
