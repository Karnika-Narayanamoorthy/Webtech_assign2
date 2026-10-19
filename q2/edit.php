<?php
declare(strict_types=1);
session_start();
require __DIR__ . '/db.php';
require __DIR__ . '/helpers.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT) ?: filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
if (!$id) { http_response_code(400); exit('Invalid student ID.'); }
$stmt = $pdo->prepare('SELECT * FROM students WHERE id = ?');
$stmt->execute([$id]);
$student = $stmt->fetch();
if (!$student) { http_response_code(404); exit('Student not found.'); }

$errors = [];
$name = $student['name']; $roll = $student['roll_number']; $email = $student['email']; $course = $student['course'];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $name = trim((string)($_POST['name'] ?? ''));
    $roll = trim((string)($_POST['roll_number'] ?? ''));
    $email = trim((string)($_POST['email'] ?? ''));
    $course = trim((string)($_POST['course'] ?? ''));
    if (mb_strlen($name) < 2 || mb_strlen($name) > 120) $errors[] = 'Name must contain 2–120 characters.';
    if ($roll === '' || strlen($roll) > 40) $errors[] = 'Enter a roll number (maximum 40 characters).';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 190) $errors[] = 'Enter a valid email address.';
    if ($course === '' || mb_strlen($course) > 100) $errors[] = 'Enter a course (maximum 100 characters).';
    if (!$errors) {
        try {
            $update = $pdo->prepare('UPDATE students SET name=?, roll_number=?, email=?, course=? WHERE id=?');
            $update->execute([$name, $roll, $email, $course, $id]);
            redirect('view.php?id=' . (int)$id . '&message=' . urlencode('Student updated successfully.'));
        } catch (PDOException $exception) {
            $errors[] = $exception->getCode() === '23000' ? 'Roll number or email is already used by another student.' : 'Update failed.';
        }
    }
}
$token = csrf_token();
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Edit Student</title><link rel="stylesheet" href="assets/style.css"></head>
<body><main class="wrap narrow"><a href="view.php?id=<?= (int)$id ?>">← Back to profile</a><h1>Edit student</h1>
<?php foreach ($errors as $error): ?><p class="error-box"><?= e($error) ?></p><?php endforeach; ?>
<form class="panel" method="post"><input type="hidden" name="csrf_token" value="<?= e($token) ?>"><input type="hidden" name="id" value="<?= (int)$id ?>">
<label>Full name<input name="name" required maxlength="120" value="<?= e($name) ?>"></label>
<label>Roll number<input name="roll_number" required maxlength="40" value="<?= e($roll) ?>"></label>
<label>Email<input name="email" type="email" required maxlength="190" value="<?= e($email) ?>"></label>
<label>Course<input name="course" required maxlength="100" value="<?= e($course) ?>"></label>
<button type="submit">Update student</button></form></main></body></html>
