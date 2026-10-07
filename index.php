<?php
session_start();

// Load configuration
require_once __DIR__ . '/config/database.php';

// Simple router - currently just loads the home page
$page = isset($_GET['page']) ? $_GET['page'] : 'home';

// For now, just show a simple welcome message
echo '<!DOCTYPE html>';
echo '<html lang="en">';
echo '<head>';
echo '<meta charset="UTF-8">';
echo '<meta name="viewport" content="width=device-width, initial-scale=1.0">';
echo '<title>Lightweight CMS</title>';
echo '<style>';
echo 'body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif; margin: 0; padding: 20px; background: #f5f5f5; }';
echo '.container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }';
echo 'h1 { color: #333; }';
echo 'p { color: #666; line-height: 1.6; }';
echo '</style>';
echo '</head>';
echo '<body>';
echo '<div class="container">';
echo '<h1>Lightweight CMS</h1>';
echo '<p>Welcome to the Lightweight CMS application.</p>';
echo '<p>The application is running successfully!</p>';
echo '</div>';
echo '</body>';
echo '</html>';
