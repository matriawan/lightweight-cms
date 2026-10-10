<?php include __DIR__ . '/header.php'; ?>
        <h1>Settings</h1>
        <?php foreach ($errors as $error): ?>
            <p class="message error"><?= htmlspecialchars($error) ?></p>
        <?php endforeach; ?>
        <form method="post" action="index.php?page=settings" enctype="multipart/form-data" class="form">
            <?= csrfField() ?>
            <label for="title">Site Title</label>
            <input type="text" id="title" name="title" value="<?= htmlspecialchars($form['title']) ?>" maxlength="100" required>

            <label for="description">Site Description</label>
            <textarea id="description" name="description" rows="3" maxlength="300"><?= htmlspecialchars($form['description']) ?></textarea>

            <label for="per_page">Items Per Page (1-50)</label>
            <input type="number" id="per_page" name="per_page" value="<?= htmlspecialchars($form['per_page']) ?>" min="1" max="50" required>

            <label for="header_text">Header Text (Public Pages)</label>
            <textarea id="header_text" name="header_text" rows="3" maxlength="1000"><?= htmlspecialchars($form['header_text']) ?></textarea>
            <small>Safe HTML is allowed. Max. 1000 characters.</small>

            <label for="banner">Header Banner (PNG, Max. 1 MB)</label>
            <?php if ($currentImages['banner'] !== ''): ?>
                <img src="<?= htmlspecialchars($currentImages['banner']) ?>" alt="Current Header Banner" class="current-image">
            <?php endif; ?>
            <input type="file" id="banner" name="banner" accept="<?= htmlspecialchars(imageAcceptAttribute($images['banner']['types'])) ?>">
            <small>A new file replaces the current one.</small>

            <label for="favicon">Favicon (ICO or PNG, Max. 1 MB)</label>
            <?php if ($currentImages['favicon'] !== ''): ?>
                <img src="<?= htmlspecialchars($currentImages['favicon']) ?>" alt="Current favicon" class="current-image current-favicon">
            <?php endif; ?>
            <input type="file" id="favicon" name="favicon" accept="<?= htmlspecialchars(imageAcceptAttribute($images['favicon']['types'])) ?>">
            <small>A new file replaces the current one.</small>

            <label for="footer_text">Footer Text (Public Pages)</label>
            <textarea id="footer_text" name="footer_text" rows="3" maxlength="2000"><?= htmlspecialchars($form['footer_text']) ?></textarea>
            <small>Safe HTML is allowed. Max. 2000 characters.</small>

            <button type="submit">Save</button>
        </form>
<?php include __DIR__ . '/footer.php'; ?>
