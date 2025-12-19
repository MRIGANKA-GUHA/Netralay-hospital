<?php
require_once 'includes/config.php';
requireLogin();
if (!hasRole('admin')) {
    header('Location: dashboard.php');
    exit;
}
$action = $_GET['action'] ?? 'list';
$error = '';
$success = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $action == 'add') {
    $full_name = sanitize($_POST['full_name']);
    $username = sanitize($_POST['username']);
    $email = sanitize($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $specialization = sanitize($_POST['specialization']);
    $address = sanitize($_POST['address']);
    $phone = sanitize($_POST['phone']);
    $license_number = sanitize($_POST['license_number']);
    $consultation_fee = floatval($_POST['consultation_fee']);
    $department = sanitize($_POST['department']);
    $available_days = isset($_POST['available_days']) ? json_encode($_POST['available_days']) : json_encode([]);
    $available_time_start = $_POST['available_time_start'];
    $available_time_end = $_POST['available_time_end'];
    
    // Generate sequential doctor ID
    $stmt_max = $pdo->query("SELECT MAX(CAST(SUBSTRING(doctor_id, 4) AS UNSIGNED)) as max_id FROM doctors WHERE doctor_id LIKE 'DOC%'");
    $max_result = $stmt_max->fetch();
    $next_id = ($max_result['max_id'] ?? 0) + 1;
    $doctor_id = 'DOC' . str_pad($next_id, 3, '0', STR_PAD_LEFT);
    
    try {
        $stmt = $pdo->prepare("INSERT INTO users (full_name, username, email, password, role, is_active, address, phone) VALUES (?, ?, ?, ?, 'doctor', 1, ?, ?)");
        $stmt->execute([$full_name, $username, $email, $password, $address, $phone]);
        $user_id = $pdo->lastInsertId();
        $stmt2 = $pdo->prepare("INSERT INTO doctors (doctor_id, user_id, specialization, license_number, department, consultation_fee, available_days, available_time_start, available_time_end) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt2->execute([$doctor_id, $user_id, $specialization, $license_number, $department, $consultation_fee, $available_days, $available_time_start, $available_time_end]);
        $success = 'Doctor added successfully!';
        redirect('doctors.php');
    } catch (PDOException $e) {
        $error = 'Error adding doctor: ' . $e->getMessage();
    }
}

// Handle edit doctor
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $action == 'edit' && isset($_GET['id'])) {
    $doctor_id = $_GET['id'];
    $full_name = sanitize($_POST['full_name']);
    $username = sanitize($_POST['username']);
    $email = sanitize($_POST['email']);
    $specialization = sanitize($_POST['specialization']);
    $address = sanitize($_POST['address']);
    $phone = sanitize($_POST['phone']);
    $license_number = sanitize($_POST['license_number']);
    $consultation_fee = floatval($_POST['consultation_fee']);
    $department = sanitize($_POST['department']);
    $available_days = isset($_POST['available_days']) ? json_encode($_POST['available_days']) : json_encode([]);
    $available_time_start = $_POST['available_time_start'];
    $available_time_end = $_POST['available_time_end'];
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    $new_password = trim($_POST['password'] ?? '');
    try {
        // Get user_id from doctor
        $stmt = $pdo->prepare("SELECT user_id FROM doctors WHERE doctor_id = ?");
        $stmt->execute([$doctor_id]);
        $row = $stmt->fetch();
        if ($row) {
            $user_id = $row['user_id'];
            if ($new_password !== '') {
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE users SET full_name = ?, username = ?, email = ?, is_active = ?, address = ?, phone = ?, password = ? WHERE user_id = ?");
                $stmt->execute([$full_name, $username, $email, $is_active, $address, $phone, $hashed_password, $user_id]);
            } else {
                $stmt = $pdo->prepare("UPDATE users SET full_name = ?, username = ?, email = ?, is_active = ?, address = ?, phone = ? WHERE user_id = ?");
                $stmt->execute([$full_name, $username, $email, $is_active, $address, $phone, $user_id]);
            }
            $stmt2 = $pdo->prepare("UPDATE doctors SET specialization = ?, license_number = ?, department = ?, consultation_fee = ?, available_days = ?, available_time_start = ?, available_time_end = ? WHERE doctor_id = ?");
            $stmt2->execute([$specialization, $license_number, $department, $consultation_fee, $available_days, $available_time_start, $available_time_end, $doctor_id]);
            $success = 'Doctor updated successfully!';
            redirect('doctors.php');
        } else {
            $error = 'Doctor not found.';
        }
    } catch (PDOException $e) {
        $error = 'Error updating doctor: ' . $e->getMessage();
    }
}
$doctors = [];
try {
    $stmt = $pdo->query("SELECT d.*, u.full_name, u.email, u.is_active FROM doctors d JOIN users u ON d.user_id = u.user_id ORDER BY d.doctor_id ASC");
    $doctors = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = 'Error fetching doctors: ' . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Doctors - <?php echo SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
</head>
<body>
<?php include 'includes/navbar.php'; ?>
<div class="container-fluid">
    <div class="dashboard-wrapper">
        <main class="dashboard-main">
            <?php if ($action == 'list'): ?>
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-0 pb-2 mb-3 border-bottom">
                <h1 class="dashboard-title display-6 fw-bold mb-0 d-flex align-items-center gap-2">
                    Doctors Management
                </h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <a href="doctors.php?action=add" class="btn btn-primary" style="padding: 0.75rem 1.25rem;">
                         Add Doctor
                    </a>
                </div>
            </div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="alert alert-danger"> <?php echo $error; ?> </div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="alert alert-success"> <?php echo $success; ?> </div>
            <?php endif; ?>
            
            <?php if ($action == 'add' || ($action == 'edit' && isset($_GET['id']))): ?>
                <?php
                $edit_doctor = null;
                if ($action == 'edit' && isset($_GET['id'])) {
                    $edit_id = $_GET['id'];
                    $stmt = $pdo->prepare("SELECT d.*, u.full_name, u.username, u.email, u.is_active, u.phone, u.address FROM doctors d JOIN users u ON d.user_id = u.user_id WHERE d.doctor_id = ?");
                    $stmt->execute([$edit_id]);
                    $edit_doctor = $stmt->fetch();
                    $edit_doctor['available_days'] = isset($edit_doctor['available_days']) ? json_decode($edit_doctor['available_days'], true) : [];
                }
                ?>
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-0 pb-2 mb-4">
                    <h1 class="dashboard-title display-6 fw-bold mb-0 d-flex align-items-center gap-3">
                        <?php echo $action == 'add' ? 'Add New Doctor' : 'Edit Doctor'; ?>
                    </h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <a href="doctors.php" class="btn btn-secondary" style="padding: 0.75rem 1.25rem;">
                            <i class="fas fa-arrow-left"></i> Back to List
                        </a>
                    </div>
                </div>
                
                <?php if ($error): ?>
                    <div class="alert alert-danger alert-modern">
                        <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                    </div>
                <?php endif; ?>
                
                <div class="card modern-form-card">
                    <div class="card-body p-4">
                        <form method="POST" class="modern-form">
                            <!-- Personal Information Section -->
                            <div class="form-section mb-4">
                                <h6 class="text-uppercase text-muted fw-bold mb-3" style="font-size: 1.20rem; letter-spacing: 1px;">
                                    Personal Information
                                </h6>
                                <div class="row g-4">
                                    <div class="col-md-6">
                                        <div class="form-group-modern">
                                            <label for="full_name" class="form-label-modern" style="font-size: 1.00rem;">
                                                Full Name <span class="text-danger">*</span>
                                            </label>
                                            <input type="text" class="form-control form-control-modern" id="full_name" name="full_name" 
                                                   value="<?php echo $edit_doctor ? htmlspecialchars($edit_doctor['full_name']) : ''; ?>" 
                                                   placeholder="Enter full name" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group-modern">
                                            <label for="username" class="form-label-modern" style="font-size: 1.00rem;">
                                                Username <span class="text-danger">*</span>
                                            </label>
                                            <input type="text" class="form-control form-control-modern" id="username" name="username" 
                                                   value="<?php echo $edit_doctor ? htmlspecialchars($edit_doctor['username']) : ''; ?>" 
                                                   placeholder="Enter username" required>
                                        </div>
                                    </div>
                                </div>

                                <div class="row g-4">
                                    <div class="col-md-6">
                                        <div class="form-group-modern">
                                            <label for="email" class="form-label-modern" style="font-size: 1.00rem;">
                                                Email <span class="text-danger">*</span>
                                            </label>
                                            <input type="email" class="form-control form-control-modern" id="email" name="email" 
                                                   value="<?php echo $edit_doctor ? htmlspecialchars($edit_doctor['email']) : ''; ?>" 
                                                   placeholder="Enter email address" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group-modern">
                                            <label for="password" class="form-label-modern" style="font-size: 1.00rem;">
                                                <?php echo $action == 'edit' ? 'Change Password' : 'Password'; ?> <?php echo $action == 'add' ? '<span class="text-danger">*</span>' : ''; ?>
                                            </label>
                                            <input type="password" class="form-control form-control-modern" id="password" name="password" 
                                                   placeholder="<?php echo $action == 'edit' ? 'Leave blank to keep current password' : 'Enter password'; ?>" 
                                                   <?php echo $action == 'add' ? 'required' : ''; ?>>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Professional Information Section -->
                            <div class="form-section mb-4">
                                <h6 class="text-uppercase text-muted fw-bold mb-3" style="font-size: 1.20rem; letter-spacing: 1px;">
                                    Professional Information
                                </h6>
                                <div class="row g-4">
                                    <div class="col-md-6">
                                        <div class="form-group-modern">
                                            <label for="specialization" class="form-label-modern" style="font-size: 1.00rem;">
                                                Specialization <span class="text-danger">*</span>
                                            </label>
                                            <input type="text" class="form-control form-control-modern" id="specialization" name="specialization" 
                                                   value="<?php echo $edit_doctor ? htmlspecialchars($edit_doctor['specialization']) : ''; ?>" 
                                                   placeholder="Enter specialization" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group-modern">
                                            <label for="department" class="form-label-modern" style="font-size: 1.00rem;">
                                                Department
                                            </label>
                                            <input type="text" class="form-control form-control-modern" id="department" name="department" 
                                                   value="<?php echo $edit_doctor ? htmlspecialchars($edit_doctor['department']) : ''; ?>" 
                                                   placeholder="Enter department">
                                        </div>
                                    </div>
                                </div>

                                <div class="row g-4">
                                    <div class="col-md-6">
                                        <div class="form-group-modern">
                                            <label for="license_number" class="form-label-modern" style="font-size: 1.00rem;">
                                                License Number <span class="text-danger">*</span>
                                            </label>
                                            <input type="text" class="form-control form-control-modern" id="license_number" name="license_number" 
                                                   value="<?php echo $edit_doctor ? htmlspecialchars($edit_doctor['license_number']) : ''; ?>" 
                                                   placeholder="Enter license number" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group-modern">
                                            <label for="consultation_fee" class="form-label-modern" style="font-size: 1.00rem;">
                                                Consultation Fee <span class="text-danger">*</span>
                                            </label>
                                            <input type="number" step="0.01" class="form-control form-control-modern" id="consultation_fee" name="consultation_fee" 
                                                   value="<?php echo $edit_doctor ? htmlspecialchars($edit_doctor['consultation_fee']) : ''; ?>" 
                                                   placeholder="Enter consultation fee" required>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Availability Section -->
                            <div class="form-section mb-4">
                                <h6 class="text-uppercase text-muted fw-bold mb-3" style="font-size: 1.20rem; letter-spacing: 1px;">
                                    Availability
                                </h6>
                                <div class="row g-4">
                                    <div class="col-12">
                                        <div class="form-group-modern">
                                            <label class="form-label-modern" style="font-size: 1.00rem;">Available Days</label>
                                            <div class="d-flex flex-wrap gap-3 mt-2">
                                                <?php
                                                $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
                                                $selected_days = $edit_doctor ? $edit_doctor['available_days'] : [];
                                                foreach ($days as $day): ?>
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" name="available_days[]" id="day_<?php echo $day; ?>" value="<?php echo $day; ?>" <?php echo in_array($day, $selected_days) ? 'checked' : ''; ?>>
                                                        <label class="form-check-label" for="day_<?php echo $day; ?>"><?php echo $day; ?></label>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row g-4">
                                    <div class="col-md-6">
                                        <div class="form-group-modern">
                                            <label for="available_time_start" class="form-label-modern" style="font-size: 1.00rem;">
                                                Available Time Start
                                            </label>
                                            <input type="time" class="form-control form-control-modern" id="available_time_start" name="available_time_start" 
                                                   value="<?php echo $edit_doctor ? htmlspecialchars($edit_doctor['available_time_start']) : ''; ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group-modern">
                                            <label for="available_time_end" class="form-label-modern" style="font-size: 1.00rem;">
                                                Available Time End
                                            </label>
                                            <input type="time" class="form-control form-control-modern" id="available_time_end" name="available_time_end" 
                                                   value="<?php echo $edit_doctor ? htmlspecialchars($edit_doctor['available_time_end']) : ''; ?>">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Contact Information Section -->
                            <div class="form-section mb-4">
                                <h6 class="text-uppercase text-muted fw-bold mb-3" style="font-size: 1.20rem; letter-spacing: 1px;">
                                    Contact Information
                                </h6>
                                <div class="row g-4">
                                    <div class="col-md-6">
                                        <div class="form-group-modern">
                                            <label for="phone" class="form-label-modern" style="font-size: 1.00rem;">
                                                Phone Number <span class="text-danger">*</span>
                                            </label>
                                            <input type="text" class="form-control form-control-modern" id="phone" name="phone" 
                                                   value="<?php echo $edit_doctor ? htmlspecialchars($edit_doctor['phone']) : ''; ?>" 
                                                   placeholder="Enter phone number" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group-modern">
                                            <label for="address" class="form-label-modern" style="font-size: 1.00rem;">
                                                Address <span class="text-danger">*</span>
                                            </label>
                                            <input type="text" class="form-control form-control-modern" id="address" name="address" 
                                                   value="<?php echo $edit_doctor ? htmlspecialchars($edit_doctor['address']) : ''; ?>" 
                                                   placeholder="Enter full address" required>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <?php if ($action == 'edit'): ?>
                            <!-- Status Section -->
                            <div class="form-section mb-4">
                                <h6 class="text-uppercase text-muted fw-bold mb-3" style="font-size: 1.20rem; letter-spacing: 1px;">
                                    Status
                                </h6>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" <?php echo ($edit_doctor && $edit_doctor['is_active']) ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="is_active">Active</label>
                                </div>
                            </div>
                            <?php endif; ?>

                            <!-- Form Actions -->
                            <div class="form-actions mt-5 pt-4 border-top">
                                <button type="submit" class="btn btn-primary btn-lg px-5" style="padding: 0.75rem 1.25rem;">
                                     <?php echo $action == 'edit' ? 'Update Doctor' : 'Add Doctor'; ?>
                                </button>
                                <a href="doctors.php" class="btn btn-secondary btn-lg px-5 ms-2" style="padding: 0.75rem 1.25rem;">
                                     Cancel
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            <?php else: ?>
                <div class="card">
                    <div class="card-header">
                        <h5>Doctors List (<?php echo count($doctors); ?> total)</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle text-center">
                                <thead class="table-light">
                                    <tr>
                                        <th>Doctor ID</th>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Specialization</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($doctors as $doctor): ?>
                                    <tr>
                                        <td><strong><?php echo htmlspecialchars($doctor['doctor_id']); ?></strong></td>
                                        <td><?php echo htmlspecialchars($doctor['full_name']); ?></td>
                                        <td><?php echo htmlspecialchars($doctor['email']); ?></td>
                                        <td><?php echo htmlspecialchars($doctor['specialization']); ?></td>
                                        <td>
                                            <?php if ($doctor['is_active']): ?>
                                                <span class="badge bg-success">Active</span>
                                            <?php else: ?>
                                                <span class="badge bg-danger">Inactive</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <a href="doctors.php?action=edit&id=<?php echo $doctor['doctor_id']; ?>" class="btn btn-warning btn-sm me-1" title="Edit">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </main>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
