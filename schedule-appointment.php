<?php
require_once 'includes/config.php';
requireLogin();
if (!hasRole('patient')) {
    header('Location: dashboard.php');
    exit();
}

$doctor_id = $_GET['doctor_id'] ?? '';
$err = '';
$success = '';

// Fetch doctor info
// Fetch doctor info (including email and phone from users table)
$stmt = $pdo->prepare("SELECT d.*, u.full_name, u.email, u.phone FROM doctors d JOIN users u ON d.user_id = u.user_id WHERE d.doctor_id = ?");
$stmt->execute([$doctor_id]);
$doctor = $stmt->fetch();
if (!$doctor) {
    $err = 'Doctor not found.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $doctor) {
    $date = $_POST['appointment_date'] ?? '';
    $time = $_POST['appointment_time'] ?? '';
    $patient_id = $_SESSION['user_id'];
    // Check if time is within doctor's available time
    $available_days = json_decode($doctor['available_days'], true);
    $day_of_week = date('l', strtotime($date));
    $start = $doctor['available_time_start'];
    $end = $doctor['available_time_end'];
    // Only check time range, not available day (let JS handle day)
    if ($time < $start || $time > $end) {
        $err = 'Time is outside doctor\'s available hours.';
    } else {
        // Insert appointment with status 'scheduled' for admin approval
        $stmt = $pdo->prepare("INSERT INTO appointments (patient_id, doctor_id, appointment_date, appointment_time, status) VALUES (?, ?, ?, ?, 'scheduled')");
        $stmt->execute([$patient_id, $doctor_id, $date, $time]);
        $success = 'Appointment request sent for admin approval.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Schedule Appointment - <?php echo SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
</head>
<body>
<?php include 'includes/navbar.php'; ?>
<div class="container-fluid">
    <div class="dashboard-wrapper">
        <main class="dashboard-main">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-0 pb-2 mb-3 border-bottom">
                <h1 class="dashboard-title display-6 fw-bold mb-0 d-flex align-items-center gap-2">
                    Schedule Appointment
                </h1>
            </div>

            <?php if ($err): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i><?php echo $err; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php elseif ($success): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i><?php echo $success; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <div class="text-center mt-4">
                    <a href="dashboard-patient.php" class="btn btn-primary me-2">
                        <i class="fas fa-home me-2"></i>Go to Dashboard
                    </a>
                    <a href="doctors-list.php" class="btn btn-secondary">
                        <i class="fas fa-user-md me-2"></i>Back to Doctors
                    </a>
                </div>
            <?php endif; ?>

            <?php if ($doctor && !$success): ?>
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <!-- Doctor Info Card -->
                    <div class="card shadow-sm mb-4 modern-card">
                        <div class="card-header bg-info text-black">
                            Doctor Information</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <p class="mb-2"><strong> Name:</strong><?php echo htmlspecialchars($doctor['full_name']); ?></p>
                                    <p class="mb-2"><strong>Specialization:</strong> <?php echo htmlspecialchars($doctor['specialization']); ?></p>
                                    <p class="mb-0"><strong>Department:</strong> <?php echo htmlspecialchars($doctor['department']); ?></p>
                                </div>
                                <div class="col-md-6">
                                    <p class="mb-2"><strong>Email:</strong> <?php echo isset($doctor['email']) ? htmlspecialchars($doctor['email']) : '<span class="text-muted">Not available</span>'; ?></p>
                                    <p class="mb-2"><strong>Phone:</strong> <?php echo isset($doctor['phone']) ? htmlspecialchars($doctor['phone']) : '<span class="text-muted">Not available</span>'; ?></p>
                                    <p class="mb-0">
                                        <strong>Available Hours:</strong> 
                                        <span class="badge bg-success"><?php echo date('g:i A', strtotime($doctor['available_time_start'])); ?> - <?php echo date('g:i A', strtotime($doctor['available_time_end'])); ?></span>
                                    </p>
                                </div>
                            </div>
                            <?php if ($doctor['available_days']): ?>
                            <div class="mt-3">
                                <strong style="padding-left: 0.7rem">Available Days:</strong>
                                <?php
                                $days = json_decode($doctor['available_days'], true);
                                if ($days) {
                                    foreach ($days as $day) {
                                        echo '<span class="badge bg-primary me-1">' . htmlspecialchars($day) . '</span>';
                                    }
                                }
                                ?>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Appointment Form Card -->
                    <div class="card shadow-sm modern-card">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">Request Appointment</h5>
                        </div>
                        <div class="card-body">
                            <form method="POST">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="appointment_date" class="form-label">
                                            <strong>Appointment Date</strong>
                                        </label>
                                                                            <input type="date" class="form-control" name="appointment_date" id="appointment_date" style="padding: 0.8rem;" required min="<?php echo date('Y-m-d'); ?>">
                                                                            <div id="date-warning" class="text-danger mt-1" style="display:none;font-size:0.95rem;"></div>
                                                                            <script>
                                                                                document.addEventListener('DOMContentLoaded', function() {
                                                                                    var dateInput = document.getElementById('appointment_date');
                                                                                    var dateWarning = document.getElementById('date-warning');
                                                                                    var allowedDays = (function() {
                                                                                        var days = <?php echo json_encode(json_decode($doctor['available_days'], true)); ?>;
                                                                                        var dayMap = {"Sunday": 0, "Monday": 1, "Tuesday": 2, "Wednesday": 3, "Thursday": 4, "Friday": 5, "Saturday": 6};
                                                                                        return days.map(function(day) { return dayMap[day]; });
                                                                                    })();
                                                                                    function validateDate() {
                                                                                        var d = new Date(dateInput.value);
                                                                                        if (dateInput.value && allowedDays.length && allowedDays.indexOf(d.getDay()) === -1) {
                                                                                            dateWarning.textContent = 'Doctor is not available on this day. Please select an available day.';
                                                                                            dateWarning.style.display = 'block';
                                                                                            dateInput.value = '';
                                                                                        } else {
                                                                                            dateWarning.textContent = '';
                                                                                            dateWarning.style.display = 'none';
                                                                                        }
                                                                                    }
                                                                                    dateInput.addEventListener('input', validateDate);
                                                                                    dateInput.addEventListener('blur', validateDate);
                                                                                });
                                                                            </script>
                                     
                                        <script>
                                            window.doctorAvailability = {};
                                            <?php
                                            // Only one doctor, but use the same structure for consistency
                                            $days = json_decode($doctor['available_days'], true) ?: [];
                                            echo "window.doctorAvailability['{$doctor['doctor_id']}'] = " . json_encode($days) . ";\n";
                                            ?>
                                        </script>
                                        <script src="js/doctor-availability.js"></script>
                                        <small class="text-muted">Please select a date from available days shown above</small>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="appointment_time" class="form-label">
                                            <strong>Appointment Time</strong>
                                        </label>
                                        <select class="form-select" name="appointment_time" id="appointment_time" required style="padding: 0.8rem;">
                                            <option value="">Select Time</option>
                                            <?php
                                            $start = $doctor['available_time_start'];
                                            $end = $doctor['available_time_end'];
                                            $current = strtotime($start);
                                            $end_time = strtotime($end);
                                            while ($current <= $end_time) {
                                                $time_str = date('H:i', $current);
                                                echo '<option value="' . $time_str . '">' . date('g:i A', $current) . '</option>';
                                                $current = strtotime('+15 minutes', $current);
                                            }
                                            ?>
                                        </select>
                                        <small class="text-muted">Select a time slot within available hours</small>
                                    </div>
                                    <div class="col-12">
                                        <div class="alert alert-info">
                                            <i class="fas fa-info-circle me-2"></i>
                                            <strong>Note:</strong> Your appointment request will be sent to the admin for approval. You will be notified once it's confirmed.
                                        </div>
                                    </div>
                                    <div class="col-12 d-flex gap-2 justify-content-end">
                                        <a href="doctors-list.php" class="btn btn-secondary">
                                            Cancel
                                        </a>
                                        <button type="submit" class="btn btn-primary">
                                            Request Appointment
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
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
