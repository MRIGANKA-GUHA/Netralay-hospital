<?php
require_once 'includes/config.php';

$error = '';
$success = '';

// Helper: Check if a table has a column (to support different DB schemas)
function hasColumn(PDO $pdo, $table, $column) {
    try {
        $db = $pdo->query('SELECT DATABASE()')->fetchColumn();
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND COLUMN_NAME = ?');
        $stmt->execute([$db, $table, $column]);
        return (int)$stmt->fetchColumn() > 0;
    } catch (PDOException $e) {
        return false;
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $token = sanitize($_POST['token']);
    $new_password = sanitize($_POST['new_password']);

    if (empty($token) || empty($new_password)) {
        $error = 'Please fill in all required fields.';
    } else {
        try {
            $user = false;
            $patient = false;
            // Only query tables that actually have the columns
            if (hasColumn($pdo, 'users', 'reset_token') && hasColumn($pdo, 'users', 'reset_token_expiry')) {
                $stmt = $pdo->prepare("SELECT * FROM users WHERE reset_token = ? AND reset_token_expiry > NOW()");
                $stmt->execute([$token]);
                $user = $stmt->fetch();
            }
            if (!$user && hasColumn($pdo, 'patients', 'reset_token') && hasColumn($pdo, 'patients', 'reset_token_expiry')) {
                $stmt = $pdo->prepare("SELECT * FROM patients WHERE reset_token = ? AND reset_token_expiry > NOW()");
                $stmt->execute([$token]);
                $patient = $stmt->fetch();
            }

            if ($user || $patient) {
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                if ($user) {
                    $stmt = $pdo->prepare("UPDATE users SET password = ?, reset_token = NULL, reset_token_expiry = NULL WHERE reset_token = ?");
                    $stmt->execute([$hashed_password, $token]);
                } elseif ($patient) {
                    $stmt = $pdo->prepare("UPDATE patients SET password = ?, reset_token = NULL, reset_token_expiry = NULL WHERE reset_token = ?");
                    $stmt->execute([$hashed_password, $token]);
                }
                $success = 'Password reset successfully.';
            } else {
                $error = 'Invalid or expired token.';
            }
        } catch (PDOException $e) {
            $error = 'Error processing request: ' . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - <?php echo SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
    <style>
        .login-header h1 {
            text-transform: uppercase;
            letter-spacing: 0.08em;
            font-weight: bold;
        }
    </style>
</head>
<body class="login-page">
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <h1><?php echo SITE_NAME; ?></h1>
                <p style="font-size: 1.10rem;">Enter your new password</p>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> <?php echo $success; ?>
                    <div class="text-center mt-3">
                        <a href="login.php" class="btn btn-primary">
                            Go to Login
                        </a>
                    </div>
                </div>
            <?php endif; ?>
            
            <?php if (!$success): ?>
            <form method="POST" class="login-form">
                <input type="hidden" name="token" value="<?= $_GET['token'] ?? '' ?>">
                
                <div class="form-group">
                    <label for="new_password">New Password</label>
                    <div class="input-group" style="margin-top: 0.5rem;">
                        <span class="input-group-text"><i class="fas fa-lock"></i></span>
                        <input type="password" class="form-control" id="new_password" name="new_password" required>
                        <button type="button" class="btn btn-outline-secondary" id="togglePassword">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary w-100" style="padding: 10px 12px;">
                    Reset Password
                </button>
                
                <div class="text-center mt-3">
                    <a href="login.php" style="font-size: 0.85rem; color: #007bff; text-decoration: none;">
                        <i class="fas fa-arrow-left"></i> Back to Login
                    </a>
                </div>
            </form>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Toggle password visibility
        const toggleBtn = document.getElementById('togglePassword');
        if (toggleBtn) {
            toggleBtn.addEventListener('click', function() {
                const password = document.getElementById('new_password');
                const icon = this.querySelector('i');
                
                if (password.type === 'password') {
                    password.type = 'text';
                    icon.classList.remove('fa-eye');
                    icon.classList.add('fa-eye-slash');
                } else {
                    password.type = 'password';
                    icon.classList.remove('fa-eye-slash');
                    icon.classList.add('fa-eye');
                }
            });
        }
    </script>
</body>
</html>