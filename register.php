<?php
require_once __DIR__ . '/functions.php';
if (current_user()) redirect('index.php');
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $name = trim($_POST['name'] ?? ''); $email = strtolower(trim($_POST['email'] ?? '')); $password = $_POST['password'] ?? '';
    if (strlen($name) < 2 || strlen($name) > 80) $errors[] = 'Name must be 2 to 80 characters.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Enter a valid email address.';
    if (strlen($password) < 8) $errors[] = 'Password must be at least 8 characters.';
    if (!$errors) {
        $check = $pdo->prepare('SELECT id FROM users WHERE email = :email'); $check->execute(['email' => $email]);
        if ($check->fetch()) $errors[] = 'That email is already registered.';
        else { $create = $pdo->prepare('INSERT INTO users (name, email, password_hash) VALUES (:name, :email, :hash)'); $create->execute(['name'=>$name,'email'=>$email,'hash'=>password_hash($password, PASSWORD_DEFAULT)]); flash('success','Account created. Welcome to Sakura Kitchen!'); redirect('login.php'); }
    }
}
page_start('Create account');
?>
<div class="form-card"><p class="eyebrow">Join the community</p><h1>Create an account</h1><?php if ($errors): ?><div class="notice error"><?php foreach ($errors as $error): ?><div><?= e($error) ?></div><?php endforeach; ?></div><?php endif; ?>
<form method="post" novalidate><input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>"><label for="name">Your name</label><input id="name" name="name" required minlength="2" maxlength="80" value="<?= e($_POST['name'] ?? '') ?>"><label for="email">Email</label><input id="email" type="email" name="email" required value="<?= e($_POST['email'] ?? '') ?>"><label for="password">Password</label><input id="password" type="password" name="password" required minlength="8"><div class="form-actions"><button class="button" type="submit">Create account</button><a class="button button-secondary" href="login.php">I already have one</a></div></form></div>
<?php page_end(); ?>
