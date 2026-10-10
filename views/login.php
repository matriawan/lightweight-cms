<?php
if (currentUser()) {
    redirect('home');
}

$error = '';
$login = '';

if (isPostRequest()) {
    requireValidCsrf();
    $login = trim($_POST['login'] ?? '');
    $password = $_POST['password'] ?? '';
    $remember = !empty($_POST['remember']);

    $user = $login !== '' ? findUserForLogin($login) : null;
    // Check a dummy hash when the user is missing, so the response time is similar
    $hash = $user ? $user['password_hash'] : password_hash('dummy', PASSWORD_DEFAULT);
    $passwordOk = password_verify($password, $hash);

    if ($user && $passwordOk) {
        loginUser((int) $user['id'], $remember);
        redirect('home');
    }
    $error = 'Wrong username/email or password.';
}

$pageTitle = 'Login';
include __DIR__ . '/header.php';
?>
        <h1>Login</h1>
        <?php if ($error !== ''): ?>
            <p class="message error"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>
        <form method="post" action="index.php?page=login" class="form">
            <?= csrfField() ?>
            <label for="login">Username or email</label>
            <input type="text" id="login" name="login" value="<?= htmlspecialchars($login) ?>" required>
            <label for="password">Password</label>
            <input type="password" id="password" name="password" required>
            <label class="checkbox"><input type="checkbox" name="remember" value="1"> Remember me</label>
            <button type="submit">Login</button>
        </form>
<?php include __DIR__ . '/footer.php'; ?>
