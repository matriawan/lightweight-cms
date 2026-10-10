<?php
const SITES_DIR = __DIR__ . '/../public/sites';
const SITES_URL = 'public/sites';
const MAX_IMAGE_BYTES = 1024 * 1024;

// The fixed image files of the Site Settings page. A new upload replaces the old file.
// The file name is built from 'name' and the detected type, never from the upload.
function settingImages() {
    return [
        'banner' => ['label' => 'Header Banner', 'name' => 'header', 'types' => ['png']],
        'favicon' => ['label' => 'Favicon', 'name' => 'favicon', 'types' => ['png', 'ico']],
    ];
}

// URL of a saved image (with a version so browsers show a replaced file), or '' if there is none
function settingImageUrl($key) {
    $images = settingImages();
    if (!isset($images[$key])) {
        return '';
    }
    $newest = '';
    $newestTime = 0;
    foreach ($images[$key]['types'] as $type) {
        $fileName = $images[$key]['name'] . '.' . $type;
        $path = SITES_DIR . '/' . $fileName;
        if (is_file($path) && filemtime($path) >= $newestTime) {
            $newest = SITES_URL . '/' . $fileName . '?v=' . filemtime($path);
            $newestTime = filemtime($path);
        }
    }
    return $newest;
}

// Browser type for the favicon link
function faviconMimeType($url) {
    return preg_match('/\.ico(\?|$)/', $url) ? 'image/x-icon' : 'image/png';
}

// Value for the accept attribute of a file input
function imageAcceptAttribute($types) {
    $accept = [];
    foreach ($types as $type) {
        $accept[] = $type === 'ico' ? '.ico,image/x-icon,image/vnd.microsoft.icon' : '.png,image/png';
    }
    return implode(',', $accept);
}

// True when the user chose a file for this input
function uploadWasSent($file) {
    return is_array($file) && isset($file['error']) && $file['error'] !== UPLOAD_ERR_NO_FILE;
}

// Finds the real type from the file content: 'png', 'ico', or '' (anything else).
// The file name and the browser type are not trusted.
function detectImageType($path) {
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($path);

    if ($mime === 'image/png') {
        $info = @getimagesize($path);
        return ($info && $info[2] === IMAGETYPE_PNG) ? 'png' : '';
    }
    if ($mime === 'image/vnd.microsoft.icon' || $mime === 'image/x-icon') {
        // An ICO file starts with the bytes 00 00 01 00
        return file_get_contents($path, false, null, 0, 4) === "\x00\x00\x01\x00" ? 'ico' : '';
    }
    return '';
}

// Returns an error message, or '' when the upload is an allowed type within the size limit
function checkImageUpload($file, $label, $allowedTypes) {
    if (!is_array($file) || !isset($file['error']) || is_array($file['error'])) {
        return $label . ': the upload is not valid.';
    }
    if ($file['error'] === UPLOAD_ERR_INI_SIZE || $file['error'] === UPLOAD_ERR_FORM_SIZE) {
        return $label . ' must be at most 1 MB.';
    }
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return $label . ': the upload failed, please try again.';
    }
    if (!is_uploaded_file($file['tmp_name'])) {
        return $label . ': the upload is not valid.';
    }
    if (filesize($file['tmp_name']) > MAX_IMAGE_BYTES) {
        return $label . ' must be at most 1 MB.';
    }
    if (!in_array(detectImageType($file['tmp_name']), $allowedTypes, true)) {
        return $label . ' must be ' . ($allowedTypes === ['png'] ? 'a PNG image.' : 'an ICO or PNG image.');
    }
    return '';
}

function sitesFolderIsWritable() {
    return is_dir(SITES_DIR) && is_writable(SITES_DIR);
}

// Moves the upload next to its final place. Returns the temporary path, or '' on failure.
function stageUpload($file, $fileName) {
    $temp = SITES_DIR . '/' . $fileName . '.tmp';
    return move_uploaded_file($file['tmp_name'], $temp) ? $temp : '';
}

// Replaces the old image with the staged one (same folder, so the replace is one step).
// An older file of another allowed type (for example favicon.png after a new favicon.ico) is removed.
function publishStagedImage($tempPath, $image, $type) {
    if (!rename($tempPath, SITES_DIR . '/' . $image['name'] . '.' . $type)) {
        return false;
    }
    foreach ($image['types'] as $otherType) {
        $other = SITES_DIR . '/' . $image['name'] . '.' . $otherType;
        if ($otherType !== $type && is_file($other)) {
            unlink($other);
        }
    }
    return true;
}

function discardStagedImage($tempPath) {
    if ($tempPath !== '' && is_file($tempPath)) {
        unlink($tempPath);
    }
}
