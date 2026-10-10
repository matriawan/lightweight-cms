<?php
requireLogin();
$user = currentUser();
$error = '';

if (isPostRequest()) {
    requireValidCsrf();
    $currentPassword = $_POST['current_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if (!password_verify($currentPassword, $user['password_hash'])) {
        $error = 'Current password is wrong.';
    } elseif ($newPassword !== $confirmPassword) {
        $error = 'New password and confirmation do not match.';
    } else {
        $error = validateNewPassword($newPassword, $user['password_hash']);
    }

    if ($error === '') {
        changeUserPassword((int) $user['id'], $newPassword);
        session_regenerate_id(true);
        setFlash('Password changed.');
        redirect('home');
    }
}

$pageTitle = 'Change Password';
