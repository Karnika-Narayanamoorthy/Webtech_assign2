<?php
declare(strict_types=1);
session_start();
require __DIR__ . '/db.php';
require __DIR__ . '/helpers.php';

$students = $pdo->query(
    'SELECT s.*, COUNT(f.id) AS file_count
     FROM students s LEFT JOIN student_files f ON f.student_id = s.id
     GROUP BY s.id ORDER BY s.created_at DESC'
)->fetchAll();
$token = csrf_token();
?>
<!doctype html>
<html lang="en"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Student Admin | PHP</title><link rel="stylesheet" href="assets/style.css">
</head><body><main class="wrap">
<header class="top"><div><p class="eyebrow">PHP + MYSQL</p><h1>Student records</h1><p>Manage student information and profile files.</p></div>
<a class="button" href="add.php">+ Add student</a></header>
<?php if (isset($_GET['message'])): ?><p class="notice"><?= e((string)$_GET['message']) ?></p><?php endif; ?>
<div class="table-wrap"><table><thead><tr><th>Name</th><th>Roll number</th><th>Email</th><th>Course</th><th>Files</th><th>Actions</th></tr></thead><tbody>
<?php foreach ($students as $student): ?>
<tr>
<td><?= e($student['name']) ?></td><td><?= e($student['roll_number']) ?></td><td><?= e($student['email']) ?></td>
<td><?= e($student['course']) ?></td><td><?= (int)$student['file_count'] ?></td>
<td class="actions"><a href="view.php?id=<?= (int)$student['id'] ?>">View</a> <a href="edit.php?id=<?= (int)$student['id'] ?>">Edit</a>
<form class="inline" method="post" action="delete.php" onsubmit="return confirm('Delete this student and all linked metadata?');">
<input type="hidden" name="csrf_token" value="<?= e($token) ?>"><input type="hidden" name="id" value="<?= (int)$student['id'] ?>">
<button class="link-danger" type="submit">Delete</button></form></td>
</tr>
<?php endforeach; ?>
<?php if (!$students): ?><tr><td colspan="6">No student records yet. Add your first student.</td></tr><?php endif; ?>
</tbody></table></div>
<p class="footnote">Educational local demo. Protect this admin page before deploying publicly.</p>
</main></body></html>
