<?php
require_once 'includes/config.php';
requireLogin();
if (!hasRole('patient')) {
    header('Location: dashboard.php');
    exit();
}

// Search and filter
$search = $_GET['search'] ?? '';
$specialization_filter = $_GET['specialization'] ?? '';

$query = "SELECT d.doctor_id, u.full_name, d.specialization, d.consultation_fee, d.available_days, d.available_time_start, d.available_time_end, d.department 
          FROM doctors d 
          JOIN users u ON d.user_id = u.user_id 
          WHERE u.is_active = 1";
$params = [];

if ($search) {
    $query .= " AND (d.doctor_id LIKE ? OR u.full_name LIKE ? OR d.specialization LIKE ? OR d.department LIKE ?)";
    $search_param = "%$search%";
    $params = array_merge($params, [$search_param, $search_param, $search_param, $search_param]);
}

if ($specialization_filter) {
    $query .= " AND d.specialization = ?";
    $params[] = $specialization_filter;
}
// Fetching the doctor list
$query .= " ORDER BY d.doctor_id ASC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$doctors = $stmt->fetchAll();

// Get all unique specializations for filter dropdown
$spec_stmt = $pdo->query("SELECT DISTINCT specialization FROM doctors ORDER BY specialization");
$specializations = $spec_stmt->fetchAll(PDO::FETCH_COLUMN);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Doctors List - <?php echo SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body>
<?php include 'includes/navbar.php'; ?>
<div class="container-fluid">
    <div class="dashboard-wrapper">
        <main class="dashboard-main">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-0 pb-2 mb-3 border-bottom">
                <h1 class="dashboard-title display-6 fw-bold mb-0 d-flex align-items-center gap-2">
                    Doctors List
                </h1>
            </div>
            
            <!-- Search and Filter -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <div class="col-md-5">
                            <input type="text" class="form-control" name="search" placeholder="Search by name, specialization, department..." value="<?php echo htmlspecialchars($search); ?>">
                        </div>
                        <div class="col-md-3">
                            <select class="form-control" name="specialization">
                                <option value="">All Specializations</option>
                                <?php foreach ($specializations as $spec): ?>
                                    <option value="<?php echo htmlspecialchars($spec); ?>" <?php echo $specialization_filter == $spec ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($spec); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary w-100" style="padding: 0.75rem 1.25rem; margin-top: 0;">
                                 Search
                            </button>
                        </div>
                        <div class="col-md-2">
                            <a href="doctors-list.php" class="btn btn-secondary w-100" style="padding: 0.75rem 1.25rem; margin-top: 0;">
                                Reset
                            </a>
                        </div>
                    </form>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h5>Available Doctors (<?php echo count($doctors); ?> total)</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle text-center">
                            <thead class="table-light">
                                <tr>
                                    <th>Doctor ID</th>
                                    <th>Name</th>
                                    <th>Specialization</th>
                                    <th>Availability</th>
                                    <th>Consultation Fee</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($doctors as $doctor): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($doctor['doctor_id']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($doctor['full_name']); ?></td>
                                    <td><?php echo htmlspecialchars($doctor['specialization']); ?></td>
                                    <td>
                                        <?php 
                                        $days = json_decode($doctor['available_days'], true);
                                        $start = $doctor['available_time_start'] ? date('g:i A', strtotime($doctor['available_time_start'])) : '';
                                        $end = $doctor['available_time_end'] ? date('g:i A', strtotime($doctor['available_time_end'])) : '';
                                        echo ($days ? implode(', ', $days) : 'N/A') . ($start && $end ? "<br>($start - $end)" : '');
                                        ?>
                                    </td>
                                    <td><?php echo $doctor['consultation_fee'] !== null ? '₹' . number_format($doctor['consultation_fee'], 2) : 'N/A'; ?></td>
                                    <td>
                                        <a href="schedule-appointment.php?doctor_id=<?php echo urlencode($doctor['doctor_id']); ?>" class="btn btn-success btn-sm">
                                            <i class="fas fa-calendar-plus"></i> Schedule Appointment
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>
</body>
</html>
