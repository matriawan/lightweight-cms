<?php include __DIR__ . '/header.php'; ?>
        <h1>Upload Media</h1>
        <?php foreach ($errors as $error): ?>
            <p class="message error"><?= htmlspecialchars($error) ?></p>
        <?php endforeach; ?>
        <form method="post" action="index.php?page=media-upload" enctype="multipart/form-data" class="form">
            <?= csrfField() ?>
            <label for="file">File</label>
            <input type="file" id="file" name="file" accept="<?= htmlspecialchars(mediaAcceptAttribute()) ?>" required>
            <small>Max. 2 MB. Images: jpg, png, gif, webp. Videos: mp4, webm. Documents: pdf, txt, doc, docx, xls, xlsx, ppt, pptx.</small>
            <small>The file is saved under its upload date and time. Your file name is kept as the display name.</small>
            <button type="submit">Upload</button>
            <a href="index.php?page=media">Cancel</a>
        </form>
<?php include __DIR__ . '/footer.php'; ?>
