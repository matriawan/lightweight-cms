<?php
requireRole('admin');
$user = findUserById((int) ($_GET['id'] ?? 0));
if (!$user) {
    setFlash('User not found.', 'error');
    redirect('users');
}

// Keep the list position (search keyword and page) for the Back link
$backParams = ['page' => 'users'];
if (isset($_GET['q']) && is_string($_GET['q']) && trim($_GET['q']) !== '') {
    $backParams['q'] = trim($_GET['q']);
}
if (isset($_GET['p'])) {
    $backParams['p'] = (int) $_GET['p'];
}

$pageTitle = 'User Detail';
