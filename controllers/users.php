<?php
requireRole('admin');
$page_number = isset($_GET['p']) ? (int) $_GET['p'] : 1;
$keyword = isset($_GET['q']) && is_string($_GET['q']) ? trim($_GET['q']) : '';
// Keep the search keyword in links and forms
$searchParams = $keyword !== '' ? ['q' => $keyword] : [];

if (isPostRequest()) {
    requireValidCsrf();
    $action = $_POST['action'] ?? '';
    $target = findUserById((int) ($_POST['id'] ?? 0));

    if (!$target) {
        setFlash('User not found.', 'error');
    } elseif ($target['role'] === 'admin') {
        // The admin account can only be changed manually in the database
        setFlash('The admin account cannot be edited, reset, or deleted here.', 'error');
    } elseif ($action === 'reset') {
        resetUserPassword((int) $target['id']);
        setFlash('Password of ' . $target['username'] . ' is reset to the default password.');
    } elseif ($action === 'delete') {
        try {
            deleteUser((int) $target['id']);
            setFlash('User ' . $target['username'] . ' is deleted.');
        } catch (PDOException $e) {
            // Foreign key: the user still owns posts, pages, or media
            setFlash('User ' . $target['username'] . ' still owns content and cannot be deleted.', 'error');
        }
    }
    redirect('users', $searchParams + ['p' => $page_number]);
}

$pagination = getPagination(countUsers($keyword), $page_number, getPerPage());
$users = getUsers($pagination['per_page'], $pagination['offset'], $keyword);

$pageTitle = 'Users';
