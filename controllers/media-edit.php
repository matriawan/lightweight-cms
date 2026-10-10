<?php
requireLogin();
$me = currentUser();
$id = (int) ($_GET['id'] ?? 0);
$media = findAccessibleMedia($id, $me);
if (!$media) {
    setFlash('Media not found.', 'error');
    redirect('media');
}

$errors = [];
$form = [
    'file_name' => $media['file_name'],
    'alt_text' => (string) $media['alt_text'],
];

if (isPostRequest()) {
    requireValidCsrf();
    foreach (array_keys($form) as $key) {
        $value = isset($_POST[$key]) && is_string($_POST[$key]) ? $_POST[$key] : '';
        $form[$key] = trim(preg_replace('/[\p{C}]+/u', '', $value));
    }

    if ($form['file_name'] === '' || mb_strlen($form['file_name']) > 255) {
        $errors[] = 'Display name is required (max. 255 characters).';
    }
    if (mb_strlen($form['alt_text']) > 255) {
        $errors[] = 'Alt text must be at most 255 characters.';
    }

    if (!$errors) {
        // Only the display name and the alt text change. The file and its URL stay the same.
        $onlyUserId = $me['role'] === 'admin' ? null : (int) $me['id'];
        updateMedia($id, $form['file_name'], $form['alt_text'] === '' ? null : $form['alt_text'], $onlyUserId);
        setFlash('Media is updated.');
        redirect('media-detail', ['id' => $id]);
    }
}

$pageTitle = 'Edit Media';
