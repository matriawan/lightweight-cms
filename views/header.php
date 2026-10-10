<?php
// Shared page top. Set $pageTitle before including.
$navUser = currentUser();
$flash = getFlash();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?> - <?= htmlspecialchars($siteTitle) ?></title>
    <meta name="description" content="<?= htmlspecialchars($siteDescription) ?>">
    <?php if ($faviconUrl !== ''): ?>
        <link rel="icon" type="<?= faviconMimeType($faviconUrl) ?>" href="<?= htmlspecialchars($faviconUrl) ?>">
    <?php endif; ?>
    <link rel="stylesheet" href="public/css/style.css">
</head>
<body>
    <div class="container">
        <?php if (!empty($isPublicPage)) { include __DIR__ . '/public-header.php'; } ?>
        <nav class="nav">
            <a href="index.php">Home</a>
            <?php if ($navUser): ?>
                <?php if ($navUser['role'] === 'admin' && !$navUser['must_change_password']): ?>
                    <a href="index.php?page=users">Users</a>
                    <a href="index.php?page=settings">Settings</a>
                <?php endif; ?>
                <a href="index.php?page=change-password">Change Password</a>
                <form method="post" action="index.php?page=logout" class="inline-form">
                    <?= csrfField() ?>
                    <button type="submit" class="logout-button">Logout (<?= htmlspecialchars($navUser['username']) ?>)</button>
                </form>
            <?php else: ?>
                <a href="index.php?page=login">Login</a>
            <?php endif; ?>
        </nav>
        <?php if ($flash): ?>
            <p class="message <?= $flash['type'] === 'error' ? 'error' : 'success' ?>"><?= htmlspecialchars($flash['message']) ?></p>
        <?php endif; ?>
