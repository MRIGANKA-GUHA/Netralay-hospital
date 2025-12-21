<?php
require_once 'includes/database.php';

// Application configuration
define('SITE_URL', 'http://localhost/netralay-hospital/');
define('SITE_NAME', 'Netralay Hospital');

// Session configuration
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 0); // Set to 1 for HTTPS
session_start();



// Helper functions
function redirect($url) {
    header("Location: " . $url);
    exit();
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        redirect('login.php');
    }
}

function hasRole($role) {
    return isset($_SESSION['role']) && $_SESSION['role'] === $role;
}

function generateId($prefix, $table, $column, $padLength = 6) {
    global $pdo;
    // Find the current max numeric part for the given prefix
    $stmt = $pdo->prepare("SELECT MAX(CAST(SUBSTRING($column, LENGTH(?) + 1) AS UNSIGNED)) as max_id FROM $table WHERE $column LIKE CONCAT(?, '%')");
    $stmt->execute([$prefix, $prefix]);
    $row = $stmt->fetch();
    $nextId = ($row['max_id'] ?? 0) + 1;
    return $prefix . str_pad($nextId, $padLength, '0', STR_PAD_LEFT);
}

function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

function formatDate($date) {
    return date('M d, Y', strtotime($date));
}

function formatDateTime($datetime) {
    return date('M d, Y g:i A', strtotime($datetime));
}

function showAlert($message, $type = 'info') {
    $_SESSION['alert'] = [
        'message' => $message,
        'type' => $type
    ];
}

function displayAlert() {
    if (isset($_SESSION['alert'])) {
        $alert = $_SESSION['alert'];
        echo '<div class="alert alert-' . $alert['type'] . ' alert-dismissible fade show" role="alert">';
        echo htmlspecialchars($alert['message']);
        echo '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
        echo '</div>';
        unset($_SESSION['alert']);
    }
}
?>