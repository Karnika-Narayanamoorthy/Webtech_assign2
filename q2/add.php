<?php
declare(strict_types=1);
session_start();
require __DIR__ . '/db.php';
require __DIR__ . '/helpers.php';

$errors = [];
$name = $roll = $email = $course = '';
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
            $stmt = $pdo->prepare('INSERT INTO students (name, roll_number, email, course) VALUES (?, ?, ?, ?)');
            $stmt->execute([$name, $roll, $email, $course]);
            redirect('view.php?id=' . (int)$pdo->lastInsertId() . '&message=' . urlencode('Student added successfully.'));
        } catch (PDOException $exception) {
            $errors[] = $exception->getCode() === '23000'
                ? 'Roll number or email already exists.'
                : 'Could not save the student. Check the database settings.';
        }
    }
}
$token = csrf_token();
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Add Student</title><link rel="stylesheet" href="assets/style.css"></head>
<body><main class="wrap narrow"><a href="index.php">← Back to students</a><h1>Add student</h1>
<?php foreach ($errors as $error): ?><p class="error-box"><?= e($error) ?></p><?php endforeach; ?>
<form class="panel" method="post">
<input type="hidden" name="csrf_token" value="<?= e($token) ?>">
<label>Full name<input name="name" required maxlength="120" value="<?= e($name) ?>"></label>
<label>Roll number<input name="roll_number" required maxlength="40" value="<?= e($roll) ?>"></label>
<label>Email<input name="email" type="email" required maxlength="190" value="<?= e($email) ?>"></label>
<label>Course<input name="course" required maxlength="100" value="<?= e($course) ?>"></label>
<button type="submit">Save student</button></form></main></body></html>
