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
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['enquiry_id'])) {
    try {
        $stmt = $pdo->prepare("DELETE FROM enquiries WHERE enquiry_id = ?");
        $stmt->execute([$_GET['enquiry_id']]);
        $success = 'Enquiry deleted successfully!';
    } catch (PDOException $e) {
        $error = 'Error deleting enquiry: ' . $e->getMessage();
    }
}

// Fetch all enquiries
$enquiries = [];
try {
    $stmt = $pdo->query("SELECT * FROM enquiries ORDER BY enquiry_id ASC");
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
            
            <div class="card shadow-sm" style="border-radius: 12px; overflow: hidden;">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0" style="border-radius: 12px;">
                            <thead class="table-dark">
                                <tr>
                                    <th style="width: 10%; padding: 1rem 0.75rem;">Enquiry ID</th>
                                    <th style="width: 15%; padding: 1rem 0.75rem;">Full Name</th>
                                    <th style="width: 15%; padding: 1rem 0.75rem;">Email</th>
                                    <th style="width: 10%; padding: 1rem 0.75rem;">Phone</th>
                                    <th style="width: 15%; padding: 1rem 0.75rem;">Subject</th>
                                    <th style="width: 25%; padding: 1rem 0.75rem;">Message</th>
                                    <th style="width: 10%; padding: 1rem 0.75rem;">Submitted</th>
                                    <th style="width: 5%; padding: 1rem 0.75rem;" class="text-center">Actions</th>
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
                                        <tr style="border-bottom: 1px solid #e9ecef;">
                                            <td class="fw-bold" style="padding: 1rem 0.75rem; vertical-align: middle;"><?php echo htmlspecialchars($enquiry['enquiry_id']); ?></td>
                                            <td style="padding: 1rem 0.75rem; vertical-align: middle;"><?php echo htmlspecialchars($enquiry['full_name']); ?></td>
                                            <td style="padding: 1rem 0.75rem; vertical-align: middle;">
                                                <a href="mailto:<?php echo htmlspecialchars($enquiry['email']); ?>" class="text-decoration-none text-primary">
                                                    <?php echo htmlspecialchars($enquiry['email']); ?>
                                                </a>
                                            </td>
                                            <td style="padding: 1rem 0.75rem; vertical-align: middle;">
                                                <a href="tel:<?php echo htmlspecialchars($enquiry['phone']); ?>" class="text-decoration-none text-success">
                                                    <?php echo htmlspecialchars($enquiry['phone']); ?>
                                                </a>
                                            </td>
                                            <td style="padding: 1rem 0.75rem; vertical-align: middle;"><?php echo htmlspecialchars($enquiry['subject']); ?></td>
                                            <td style="padding: 1rem 0.75rem; vertical-align: middle;">
                                                <div style="max-height: 60px; overflow-y: auto; font-size: 0.9rem; background: #f8f9fa; padding: 0.5rem; border-radius: 6px;">
                                                    <?php echo nl2br(htmlspecialchars($enquiry['message'])); ?>
                                                </div>
                                            </td>
                                            <td style="padding: 1rem 0.75rem; vertical-align: middle;">
                                                <small class="text-muted">
                                                    <?php echo date('M d, Y', strtotime($enquiry['submitted_at'])); ?><br>
                                                    <?php echo date('h:i A', strtotime($enquiry['submitted_at'])); ?>
                                                </small>
                                            </td>
                                            <td class="text-center" style="padding: 1rem 0.75rem; vertical-align: middle;">
                                                <div class="btn-group btn-group-sm">
                                                    <button type="button" class="btn btn-info btn-action-icon" data-bs-toggle="modal" data-bs-target="#viewModal<?php echo $enquiry['enquiry_id']; ?>" title="View Details">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                    <a href="?action=delete&enquiry_id=<?php echo $enquiry['enquiry_id'];  ?>" 
                                                       class="btn btn-danger btn-action-icon" 
                                                       onclick="return confirm('Are you sure you want to delete this enquiry?')" 
                                                       title="Delete">
                                                        <i class="fas fa-trash"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
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

<!-- Modals Section -->
<?php if (!empty($enquiries)): ?>
    <?php foreach ($enquiries as $enquiry): ?>
        <!-- View Modal -->
        <div class="modal fade" id="viewModal<?php echo $enquiry['enquiry_id']; ?>" tabindex="-1" aria-labelledby="viewModalLabel<?php echo $enquiry['enquiry_id']; ?>" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-scrollable modal-dialog-centered">
                <div class="modal-content" style="border-radius: 12px; overflow: hidden; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.2);">
                    <div class="modal-header" style="background: linear-gradient(135deg, #007bff, #0056b3); border: none; padding: 1.5rem;">
                        <h5 class="modal-title text-white fw-bold" id="viewModalLabel<?php echo $enquiry['enquiry_id']; ?>">Enquiry Details - #<?php echo $enquiry['enquiry_id']; ?></h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body" style="max-height: 60vh; overflow-y: auto; padding: 1.5rem;">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="fw-bold text-muted small text-uppercase">Full Name</label>
                                <p class="mb-0 mt-1"><?php echo htmlspecialchars($enquiry['full_name']); ?></p>
                            </div>
                            <div class="col-md-6">
                                <label class="fw-bold text-muted small text-uppercase">Email</label>
                                <p class="mb-0 mt-1">
                                    <a href="mailto:<?php echo htmlspecialchars($enquiry['email']); ?>" class="text-primary text-decoration-none">
                                        <?php echo htmlspecialchars($enquiry['email']); ?>
                                    </a>
                                </p>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="fw-bold text-muted small text-uppercase">Phone</label>
                                <p class="mb-0 mt-1">
                                    <a href="tel:<?php echo htmlspecialchars($enquiry['phone']); ?>" class="text-success text-decoration-none">
                                        <?php echo htmlspecialchars($enquiry['phone']); ?>
                                    </a>
                                </p>
                            </div>
                            <div class="col-md-6">
                                <label class="fw-bold text-muted small text-uppercase">Submitted At</label>
                                <p class="mb-0 mt-1"><?php echo date('M d, Y h:i A', strtotime($enquiry['submitted_at'])); ?></p>
                            </div>
                        </div>
                        <div class="mb-3" style="padding-left: 0.7rem;">
                            <label class="fw-bold text-muted small text-uppercase">Subject</label>
                            <p class="mb-0 mt-1 p-2 bg-light rounded"><?php echo htmlspecialchars($enquiry['subject']); ?></p>
                        </div>
                        <div>
                            <label class="fw-bold text-muted small text-uppercase" style="padding-left: 0.7rem;">Message</label>
                            <div class="border rounded p-3 bg-light mt-2" style="border-radius: 8px !important;">
                                <?php echo nl2br(htmlspecialchars($enquiry['message'])); ?>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer" style="padding: 1.5rem; border: none; background: #f8f9fa;">
                        <a href="mailto:<?php echo htmlspecialchars($enquiry['email']); ?>" class="btn btn-primary rounded-pill px-4">
                            <i class="fas fa-reply me-2"></i> Reply via Email
                        </a>
                        <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>
            </div>
        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
