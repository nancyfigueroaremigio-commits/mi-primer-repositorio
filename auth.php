<?php
// auth.php
// Helpers de autenticación, sesión y CSRF
 
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Tiempo de expiración en segundos (30 min por defecto)
if (!defined('SESSION_TIMEOUT')) define('SESSION_TIMEOUT', 1800);

// Refrescar y validar sesión
function session_check() {
    if (!isset($_SESSION['user'])) return false;
    if (!isset($_SESSION['last_activity'])) return false;
    if (time() - $_SESSION['last_activity'] > SESSION_TIMEOUT) {
        session_unset();
        session_destroy();
        return false;
    }
    $_SESSION['last_activity'] = time();
    return true;
}

function is_logged_in() {
    return session_check();
}

function current_user() {
    return isset($_SESSION['user']) ? $_SESSION['user'] : null;
}

function current_role() {
    return isset($_SESSION['user']['role']) ? $_SESSION['user']['role'] : null;
}

function require_login() {
    if (!is_logged_in()) {
        header('Location: login.php');
        exit;
    }
}

function require_role($role) {
    if (!is_logged_in() || current_role() !== $role) {
        http_response_code(403);
        echo "403 Forbidden - No tienes permiso para ver esto.";
        exit;
    }
}

// CSRF token simple
function csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(16));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

