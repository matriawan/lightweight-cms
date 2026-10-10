<?php include __DIR__ . '/header.php'; ?>
        <h1>Media Detail</h1>
        <div class="media-preview">
            <?php if (!$fileExists): ?>
                <p class="message error">The file is missing from the media folder.</p>
            <?php elseif ($group === 'image'): ?>
                <img src="<?= htmlspecialchars($fileUrl) ?>" alt="<?= htmlspecialchars((string) ($media['alt_text'] ?? '')) ?>">
            <?php elseif ($group === 'video'): ?>
                <video controls preload="metadata" src="<?= htmlspecialchars($fileUrl) ?>"></video>
            <?php else: ?>
                <p><a href="<?= htmlspecialchars($fileUrl) ?>">Open file</a></p>
            <?php endif; ?>
        </div>
        <table class="detail">
            <tr><th>Display Name</th><td class="wrap"><?= htmlspecialchars($media['file_name']) ?></td></tr>
            <tr><th>Stored File</th><td><?= htmlspecialchars(basename($media['file_path'])) ?></td></tr>
            <tr><th>Type</th><td><?= htmlspecialchars($media['mime_type']) ?></td></tr>
            <tr><th>Size</th><td><?= htmlspecialchars(formatFileSize((int) $media['file_size'])) ?></td></tr>
            <tr><th>Alt Text</th><td class="wrap"><?= $media['alt_text'] === null || $media['alt_text'] === '' ? '-' : htmlspecialchars($media['alt_text']) ?></td></tr>
            <tr><th>Uploader</th><td><?= htmlspecialchars($media['display_name']) ?> (<?= htmlspecialchars($media['username']) ?>)</td></tr>
            <tr><th>Uploaded</th><td><?= htmlspecialchars($media['created_at']) ?></td></tr>
        </table>
        <label for="file_url" class="url-label">Link (use it in posts and single pages)</label>
        <input type="text" id="file_url" class="url-field" value="<?= htmlspecialchars($fullUrl) ?>" readonly onclick="this.select()">
        <p>
            <a href="index.php?page=media-edit&amp;id=<?= (int) $media['id'] ?>" class="link-edit">Edit</a>
            <a href="index.php?<?= htmlspecialchars(http_build_query($backParams)) ?>">Back to Media</a>
        </p>
<?php include __DIR__ . '/footer.php'; ?>
