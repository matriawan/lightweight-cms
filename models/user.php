<?php
define('DEFAULT_PASSWORD', '123456');

function findUserById($id) {
    $stmt = getDatabase()->prepare('SELECT * FROM t_user WHERE id = ?');
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
}

function findUserForLogin($login) {
    $stmt = getDatabase()->prepare('SELECT * FROM t_user WHERE username = :login_name OR email = :login_email LIMIT 1');
    $stmt->execute(['login_name' => $login, 'login_email' => $login]);
    return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
}

// Builds the WHERE part for searching name and email. No keyword means no filter.
function userSearchCondition($keyword) {
    if ($keyword === '') {
        return ['', []];
    }
    // Escape LIKE wildcards so the keyword is searched as plain text
    $like = '%' . str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $keyword) . '%';
    return [' WHERE display_name LIKE :kw_name OR email LIKE :kw_email', ['kw_name' => $like, 'kw_email' => $like]];
}

function countUsers($keyword = '') {
    [$where, $params] = userSearchCondition($keyword);
    $stmt = getDatabase()->prepare('SELECT COUNT(*) FROM t_user' . $where);
    $stmt->execute($params);
    return (int) $stmt->fetchColumn();
}

function getUsers($limit, $offset, $keyword = '') {
    [$where, $params] = userSearchCondition($keyword);
    $stmt = getDatabase()->prepare('SELECT id, username, email, display_name, role, must_change_password FROM t_user' . $where . ' ORDER BY username LIMIT :limit OFFSET :offset');
    foreach ($params as $name => $value) {
        $stmt->bindValue(':' . $name, $value);
    }
    $stmt->bindValue(':limit', (int) $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', (int) $offset, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// $excludeId lets an edit ignore the user's own row
function usernameExists($username, $excludeId = 0) {
    $stmt = getDatabase()->prepare('SELECT COUNT(*) FROM t_user WHERE username = ? AND id <> ?');
    $stmt->execute([$username, $excludeId]);
    return $stmt->fetchColumn() > 0;
}

function emailExists($email, $excludeId = 0) {
    $stmt = getDatabase()->prepare('SELECT COUNT(*) FROM t_user WHERE email = ? AND id <> ?');
    $stmt->execute([$email, $excludeId]);
    return $stmt->fetchColumn() > 0;
}

// There is only one admin (the seeded account), so new users are always authors.
// New users get the default password and must change it at first login.
function createUser($username, $email, $displayName, $bio) {
    $stmt = getDatabase()->prepare("INSERT INTO t_user (username, email, password_hash, display_name, bio, role, must_change_password) VALUES (?, ?, ?, ?, ?, 'author', 1)");
    $stmt->execute([$username, $email, password_hash(DEFAULT_PASSWORD, PASSWORD_DEFAULT), $displayName, $bio]);
}

// Username, role, password, and flags are never changed here. The row is chosen by id.
// The admin row is never changed or deleted by the functions below (manual database change only).
function updateUser($id, $email, $displayName, $bio) {
    $stmt = getDatabase()->prepare("UPDATE t_user SET email = ?, display_name = ?, bio = ? WHERE id = ? AND role <> 'admin'");
    $stmt->execute([$email, $displayName, $bio, $id]);
}

function deleteUser($id) {
    $stmt = getDatabase()->prepare("DELETE FROM t_user WHERE id = ? AND role <> 'admin'");
    $stmt->execute([$id]);
}

function resetUserPassword($id) {
    $stmt = getDatabase()->prepare("UPDATE t_user SET password_hash = ?, must_change_password = 1 WHERE id = ? AND role <> 'admin'");
    $stmt->execute([password_hash(DEFAULT_PASSWORD, PASSWORD_DEFAULT), $id]);
}

function changeUserPassword($id, $newPassword) {
    $stmt = getDatabase()->prepare('UPDATE t_user SET password_hash = ?, must_change_password = 0 WHERE id = ?');
    $stmt->execute([password_hash($newPassword, PASSWORD_DEFAULT), $id]);
}
