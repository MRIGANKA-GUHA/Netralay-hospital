<?php
require_once 'includes/config.php';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');

    // Basic validation
    $errors = [];
    if (empty($full_name)) $errors[] = 'Full name is required.';
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid email is required.';
    if (empty($phone)) {
        $errors[] = 'Phone is required.';
    } elseif (!preg_match('/^[0-9]+$/', $phone)) {
        $errors[] = 'Phone number must contain only digits.';
    }
    if (empty($subject)) $errors[] = 'Subject is required.';
    if (empty($message)) $errors[] = 'Message is required.';

    if (empty($errors)) {
        try {
            // Generate custom enquiry_id
            $enquiry_id = generateId('ENQ', 'enquiries', 'enquiry_id', 5);
            $stmt = $pdo->prepare("INSERT INTO enquiries (enquiry_id, full_name, email, phone, subject, message) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$enquiry_id, $full_name, $email, $phone, $subject, $message]);
            $success = true;
        } catch (PDOException $e) {
            $errors[] = 'Failed to submit enquiry. Please try again later.';
        }
    }
}

// If this is an AJAX request, return JSON
if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
    header('Content-Type: application/json');
    if (!empty($success)) {
        echo json_encode(['success' => true, 'message' => 'Enquiry submitted successfully.']);
    } else {
        echo json_encode(['success' => false, 'errors' => $errors]);
    }
    exit;
}

// If not AJAX, redirect or show a simple message
if (!empty($success)) {
    header('Location: index.php?enquiry=success');
    exit;
}
?>
