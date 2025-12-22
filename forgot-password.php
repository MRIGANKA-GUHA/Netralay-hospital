<?php
require_once 'includes/config.php';
require_once 'PHPMailer-master/src/PHPMailer.php';
require_once 'PHPMailer-master/src/SMTP.php';
require_once 'PHPMailer-master/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

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
    $email = sanitize($_POST['email']);

    if (empty($email)) {
        $error = 'Please enter your email address.';
    } else {
        try {
            // Check users table
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            // Check patients table if not found in users
            if (!$user) {
                $stmt = $pdo->prepare("SELECT * FROM patients WHERE email = ?");
                $stmt->execute([$email]);
                $patient = $stmt->fetch();
            } else {
                $patient = false;
            }

            if ($user || $patient) {
                $token = bin2hex(random_bytes(50));
                $updated = false;
                if ($user && hasColumn($pdo, 'users', 'reset_token') && hasColumn($pdo, 'users', 'reset_token_expiry')) {
                    $stmt = $pdo->prepare("UPDATE users SET reset_token = ?, reset_token_expiry = DATE_ADD(NOW(), INTERVAL 1 HOUR) WHERE email = ?");
                    $stmt->execute([$token, $email]);
                    $updated = true;
                } elseif ($patient && hasColumn($pdo, 'patients', 'reset_token') && hasColumn($pdo, 'patients', 'reset_token_expiry')) {
                    $stmt = $pdo->prepare("UPDATE patients SET reset_token = ?, reset_token_expiry = DATE_ADD(NOW(), INTERVAL 1 HOUR) WHERE email = ?");
                    $stmt->execute([$token, $email]);
                    $updated = true;
                }

                $mail = new PHPMailer(true);

                try {
                    $mail->isSMTP();
                    $mail->Host = 'smtp.gmail.com';
                    $mail->SMTPAuth = true;
                    $mail->Username = 'mrigankaguha42@gmail.com';
                    $mail->Password = '';
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                    $mail->Port = 587;

                    $mail->setFrom('mrigankaguha42@gmail.com', 'Netralay Hospital');
                    $mail->addAddress($email);

                    $mail->isHTML(true);
                    $mail->Subject = 'Password Reset Request';
                    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
                    $host = $_SERVER['HTTP_HOST'];
                    $basePath = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
                    $resetLink = $scheme . '://' . $host . $basePath . '/reset-password.php?token=' . $token;
                    $mail->Body = "<p>Click the link below to reset your password:</p><p><a href='" . htmlspecialchars($resetLink, ENT_QUOTES) . "'>Reset Password</a></p>";

                    $mail->send();
                    $success = $updated ? 'Password reset email sent successfully.' : 'Password reset is not supported for this account type on the current database schema.';
                } catch (Exception $e) {
                    $error = 'Error sending email: ' . $mail->ErrorInfo;
                }
            } else {
                $error = 'No account found with that email address.';
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
    <title>Forgot Password - <?php echo SITE_NAME; ?></title>
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
                <p style="font-size: 1.10rem;">Reset your password</p>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> <?php echo $success; ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" class="login-form">
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <div class="input-group" style="margin-top: 0.5rem;">
                        <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                        <input type="email" class="form-control" id="email" name="email" 
                               value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" 
                               required>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary w-100" style="padding: 10px 12px;">
                    Send Reset Link
                </button>
                
                <div class="text-center mt-3">
                    <a href="login.php" style="font-size: 0.85rem; color: #007bff; text-decoration: none;">
                        <i class="fas fa-arrow-left"></i> Back
                    </a>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>