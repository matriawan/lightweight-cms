<?php if ($bannerUrl !== '' || trim($headerText) !== ''): ?>
        <header class="site-header">
            <?php if ($bannerUrl !== ''): ?>
                <img src="<?= htmlspecialchars($bannerUrl) ?>" alt="<?= htmlspecialchars($siteTitle) ?>" class="site-banner">
            <?php endif; ?>
            <?php if (trim($headerText) !== ''): ?>
                <div class="site-header-text"><?= sanitizeHtml($headerText) ?></div>
            <?php endif; ?>
        </header>
<?php endif; ?>
