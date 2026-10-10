<?php include __DIR__ . '/header.php'; ?>
        <h1>Edit Media</h1>
        <?php foreach ($errors as $error): ?>
            <p class="message error"><?= htmlspecialchars($error) ?></p>
        <?php endforeach; ?>
        <form method="post" action="index.php?page=media-edit&amp;id=<?= (int) $id ?>" class="form">
            <?= csrfField() ?>
            <label for="file_name">Display Name</label>
            <input type="text" id="file_name" name="file_name" value="<?= htmlspecialchars($form['file_name']) ?>" maxlength="255" required>
            <label for="alt_text">Alt Text (optional)</label>
            <input type="text" id="alt_text" name="alt_text" value="<?= htmlspecialchars($form['alt_text']) ?>" maxlength="255">
            <small>Only the display name and the alt text can be changed. The file and its link stay the same.</small>
            <button type="submit">Save</button>
            <a href="index.php?page=media-detail&amp;id=<?= (int) $id ?>">Cancel</a>
        </form>
<?php include __DIR__ . '/footer.php'; ?>
