<?php
// Builds the WHERE part for the media list.
// $onlyUserId limits the list to one user (authors see only their own files).
// $filters: 'uploader' (user id), 'type' (image, video, document), 'q' (name keyword)
function mediaListCondition($onlyUserId, $filters) {
    $where = [];
    $params = [];

    if ($onlyUserId !== null) {
        $where[] = 'm.user_id = :only_user';
        $params['only_user'] = (int) $onlyUserId;
    }
    if (!empty($filters['uploader'])) {
        $where[] = 'm.user_id = :uploader';
        $params['uploader'] = (int) $filters['uploader'];
    }
    if (($filters['type'] ?? '') === 'image') {
        $where[] = "m.mime_type LIKE 'image/%'";
    } elseif (($filters['type'] ?? '') === 'video') {
        $where[] = "m.mime_type LIKE 'video/%'";
    } elseif (($filters['type'] ?? '') === 'document') {
        $where[] = "m.mime_type NOT LIKE 'image/%' AND m.mime_type NOT LIKE 'video/%'";
    }
    if (($filters['q'] ?? '') !== '') {
        // Escape LIKE wildcards so the keyword is searched as plain text
        $params['keyword'] = '%' . str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $filters['q']) . '%';
        $where[] = 'm.file_name LIKE :keyword';
    }

    return [$where ? ' WHERE ' . implode(' AND ', $where) : '', $params];
}

function countMedia($onlyUserId, $filters) {
    [$where, $params] = mediaListCondition($onlyUserId, $filters);
    $stmt = getDatabase()->prepare('SELECT COUNT(*) FROM t_media m' . $where);
    $stmt->execute($params);
    return (int) $stmt->fetchColumn();
}

function getMediaList($onlyUserId, $filters, $limit, $offset) {
    [$where, $params] = mediaListCondition($onlyUserId, $filters);
    $stmt = getDatabase()->prepare('SELECT m.*, u.username FROM t_media m JOIN t_user u ON u.id = m.user_id' . $where . ' ORDER BY m.created_at DESC, m.id DESC LIMIT :limit OFFSET :offset');
    foreach ($params as $name => $value) {
        $stmt->bindValue(':' . $name, $value);
    }
    $stmt->bindValue(':limit', (int) $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', (int) $offset, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Users that have uploaded media (for the admin's uploader filter)
function getMediaUploaders() {
    return getDatabase()->query('SELECT DISTINCT u.id, u.username, u.display_name FROM t_user u JOIN t_media m ON m.user_id = u.id ORDER BY u.username')->fetchAll(PDO::FETCH_ASSOC);
}

// Returns the media row only when the user may manage it: an admin for any row,
// an author only for their own. Every page and action uses this, so ownership is checked on the server.
function findAccessibleMedia($id, $user) {
    $stmt = getDatabase()->prepare('SELECT m.*, u.username, u.display_name FROM t_media m JOIN t_user u ON u.id = m.user_id WHERE m.id = ?');
    $stmt->execute([(int) $id]);
    $media = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$media) {
        return null;
    }
    if ($user['role'] !== 'admin' && (int) $media['user_id'] !== (int) $user['id']) {
        return null;
    }
    return $media;
}

function createMedia($userId, $fileName, $filePath, $mimeType, $fileSize) {
    $stmt = getDatabase()->prepare('INSERT INTO t_media (user_id, file_name, file_path, mime_type, file_size) VALUES (?, ?, ?, ?, ?)');
    $stmt->execute([$userId, $fileName, $filePath, $mimeType, $fileSize]);
    return (int) getDatabase()->lastInsertId();
}

// $onlyUserId is set for authors, so the SQL itself cannot touch another user's row
function updateMedia($id, $fileName, $altText, $onlyUserId = null) {
    $sql = 'UPDATE t_media SET file_name = ?, alt_text = ? WHERE id = ?';
    $params = [$fileName, $altText, $id];
    if ($onlyUserId !== null) {
        $sql .= ' AND user_id = ?';
        $params[] = $onlyUserId;
    }
    $stmt = getDatabase()->prepare($sql);
    $stmt->execute($params);
}

// $pdo lets the caller delete inside its own transaction (getDatabase() opens a new connection each time)
function deleteMedia($id, $onlyUserId = null, $pdo = null) {
    $sql = 'DELETE FROM t_media WHERE id = ?';
    $params = [$id];
    if ($onlyUserId !== null) {
        $sql .= ' AND user_id = ?';
        $params[] = $onlyUserId;
    }
    $stmt = ($pdo ?? getDatabase())->prepare($sql);
    $stmt->execute($params);
}
