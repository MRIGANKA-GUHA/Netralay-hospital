<?php
require_once 'includes/config.php';
requireLogin();

if (!hasRole('admin')) {
    header('Location: dashboard.php');
    exit;
}

$error = '';
$success = '';

// Handle delete action
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    try {
        $stmt = $pdo->prepare("DELETE FROM enquiries WHERE id = ?");
        $stmt->execute([$_GET['id']]);
        $success = 'Enquiry deleted successfully!';
    } catch (PDOException $e) {
        $error = 'Error deleting enquiry: ' . $e->getMessage();
    }
}

// Fetch all enquiries
$enquiries = [];
try {
    $stmt = $pdo->query("SELECT * FROM enquiries ORDER BY submitted_at DESC");
    $enquiries = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = 'Error fetching enquiries: ' . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enquiries - <?php echo SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
</head>
<body>
<?php include 'includes/navbar.php'; ?>

<div class="container-fluid">
    <div class="dashboard-wrapper">
        <main class="dashboard-main">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-0 pb-2 mb-3 border-bottom">
                <h1 class="dashboard-title display-6 fw-bold mb-0 d-flex align-items-center gap-2">
                    Enquiries Management
                </h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <span class="badge bg-primary" style="font-size: 1rem; padding: 0.75rem 1.25rem;">
                        Total: <?php echo count($enquiries); ?>
                    </span>
                </div>
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
            
            <div class="card modern-card shadow-sm">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover modern-table mb-0">
                            <thead>
                                <tr>
                                    <th style="width: 5%;">ID</th>
                                    <th style="width: 15%;">Full Name</th>
                                    <th style="width: 15%;">Email</th>
                                    <th style="width: 10%;">Phone</th>
                                    <th style="width: 15%;">Subject</th>
                                    <th style="width: 25%;">Message</th>
                                    <th style="width: 10%;">Submitted</th>
                                    <th style="width: 5%;" class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($enquiries)): ?>
                                    <tr>
                                        <td colspan="8" class="text-center py-5">
                                            <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                            <p class="text-muted mb-0">No enquiries found</p>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($enquiries as $enquiry): ?>
                                        <tr>
                                            <td class="fw-bold"><?php echo htmlspecialchars($enquiry['id']); ?></td>
                                            <td><?php echo htmlspecialchars($enquiry['full_name']); ?></td>
                                            <td>
                                                <a href="mailto:<?php echo htmlspecialchars($enquiry['email']); ?>" class="text-decoration-none">
                                                    <?php echo htmlspecialchars($enquiry['email']); ?>
                                                </a>
                                            </td>
                                            <td>
                                                <a href="tel:<?php echo htmlspecialchars($enquiry['phone']); ?>" class="text-decoration-none">
                                                    <?php echo htmlspecialchars($enquiry['phone']); ?>
                                                </a>
                                            </td>
                                            <td><?php echo htmlspecialchars($enquiry['subject']); ?></td>
                                            <td>
                                                <div style="max-height: 60px; overflow-y: auto; font-size: 0.9rem;">
                                                    <?php echo nl2br(htmlspecialchars($enquiry['message'])); ?>
                                                </div>
                                            </td>
                                            <td>
                                                <small class="text-muted">
                                                    <?php echo date('M d, Y', strtotime($enquiry['submitted_at'])); ?><br>
                                                    <?php echo date('h:i A', strtotime($enquiry['submitted_at'])); ?>
                                                </small>
                                            </td>
                                            <td class="text-center">
                                                <div class="btn-group btn-group-sm">
                                                    <button type="button" class="btn btn-info btn-action-icon" data-bs-toggle="modal" data-bs-target="#viewModal<?php echo $enquiry['id']; ?>" title="View Details">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                    <a href="?action=delete&id=<?php echo $enquiry['id']; ?>" 
                                                       class="btn btn-danger btn-action-icon" 
                                                       onclick="return confirm('Are you sure you want to delete this enquiry?')" 
                                                       title="Delete">
                                                        <i class="fas fa-trash"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                        
                                        <!-- View Modal -->
                                        <div class="modal fade" id="viewModal<?php echo $enquiry['id']; ?>" tabindex="-1">
                                            <div class="modal-dialog modal-lg">
                                                <div class="modal-content">
                                                    <div class="modal-header bg-primary text-white">
                                                        <h5 class="modal-title">Enquiry Details - #<?php echo $enquiry['id']; ?></h5>
                                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div class="row mb-3">
                                                            <div class="col-md-6">
                                                                <label class="fw-bold text-muted small">Full Name</label>
                                                                <p class="mb-0"><?php echo htmlspecialchars($enquiry['full_name']); ?></p>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <label class="fw-bold text-muted small">Email</label>
                                                                <p class="mb-0">
                                                                    <a href="mailto:<?php echo htmlspecialchars($enquiry['email']); ?>">
                                                                        <?php echo htmlspecialchars($enquiry['email']); ?>
                                                                    </a>
                                                                </p>
                                                            </div>
                                                        </div>
                                                        <div class="row mb-3">
                                                            <div class="col-md-6">
                                                                <label class="fw-bold text-muted small">Phone</label>
                                                                <p class="mb-0">
                                                                    <a href="tel:<?php echo htmlspecialchars($enquiry['phone']); ?>">
                                                                        <?php echo htmlspecialchars($enquiry['phone']); ?>
                                                                    </a>
                                                                </p>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <label class="fw-bold text-muted small">Submitted At</label>
                                                                <p class="mb-0"><?php echo date('M d, Y h:i A', strtotime($enquiry['submitted_at'])); ?></p>
                                                            </div>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="fw-bold text-muted small">Subject</label>
                                                            <p class="mb-0"><?php echo htmlspecialchars($enquiry['subject']); ?></p>
                                                        </div>
                                                        <div>
                                                            <label class="fw-bold text-muted small">Message</label>
                                                            <div class="border rounded p-3 bg-light">
                                                                <?php echo nl2br(htmlspecialchars($enquiry['message'])); ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <a href="mailto:<?php echo htmlspecialchars($enquiry['email']); ?>" class="btn btn-primary">
                                                            <i class="fas fa-reply"></i> Reply via Email
                                                        </a>
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
