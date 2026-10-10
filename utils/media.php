<?php
const MEDIA_DIR = __DIR__ . '/../public/media';
const MEDIA_URL = 'public/media';
const MAX_MEDIA_BYTES = 2 * 1024 * 1024;

// Allowed files: extension => type group and the real content types (from finfo) it may have.
// The stored file always gets this extension, so a user cannot choose it.
function allowedMediaTypes() {
    return [
        'jpg' => ['group' => 'image', 'mimes' => ['image/jpeg']],
        'png' => ['group' => 'image', 'mimes' => ['image/png']],
        'gif' => ['group' => 'image', 'mimes' => ['image/gif']],
        'webp' => ['group' => 'image', 'mimes' => ['image/webp']],
        'mp4' => ['group' => 'video', 'mimes' => ['video/mp4', 'video/x-m4v']],
        'webm' => ['group' => 'video', 'mimes' => ['video/webm']],
        'pdf' => ['group' => 'document', 'mimes' => ['application/pdf']],
        'txt' => ['group' => 'document', 'mimes' => ['text/plain']],
        'doc' => ['group' => 'document', 'mimes' => ['application/msword']],
        'docx' => ['group' => 'document', 'mimes' => ['application/vnd.openxmlformats-officedocument.wordprocessingml.document']],
        'xls' => ['group' => 'document', 'mimes' => ['application/vnd.ms-excel']],
        'xlsx' => ['group' => 'document', 'mimes' => ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']],
        'ppt' => ['group' => 'document', 'mimes' => ['application/vnd.ms-powerpoint']],
        'pptx' => ['group' => 'document', 'mimes' => ['application/vnd.openxmlformats-officedocument.presentationml.presentation']],
    ];
}

function mediaGroupLabels() {
    return ['image' => 'Images', 'video' => 'Videos', 'document' => 'Documents'];
}

// The name the user's browser sent, without folders and control characters (not shortened)
function mediaOriginalName($name) {
    $name = preg_replace('/^.*[\/\\\\]/', '', (string) $name);
    return trim(preg_replace('/[\p{C}]+/u', '', $name));
}

// The display name saved in t_media: cleaned and at most 255 characters.
// A long name is shortened in the middle part, so the extension stays.
function cleanMediaName($name) {
    $name = mediaOriginalName($name);
    if (mb_strlen($name) > 255) {
        $ext = pathinfo($name, PATHINFO_EXTENSION);
        $suffix = ($ext !== '' && mb_strlen($ext) <= 10) ? '.' . $ext : '';
        $name = mb_substr($name, 0, 255 - mb_strlen($suffix)) . $suffix;
    }
    return $name === '' ? 'file' : $name;
}

// Checks one upload from $_FILES. Returns an error message, or '' when it is allowed.
// $info gets 'ext' and 'mime' of the real file content.
function checkMediaUpload($file, &$info) {
    $info = [];
    if ($file === null) {
        return 'Choose a file to upload.';
    }
    if (!is_array($file) || !isset($file['error']) || is_array($file['error'])) {
        return 'The upload is not valid.';
    }
    if ($file['error'] === UPLOAD_ERR_NO_FILE) {
        return 'Choose a file to upload.';
    }
    if ($file['error'] === UPLOAD_ERR_INI_SIZE || $file['error'] === UPLOAD_ERR_FORM_SIZE) {
        return 'The file must be at most 2 MB.';
    }
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return 'The upload failed, please try again.';
    }
    if (!is_uploaded_file($file['tmp_name'])) {
        return 'The upload is not valid.';
    }
    if (filesize($file['tmp_name']) > MAX_MEDIA_BYTES) {
        return 'The file must be at most 2 MB.';
    }
    if (filesize($file['tmp_name']) === 0) {
        return 'The file is empty.';
    }

    // The extension must be allowed, and the real content must match it
    $ext = strtolower(pathinfo(mediaOriginalName($file['name'] ?? ''), PATHINFO_EXTENSION));
    if ($ext === 'jpeg') {
        $ext = 'jpg';
    }
    $types = allowedMediaTypes();
    if (!isset($types[$ext])) {
        return 'This file type is not allowed.';
    }
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($file['tmp_name']);
    if (!in_array($mime, $types[$ext]['mimes'], true)) {
        return 'The file content does not match its type (.' . $ext . ').';
    }

    $info = ['ext' => $ext, 'mime' => $mime];
    return '';
}

function mediaFolderIsWritable() {
    return is_dir(MEDIA_DIR) && is_writable(MEDIA_DIR);
}

// Upload date and time as 20261010-134501. It uses the database clock, the same one as t_media.created_at,
// so the file name and the upload time shown in the list always agree.
function mediaTimestamp() {
    try {
        return getDatabase()->query("SELECT DATE_FORMAT(NOW(), '%Y%m%d-%H%i%s')")->fetchColumn();
    } catch (PDOException $e) {
        return date('Ymd-His');
    }
}

// Moves the upload into public/media/ under its date and time name, for example 20261010-134501.png.
// Two uploads in the same second get a suffix (-2, -3, ...). Returns the stored file name, or '' on failure.
function storeMediaFile($file, $ext) {
    $base = mediaTimestamp();
    for ($i = 1; $i <= 100; $i++) {
        $fileName = $base . ($i > 1 ? '-' . $i : '') . '.' . $ext;
        $path = MEDIA_DIR . '/' . $fileName;
        // Mode "x" creates the file only if it does not exist yet, so a name is never reused
        $handle = @fopen($path, 'x');
        if ($handle === false) {
            continue;
        }
        fclose($handle);
        if (move_uploaded_file($file['tmp_name'], $path)) {
            return $fileName;
        }
        unlink($path);
        return '';
    }
    return '';
}

// Path inside public/media/. Only the base name of the saved path is used.
function mediaDiskPath($filePath) {
    return MEDIA_DIR . '/' . basename($filePath);
}

// Removes the file. Returns true when the file is gone (also when it was already missing).
function deleteMediaFile($filePath) {
    $path = mediaDiskPath($filePath);
    if (is_file($path)) {
        return unlink($path);
    }
    return true;
}

// Full URL of a stored file, built from the current request, for use in posts and single pages
function mediaFullUrl($filePath) {
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $folder = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/')), '/');
    return $scheme . '://' . $host . $folder . '/' . MEDIA_URL . '/' . basename($filePath);
}

// Relative URL for thumbnails and previews on our own pages
function mediaRelativeUrl($filePath) {
    return MEDIA_URL . '/' . basename($filePath);
}

function mediaGroupOfMime($mime) {
    if (strpos($mime, 'image/') === 0) {
        return 'image';
    }
    if (strpos($mime, 'video/') === 0) {
        return 'video';
    }
    return 'document';
}

function formatFileSize($bytes) {
    if ($bytes >= 1024 * 1024) {
        return number_format($bytes / (1024 * 1024), 1) . ' MB';
    }
    if ($bytes >= 1024) {
        return number_format($bytes / 1024, 1) . ' KB';
    }
    return $bytes . ' B';
}

// Value for the accept attribute of the file input (only a hint for the browser)
function mediaAcceptAttribute() {
    $list = [];
    foreach (array_keys(allowedMediaTypes()) as $ext) {
        $list[] = '.' . $ext;
    }
    $list[] = '.jpeg';
    return implode(',', $list);
}
