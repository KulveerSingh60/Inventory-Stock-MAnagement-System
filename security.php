<?php
/**
 * security.php - CSRF protection + auth/role helpers for StockMaster Pro
 *
 * Include this AFTER session_start() on every page.
 *
 * Provides:
 *   csrf_token()      - generate/return the per-session CSRF token
 *   csrf_field()      - echo a hidden CSRF input for forms
 *   verify_csrf()     - validate CSRF token from POST (must be called before
 *                       any state-changing action)
 *   require_login()   - redirect unauthenticated users to login.php
 *   require_admin()   - restrict a page to admin role only
 *   current_role()    - return the logged-in user's role
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Return (and lazily generate) the CSRF token stored in the session.
 */
function csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Echo a hidden CSRF field for use inside <form> tags.
 */
function csrf_field() {
    echo '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') . '">';
}

/**
 * Validate the CSRF token submitted via POST.
 * Returns true when valid, otherwise stops with a 403 response.
 */
function verify_csrf() {
    $token = isset($_POST['csrf_token']) ? (string)$_POST['csrf_token'] : '';
    if ($token === '' || !hash_equals(csrf_token(), $token)) {
        http_response_code(403);
        die("Invalid or missing CSRF token. Please go back, refresh the page, and try again.");
    }
    return true;
}

/**
 * Redirect to the login page when the user is not authenticated.
 */
function require_login() {
    if (empty($_SESSION['isLoggedIn'])) {
        header("Location: login.php");
        exit();
    }
}

/**
 * Restrict the page to admin-level users only.
 * Unauthenticated users are redirected; staff users get a 403 page.
 */
function require_admin() {
    if (empty($_SESSION['isLoggedIn'])) {
        header("Location: login.php");
        exit();
    }
    if (($_SESSION['role'] ?? '') !== 'admin') {
        http_response_code(403);
        die("<h2>403 Forbidden</h2><p>You need administrator privileges to access this page.</p>");
    }
}

/**
 * Return the current user's role, or '' when not logged in.
 */
function current_role() {
    return $_SESSION['role'] ?? '';
}
