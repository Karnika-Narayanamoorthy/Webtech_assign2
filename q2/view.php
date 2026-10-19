<?php
declare(strict_types=1);
session_start();
require __DIR__ . '/db.php';
require __DIR__ . '/helpers.php';
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) { http_response_code(400); exit('Invalid student ID.'); }
$stmt = $pdo->prepare('SELECT * FROM students WHERE id = ?');
$stmt->execute([$id]);
$student = $stmt->fetch();
if (!$student) { http_response_code(404); exit('Student not found.'); }
$filesStmt = $pdo->prepare('SELECT * FROM student_files WHERE student_id = ? ORDER BY uploaded_at DESC');
$filesStmt->execute([$id]);
$files = $filesStmt->fetchAll();
$token = csrf_token();
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Student profile</title><link rel="stylesheet" href="assets/style.css"></head>
<body><main class="wrap narrow"><a href="index.php">← All students</a><header class="top"><div><p class="eyebrow">STUDENT PROFILE</p><h1><?= e($student['name']) ?></h1></div><a class="button secondary" href="edit.php?id=<?= (int)$id ?>">Edit</a></header>
<?php if (isset($_GET['message'])): ?><p class="notice"><?= e((string)$_GET['message']) ?></p><?php endif; ?>
<section class="panel details"><p><strong>Roll number</strong><span><?= e($student['roll_number']) ?></span></p><p><strong>Email</strong><span><?= e($student['email']) ?></span></p><p><strong>Course</strong><span><?= e($student['course']) ?></span></p></section>
<h2>Uploaded files</h2>
<div class="file-grid">
<?php foreach ($files as $file): ?><article class="file-card">
<?php if (str_starts_with($file['mime_type'], 'image/')): ?><a href="file.php?id=<?= (int)$file['id'] ?>"><img class="preview" src="file.php?id=<?= (int)$file['id'] ?>" alt="Uploaded <?= e($file['file_category']) ?>"></a><?php else: ?><div class="pdf-icon">PDF</div><?php endif; ?>
<p><strong><?= e($file['original_name']) ?></strong></p><p class="muted"><?= e($file['file_category']) ?> · <?= e($file['mime_type']) ?> · <?= number_format(((int)$file['file_size']) / 1024, 1) ?> KB</p>
<a href="file.php?id=<?= (int)$file['id'] ?>">Open / download</a>
</article><?php endforeach; ?>
<?php if (!$files): ?><p>No files uploaded for this student yet.</p><?php endif; ?>
</div>
<h2>Upload a file</h2>
<form class="panel" method="post" action="upload.php" enctype="multipart/form-data">
<input type="hidden" name="csrf_token" value="<?= e($token) ?>"><input type="hidden" name="student_id" value="<?= (int)$id ?>">
<label>Category<select name="file_category"><option value="profile_image">Profile image</option><option value="id_proof">ID proof</option><option value="certificate">Certificate</option><option value="other">Other</option></select></label>
<label>File (JPG, PNG or PDF; maximum 5 MB)<input type="file" name="upload" required accept=".jpg,.jpeg,.png,.pdf"></label>
<button type="submit">Upload file</button></form></main></body></html>
