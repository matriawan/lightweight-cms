<?php
session_start();

// Load configuration
require_once __DIR__ . '/config/database.php';

// Only pages in this list can be loaded
$allowedPages = ['home'];

$page = isset($_GET['page']) ? $_GET['page'] : 'home';
if (!in_array($page, $allowedPages, true)) {
    $page = 'home';
}

include __DIR__ . '/views/' . $page . '.php';
