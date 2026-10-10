<?php include __DIR__ . '/header.php'; ?>
        <h1>Media</h1>
        <p><a href="index.php?page=media-upload" class="link-add">Upload Media</a></p>
        <form method="get" action="index.php" class="search-form">
            <input type="hidden" name="page" value="media">
            <input type="text" name="q" value="<?= htmlspecialchars($keyword) ?>" placeholder="Search name" maxlength="100">
            <select name="type">
                <option value="">All Types</option>
                <?php foreach (mediaGroupLabels() as $group => $label): ?>
                    <option value="<?= $group ?>" <?= $type === $group ? 'selected' : '' ?>><?= $label ?></option>
                <?php endforeach; ?>
            </select>
            <?php if ($isAdmin): ?>
                <select name="uploader">
                    <option value="">All Uploaders</option>
                    <?php foreach ($uploaders as $person): ?>
                        <option value="<?= (int) $person['id'] ?>" <?= $uploader === (int) $person['id'] ? 'selected' : '' ?>><?= htmlspecialchars($person['username']) ?></option>
                    <?php endforeach; ?>
                </select>
            <?php endif; ?>
            <button type="submit">Filter</button>
            <?php if ($filterParams): ?>
                <a href="index.php?page=media">Clear</a>
            <?php endif; ?>
        </form>
        <?php if ($filterParams): ?>
            <p>Found <?= (int) $pagination['total_data'] ?> file(s).</p>
        <?php endif; ?>
        <div class="table-wrap">
        <table>
            <tr>
                <th>Preview</th><th>Name</th><th>Size</th><th>Uploaded</th>
                <?php if ($isAdmin): ?><th>Uploader</th><?php endif; ?>
                <th>Actions</th>
            </tr>
            <?php if (!$items): ?>
                <tr><td colspan="<?= $isAdmin ? 6 : 5 ?>">No media found.</td></tr>
            <?php endif; ?>
            <?php foreach ($items as $item): ?>
                <?php $itemGroup = mediaGroupOfMime($item['mime_type']); ?>
                <tr>
                    <td>
                        <?php if ($itemGroup === 'image'): ?>
                            <img src="<?= htmlspecialchars(mediaRelativeUrl($item['file_path'])) ?>" alt="<?= htmlspecialchars((string) ($item['alt_text'] ?? '')) ?>" class="media-thumb" loading="lazy">
                        <?php else: ?>
                            <span class="media-type"><?= htmlspecialchars(strtoupper(pathinfo($item['file_path'], PATHINFO_EXTENSION))) ?></span>
                        <?php endif; ?>
                    </td>
                    <td class="media-name"><?= htmlspecialchars($item['file_name']) ?></td>
                    <td><?= htmlspecialchars(formatFileSize((int) $item['file_size'])) ?></td>
                    <td><?= htmlspecialchars($item['created_at']) ?></td>
                    <?php if ($isAdmin): ?><td><?= htmlspecialchars($item['username']) ?></td><?php endif; ?>
                    <td class="actions">
                        <a href="index.php?<?= htmlspecialchars(http_build_query($filterParams + ['page' => 'media-detail', 'id' => $item['id'], 'p' => $pagination['current_page']])) ?>" class="link-detail">Detail</a>
                        <a href="index.php?page=media-edit&amp;id=<?= (int) $item['id'] ?>" class="link-edit">Edit</a>
                        <form method="post" action="index.php?<?= htmlspecialchars(http_build_query($filterParams + ['page' => 'media', 'p' => $pagination['current_page']])) ?>" class="inline-form">
                            <?= csrfField() ?>
                            <input type="hidden" name="id" value="<?= (int) $item['id'] ?>">
                            <button type="submit" class="link-button link-delete">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
        </div>
        <p class="legend">Deleting a file also removes it from the media folder. Links to it that are already used in posts or single pages will stop working.</p>
        <?= renderPagination($pagination, $filterParams + ['page' => 'media']) ?>
<?php include __DIR__ . '/footer.php'; ?>
