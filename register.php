<?php
require_once 'includes/config.php';

// Redirect if already logged in
if (isLoggedIn()) {
    redirect('dashboard.php');
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitize inputs
    $first_name = sanitize($_POST['first_name']);
    $last_name = sanitize($_POST['last_name']);
    $date_of_birth = $_POST['date_of_birth'];
    $gender = $_POST['gender'];
    $blood_type = sanitize($_POST['blood_type']);
    $phone = sanitize($_POST['phone']);
    $email = sanitize($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $address = sanitize($_POST['address']);
    $emergency_contact_name = sanitize($_POST['emergency_contact_name']);
    $emergency_contact_phone = sanitize($_POST['emergency_contact_phone']);
    $allergies = sanitize($_POST['allergies']);
    $insurance_provider = sanitize($_POST['insurance_provider']);
    $insurance_number = sanitize($_POST['insurance_number']);
    
    // Validation
    if (empty($first_name) || empty($last_name) || empty($date_of_birth) || empty($gender) || 
        empty($phone) || empty($email) || empty($password) || empty($address)) {
        $error = 'Please fill in all required fields';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters long';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match';
    } else {
        try {
            // Check if email already exists
            $stmt = $pdo->prepare("SELECT patient_id FROM patients WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                $error = 'Email already registered. Please use a different email.';
            } else {
                // Generate sequential patient ID
                $patient_id = generateId('NET', 'patients', 'patient_id', 6);
                
                // Hash the password
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                
                // Insert patient record
                $stmt = $pdo->prepare("INSERT INTO patients (patient_id, first_name, last_name, date_of_birth, gender, phone, email, password, address, emergency_contact_name, emergency_contact_phone, blood_type, allergies, insurance_provider, insurance_number) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                
                $stmt->execute([
                    $patient_id,
                    $first_name,
                    $last_name,
                    $date_of_birth,
                    $gender,
                    $phone,
                    $email,
                    $hashed_password,
                    $address,
                    $emergency_contact_name,
                    $emergency_contact_phone,
                    $blood_type ?: null,
                    $allergies ?: null,
                    $insurance_provider ?: null,
                    $insurance_number ?: null
                ]);
                $success = 'Registration successful! Your Patient ID is: ' . $patient_id . '.';
            }
        } catch (PDOException $e) {
            $error = 'Registration failed. Please try again.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patient Registration - <?php echo SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
    <style>
        .register-header h1 {
            text-transform: uppercase;
            letter-spacing: 0.08em;
            font-weight: bold;
        }
        .register-form .form-group {
            margin-bottom: 1rem;
        }
        .register-form label {
            font-weight: 500;
            margin-bottom: 0.5rem;
        }
        .required::after {
            content: " *";
            color: #dc3545;
        }
    </style>
</head>
<body class="login-page">
    <div class="login-container" style="width: 100%; max-width: 960px; padding: 20px;">
        <div class="login-card" style="max-width: 900px;">
            <div class="login-header register-header">
                <h1><?php echo SITE_NAME; ?></h1>
                <p style="font-size: 1.10rem;">Patient Registration</p>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> <?php echo $success; ?>
                    <div class="mt-3">
                        <a href="login.php" class="btn btn-primary">Go to Login</a>
                    </div>
                </div>
            <?php else: ?>
            
            <form method="POST" class="register-form login-form">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="first_name" class="required">First Name</label>
                            <input type="text" class="form-control" id="first_name" name="first_name" 
                                   value="<?php echo isset($_POST['first_name']) ? htmlspecialchars($_POST['first_name']) : ''; ?>" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="last_name" class="required">Last Name</label>
                            <input type="text" class="form-control" id="last_name" name="last_name" 
                                   value="<?php echo isset($_POST['last_name']) ? htmlspecialchars($_POST['last_name']) : ''; ?>" required>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="date_of_birth" class="required">Date of Birth</label>
                            <input type="date" class="form-control" id="date_of_birth" name="date_of_birth" 
                                   value="<?php echo isset($_POST['date_of_birth']) ? htmlspecialchars($_POST['date_of_birth']) : ''; ?>" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="gender" class="required">Gender</label>
                            <select class="form-control" id="gender" name="gender" required>
                                <option value="">Select Gender</option>
                                <option value="Male" <?php echo (isset($_POST['gender']) && $_POST['gender'] == 'Male') ? 'selected' : ''; ?>>Male</option>
                                <option value="Female" <?php echo (isset($_POST['gender']) && $_POST['gender'] == 'Female') ? 'selected' : ''; ?>>Female</option>
                                <option value="Other" <?php echo (isset($_POST['gender']) && $_POST['gender'] == 'Other') ? 'selected' : ''; ?>>Other</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="blood_type">Blood Type</label>
                            <select class="form-control" id="blood_type" name="blood_type">
                                <option value="">Select Blood Type</option>
                                <option value="A+" <?php echo (isset($_POST['blood_type']) && $_POST['blood_type'] == 'A+') ? 'selected' : ''; ?>>A+</option>
                                <option value="A-" <?php echo (isset($_POST['blood_type']) && $_POST['blood_type'] == 'A-') ? 'selected' : ''; ?>>A-</option>
                                <option value="B+" <?php echo (isset($_POST['blood_type']) && $_POST['blood_type'] == 'B+') ? 'selected' : ''; ?>>B+</option>
                                <option value="B-" <?php echo (isset($_POST['blood_type']) && $_POST['blood_type'] == 'B-') ? 'selected' : ''; ?>>B-</option>
                                <option value="AB+" <?php echo (isset($_POST['blood_type']) && $_POST['blood_type'] == 'AB+') ? 'selected' : ''; ?>>AB+</option>
                                <option value="AB-" <?php echo (isset($_POST['blood_type']) && $_POST['blood_type'] == 'AB-') ? 'selected' : ''; ?>>AB-</option>
                                <option value="O+" <?php echo (isset($_POST['blood_type']) && $_POST['blood_type'] == 'O+') ? 'selected' : ''; ?>>O+</option>
                                <option value="O-" <?php echo (isset($_POST['blood_type']) && $_POST['blood_type'] == 'O-') ? 'selected' : ''; ?>>O-</option>
                            </select>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="phone" class="required">Phone Number</label>
                            <input type="tel" class="form-control" id="phone" name="phone" 
                                   value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="email" class="required">Email Address</label>
                            <input type="email" class="form-control" id="email" name="email" 
                                   value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="password" class="required">Password</label>
                            <input type="password" class="form-control" id="password" name="password" 
                                   minlength="6" required>
                            <small class="text-muted">Minimum 6 characters</small>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="confirm_password" class="required">Confirm Password</label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" 
                                   minlength="6" required>
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="address" class="required">Address</label>
                    <textarea class="form-control" id="address" name="address" rows="2" required><?php echo isset($_POST['address']) ? htmlspecialchars($_POST['address']) : ''; ?></textarea>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="emergency_contact_name">Emergency Contact Name</label>
                            <input type="text" class="form-control" id="emergency_contact_name" name="emergency_contact_name" 
                                   value="<?php echo isset($_POST['emergency_contact_name']) ? htmlspecialchars($_POST['emergency_contact_name']) : ''; ?>">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="emergency_contact_phone">Emergency Contact Phone</label>
                            <input type="tel" class="form-control" id="emergency_contact_phone" name="emergency_contact_phone" 
                                   value="<?php echo isset($_POST['emergency_contact_phone']) ? htmlspecialchars($_POST['emergency_contact_phone']) : ''; ?>">
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="allergies">Allergies</label>
                    <textarea class="form-control" id="allergies" name="allergies" rows="2" placeholder="List any allergies (optional)"><?php echo isset($_POST['allergies']) ? htmlspecialchars($_POST['allergies']) : ''; ?></textarea>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="insurance_provider">Insurance Provider</label>
                            <input type="text" class="form-control" id="insurance_provider" name="insurance_provider" 
                                   value="<?php echo isset($_POST['insurance_provider']) ? htmlspecialchars($_POST['insurance_provider']) : ''; ?>">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="insurance_number">Insurance Number</label>
                            <input type="text" class="form-control" id="insurance_number" name="insurance_number" 
                                   value="<?php echo isset($_POST['insurance_number']) ? htmlspecialchars($_POST['insurance_number']) : ''; ?>">
                        </div>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary w-100" style="padding: 10px 12px; margin-top: 1rem;">
                    Register
                </button>
                
                <div class="text-center mt-3">
                    <p style="margin-bottom: 0.5rem;">Already have an account? <a href="login.php" style="color: #007bff; text-decoration: none;">Login here</a></p>
                    <a href="index.php" class="btn btn-outline-secondary mt-2" style="padding: 8px 20px;">Back to Home</a>
                </div>
            </form>
            
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
