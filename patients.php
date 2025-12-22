<?php
require_once 'includes/config.php';
requireLogin();

$action = $_GET['action'] ?? 'list';
$success = '';
$error = '';
$view_patient = null;

// Handle form submissions

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['bulk_delete'])) {
        // Bulk delete selected patients
        if (!empty($_POST['selected_patients'])) {
            $ids = array_map('strval', $_POST['selected_patients']);
            if (!empty($ids)) {
                $placeholders = implode(',', array_fill(0, count($ids), '?'));
                try {
                    // First delete all appointments associated with these patients
                    $stmt = $pdo->prepare("DELETE FROM appointments WHERE patient_id IN ($placeholders)");
                    $stmt->execute($ids);
                    // Then delete the patients
                    $stmt = $pdo->prepare("DELETE FROM patients WHERE patient_id IN ($placeholders)");
                    $stmt->execute($ids);
                    showAlert(count($ids) . ' patient(s) and their appointments deleted successfully!', 'success');
                    redirect('patients.php');
                } catch (PDOException $e) {
                    $error = 'Error deleting patients: ' . $e->getMessage();
                }
            }
        } else {
            $error = 'Please select at least one patient to delete.';
        }
    } elseif ($action == 'add') {
        // Add new patient
        $first_name = sanitize($_POST['first_name']);
        $last_name = sanitize($_POST['last_name']);
        $date_of_birth = sanitize($_POST['date_of_birth']);
        $gender = sanitize($_POST['gender']);
        $phone = sanitize($_POST['phone']);
        $email = sanitize($_POST['email']);
        $address = sanitize($_POST['address']);
        $emergency_contact_name = sanitize($_POST['emergency_contact_name']);
        $emergency_contact_phone = sanitize($_POST['emergency_contact_phone']);
        $blood_type = sanitize($_POST['blood_type']);
        $allergies = sanitize($_POST['allergies']);
        $insurance_number = sanitize($_POST['insurance_number']);
        $insurance_provider = sanitize($_POST['insurance_provider']);

        if (empty($first_name) || empty($last_name) || empty($date_of_birth) || empty($gender) || empty($phone) || empty($address)) {
            $error = 'Please fill in all required fields';
        } else {
            try {
                $patient_id = generateId('NET', 'patients', 'patient_id', 6);
                $stmt = $pdo->prepare("INSERT INTO patients (patient_id, first_name, last_name, date_of_birth, gender, phone, email, address, emergency_contact_name, emergency_contact_phone, blood_type, allergies, insurance_number, insurance_provider, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$patient_id, $first_name, $last_name, $date_of_birth, $gender, $phone, $email, $address, $emergency_contact_name, $emergency_contact_phone, $blood_type, $allergies, $insurance_number, $insurance_provider, $_SESSION['user_id']]);
                showAlert('Patient registered successfully! Patient ID: ' . $patient_id, 'success');
                redirect('patients.php');
            } catch (PDOException $e) {
                $error = 'Error adding patient: ' . $e->getMessage();
            }
        }
    } elseif ($action == 'edit' && isset($_GET['id'])) {
        // Edit existing patient
        $patient_id = $_GET['id'];
        $first_name = sanitize($_POST['first_name']);
        $last_name = sanitize($_POST['last_name']);
        $date_of_birth = sanitize($_POST['date_of_birth']);
        $gender = sanitize($_POST['gender']);
        $phone = sanitize($_POST['phone']);
        $email = sanitize($_POST['email']);
        $address = sanitize($_POST['address']);
        $emergency_contact_name = sanitize($_POST['emergency_contact_name']);
        $emergency_contact_phone = sanitize($_POST['emergency_contact_phone']);
        $blood_type = sanitize($_POST['blood_type']);
        $allergies = sanitize($_POST['allergies']);
        $insurance_number = sanitize($_POST['insurance_number']);
        $insurance_provider = sanitize($_POST['insurance_provider']);

        if (empty($first_name) || empty($last_name) || empty($date_of_birth) || empty($gender) || empty($phone) || empty($address)) {
            $error = 'Please fill in all required fields';
        } else {
            try {
                $stmt = $pdo->prepare("UPDATE patients SET first_name = ?, last_name = ?, date_of_birth = ?, gender = ?, phone = ?, email = ?, address = ?, emergency_contact_name = ?, emergency_contact_phone = ?, blood_type = ?, allergies = ?, insurance_number = ?, insurance_provider = ?, updated_at = NOW() WHERE patient_id = ?");
                $stmt->execute([$first_name, $last_name, $date_of_birth, $gender, $phone, $email, $address, $emergency_contact_name, $emergency_contact_phone, $blood_type, $allergies, $insurance_number, $insurance_provider, $patient_id]);
                showAlert('Patient updated successfully!', 'success');
                redirect('patients.php');
            } catch (PDOException $e) {
                $error = 'Error updating patient: ' . $e->getMessage();
            }
        }
    } elseif (isset($_POST['delete_patient']) && $action == 'delete' && isset($_GET['id'])) {
        // Delete patient
        $patient_id = $_GET['id'];
        try {
            $stmt = $pdo->prepare("DELETE FROM patients WHERE patient_id = ?");
            $stmt->execute([$patient_id]);
            showAlert('Patient deleted successfully!', 'success');
            redirect('patients.php');
        } catch (PDOException $e) {
            $error = 'Error deleting patient: ' . $e->getMessage();
        }
    }
}

// Get patient data for editing
if (($action == 'edit' || $action == 'view') && isset($_GET['id'])) {
    $patient_id = $_GET['id'];
    try {
        $stmt = $pdo->prepare("SELECT * FROM patients WHERE patient_id = ?");
        $stmt->execute([$patient_id]);
        $patient = $stmt->fetch();
        if (!$patient) {
            showAlert('Patient not found', 'error');
            redirect('patients.php');
        }
        if ($action == 'view') {
            $view_patient = $patient;
            // Appointment summary for details view
            $appt_stats = [
                'total' => 0,
                'scheduled' => 0,
                'last' => null,
                'next' => null
            ];
            try {
                // Total appointments
                $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM appointments WHERE patient_id = ?");
                $stmt->execute([$patient_id]);
                $appt_stats['total'] = $stmt->fetch()['total'];
                // Scheduled appointments
                $stmt = $pdo->prepare("SELECT COUNT(*) as scheduled FROM appointments WHERE patient_id = ? AND status = 'scheduled'");
                $stmt->execute([$patient_id]);
                $appt_stats['scheduled'] = $stmt->fetch()['scheduled'];
                // Last appointment
                $stmt = $pdo->prepare("SELECT * FROM appointments WHERE patient_id = ? ORDER BY appointment_date DESC, appointment_time DESC LIMIT 1");
                $stmt->execute([$patient_id]);
                $appt_stats['last'] = $stmt->fetch();
                // Next appointment
                $stmt = $pdo->prepare("SELECT * FROM appointments WHERE patient_id = ? AND appointment_date >= CURDATE() AND status = 'scheduled' ORDER BY appointment_date ASC, appointment_time ASC LIMIT 1");
                $stmt->execute([$patient_id]);
                $appt_stats['next'] = $stmt->fetch();
            } catch (PDOException $e) {}
            $view_patient['appt_stats'] = $appt_stats;

            // Fetch medical history for this patient
            try {
                $stmt = $pdo->prepare("SELECT mh.*, d.specialization, d.doctor_id AS doc_id, u.full_name AS doctor_name FROM medical_history mh JOIN doctors d ON mh.doctor_id = d.doctor_id JOIN users u ON d.user_id = u.user_id WHERE mh.patient_id = ? ORDER BY mh.visit_date ASC, mh.id ASC");
                $stmt->execute([$patient_id]);
                $view_patient['medical_history'] = $stmt->fetchAll();
            } catch (PDOException $e) {
                $view_patient['medical_history'] = [];
            }
        }
    } catch (PDOException $e) {
        showAlert('Error fetching patient data', 'error');
        redirect('patients.php');
    }
}

// Search and filter patients
$search = $_GET['search'] ?? '';
$page = (int)($_GET['page'] ?? 1);
$limit = 20;
$offset = ($page - 1) * $limit;

$where_conditions = [];
$params = [];

if ($search) {
    $where_conditions[] = "(patient_id LIKE ? OR first_name LIKE ? OR last_name LIKE ? OR phone LIKE ? OR email LIKE ?)";
    $search_param = "%$search%";
    $params = array_merge($params, [$search_param, $search_param, $search_param, $search_param, $search_param]);
}

$where_clause = !empty($where_conditions) ? "WHERE " . implode(" AND ", $where_conditions) : "";

// Get total count for pagination
try {
    $count_stmt = $pdo->prepare("SELECT COUNT(*) as total FROM patients $where_clause");
    $count_stmt->execute($params);
    $total_patients = $count_stmt->fetch()['total'];
    $total_pages = ceil($total_patients / $limit);
    
    // Get patients
    $stmt = $pdo->prepare("SELECT * FROM patients $where_clause ORDER BY patient_id DESC LIMIT $limit OFFSET $offset");
    $stmt->execute($params);
    $patients = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = 'Error fetching patients: ' . $e->getMessage();
    $patients = [];
    $total_patients = 0;
    $total_pages = 0;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patients - <?php echo SITE_NAME; ?></title>
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
                <!-- Patients List -->
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-0 pb-2 mb-3 border-bottom">
                    <h1 class="dashboard-title display-6 fw-bold mb-0 d-flex align-items-center gap-2">
                        Patients Management
                    </h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <?php if (!hasRole('doctor')): ?>
                        <a href="patients.php?action=add" class="btn btn-primary" style="padding: 0.75rem 1.25rem;">
                            Add New Patient
                        </a>
                        <button type="button" class="btn btn-danger" style="background-color:#e53935; border:none; font-weight:bold; margin-left:8px; padding: 0.75rem 1.25rem;" onclick="deleteSelectedPatients()">
                            Delete Patient
                        </button>
                        
                        <?php endif; ?>
                    </div>
                </div>

                <?php displayAlert(); ?>

                <!-- Search and Filter -->
                <div class="card mb-4">
                    <div class="card-body">
                        <form method="GET" class="row g-3">
                            <div class="col-md-8">
                                <input type="text" class="form-control" name="search" placeholder="Search by Patient ID, Name, Phone, or Email..." value="<?php echo htmlspecialchars($search); ?>">
                            </div>
                            <div class="col-md-4">
                                <button type="submit" class="btn btn-primary" style="padding: 0.75rem 1.25rem;">
                                    Search
                                </button>
                                <?php if ($search): ?>
                                <a href="patients.php" class="btn btn-secondary" style="padding: 0.75rem 1.25rem;">
                                     Clear
                                </a>
                                <?php endif; ?>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Patients Table -->
                <div class="card">
                    <div class="card-header">
                        <h5>Patients List (<?php echo number_format($total_patients); ?> total)</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($patients)): ?>
                            <div class="text-center py-4">
                                <i class="fas fa-users fa-3x text-muted mb-3"></i>
                                <h5 class="text-muted">No patients found</h5>
                                <?php if ($search): ?>
                                    <p>Try adjusting your search criteria</p>
                                <?php else: ?>
                                    <p>Start by <a href="patients.php?action=add">adding your first patient</a></p>
                                <?php endif; ?>
                            </div>
                        <?php else: ?>
                            <form method="POST" id="patientsTableForm" action="patients.php">
                                <input type="hidden" name="bulk_delete" value="1">
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th><input type="checkbox" id="selectAll" onclick="toggleSelectAll(this)"></th>
                                                <th>Patient ID</th>
                                                <th>Name</th>
                                                <th>Age/Gender</th>
                                                <th>Phone</th>
                                                <th>Email</th>
                                                <th>Blood Type</th>
                                                <th>Registered</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($patients as $patient): ?>
                                            <tr>
                                                <td>
                                                    <input type="checkbox" name="selected_patients[]" value="<?php echo htmlspecialchars($patient['patient_id']); ?>">
                                                </td>
                                                <td>
                                                    <strong><?php echo htmlspecialchars($patient['patient_id']); ?></strong>
                                                </td>
                                            <td>
                                                <strong><?php echo htmlspecialchars($patient['first_name'] . ' ' . $patient['last_name']); ?></strong>
                                                <?php if ($patient['allergies']): ?>
                                                    <br><small class="text-danger"><i class="fas fa-exclamation-triangle"></i> Has allergies</small>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php 
                                                $age = date_diff(date_create($patient['date_of_birth']), date_create('today'))->y;
                                                echo $age . 'y / ' . htmlspecialchars($patient['gender']);
                                                ?>
                                            </td>
                                            <td><?php echo htmlspecialchars($patient['phone']); ?></td>
                                            <td><?php echo htmlspecialchars($patient['email']); ?></td>
                                            <td>
                                                <?php if ($patient['blood_type']): ?>
                                                    <span class="badge bg-primary"><?php echo htmlspecialchars($patient['blood_type']); ?></span>
                                                <?php else: ?>
                                                    <span class="text-muted">-</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo formatDate($patient['created_at']); ?></td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <a href="patients.php?action=view&id=<?php echo urlencode($patient['patient_id']); ?>" class="btn btn-info btn-action-icon" title="View Details">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <?php if (!hasRole('doctor')): ?>
                                                    <a href="patients.php?action=edit&id=<?php echo urlencode($patient['patient_id']); ?>" class="btn btn-warning btn-action-icon" title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <a href="appointments.php?action=add&patient_id=<?php echo urlencode($patient['patient_id']); ?>" class="btn btn-success btn-action-icon" title="Schedule Appointment">
                                                        <i class="fas fa-calendar-plus"></i>
                                                    </a>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </form>
                            </div>

                            <!-- Pagination -->
                            <?php if ($total_pages > 1): ?>
                            <nav aria-label="Patients pagination">
                                <ul class="pagination justify-content-center">
                                    <?php if ($page > 1): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?page=<?php echo ($page - 1); ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>">Previous</a>
                                        </li>
                                    <?php endif; ?>
                                    
                                    <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                                        <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>">
                                            <a class="page-link" href="?page=<?php echo $i; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>"><?php echo $i; ?></a>
                                        </li>
                                    <?php endfor; ?>
                                    
                                    <?php if ($page < $total_pages): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?page=<?php echo ($page + 1); ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>">Next</a>
                                        </li>
                                    <?php endif; ?>
                                </ul>
                            </nav>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <?php elseif ($action == 'view' && $view_patient): ?>

                <!-- View Patient Details -->
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-0 pb-2 mb-3 border-bottom">
                    <h1 class="dashboard-title display-6 fw-bold mb-0">
                         Patient Details
                    </h1>
                    <div class="btn-toolbar mb-2 mb-md-0 gap-2">
                        <button type="button" class="btn btn-success" onclick="printPatientDetails()">
                            <i class="fas fa-print"></i> Print Details
                        </button>
                        <a href="patients.php" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to List
                        </a>
                    </div>
                </div>
                
                <div id="printableArea">
                <div class="card shadow-lg border-0" style="border-radius: 1rem;">
                    <div class="card-header bg-primary text-white" style="border-radius: 1rem 1rem 0 0; background: linear-gradient(135deg, #007bff 0%, #6610f2 100%) !important;">
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center gap-3">
                                <h4 class="mb-0 fw-bold" style="font-family: 'Poppins', 'Segoe UI', sans-serif; font-size: 1.60rem;"><?php echo htmlspecialchars($view_patient['first_name'] . ' ' . $view_patient['last_name']); ?></h4>
                                <span class="badge bg-light text-dark" style="font-size: 0.9rem;"><?php echo htmlspecialchars($view_patient['patient_id']); ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-4" style="background: #fff; font-family: 'Poppins', 'Segoe UI', sans-serif;">
                        <div class="row g-4">
                            <div class="col-md-6">
                                <h6 class="text-uppercase text-muted fw-bold mb-3" style="font-size: 1.20rem; letter-spacing: 1px;">Personal Information</h6>
                                <div class="mb-2" style="font-size: 1.00rem;"><span class="text-secondary">Date of Birth:</span> <span class="fw-semibold text-dark ms-2" style="font-size: 0.95rem;"><?php echo htmlspecialchars($view_patient['date_of_birth']); ?></span></div>

                                <div class="mb-2" style="font-size: 1.00rem;"><span class="text-secondary">Gender:</span> <span class="fw-semibold text-dark ms-2" style="font-size: 0.95rem;"><?php echo htmlspecialchars($view_patient['gender']); ?></span></div>

                                <div class="mb-2" style="font-size: 1.00rem;"><span class="text-secondary">Phone:</span> <span class="fw-semibold text-dark ms-2" style="font-size: 0.95rem;"><?php echo htmlspecialchars($view_patient['phone']); ?></span></div>

                                <div class="mb-2" style="font-size: 1.00rem;"><span class="text-secondary">Email:</span> <span class="fw-semibold text-dark ms-2" style="font-size: 0.95rem;"><?php echo htmlspecialchars($view_patient['email'] ?: '-'); ?></span></div>

                                <div class="mb-2" style="font-size: 1.00rem;"><span class="text-secondary">Address:</span> <span class="fw-semibold text-dark ms-2" style="font-size: 0.95rem;"><?php echo htmlspecialchars($view_patient['address']); ?></span></div>
                                
                                <hr class="my-3">
                                <h6 class="text-uppercase text-muted fw-bold mb-3" style="font-size: 1.20rem; letter-spacing: 1px;">Appointment Summary</h6>
                                <div class="d-flex flex-wrap gap-3 align-items-center">
                                    <div class="text-center">

                                        <div class="badge rounded-pill bg-primary px-3 py-2" style="font-size: 0.95rem;"><?php echo (int)($view_patient['appt_stats']['total'] ?? 0); ?></div>
                                        <div class="small text-muted mt-1" style="font-size: 0.95rem;">Total</div>
                                    </div>
                                    <div class="text-center">
                                        <div class="badge rounded-pill bg-<?php echo ((int)($view_patient['appt_stats']['scheduled'] ?? 0) > 0) ? 'success' : 'secondary'; ?> px-3 py-2" style="font-size: 0.95rem;"><?php echo ((int)($view_patient['appt_stats']['scheduled'] ?? 0) > 0) ? 'Yes' : 'No'; ?></div>
                                        <div class="small text-muted mt-1" style="font-size: 0.95rem;">Scheduled</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-uppercase text-muted fw-bold mb-3" style="font-size: 1.20rem; letter-spacing: 1px;">Medical Information</h6>
                                <div class="mb-2"><span class="text-secondary" style="font-size: 1.00rem;">Blood Type:</span> <span class="badge bg-danger ms-2" style="font-size: 0.95rem;"><?php echo htmlspecialchars($view_patient['blood_type'] ?: '-'); ?></span></div>
                                <div class="mb-2" style="font-size: 1.00rem;"><span class="text-secondary" style="font-size: 1.00rem;">Allergies:</span> <span class="fw-semibold text-dark ms-2" style="font-size: 0.95rem;"><?php echo htmlspecialchars($view_patient['allergies'] ?: 'None'); ?></span></div>
                                
                                <hr class="my-3">
                                <h6 class="text-uppercase text-muted fw-bold mb-3" style="font-size: 1.20rem; letter-spacing: 1px;">Emergency Contact</h6>
                                <div class="mb-2" style="font-size: 1.00rem;"><span class="text-secondary">Name:</span> <span class="fw-semibold text-dark ms-2" style="font-size: 0.95rem;"><?php echo htmlspecialchars($view_patient['emergency_contact_name'] ?: '-'); ?></span></div>
                                <div class="mb-2" style="font-size: 1.00rem;"><span class="text-secondary">Phone:</span> <span class="fw-semibold text-dark ms-2" style="font-size: 0.95rem;"><?php echo htmlspecialchars($view_patient['emergency_contact_phone'] ?: '-'); ?></span></div>
                                
                                <hr class="my-3">
                                <h6 class="text-uppercase text-muted fw-bold mb-3" style="font-size: 1.20rem; letter-spacing: 1px;">Insurance Details</h6>
                                <div class="mb-2" style="font-size: 1.00rem;"><span class="text-secondary">Provider:</span> <span class="fw-semibold text-dark ms-2" style="font-size: 0.95rem;"><?php echo htmlspecialchars($view_patient['insurance_provider'] ?: '-'); ?></span></div>
                                <div class="mb-2" style="font-size: 1.00rem;"><span class="text-secondary">Number:</span> <span class="fw-semibold text-dark ms-2" style="font-size: 0.95rem;"><?php echo htmlspecialchars($view_patient['insurance_number'] ?: '-'); ?></span></div>
                                <div class="mb-2" style="font-size: 1.00rem;"><span class="text-secondary">Registered:</span> <span class="fw-semibold text-dark ms-2" style="font-size: 0.95rem;"><?php echo formatDate($view_patient['created_at']); ?></span></div>
                            </div>
                        </div>
                        
                        <hr class="my-4">
                        <h6 class="text-uppercase text-muted fw-bold mb-3" style="font-size: 1.20rem; letter-spacing: 1px;">Appointment History</h6>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="p-3 rounded-3" style="background: #f8f9fa;">
                                    <div class="small text-muted mb-1" style="font-size: 1.00rem;">Last Appointment</div>
                                    <?php if (!empty($view_patient['appt_stats']['last'])): ?>
                                        <div class="fw-semibold" style="font-size: 0.95rem;"><?php echo formatDate($view_patient['appt_stats']['last']['appointment_date']); ?> at <?php echo date('g:i A', strtotime($view_patient['appt_stats']['last']['appointment_time'])); ?></div>
                                        <span class="badge bg-<?php echo $view_patient['appt_stats']['last']['status'] == 'completed' ? 'success' : 'warning'; ?>"><?php echo ucfirst($view_patient['appt_stats']['last']['status']); ?></span>
                                    <?php else: ?>
                                        <span class="text-muted" style="font-size: 0.95rem;">No previous appointments</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="p-3 rounded-3" style="background: #f8f9fa;">
                                    <div class="small text-muted mb-1" style="font-size: 1.00rem;">Next Appointment</div>
                                    <?php 
                                    $next_doc_appt = null;
                                    if (!empty($view_patient['medical_history'])) {
                                        foreach ($view_patient['medical_history'] as $history) {
                                            if (!empty($history['follow_up_date']) && $history['follow_up_date'] !== '0000-00-00' && $history['follow_up_date'] >= date('Y-m-d')) {
                                                if ($next_doc_appt === null || $history['follow_up_date'] < $next_doc_appt) {
                                                    $next_doc_appt = $history['follow_up_date'];
                                                }
                                            }
                                        }
                                    }
                                    ?>
                                    <?php if ($next_doc_appt): ?>
                                        <div class="fw-semibold" style="font-size: 0.95rem;"><?php echo formatDate($next_doc_appt); ?></div>
                                        <span class="badge bg-info">Set by Doctor</span>
                                    <?php elseif (!empty($view_patient['appt_stats']['next'])): ?>
                                        <div class="fw-semibold" style="font-size: 0.95rem;"><?php echo formatDate($view_patient['appt_stats']['next']['appointment_date']); ?> at <?php echo date('g:i A', strtotime($view_patient['appt_stats']['next']['appointment_time'])); ?></div>
                                        <span class="badge bg-success"><?php echo ucfirst($view_patient['appt_stats']['next']['status']); ?></span>
                                    <?php else: ?>
                                        <span class="text-muted" style="font-size: 0.95rem;">No upcoming appointment</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Medical History Section -->
                <div class="card shadow-lg border-0 mt-4" style="border-radius: 1rem;">
                    <div class="card-header text-white" style="background: linear-gradient(135deg, #6610f2 0%, #e83e8c 100%); border-radius: 1rem 1rem 0 0;">
                        <h5 class="mb-0 text-black" style="font-family: 'Poppins', 'Segoe UI', sans-serif; font-size: 1.1rem;">Medical History</h5>
                    </div>
                    <div class="card-body p-4" style="background: #fff; font-family: 'Poppins', 'Segoe UI', sans-serif;">
                        <?php if (!empty($view_patient['medical_history'])): ?>
                            <?php foreach ($view_patient['medical_history'] as $index => $history): ?>
                                <div class="p-3 mb-3 rounded-3" style="background: #f8f9fa; border-left: 4px solid #6610f2;">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="badge bg-primary" style="font-size: 0.85rem;">Visit #<?php echo $index + 1; ?></span>
                                        <span class="text-muted" style="font-size: 0.85rem;"><?php echo htmlspecialchars($history['visit_date']); ?></span>
                                    </div>
                                    <div class="mb-2" style="font-size: 0.9rem;"><i class="fas fa-user-md me-2 text-primary"></i><span class="fw-semibold"><?php echo htmlspecialchars($history['doctor_name']); ?></span> <span class="badge bg-light text-dark border ms-1"><?php echo htmlspecialchars($history['specialization']); ?></span></div>
                                    <div class="mb-2" style="font-size: 0.9rem;"><span class="text-secondary">Diagnosis:</span> <span class="fw-medium text-dark"><?php echo nl2br(htmlspecialchars($history['diagnosis'])); ?></span></div>
                                    <?php if (!empty($history['treatment'])): ?>
                                        <div class="mb-2" style="font-size: 0.9rem;"><span class="text-secondary">Treatment:</span> <span class="text-dark"><?php echo nl2br(htmlspecialchars($history['treatment'])); ?></span></div>
                                    <?php endif; ?>
                                    <?php if (!empty($history['prescribed_medications'])): ?>
                                        <div class="mb-2" style="font-size: 0.9rem;"><span class="text-secondary">Medications:</span> <span class="text-dark"><?php echo nl2br(htmlspecialchars($history['prescribed_medications'])); ?></span></div>
                                    <?php endif; ?>
                                    <?php if (!empty($history['notes'])): ?>
                                        <div class="mb-0" style="font-size: 0.9rem;"><span class="text-secondary">Notes:</span> <span class="text-dark fst-italic"><?php echo nl2br(htmlspecialchars($history['notes'])); ?></span></div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="text-center py-4">
                                <i class="fas fa-file-medical fa-3x text-muted mb-3"></i>
                                <p class="text-muted mb-0" style="font-size: 0.95rem;">No medical history found for this patient.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                </div><!-- End printableArea -->
                <?php elseif ($action == 'add' || $action == 'edit'): ?>
                <!-- Add/Edit Patient Form -->
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-0 pb-2 mb-4">
                    <h1 class="dashboard-title display-6 fw-bold mb-0 d-flex align-items-center gap-3">   
                        <?php echo $action == 'add' ? 'Add New Patient' : 'Edit Patient'; ?>
                    </h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <a href="patients.php" class="btn btn-secondary" style="padding: 0.75rem 1.25rem;">
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
                                            <label for="first_name" class="form-label-modern" style="font-size: 1.00rem;">
                                                 First Name <span class="text-danger">*</span>
                                            </label>
                                            <input type="text" class="form-control form-control-modern" id="first_name" name="first_name" 
                                                   value="<?php echo htmlspecialchars($patient['first_name'] ?? ''); ?>" 
                                                   placeholder="Enter first name" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group-modern">
                                            <label for="last_name" class="form-label-modern" style="font-size: 1.00rem;">
                                                Last Name <span class="text-danger">*</span>
                                            </label>
                                            <input type="text" class="form-control form-control-modern" id="last_name" name="last_name" 
                                                   value="<?php echo htmlspecialchars($patient['last_name'] ?? ''); ?>" 
                                                   placeholder="Enter last name" required>
                                        </div>
                                    </div>
                                </div>

                                <div class="row g-4 mt-2">
                                    <div class="col-md-4">
                                        <div class="form-group-modern">
                                            <label for="date_of_birth" class="form-label-modern" style="font-size: 1.00rem;">
                                                 Date of Birth <span class="text-danger">*</span>
                                            </label>
                                            <input type="date" class="form-control form-control-modern" id="date_of_birth" name="date_of_birth" 
                                                   value="<?php echo htmlspecialchars($patient['date_of_birth'] ?? ''); ?>" required>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group-modern">
                                            <label for="gender" class="form-label-modern" style="font-size: 1.00rem;">
                                                Gender <span class="text-danger">*</span>
                                            </label>
                                            <select class="form-control form-control-modern" id="gender" name="gender" required>
                                                <option value="">Select Gender</option>
                                                <option value="Male" <?php echo (isset($patient['gender']) && $patient['gender'] == 'Male') ? 'selected' : ''; ?>>Male</option>
                                                <option value="Female" <?php echo (isset($patient['gender']) && $patient['gender'] == 'Female') ? 'selected' : ''; ?>>Female</option>
                                                <option value="Other" <?php echo (isset($patient['gender']) && $patient['gender'] == 'Other') ? 'selected' : ''; ?>>Other</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group-modern">
                                            <label for="blood_type" class="form-label-modern" style="font-size: 1.00rem;">
                                                 Blood Type
                                            </label>
                                            <select class="form-control form-control-modern" id="blood_type" name="blood_type">
                                                <option value="">Select Blood Type</option>
                                                <?php
                                                $blood_types = ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'];
                                                foreach ($blood_types as $type):
                                                ?>
                                                    <option value="<?php echo $type; ?>" <?php echo (isset($patient['blood_type']) && $patient['blood_type'] == $type) ? 'selected' : ''; ?>><?php echo $type; ?></option>
                                                <?php endforeach; ?>
                                            </select>
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
                                            <input type="tel" class="form-control form-control-modern" id="phone" name="phone" 
                                                   value="<?php echo htmlspecialchars($patient['phone'] ?? ''); ?>" 
                                                   placeholder="Enter phone number" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group-modern">
                                            <label for="email" class="form-label-modern" style="font-size: 1.00rem;">
                                                 Email Address
                                            </label>
                                            <input type="email" class="form-control form-control-modern" id="email" name="email" 
                                                   value="<?php echo htmlspecialchars($patient['email'] ?? ''); ?>" 
                                                   placeholder="Enter email address">
                                        </div>
                                    </div>
                                </div>

                                <div class="row g-4 mt-2">
                                    <div class="col-12">
                                        <div class="form-group-modern">
                                            <label for="address" class="form-label-modern" style="font-size: 1.00rem;">
                                                 Address <span class="text-danger">*</span>
                                            </label>
                                            <textarea class="form-control form-control-modern" id="address" name="address" rows="2" 
                                                      placeholder="Enter full address" required><?php echo htmlspecialchars($patient['address'] ?? ''); ?></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Emergency Contact Section -->
                            <div class="form-section mb-4">
                                <h6 class="text-uppercase text-muted fw-bold mb-3" style="font-size: 1.20rem; letter-spacing: 1px;">
                                     Emergency Contact
                                </h6>
                                <div class="row g-4">
                                    <div class="col-md-6">
                                        <div class="form-group-modern">
                                            <label for="emergency_contact_name" class="form-label-modern" style="font-size: 1.00rem;">
                                                Contact Name
                                            </label>
                                            <input type="text" class="form-control form-control-modern" id="emergency_contact_name" name="emergency_contact_name" 
                                                   value="<?php echo htmlspecialchars($patient['emergency_contact_name'] ?? ''); ?>" 
                                                   placeholder="Enter emergency contact name">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group-modern">
                                            <label for="emergency_contact_phone" class="form-label-modern" style="font-size: 1.00rem;">
                                                 Contact Phone
                                            </label>
                                            <input type="tel" class="form-control form-control-modern" id="emergency_contact_phone" name="emergency_contact_phone" 
                                                   value="<?php echo htmlspecialchars($patient['emergency_contact_phone'] ?? ''); ?>" 
                                                   placeholder="Enter emergency contact phone">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Medical Information Section -->
                            <div class="form-section mb-4">
                                <h6 class="text-uppercase text-muted fw-bold mb-3" style="font-size: 1.20rem; letter-spacing: 1px;">
                                     Medical Information
                                </h6>
                                <div class="row g-4">
                                    <div class="col-12">
                                        <div class="form-group-modern">
                                            <label for="allergies" class="form-label-modern" style="font-size: 1.00rem;">
                                                 Allergies
                                            </label>
                                            <textarea class="form-control form-control-modern" id="allergies" name="allergies" rows="2" 
                                                      placeholder="List any known allergies..."><?php echo htmlspecialchars($patient['allergies'] ?? ''); ?></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Insurance Information Section -->
                            <div class="form-section mb-4">
                                <h6 class="text-uppercase text-muted fw-bold mb-3" style="font-size: 1.20rem; letter-spacing: 1px;">
                                     Insurance Information
                                </h6>
                                <div class="row g-4">
                                    <div class="col-md-6">
                                        <div class="form-group-modern">
                                            <label for="insurance_provider" class="form-label-modern" style="font-size: 1.00rem;">
                                                Insurance Provider
                                            </label>
                                            <input type="text" class="form-control form-control-modern" id="insurance_provider" name="insurance_provider" 
                                                   value="<?php echo htmlspecialchars($patient['insurance_provider'] ?? ''); ?>" 
                                                   placeholder="Enter insurance provider">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group-modern">
                                            <label for="insurance_number" class="form-label-modern" style="font-size: 1.00rem;">
                                                 Insurance Number
                                            </label>
                                            <input type="text" class="form-control form-control-modern" id="insurance_number" name="insurance_number" 
                                                   value="<?php echo htmlspecialchars($patient['insurance_number'] ?? ''); ?>" 
                                                   placeholder="Enter insurance number">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Form Actions -->
                            <div class="form-actions mt-5 pt-4 border-top">
                                <button type="submit" class="btn btn-primary btn-lg px-5" style="padding: 0.75rem 1.25rem;">
                                    <?php echo $action == 'add' ? 'Add Patient' : 'Update Patient'; ?>
                                </button>
                                <a href="patients.php" class="btn btn-secondary btn-lg px-5 ms-2" style="padding: 0.75rem 1.25rem;">
                                    Cancel
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
                <?php endif; ?>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function toggleSelectAll(source) {
            const checkboxes = document.querySelectorAll('input[name="selected_patients[]"]');
            for (let i = 0; i < checkboxes.length; i++) {
                checkboxes[i].checked = source.checked;
            }
        }

        function deleteSelectedPatients() {
            const checkboxes = document.querySelectorAll('input[name="selected_patients[]"]:checked');
            if (checkboxes.length === 0) {
                alert('Please select at least one patient to delete.');
                return false;
            }
            if (confirm('Are you sure you want to delete the selected patient(s)? This action cannot be undone.')) {
                document.getElementById('patientsTableForm').submit();
            }
        }

        function printPatientDetails() {
            var printContents = document.getElementById('printableArea').innerHTML;
            var originalContents = document.body.innerHTML;
            var printWindow = window.open('', '_blank', 'width=800,height=600');
            printWindow.document.write('<html><head><title>Patient Details</title>');
            printWindow.document.write('<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">');
            printWindow.document.write('<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">');
            printWindow.document.write('<style>');
            printWindow.document.write('body { font-family: "Poppins", "Segoe UI", sans-serif; padding: 20px; background: #fff; }');
            printWindow.document.write('.card { border: 1px solid #ddd !important; box-shadow: none !important; }');
            printWindow.document.write('.card-header { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }');
            printWindow.document.write('.badge { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }');
            printWindow.document.write('.patient-avatar { background-color: #007bff !important; -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }');
            printWindow.document.write('@media print { .card-header { background: #007bff !important; } }');
            printWindow.document.write('</style>');
            printWindow.document.write('</head><body>');
            printWindow.document.write('<div class="container">' + printContents + '</div>');
            printWindow.document.write('</body></html>');
            printWindow.document.close();
            printWindow.onload = function() {
                printWindow.focus();
                printWindow.print();
                printWindow.close();
            };
        }
    </script>
</body>
</html>