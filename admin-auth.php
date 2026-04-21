<?php
/**
 * Admin Authentication Helper for ZamboSur Crafts Admin Panel
 */

require_once 'config.php';

/**
 * Start admin session safely with Cross-Site (CORS) support
 */
function startAdminSession() {
    if (session_status() === PHP_SESSION_NONE) {
        // REQUIRED for Render/Cross-Domain sessions
        session_set_cookie_params([
            'lifetime' => 86400, // 24 hours
            'path' => '/',
            // This allows the cookie to be sent from the API to the Frontend
            'samesite' => 'None', 
            'secure' => true,     // Must be true if samesite is None
            'httponly' => true
        ]);
        
        session_start();
    }
}

/**
 * Check if user is logged in as admin
 * @return bool
 */
function isAdminLoggedIn() {
    startAdminSession();
    // Debugging: If you still get 401, you can temporarily error_log($_SESSION)
    return isset($_SESSION['admin_id']) && !empty($_SESSION['admin_id']);
}

/**
 * Login admin
 */
function adminLogin($username, $password) {
    startAdminSession();
    
    if (empty($username) || empty($password)) {
        return ['success' => false, 'error' => 'Username and password required'];
    }
    
    $conn = getDBConnection();
    $conn->set_charset("utf8mb4");

    $stmt = $conn->prepare("SELECT id, username, password_hash FROM admins WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        $inputPassword = trim($password);
        $hashedPassword = trim($row['password_hash']);

        if (password_verify($inputPassword, $hashedPassword)) {
            // Regeneration prevents session fixation attacks
            session_regenerate_id(true); 
            
            $_SESSION['admin_id'] = $row['id'];
            $_SESSION['admin_username'] = $row['username'];
            
            $stmt->close();
            $conn->close();
            return ['success' => true, 'message' => 'Login successful'];
        } else {
            $stmt->close();
            $conn->close();
            return ['success' => false, 'error' => 'Invalid password'];
        }
    }
    
    $stmt->close();
    $conn->close();
    return ['success' => false, 'error' => 'Admin not found'];
}

/**
 * Logout admin
 */
function adminLogout() {
    startAdminSession();
    $_SESSION = array(); 
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    session_destroy();
    return ['success' => true, 'message' => 'Logged out successfully'];
}
