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
