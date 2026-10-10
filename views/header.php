<?php
// Shared page top. Set $pageTitle before including.
// $layout is 'public' (home and login) or 'panel' (default: every page of the admin and author panel).
$navUser = currentUser();
$flash = getFlash();
$layout = $layout ?? 'panel';
if ($layout === 'panel' && !$navUser) {
    $layout = 'public';
}
$publicSingles = $layout === 'public' ? getPublishedSingles() : [];
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
        <?php include __DIR__ . ($layout === 'public' ? '/public-nav.php' : '/panel-nav.php'); ?>
        <?php if (!empty($isPublicPage)) { include __DIR__ . '/public-header.php'; } ?>
        <?php if ($flash): ?>
            <p class="message <?= $flash['type'] === 'error' ? 'error' : 'success' ?>"><?= htmlspecialchars($flash['message']) ?></p>
        <?php endif; ?>
