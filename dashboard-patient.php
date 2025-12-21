<?php
require_once 'includes/config.php';
requireLogin();
if ($_SESSION['role'] !== 'patient') {
    redirect('dashboard.php');
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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patient Dashboard - <?php echo SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
</head>
<body>
<?php include 'includes/navbar.php'; ?>
<div class="container-fluid">
    <div class="dashboard-wrapper">
        <main class="dashboard-main">
            <div class="d-flex justify-content-between align-items-center pt-0 pb-2 mb-3 border-bottom">
                <h1 class="dashboard-title display-6 fw-bold mb-0">
                    Welcome, <?php echo htmlspecialchars($patient['first_name']); ?>!
                </h1>
            </div>
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="card shadow-sm mb-3 modern-card">
                        <div class="card-header bg-primary text-black">
                             Your Profile
                        </div>
                        <div class="card-body">
                            <p><strong>Patient ID:</strong> <?php echo htmlspecialchars($patient['patient_id']); ?></p>
                            <p><strong>Name:</strong> <?php echo htmlspecialchars($patient['first_name'] . ' ' . $patient['last_name']); ?></p>
                            <p><strong>Email:</strong> <?php echo htmlspecialchars($patient['email']); ?></p>
                            <p><strong>Phone:</strong> <?php echo htmlspecialchars($patient['phone']); ?></p>
                            <p><strong>Blood Type:</strong> <?php echo $patient['blood_type'] ? htmlspecialchars($patient['blood_type']) : 'Not specified'; ?></p>
                            <?php if ($patient['allergies']): ?>
                                <p class="mb-0"><strong>Allergies:</strong> <?php echo htmlspecialchars($patient['allergies']); ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card shadow-sm mb-3 modern-card">
                        <div class="card-header bg-success text-black">
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
                                            <span class="badge bg-info text-dark"><?php echo ucfirst(htmlspecialchars($appt['status'])); ?></span>
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
                        <div class="card-header bg-info text-black">
                             Recent Medical History
                        </div>
                        <div class="card-body p-0">
                            <?php if (empty($history)): ?>
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
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($history as $h): ?>
                                                <tr>
                                                    <td><?php echo date('M d, Y', strtotime($h['visit_date'])); ?></td>
                                                    <td><?php echo htmlspecialchars($h['diagnosis']); ?></td>
                                                    <td><?php echo htmlspecialchars($h['treatment']); ?></td>
                                                    <td><?php echo htmlspecialchars($h['notes']); ?></td>
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
