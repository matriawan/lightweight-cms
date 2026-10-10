        <nav class="nav public-nav">
            <div class="public-menu">
                <?php foreach ($publicSingles as $single): ?>
                    <a href="index.php?page=single&amp;id=<?= (int) $single['id'] ?>"><?= htmlspecialchars($single['title']) ?></a>
                <?php endforeach; ?>
            </div>
            <div class="public-user">
                <?php if ($navUser): ?>
                    <a href="index.php?page=dashboard"><?= htmlspecialchars($navUser['display_name']) ?></a>
                <?php else: ?>
                    <a href="index.php?page=login">Login</a>
                <?php endif; ?>
            </div>
        </nav>
