<?php
requireRole('admin');
$id = (int) ($_GET['id'] ?? 0);
$editing = null;

if ($id > 0) {
    $editing = findUserById($id);
    if (!$editing) {
        setFlash('User not found.', 'error');
        redirect('users');
    }
    // The admin account can only be changed manually in the database
    if ($editing['role'] === 'admin') {
        setFlash('The admin account cannot be edited here.', 'error');
        redirect('users');
    }
}

$errors = [];
$form = [
    'username' => $editing['username'] ?? '',
    'email' => $editing['email'] ?? '',
    'display_name' => $editing['display_name'] ?? '',
    'bio' => $editing['bio'] ?? '',
];

if (isPostRequest()) {
    requireValidCsrf();
    foreach (['username', 'email', 'display_name', 'bio'] as $field) {
        // Username cannot change when editing
        if ($field === 'username' && $editing) {
            continue;
        }
        $form[$field] = trim((string) ($_POST[$field] ?? ''));
    }

    if (!$editing) {
        if (!preg_match('/^[A-Za-z0-9_.-]{3,50}$/', $form['username'])) {
            $errors[] = 'Username must be 3-50 characters: letters, numbers, dot, dash, underscore.';
        } elseif (usernameExists($form['username'])) {
            $errors[] = 'Username is already used.';
        }
    }
    if (!filter_var($form['email'], FILTER_VALIDATE_EMAIL) || strlen($form['email']) > 100) {
        $errors[] = 'Email is not valid.';
    } elseif (emailExists($form['email'], $id)) {
        $errors[] = 'Email is already used.';
    }
    if ($form['display_name'] === '' || mb_strlen($form['display_name']) > 100) {
        $errors[] = 'Display name is required (max. 100 characters).';
    }
    if (mb_strlen($form['bio']) > 2000) {
        $errors[] = 'Bio must be at most 2000 characters.';
    }

    if (!$errors) {
        $bio = $form['bio'] === '' ? null : $form['bio'];
        if ($editing) {
            updateUser($id, $form['email'], $form['display_name'], $bio);
            setFlash('User ' . $editing['username'] . ' is updated.');
        } else {
            createUser($form['username'], $form['email'], $form['display_name'], $bio);
            setFlash('User ' . $form['username'] . ' is created with the default password.');
        }
        redirect('users');
    }
}

$pageTitle = $editing ? 'Edit User' : 'Add User';
include __DIR__ . '/header.php';
?>
        <h1><?= htmlspecialchars($pageTitle) ?></h1>
        <?php foreach ($errors as $error): ?>
            <p class="message error"><?= htmlspecialchars($error) ?></p>
        <?php endforeach; ?>
        <form method="post" action="index.php?page=user-form<?= $editing ? '&amp;id=' . $id : '' ?>" class="form">
            <?= csrfField() ?>
            <label for="username">Username</label>
            <input type="text" id="username" name="username" value="<?= htmlspecialchars($form['username']) ?>" maxlength="50" <?= $editing ? 'disabled' : 'required' ?>>
            <label for="email">Email</label>
            <input type="email" id="email" name="email" value="<?= htmlspecialchars($form['email']) ?>" maxlength="100" required>
            <label for="display_name">Display name</label>
            <input type="text" id="display_name" name="display_name" value="<?= htmlspecialchars($form['display_name']) ?>" maxlength="100" required>
            <label for="bio">Bio (optional)</label>
            <textarea id="bio" name="bio" rows="4"><?= htmlspecialchars($form['bio']) ?></textarea>
            <small>HTML is allowed (p, br, b, strong, i, em, u, s, ul, ol, li, a, h3, h4, blockquote, code, pre, hr). Other tags and all attributes are removed when the bio is shown.</small>
            <?php if (!$editing): ?>
                <p>New users are editors. The initial password is <strong><?= htmlspecialchars(DEFAULT_PASSWORD) ?></strong>. The user must change it at first login.</p>
            <?php endif; ?>
            <button type="submit">Save</button>
            <a href="index.php?page=users">Cancel</a>
        </form>
<?php include __DIR__ . '/footer.php'; ?>
