<?php
// Remember me keeps the session cookie for 30 days
define('REMEMBER_ME_SECONDS', 30 * 24 * 60 * 60);

function startSecureSession() {
    // Server must keep session data as long as the longest cookie
    ini_set('session.gc_maxlifetime', (string) REMEMBER_ME_SECONDS);
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

function redirect($page, $params = []) {
    $params = ['page' => $page] + $params;
    header('Location: index.php?' . http_build_query($params));
    exit;
}

// One-time message shown on the next page. $type is 'success' or 'error'.
function setFlash($message, $type = 'success') {
    $_SESSION['flash'] = ['message' => $message, 'type' => $type];
}

function getFlash() {
    $flash = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $flash;
}

// ---- CSRF ----

function csrfToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrfField() {
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(csrfToken()) . '">';
}

// Stop the request when a POST has no valid token
function requireValidCsrf() {
    $sent = $_POST['csrf_token'] ?? '';
    if (!is_string($sent) || !hash_equals(csrfToken(), $sent)) {
        http_response_code(403);
        echo 'Invalid request token.';
        exit;
    }
}

function isPostRequest() {
    return $_SERVER['REQUEST_METHOD'] === 'POST';
}

// ---- Login state ----

// Returns the signed-in user row, or null for guests
function currentUser() {
    static $user = false;
    if ($user === false) {
        $user = null;
        if (!empty($_SESSION['user_id'])) {
            $user = findUserById((int) $_SESSION['user_id']);
            if (!$user) {
                unset($_SESSION['user_id']);
            }
        }
    }
    return $user;
}

function loginUser($userId, $remember) {
    session_regenerate_id(true);
    $_SESSION['user_id'] = $userId;

    // Remember me: send the session cookie again with a longer lifetime.
    // Without it, the cookie ends when the browser closes.
    if ($remember) {
        setcookie(session_name(), session_id(), [
            'expires' => time() + REMEMBER_ME_SECONDS,
            'path' => '/',
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
    }
}

function logoutUser() {
    $_SESSION = [];
    $params = session_get_cookie_params();
    setcookie(session_name(), '', [
        'expires' => time() - 3600,
        'path' => $params['path'],
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_destroy();
}

function requireLogin() {
    if (!currentUser()) {
        redirect('login');
    }
}

function requireRole($role) {
    requireLogin();
    if (currentUser()['role'] !== $role) {
        http_response_code(403);
        echo 'You do not have permission to open this page.';
        exit;
    }
}

// ---- Password rules ----

// Returns an error message, or '' when the new password is acceptable
function validateNewPassword($newPassword, $currentHash) {
    if (strlen($newPassword) < 6) {
        return 'New password must be at least 6 characters.';
    }
    if ($newPassword === DEFAULT_PASSWORD) {
        return 'New password must not be the default password.';
    }
    if (password_verify($newPassword, $currentHash)) {
        return 'New password must be different from the current password.';
    }
    return '';
}
