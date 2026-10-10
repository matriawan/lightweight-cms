<?php
function getSetting($key, $default = '') {
    try {
        $pdo = getDatabase();
        $stmt = $pdo->prepare('SELECT setting_value FROM t_setting WHERE setting_key = ?');
        $stmt->execute([$key]);
        $value = $stmt->fetchColumn();
        return $value === false ? $default : $value;
    } catch (Exception $e) {
        return $default;
    }
}

function getPerPage() {
    $perPage = (int) getSetting('per_page', 5);
    return $perPage > 0 ? $perPage : 5;
}

// The only settings the Site Settings page can change. Any other key is ignored.
const EDITABLE_SETTINGS = ['title', 'description', 'per_page', 'header_text', 'footer_text'];

// Saves all given editable settings in one transaction (all or nothing)
function saveSettings($values) {
    $pdo = getDatabase();
    $pdo->beginTransaction();
    try {
        $stmt = $pdo->prepare('INSERT INTO t_setting (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = ?');
        foreach (EDITABLE_SETTINGS as $key) {
            if (array_key_exists($key, $values)) {
                $stmt->execute([$key, (string) $values[$key], (string) $values[$key]]);
            }
        }
        $pdo->commit();
    } catch (PDOException $e) {
        $pdo->rollBack();
        throw $e;
    }
}
