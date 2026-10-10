<?php include __DIR__ . '/header.php'; ?>
        <div class="home-bar">
            <nav class="category-menu">
                <a href="index.php" class="<?= $categoryId === 0 ? 'active' : '' ?>">All</a>
                <?php foreach ($categories as $category): ?>
                    <a href="index.php?category=<?= (int) $category['id'] ?>" class="<?= $categoryId === (int) $category['id'] ? 'active' : '' ?>"><?= htmlspecialchars($category['name']) ?></a>
                <?php endforeach; ?>
            </nav>
            <form method="get" action="index.php" class="search-form home-search">
                <input type="text" name="q" value="<?= htmlspecialchars($keyword) ?>" placeholder="Search posts" maxlength="100">
                <select name="category">
                    <option value="">All Categories</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?= (int) $category['id'] ?>" <?= $categoryId === (int) $category['id'] ? 'selected' : '' ?>><?= htmlspecialchars($category['name']) ?></option>
                    <?php endforeach; ?>
                </select>
                <button type="submit">Search</button>
                <?php if ($filterParams): ?>
                    <a href="index.php">Clear</a>
                <?php endif; ?>
            </form>
        </div>
        <?php if ($filterParams): ?>
            <p>Found <?= (int) $pagination['total_data'] ?> post(s).</p>
        <?php endif; ?>
        <?php if (!$posts): ?>
            <p>No posts found.</p>
        <?php endif; ?>
        <?php foreach ($posts as $post): ?>
            <article class="post-item">
                <h2><a href="index.php?page=post&amp;id=<?= (int) $post['id'] ?>"><?= htmlspecialchars($post['title']) ?></a></h2>
                <p class="post-meta">
                    <?php if ($post['category_name'] !== null): ?>
                        <a href="index.php?category=<?= (int) $post['category_id'] ?>"><?= htmlspecialchars($post['category_name']) ?></a> &middot;
                    <?php endif; ?>
                    <?= htmlspecialchars($post['author_name']) ?> &middot; <?= htmlspecialchars(date('j M Y', strtotime($post['shown_at']))) ?>
                </p>
                <p><?= htmlspecialchars(postSnippet($post)) ?></p>
            </article>
        <?php endforeach; ?>
        <?= renderPagination($pagination, $filterParams) ?>
<?php include __DIR__ . '/footer.php'; ?>
