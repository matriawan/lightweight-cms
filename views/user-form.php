<?php include __DIR__ . '/header.php'; ?>
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
