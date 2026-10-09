<?php
require_once __DIR__ . '/functions.php';
if (current_user()) redirect('index.php');
$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf(); $email = strtolower(trim($_POST['email'] ?? '')); $password = $_POST['password'] ?? '';
    $statement = $pdo->prepare('SELECT id, name, email, password_hash FROM users WHERE email = :email'); $statement->execute(['email'=>$email]); $user = $statement->fetch();
    if (!$user || !password_verify($password, $user['password_hash'])) $error = 'Email or password is incorrect.';
    else { session_regenerate_id(true); unset($user['password_hash']); $_SESSION['user'] = $user; redirect('index.php'); }
}
page_start('Log in');
?>
<div class="form-card"><p class="eyebrow">Welcome back</p><h1>Log in</h1><?php if ($error): ?><div class="notice error"><?= e($error) ?></div><?php endif; ?><?php $flash = get_flash(); if ($flash): ?><div class="notice <?= e($flash['type']) ?>"><?= e($flash['message']) ?></div><?php endif; ?>
<form method="post"><input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>"><label for="email">Email</label><input id="email" type="email" name="email" required value="<?= e($_POST['email'] ?? '') ?>"><label for="password">Password</label><input id="password" type="password" name="password" required><div class="form-actions"><button class="button" type="submit">Log in</button><a class="button button-secondary" href="register.php">Create account</a></div></form></div>
<?php page_end(); ?>
