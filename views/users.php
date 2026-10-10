<?php
requireRole('admin');
$page_number = isset($_GET['p']) ? (int) $_GET['p'] : 1;
$keyword = isset($_GET['q']) && is_string($_GET['q']) ? trim($_GET['q']) : '';
// Keep the search keyword in links and forms
$searchParams = $keyword !== '' ? ['q' => $keyword] : [];

if (isPostRequest()) {
    requireValidCsrf();
    $action = $_POST['action'] ?? '';
    $target = findUserById((int) ($_POST['id'] ?? 0));

    if (!$target) {
        setFlash('User not found.', 'error');
    } elseif ($target['role'] === 'admin') {
        // The admin account can only be changed manually in the database
        setFlash('The admin account cannot be edited, reset, or deleted here.', 'error');
    } elseif ($action === 'reset') {
        resetUserPassword((int) $target['id']);
        setFlash('Password of ' . $target['username'] . ' is reset to the default password.');
    } elseif ($action === 'delete') {
        try {
            deleteUser((int) $target['id']);
            setFlash('User ' . $target['username'] . ' is deleted.');
        } catch (PDOException $e) {
            // Foreign key: the user still owns posts, pages, or media
            setFlash('User ' . $target['username'] . ' still owns content and cannot be deleted.', 'error');
        }
    }
    redirect('users', $searchParams + ['p' => $page_number]);
}

$pagination = getPagination(countUsers($keyword), $page_number, getPerPage());
$users = getUsers($pagination['per_page'], $pagination['offset'], $keyword);

$pageTitle = 'Users';
include __DIR__ . '/header.php';
?>
        <h1>Users</h1>
        <p><a href="index.php?page=user-form" class="link-add">Add User</a></p>
        <form method="get" action="index.php" class="search-form">
            <input type="hidden" name="page" value="users">
            <input type="text" name="q" value="<?= htmlspecialchars($keyword) ?>" placeholder="Search name or email" maxlength="100">
            <button type="submit">Search</button>
            <?php if ($keyword !== ''): ?>
                <a href="index.php?page=users">Clear</a>
            <?php endif; ?>
        </form>
        <?php if ($keyword !== ''): ?>
            <p>Found <?= (int) $pagination['total_data'] ?> user(s) for "<?= htmlspecialchars($keyword) ?>".</p>
        <?php endif; ?>
        <div class="table-wrap">
        <table>
            <tr><th>Username</th><th>Name</th><th>Email</th><th>Role</th><th>Actions</th></tr>
            <?php if (!$users): ?>
                <tr><td colspan="5">No users found.</td></tr>
            <?php endif; ?>
            <?php foreach ($users as $user): ?>
                <tr>
                    <td><?= htmlspecialchars($user['username']) ?></td>
                    <td><?= htmlspecialchars($user['display_name']) ?></td>
                    <td><?= htmlspecialchars($user['email']) ?></td>
                    <td class="<?= $user['must_change_password'] ? 'role-default' : 'role-changed' ?>" title="<?= $user['must_change_password'] ? 'Password not changed yet' : 'Password changed' ?>"><?= htmlspecialchars(ucfirst($user['role'])) ?></td>
                    <td class="actions">
                        <a href="index.php?<?= htmlspecialchars(http_build_query($searchParams + ['page' => 'user-detail', 'id' => $user['id'], 'p' => $pagination['current_page']])) ?>" class="link-detail">Detail</a>
                        <?php if ($user['role'] === 'admin'): ?>
                            <span class="text-disabled">Protected</span>
                        <?php else: ?>
                            <a href="index.php?page=user-form&amp;id=<?= (int) $user['id'] ?>" class="link-edit">Edit</a>
                            <form method="post" action="index.php?<?= htmlspecialchars(http_build_query($searchParams + ['page' => 'users', 'p' => $pagination['current_page']])) ?>" class="inline-form">
                                <?= csrfField() ?>
                                <input type="hidden" name="id" value="<?= (int) $user['id'] ?>">
                                <button type="submit" name="action" value="reset" class="link-button link-reset">Reset</button>
                                <button type="submit" name="action" value="delete" class="link-button link-delete">Delete</button>
                            </form>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
        </div>
        <p class="legend">
            <span class="role-default">Red</span>: password not changed yet (still default).
            <span class="role-changed">Green</span>: password already changed.
        </p>
        <?= renderPagination($pagination, $searchParams + ['page' => 'users']) ?>
<?php include __DIR__ . '/footer.php'; ?>
