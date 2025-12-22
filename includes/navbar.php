<nav class="navbar navbar-expand-lg modern-navbar fixed-top">
    <div class="container-fluid">
        <!-- Brand Left -->
        <a class="navbar-brand d-flex align-items-center gap-2" href="<?php echo hasRole('patient') ? 'dashboard-patient.php' : 'dashboard.php'; ?>">
            <span class="fw-bold fs-4" style="padding-left: 12px; background: linear-gradient(90deg, #007bff, #00d4ff); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; text-fill-color: transparent;">
                <?php echo SITE_NAME; ?>
            </span>
        </a>
        
        <!-- Toggler for mobile -->
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <!-- Navigation Links -->
        <div class="collapse navbar-collapse justify-content-lg-center" id="navbarNav">
            <!-- Centered Nav Links -->
            <ul class="navbar-nav gap-2">
                <?php if (!hasRole('patient')): ?>
                <li class="nav-item">
                    <a class="nav-link d-flex align-items-center gap-2 nav-animate <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'nav-active' : ''; ?>" href="dashboard.php">
                        <span style="font-size: 1.00rem;">Dashboard</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link d-flex align-items-center gap-2 nav-animate <?php echo basename($_SERVER['PHP_SELF']) == 'patients.php' ? 'nav-active' : ''; ?>" href="patients.php">
                        <span style="font-size: 1.00rem;">Patients</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link d-flex align-items-center gap-2 nav-animate <?php echo basename($_SERVER['PHP_SELF']) == 'appointments.php' ? 'nav-active' : ''; ?>" href="appointments.php">
                         <span style="font-size: 1.00rem;">Appointments</span>
                    </a>
                </li>
                <?php endif; ?>
                <?php if (hasRole('admin')): ?>
                <li class="nav-item">
                    <a class="nav-link d-flex align-items-center gap-2 nav-animate <?php echo basename($_SERVER['PHP_SELF']) == 'doctors.php' ? 'nav-active' : ''; ?>" href="doctors.php">
                        <span style="font-size: 1.00rem;">Doctors</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link d-flex align-items-center gap-2 nav-animate <?php echo basename($_SERVER['PHP_SELF']) == 'enquiries-list.php' ? 'nav-active' : ''; ?>" href="enquiries-list.php">
                        <span style="font-size: 1.00rem;">Enquiries</span>
                    </a>
                </li>
                <?php endif; ?>
                <?php if (hasRole('patient')): ?>
                <li class="nav-item">
                    <a class="nav-link d-flex align-items-center gap-2 nav-animate <?php echo (basename($_SERVER['PHP_SELF']) == 'dashboard-patient.php') ? 'nav-active' : ''; ?>" href="dashboard-patient.php">
                        <span style="font-size: 1.00rem;">My Dashboard</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link d-flex align-items-center gap-2 nav-animate <?php echo (basename($_SERVER['PHP_SELF']) == 'doctors-list.php') ? 'nav-active' : ''; ?>" href="doctors-list.php">
                        <span style="font-size: 1.00rem;">Doctors</span>
                    </a>
                </li>
                <?php endif; ?>
            </ul>

            <!-- Mobile-only User Profile (inside collapse) -->
            <ul class="navbar-nav d-lg-none mt-3">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle d-flex align-items-center gap-2" href="#" id="userDropdownMobile" role="button" data-bs-toggle="dropdown">
                        <span style="font-size: 1.00rem;"><?php echo htmlspecialchars($_SESSION['full_name']); ?></span>
                        <span class="badge bg-light text-dark ms-1" style="font-size: 0.90rem;"><?php echo ucfirst($_SESSION['role']); ?></span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end shadow rounded-3 p-2" style="min-width: 200px;">
                        <li>
                            <a class="dropdown-item d-flex align-items-center gap-2 py-2" href="forgot-password.php">
                                <i class="fas fa-key text-warning"></i> <span>Forgot Password</span>
                            </a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item d-flex align-items-center gap-2 py-2" href="logout.php">
                                <i class="fas fa-sign-out-alt text-danger"></i> <span>Logout</span>
                            </a>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>

        <!-- Desktop User Profile Right (outside collapse) -->
        <ul class="navbar-nav d-none d-lg-flex">
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle d-flex align-items-center gap-2" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                    <span style="font-size: 1.00rem;"><?php echo htmlspecialchars($_SESSION['full_name']); ?></span>
                    <span class="badge bg-light text-dark ms-1" style="font-size: 0.90rem;"><?php echo ucfirst($_SESSION['role']); ?></span>
                </a>
                <ul class="dropdown-menu dropdown-menu-end shadow rounded-3 p-2" style="min-width: 200px;">
                    <li>
                        <a class="dropdown-item d-flex align-items-center gap-2 py-2" href="forgot-password.php">
                            <i class="fas fa-key text-warning"></i> <span>Forgot Password</span>
                        </a>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <a class="dropdown-item d-flex align-items-center gap-2 py-2" href="logout.php">
                            <i class="fas fa-sign-out-alt text-danger"></i> <span>Logout</span>
                        </a>
                    </li>
                </ul>
            </li>
        </ul>
    </div>
</nav>

<script>
    // Auto-close navbar on scroll (mobile)
    (function () {
        const collapseEl = document.getElementById('navbarNav');
        if (!collapseEl) return;

        function closeIfMobile() {
            // Bootstrap lg breakpoint is 992px
            if (window.innerWidth >= 992) return;
            if (!collapseEl.classList.contains('show')) return;
            if (!window.bootstrap || !bootstrap.Collapse) return;

            const instance = bootstrap.Collapse.getOrCreateInstance(collapseEl, { toggle: false });
            instance.hide();
        }

        window.addEventListener('scroll', closeIfMobile, { passive: true });
    })();
</script>