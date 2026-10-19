<?php
declare(strict_types=1);
require __DIR__ . '/db.php';
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) { http_response_code(400); exit('Invalid file ID.'); }
$stmt = $pdo->prepare('SELECT * FROM student_files WHERE id = ?');
$stmt->execute([$id]);
$file = $stmt->fetch();
if (!$file) { http_response_code(404); exit('File not found.'); }
$base = realpath(__DIR__ . '/uploads');
$path = realpath(__DIR__ . '/' . $file['file_path']);
if (!$base || !$path || !str_starts_with($path, $base . DIRECTORY_SEPARATOR) || !is_file($path)) {
    http_response_code(404); exit('Stored file not found.');
}
header('X-Content-Type-Options: nosniff');
header('Content-Type: ' . $file['mime_type']);
$inline = str_starts_with($file['mime_type'], 'image/');
header('Content-Disposition: ' . ($inline ? 'inline' : 'attachment') . '; filename="' . rawurlencode($file['original_name']) . '"');
header('Content-Length: ' . filesize($path));
readfile($path);
