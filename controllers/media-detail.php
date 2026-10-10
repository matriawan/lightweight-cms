<?php
requireLogin();
$me = currentUser();
$media = findAccessibleMedia((int) ($_GET['id'] ?? 0), $me);
if (!$media) {
    setFlash('Media not found.', 'error');
    redirect('media');
}

// Keep the list position (filters and page) for the Back link
$backParams = ['page' => 'media'];
foreach (['q', 'type', 'uploader'] as $name) {
    if (isset($_GET[$name]) && is_string($_GET[$name]) && trim($_GET[$name]) !== '') {
        $backParams[$name] = trim($_GET[$name]);
    }
}
if (isset($_GET['p'])) {
    $backParams['p'] = (int) $_GET['p'];
}

$group = mediaGroupOfMime($media['mime_type']);
$fileUrl = mediaRelativeUrl($media['file_path']);
$fullUrl = mediaFullUrl($media['file_path']);
$fileExists = is_file(mediaDiskPath($media['file_path']));

$pageTitle = 'Media Detail';
