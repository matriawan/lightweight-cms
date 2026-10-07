<?php
session_start();

// Load configuration
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/models/setting.php';
require_once __DIR__ . '/utils/pagination.php';

$siteTitle = getSetting('title', 'Lightweight CMS');
$siteDescription = getSetting('description');

// Only pages in this list can be loaded
$allowedPages = ['home'];

$page = isset($_GET['page']) ? $_GET['page'] : 'home';
if (!in_array($page, $allowedPages, true)) {
    $page = 'home';
}

include __DIR__ . '/views/' . $page . '.php';
