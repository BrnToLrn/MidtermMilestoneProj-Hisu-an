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
<section class="auth-layout">
    <div class="auth-intro">
        <p class="eyebrow">いらっしゃいませ · Welcome</p>
        <h1>Gather around the table.</h1>
        <p>Return to Sakura Kitchen and discover comforting Japanese dishes shared by home cooks.</p>
        <div class="auth-note"><span>✦</span><div><strong>One kitchen, many stories.</strong><small>Save favorites, share recipes, and cook something special today.</small></div></div>
    </div>
    <div class="form-card auth-card">
        <p class="eyebrow">Welcome back</p><h2>Log in</h2>
        <?php if ($error): ?><div class="notice error"><?= e($error) ?></div><?php endif; ?><?php $flash = get_flash(); if ($flash): ?><div class="notice <?= e($flash['type']) ?>"><?= e($flash['message']) ?></div><?php endif; ?>
        <form method="post"><input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>"><label for="email">Email address</label><input id="email" type="email" name="email" autocomplete="email" required value="<?= e($_POST['email'] ?? '') ?>"><label for="password">Password</label><input id="password" type="password" name="password" autocomplete="current-password" required><button class="button auth-submit" type="submit">Log in <span>→</span></button></form>
        <p class="auth-switch">New to Sakura Kitchen? <a href="register.php">Create an account</a></p>
    </div>
</section>
<?php page_end(); ?>
