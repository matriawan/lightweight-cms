<?php
// Load configuration
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/models/setting.php';
require_once __DIR__ . '/models/user.php';
require_once __DIR__ . '/utils/auth.php';
require_once __DIR__ . '/utils/pagination.php';

startSecureSession();

$siteTitle = getSetting('title', 'Lightweight CMS');
$siteDescription = getSetting('description');

// Only pages in this list can be loaded
$allowedPages = ['home', 'login', 'logout', 'change-password', 'users', 'user-form', 'user-detail'];

$page = isset($_GET['page']) ? $_GET['page'] : 'home';
if (!in_array($page, $allowedPages, true)) {
    $page = 'home';
}

// A user with a default password can only change the password or log out
$loggedInUser = currentUser();
if ($loggedInUser && $loggedInUser['must_change_password'] && !in_array($page, ['change-password', 'logout'], true)) {
    redirect('change-password');
}

include __DIR__ . '/views/' . $page . '.php';
