<?php
session_start();

// Load configuration
require_once __DIR__ . '/config/database.php';

// Simple router - currently just loads the home page
$page = isset($_GET['page']) ? $_GET['page'] : 'home';

// Load view based on page
$viewFile = __DIR__ . '/views/' . $page . '.php';
if (file_exists($viewFile)) {
    include $viewFile;
} else {
    include __DIR__ . '/views/home.php';
}
