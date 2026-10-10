<?php
requireLogin();
$me = currentUser();
$errors = [];

if (isPostRequest()) {
    if (empty($_POST) && empty($_FILES) && (int) ($_SERVER['CONTENT_LENGTH'] ?? 0) > 0) {
        // PHP drops the whole request when it is bigger than post_max_size
        $errors[] = 'The upload is too large (max. 2 MB per file).';
    } else {
        requireValidCsrf();
        $file = $_FILES['file'] ?? null;
        $error = checkMediaUpload($file, $info);
        if ($error === '' && !mediaFolderIsWritable()) {
            $error = 'The media folder is not writable.';
        }

        if ($error !== '') {
            $errors[] = $error;
        } else {
            $storedName = storeMediaFile($file, $info['ext']);
            if ($storedName === '') {
                $errors[] = 'The file could not be stored.';
            } else {
                try {
                    $id = createMedia(
                        (int) $me['id'],
                        cleanMediaName($file['name']),
                        MEDIA_URL . '/' . $storedName,
                        $info['mime'],
                        filesize(MEDIA_DIR . '/' . $storedName)
                    );
                    setFlash('The file is uploaded. Copy its link below.');
                    redirect('media-detail', ['id' => $id]);
                } catch (PDOException $e) {
                    // Do not keep a file that has no row
                    unlink(MEDIA_DIR . '/' . $storedName);
                    $errors[] = 'The file could not be saved. Please try again.';
                }
            }
        }
    }
}

$pageTitle = 'Upload Media';
