<?php
require_once 'includes/config.php';

// Strict timeout: logout exactly 20 minutes after login, regardless of activity
$timeout_duration = 1200; // 20 minutes
if (!isset($_SESSION['LOGIN_TIME'])) {
    $_SESSION['LOGIN_TIME'] = time();
}
if ((time() - $_SESSION['LOGIN_TIME']) > $timeout_duration) {
    session_unset();
    session_destroy();
    header('Location: login.php?timeout=1');
    exit();
}

// Fetch patient details
$patient_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM patients WHERE patient_id = ?");
$stmt->execute([$patient_id]);
$patient = $stmt->fetch();

// Fetch upcoming appointments
$appointments = [];
$stmt = $pdo->prepare("SELECT a.*, d.specialization, d.department, u.full_name AS doctor_name FROM appointments a JOIN doctors d ON a.doctor_id = d.doctor_id JOIN users u ON d.user_id = u.user_id WHERE a.patient_id = ? ORDER BY a.appointment_date ASC, a.appointment_time ASC");
$stmt->execute([$patient_id]);
$appointments = $stmt->fetchAll();

// Fetch recent medical history
$history = [];
$stmt = $pdo->prepare("SELECT * FROM medical_history WHERE patient_id = ? ORDER BY visit_date DESC LIMIT 5");
$stmt->execute([$patient_id]);
$history = $stmt->fetchAll();

// Status color function (same as admin)
function getStatusColor($status) {
    switch ($status) {
        case 'confirmed':
            return 'success';
        case 'completed':
            return 'primary';
        case 'cancelled':
            return 'danger';
        case 'no-show':
            return 'warning';
        default:
            return 'secondary';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patient Dashboard - <?php echo SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
</head>
<body>
<?php include 'includes/navbar.php'; ?>
<div class="container-fluid">
    <div class="dashboard-wrapper">
        <main class="dashboard-main">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-0 pb-2 mb-3 border-bottom">
                <h1 class="dashboard-title display-6 fw-bold mb-0 d-flex align-items-center gap-2">
                     Welcome, <?php echo htmlspecialchars($patient['first_name']); ?>!
                </h1>
            </div>
            
            <!-- Statistics Cards -->
            <div class="row mb-4">
                <div class="col-12 col-md-4 mb-3">
                    <div class="card modern-stat-card h-100">
                        <div class="card-body d-flex align-items-center justify-content-between">
                            <div>
                                <div class="stat-label text-uppercase mb-2">
                                    Total Appointments
                                </div>
                                <div class="stat-value display-5 fw-bold">
                                    <?php echo count($appointments); ?>
                                </div>
                            </div>
                            <div class="stat-icon bg-gradient-primary">
                                <i class="fas fa-calendar-check fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-4 mb-3">
                    <div class="card modern-stat-card h-100">
                        <div class="card-body d-flex align-items-center justify-content-between">
                            <div>
                                <div class="stat-label text-uppercase mb-2">
                                    Patient ID
                                </div>
                                <div class="stat-value display-6 fw-bold">
                                    <?php echo htmlspecialchars($patient['patient_id']); ?>
                                </div>
                            </div>
                            <div class="stat-icon bg-gradient-success">
                                <i class="fas fa-id-card fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-4 mb-3">
                    <div class="card modern-stat-card h-100">
                        <div class="card-body d-flex align-items-center justify-content-between">
                            <div>
                                <div class="stat-label text-uppercase mb-2">
                                    Blood Type
                                </div>
                                <div class="stat-value display-5 fw-bold">
                                    <?php echo $patient['blood_type'] ? htmlspecialchars($patient['blood_type']) : 'N/A'; ?>
                                </div>
                            </div>
                            <div class="stat-icon bg-gradient-danger">
                                <i class="fas fa-heartbeat fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="card shadow-sm mb-3 modern-card">
                        <div class="card-header bg-primary text-black d-flex align-items-center gap-2">
                             Your Profile
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <p class="mb-2"><strong>Name:</strong> <?php echo htmlspecialchars($patient['first_name'] . ' ' . $patient['last_name']); ?></p>
                                    <p class="mb-2"><strong>Date of Birth:</strong> <?php echo htmlspecialchars($patient['date_of_birth']); ?></p>
                                    <p class="mb-2"><strong>Gender:</strong> <?php echo htmlspecialchars($patient['gender']); ?></p>
                                    <p class="mb-2"><strong>Email:</strong> <?php echo htmlspecialchars($patient['email']); ?></p>
                                    <p class="mb-2"><strong>Phone:</strong> <?php echo htmlspecialchars($patient['phone']); ?></p>
                                    <p class="mb-0"><strong>Address:</strong> <?php echo htmlspecialchars($patient['address']); ?></p>
                                </div>
                                <div class="col-md-6">
                                    <p class="mb-2"><strong>Blood Type:</strong> <?php echo $patient['blood_type'] ? htmlspecialchars($patient['blood_type']) : 'Not specified'; ?></p>
                                    <p class="mb-2"><strong>Allergies:</strong> <?php echo $patient['allergies'] ? htmlspecialchars($patient['allergies']) : 'None'; ?></p>
                                    <p class="mb-2"><strong>Emergency Contact:</strong> <?php echo $patient['emergency_contact_name'] ? htmlspecialchars($patient['emergency_contact_name']) . ' (' . htmlspecialchars($patient['emergency_contact_phone']) . ')' : 'Not specified'; ?></p>
                                    <p class="mb-2"><strong>Insurance Provider:</strong> <?php echo $patient['insurance_provider'] ? htmlspecialchars($patient['insurance_provider']) : 'Not specified'; ?></p>
                                    <p class="mb-0"><strong>Insurance Number:</strong> <?php echo $patient['insurance_number'] ? htmlspecialchars($patient['insurance_number']) : 'Not specified'; ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card shadow-sm mb-3 modern-card">
                        <div class="card-header bg-success text-black d-flex align-items-center gap-2">
                             Upcoming Appointments
                        </div>
                        <div class="card-body">
                            <?php if (empty($appointments)): ?>
                                <p class="text-muted mb-0">No upcoming appointments.</p>
                            <?php else: ?>
                                <ul class="list-group list-group-flush">
                                    <?php foreach ($appointments as $appt): ?>
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            <span>
                                                <strong><?php echo date('M d, Y', strtotime($appt['appointment_date'])); ?></strong> at <strong><?php echo date('h:i A', strtotime($appt['appointment_time'])); ?></strong><br>
                                                <small class="text-muted">Dr. <?php echo htmlspecialchars($appt['doctor_name']); ?> - <?php echo htmlspecialchars($appt['specialization']); ?></small>
                                            </span>
                                            <span class="badge bg-<?php echo getStatusColor($appt['status']); ?>">
                                                <?php echo ucfirst(htmlspecialchars($appt['status'])); ?>
                                            </span>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row mb-4">
                <div class="col-md-12">
                    <div class="card shadow-sm modern-card">
                        <div class="card-header bg-info text-black d-flex align-items-center gap-2">
                             Recent Medical History
                        </div>
                        <div class="card-body p-0">
                            <?php 
                            // Find the next appointment date (future appointment)
                            $next_appointment = null;
                            foreach ($appointments as $appt) {
                                if (strtotime($appt['appointment_date'] . ' ' . $appt['appointment_time']) > time()) {
                                    $next_appointment = $appt;
                                    break;
                                }
                            }
                            ?>
                            <?php if (empty($history) && !$next_appointment): ?>
                                <p class="text-muted mb-0 p-3">No recent medical history found.</p>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-hover modern-table mb-0">
                                        <thead>
                                            <tr>
                                                <th>Date</th>
                                                <th>Diagnosis</th>
                                                <th>Treatment</th>
                                                <th>Notes</th>
                                                <th>Next Appointment</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php 
                                            $next_appointment_date = $next_appointment ? $next_appointment['appointment_date'] : null;
                                            $next_appointment_time = $next_appointment ? $next_appointment['appointment_time'] : null;
                                            $next_appointment_info = $next_appointment ? 'Dr. ' . htmlspecialchars($next_appointment['doctor_name']) . ' (' . htmlspecialchars($next_appointment['specialization']) . ')' : '';
                                            ?>
                                            <?php foreach ($history as $h): ?>
                                                <tr>
                                                    <td><?php echo date('M d, Y', strtotime($h['visit_date'])); ?></td>
                                                    <td><?php echo htmlspecialchars($h['diagnosis']); ?></td>
                                                    <td><?php echo htmlspecialchars($h['treatment']); ?></td>
                                                    <td><?php echo htmlspecialchars($h['notes']); ?></td>
                                                    <td>
                                                        <?php if ($next_appointment && strtotime($next_appointment['appointment_date']) >= strtotime($h['visit_date'])): ?>
                                                            <?php echo date('M d, Y', strtotime($next_appointment['appointment_date'])); ?><br>
                                                            <small><?php echo date('h:i A', strtotime($next_appointment['appointment_time'])); ?></small><br>
                                                            <span class="text-muted"><?php echo $next_appointment_info; ?></span>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
