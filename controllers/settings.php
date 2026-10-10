<?php
requireRole('admin');

$images = settingImages();
$errors = [];
$form = [
    'title' => getSetting('title', 'Lightweight CMS'),
    'description' => getSetting('description'),
    'per_page' => (string) getPerPage(),
    'header_text' => getSetting('header_text'),
    'footer_text' => getSetting('footer_text'),
];

if (isPostRequest()) {
    requireValidCsrf();
    // Only the fields of this form are read. Any other posted key is ignored.
    foreach (array_keys($form) as $key) {
        $form[$key] = isset($_POST[$key]) && is_string($_POST[$key]) ? trim($_POST[$key]) : '';
    }

    if ($form['title'] === '' || mb_strlen($form['title']) > 100) {
        $errors[] = 'Site title is required (max. 100 characters).';
    }
    if (mb_strlen($form['description']) > 300) {
        $errors[] = 'Site description must be at most 300 characters.';
    }
    if (filter_var($form['per_page'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 50]]) === false) {
        $errors[] = 'Items per page must be a whole number from 1 to 50.';
    }
    if (mb_strlen($form['header_text']) > 1000) {
        $errors[] = 'Header text must be at most 1000 characters.';
    }
    if (mb_strlen($form['footer_text']) > 2000) {
        $errors[] = 'Footer text must be at most 2000 characters.';
    }

    // Images: check every chosen file first
    $uploads = [];
    foreach ($images as $field => $image) {
        $file = $_FILES[$field] ?? null;
        if (!uploadWasSent($file)) {
            continue;
        }
        $error = checkImageUpload($file, $image['label'], $image['types']);
        if ($error !== '') {
            $errors[] = $error;
        } else {
            $uploads[$field] = $file;
        }
    }
    if ($uploads && !sitesFolderIsWritable()) {
        $errors[] = 'The sites folder is not writable.';
    }

    if (!$errors) {
        // Stage the images first, so nothing is replaced if saving the text fails
        $staged = [];
        $stagedTypes = [];
        foreach ($uploads as $field => $file) {
            $type = detectImageType($file['tmp_name']);
            $temp = stageUpload($file, $images[$field]['name'] . '.' . $type);
            if ($temp === '') {
                $errors[] = $images[$field]['label'] . ' could not be stored.';
                break;
            }
            $staged[$field] = $temp;
            $stagedTypes[$field] = $type;
        }

        if (!$errors) {
            try {
                saveSettings($form);
            } catch (PDOException $e) {
                $errors[] = 'The settings could not be saved. Please try again.';
            }
        }

        if ($errors) {
            foreach ($staged as $temp) {
                discardStagedImage($temp);
            }
        } else {
            foreach ($staged as $field => $temp) {
                publishStagedImage($temp, $images[$field], $stagedTypes[$field]);
            }
            setFlash('Settings are saved.');
            redirect('settings');
        }
    }
}

$currentImages = [];
foreach ($images as $field => $image) {
    $currentImages[$field] = settingImageUrl($field);
}

$pageTitle = 'Settings';
