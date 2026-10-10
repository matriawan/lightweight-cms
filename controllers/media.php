<?php
requireLogin();
$me = currentUser();
$isAdmin = $me['role'] === 'admin';
// Authors see and manage only their own files
$onlyUserId = $isAdmin ? null : (int) $me['id'];

$page_number = isset($_GET['p']) ? (int) $_GET['p'] : 1;
$keyword = isset($_GET['q']) && is_string($_GET['q']) ? trim($_GET['q']) : '';
$type = isset($_GET['type']) && is_string($_GET['type']) && isset(mediaGroupLabels()[$_GET['type']]) ? $_GET['type'] : '';
$uploader = $isAdmin && isset($_GET['uploader']) && is_string($_GET['uploader']) ? (int) $_GET['uploader'] : 0;

$filters = ['q' => $keyword, 'type' => $type, 'uploader' => $uploader];
// Keep the filters in links and forms
$filterParams = [];
foreach ($filters as $name => $value) {
    if ($value !== '' && $value !== 0) {
        $filterParams[$name] = $value;
    }
}

if (isPostRequest()) {
    requireValidCsrf();
    $media = findAccessibleMedia((int) ($_POST['id'] ?? 0), $me);

    if (!$media) {
        setFlash('Media not found.', 'error');
    } else {
        // Remove the row and the file together. If the file cannot be removed, the row stays.
        $pdo = getDatabase();
        $pdo->beginTransaction();
        try {
            deleteMedia((int) $media['id'], $onlyUserId, $pdo);
            if (deleteMediaFile($media['file_path'])) {
                $pdo->commit();
                setFlash('"' . $media['file_name'] . '" is deleted.');
            } else {
                $pdo->rollBack();
                setFlash('The file could not be removed from the media folder, so nothing was deleted.', 'error');
            }
        } catch (PDOException $e) {
            $pdo->rollBack();
            setFlash('The file could not be deleted. Please try again.', 'error');
        }
    }
    redirect('media', $filterParams + ['p' => $page_number]);
}

$pagination = getPagination(countMedia($onlyUserId, $filters), $page_number, getPerPage());
$items = getMediaList($onlyUserId, $filters, $pagination['per_page'], $pagination['offset']);
$uploaders = $isAdmin ? getMediaUploaders() : [];

$pageTitle = 'Media';
