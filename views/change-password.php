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
include __DIR__ . '/header.php';
?>
        <h1>Change Password</h1>
        <?php if ($user['must_change_password']): ?>
            <p class="message">You must change your password before you continue.</p>
        <?php endif; ?>
        <?php if ($error !== ''): ?>
            <p class="message error"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>
        <form method="post" action="index.php?page=change-password" class="form">
            <?= csrfField() ?>
            <label for="current_password">Current password</label>
            <input type="password" id="current_password" name="current_password" required>
            <label for="new_password">New password (min. 6 characters)</label>
            <input type="password" id="new_password" name="new_password" required>
            <label for="confirm_password">Confirm new password</label>
            <input type="password" id="confirm_password" name="confirm_password" required>
            <button type="submit">Change Password</button>
        </form>
<?php include __DIR__ . '/footer.php'; ?>
