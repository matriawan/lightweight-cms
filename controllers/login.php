<?php
// The login page uses the public navigation only
$layout = 'public';

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
