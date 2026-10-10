<?php
requireRole('admin');
$id = (int) ($_GET['id'] ?? 0);
$editing = null;

if ($id > 0) {
    $editing = findUserById($id);
    if (!$editing) {
        setFlash('User not found.', 'error');
        redirect('users');
    }
    // The admin account can only be changed manually in the database
    if ($editing['role'] === 'admin') {
        setFlash('The admin account cannot be edited here.', 'error');
        redirect('users');
    }
}

$errors = [];
$form = [
    'username' => $editing['username'] ?? '',
    'email' => $editing['email'] ?? '',
    'display_name' => $editing['display_name'] ?? '',
    'bio' => $editing['bio'] ?? '',
];

if (isPostRequest()) {
    requireValidCsrf();
    foreach (['username', 'email', 'display_name', 'bio'] as $field) {
        // Username cannot change when editing
        if ($field === 'username' && $editing) {
            continue;
        }
        $form[$field] = trim((string) ($_POST[$field] ?? ''));
    }

    if (!$editing) {
        if (!preg_match('/^[A-Za-z0-9_.-]{3,50}$/', $form['username'])) {
            $errors[] = 'Username must be 3-50 characters: letters, numbers, dot, dash, underscore.';
        } elseif (usernameExists($form['username'])) {
            $errors[] = 'Username is already used.';
        }
    }
    if (!filter_var($form['email'], FILTER_VALIDATE_EMAIL) || strlen($form['email']) > 100) {
        $errors[] = 'Email is not valid.';
    } elseif (emailExists($form['email'], $id)) {
        $errors[] = 'Email is already used.';
    }
    if ($form['display_name'] === '' || mb_strlen($form['display_name']) > 100) {
        $errors[] = 'Display name is required (max. 100 characters).';
    }
    if (mb_strlen($form['bio']) > 2000) {
        $errors[] = 'Bio must be at most 2000 characters.';
    }

    if (!$errors) {
        $bio = $form['bio'] === '' ? null : $form['bio'];
        if ($editing) {
            updateUser($id, $form['email'], $form['display_name'], $bio);
            setFlash('User ' . $editing['username'] . ' is updated.');
        } else {
            createUser($form['username'], $form['email'], $form['display_name'], $bio);
            setFlash('User ' . $form['username'] . ' is created with the default password.');
        }
        redirect('users');
    }
}

$pageTitle = $editing ? 'Edit User' : 'Add User';
