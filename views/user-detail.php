<?php
requireRole('admin');
$user = findUserById((int) ($_GET['id'] ?? 0));
if (!$user) {
    setFlash('User not found.', 'error');
    redirect('users');
}

// Keep the list position (search keyword and page) for the Back link
$backParams = ['page' => 'users'];
if (isset($_GET['q']) && is_string($_GET['q']) && trim($_GET['q']) !== '') {
    $backParams['q'] = trim($_GET['q']);
}
if (isset($_GET['p'])) {
    $backParams['p'] = (int) $_GET['p'];
}

$pageTitle = 'User Detail';
include __DIR__ . '/header.php';
?>
        <h1>User Detail</h1>
        <table class="detail">
            <tr><th>Username</th><td><?= htmlspecialchars($user['username']) ?></td></tr>
            <tr><th>Name</th><td><?= htmlspecialchars($user['display_name']) ?></td></tr>
            <tr><th>Email</th><td><?= htmlspecialchars($user['email']) ?></td></tr>
            <tr><th>Role</th><td><?= htmlspecialchars(ucfirst($user['role'])) ?></td></tr>
            <tr>
                <th>Password</th>
                <td class="<?= $user['must_change_password'] ? 'role-default' : 'role-changed' ?>">
                    <?= $user['must_change_password'] ? 'Not changed yet (still default)' : 'Already changed' ?>
                </td>
            </tr>
            <tr><th>Bio</th><td class="wrap bio"><?= $user['bio'] === null || $user['bio'] === '' ? '-' : sanitizeHtml($user['bio']) ?></td></tr>
            <tr><th>Created</th><td><?= htmlspecialchars($user['created_at']) ?></td></tr>
            <tr><th>Updated</th><td><?= htmlspecialchars($user['updated_at']) ?></td></tr>
        </table>
        <p><a href="index.php?<?= htmlspecialchars(http_build_query($backParams)) ?>">Back to Users</a></p>
<?php include __DIR__ . '/footer.php'; ?>
