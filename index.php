<?php
// Load configuration
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/models/setting.php';
require_once __DIR__ . '/models/user.php';
require_once __DIR__ . '/models/media.php';
require_once __DIR__ . '/models/single.php';
require_once __DIR__ . '/models/category.php';
require_once __DIR__ . '/models/post.php';
require_once __DIR__ . '/utils/auth.php';
require_once __DIR__ . '/utils/pagination.php';
require_once __DIR__ . '/utils/html.php';
require_once __DIR__ . '/utils/image.php';
require_once __DIR__ . '/utils/media.php';

startSecureSession();

$siteTitle = getSetting('title', 'Lightweight CMS');
$siteDescription = getSetting('description');
$faviconUrl = settingImageUrl('favicon');

// Only pages in this list can be loaded
$allowedPages = ['home', 'login', 'logout', 'change-password', 'users', 'user-form', 'user-detail', 'settings', 'media', 'media-upload', 'media-detail', 'media-edit', 'dashboard', 'posts', 'categories', 'singles'];

$page = isset($_GET['page']) ? $_GET['page'] : 'home';
if (!in_array($page, $allowedPages, true)) {
    $page = 'home';
}

// A user with a default password can only change the password or log out
$loggedInUser = currentUser();
if ($loggedInUser && $loggedInUser['must_change_password'] && !in_array($page, ['change-password', 'logout'], true)) {
    redirect('change-password');
}

// The controller handles the request (access, POST, redirects) and prepares data.
// The view only shows it. A page may have only one of them (home has no controller).
$controllerFile = __DIR__ . '/controllers/' . $page . '.php';
$viewFile = __DIR__ . '/views/' . $page . '.php';
if (is_file($controllerFile)) {
    require $controllerFile;
}
if (is_file($viewFile)) {
    include $viewFile;
}
