<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to Netralay Hospital</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: url('images/back-img.jpg') no-repeat center center fixed;
            background-size: cover;
            min-height: 100vh;
        }

        /* Navbar Styles */
        .navbar {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(20px) saturate(180%);
            -webkit-backdrop-filter: blur(20px) saturate(180%);
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
            padding: 0.5rem 2rem;
            box-shadow: 0 4px 30px rgba(0, 0, 0, 0.3);
            min-height: 72px;
        }

        .navbar-brand {
            font-size: 1.5rem;
            font-weight: 700;
            color: white !important;
            text-shadow: 2px 2px 8px rgba(0, 0, 0, 0.4);
            letter-spacing: 0.5px;
        }

        .navbar-nav .nav-link {
            color: white !important;
            font-size: 1rem;
            font-weight: 500;
            margin-left: 0.5rem;
            padding: 0.5rem 1.125rem;
            border-radius: 8px;
            transition: all 0.3s ease;
            text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.3);
        }

        .navbar-nav .nav-link:hover {
            background: rgba(255, 255, 255, 0.25);
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }

        /* Main Content */
        .main-content {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }

        .hero-section {
            text-align: center;
            color: white;
            max-width: 800px;
        }

        .hero-section h1 {
            font-size: 4rem;
            font-weight: 800;
            margin-bottom: 1.5rem;
            text-shadow: 4px 4px 12px rgba(0, 0, 0, 0.5);
            animation: fadeInUp 1s ease;
            line-height: 1.2;
            letter-spacing: 1px;
        }

        .hero-section p {
            font-size: 1.4rem;
            margin-bottom: 2.5rem;
            text-shadow: 2px 2px 6px rgba(0, 0, 0, 0.4);
            animation: fadeInUp 1.2s ease;
            line-height: 1.6;
            max-width: 700px;
            margin-left: auto;
            margin-right: auto;
        }

        .hero-buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
            animation: fadeInUp 1.4s ease;
        }

        .hero-btn {
            padding: 1.1rem 2.8rem;
            font-size: 1.15rem;
            font-weight: 600;
            border: none;
            border-radius: 50px;
            cursor: pointer;
            transition: all 0.4s ease;
            text-decoration: none;
            display: inline-block;
            letter-spacing: 0.5px;
        }

        .btn-primary-custom {
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.95), rgba(255, 255, 255, 0.85));
            color: #007bff;
            box-shadow: 0 6px 25px rgba(0, 0, 0, 0.25);
        }

        .btn-primary-custom:hover {
            background: white;
            transform: translateY(-5px) scale(1.05);
            box-shadow: 0 10px 35px rgba(0, 0, 0, 0.35);
            color: #0056b3;
        }

        .btn-outline-custom {
            background: rgba(255, 255, 255, 0.1);
            color: white;
            border: 2px solid rgba(255, 255, 255, 0.8);
            box-shadow: 0 6px 25px rgba(0, 0, 0, 0.25);
        }

        .btn-outline-custom:hover {
            background: rgba(255, 255, 255, 0.25);
            border-color: white;
            transform: translateY(-5px) scale(1.05);
            box-shadow: 0 10px 35px rgba(0, 0, 0, 0.35);
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Responsive */
        /* Full Page Sections */
        .full-page-section {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 6rem 2rem;
        }

        .section-title {
            font-size: 3rem;
            font-weight: 700;
            margin-bottom: 3.5rem;
            text-align: center;
            text-shadow: 3px 3px 10px rgba(0, 0, 0, 0.4);
            letter-spacing: 1px;
        }

        /* Doctor Cards */
        .doctor-card {
            background: rgba(255, 255, 255, 0.25);
            border-radius: 20px;
            padding: 3.5rem 2.5rem;
            text-align: center;
            transition: all 0.4s ease;
            border: 1px solid rgba(255, 255, 255, 0.4);
            height: 100%;
            min-height: 550px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.15);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: flex-start;
        }

        .doctor-card:hover {
            transform: translateY(-15px) scale(1.03);
            box-shadow: 0 15px 45px rgba(0, 0, 0, 0.35);
            background: rgba(255, 255, 255, 0.35);
            border-color: rgba(255, 255, 255, 0.6);
        }

        .doctor-image {
            width: 250px;
            height: 250px;
            margin: 0 auto 1.5rem;
            border-radius: 50%;
            overflow: hidden;
            border: 5px solid rgba(255, 255, 255, 0.6);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.3);
            transition: all 0.4s ease;
        }

        .doctor-card:hover .doctor-image {
            transform: scale(1.1);
            border-color: rgba(255, 255, 255, 0.9);
            box-shadow: 0 12px 35px rgba(0, 0, 0, 0.4);
        }

        .doctor-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: all 0.4s ease;
        }

        .doctor-card:hover .doctor-image img {
            transform: scale(1.1);
        }

        .doctor-info h4 {
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 0.8rem;
            text-shadow: 2px 2px 6px rgba(0, 0, 0, 0.5);
            color: white;
            min-height: auto;
            line-height: 1.3;
        }

        .doctor-info .specialization {
            font-size: 1.25rem;
            color: rgba(255, 255, 255, 0.95);
            margin-bottom: 0.6rem;
            font-weight: 600;
            text-shadow: 1px 1px 4px rgba(0, 0, 0, 0.4);
            line-height: 1.4;
        }

        .doctor-info .experience {
            font-size: 1.1rem;
            color: rgba(255, 255, 255, 0.9);
            margin: 0;
            font-weight: 500;
            text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.3);
            line-height: 1.4;
        }

        @media (max-width: 768px) {
            .navbar {
                padding: 1rem;
            }

            .navbar-brand {
                font-size: 1.4rem;
            }

            .navbar-nav .nav-link {
                margin-left: 0;
                margin-top: 0.5rem;
            }

            .hero-section h1 {
                font-size: 2.5rem;
            }

            .hero-section p {
                font-size: 1.1rem;
            }

            .hero-btn {
                padding: 0.9rem 2rem;
                font-size: 1rem;
            }

            .section-title {
                font-size: 2rem;
            }

            .doctor-card {
                padding: 2rem 1.5rem;
                min-height: 400px;
            }

            .doctor-image {
                width: 150px;
                height: 150px;
            }

            .doctor-info h4 {
                font-size: 1.4rem;
            }

            .doctor-info .specialization {
                font-size: 1.1rem;
            }

            .doctor-info .experience {
                font-size: 1rem;
            }

            .full-page-section {
                padding: 4rem 1.5rem;
            }
        }

        @media (max-width: 576px) {
            .doctor-card {
                padding: 1.5rem 1rem;
                min-height: 350px;
            }

            .doctor-image {
                width: 120px;
                height: 120px;
            }

            .doctor-info h4 {
                font-size: 1.2rem;
            }

            .doctor-info .specialization {
                font-size: 1rem;
            }

            .doctor-info .experience {
                font-size: 0.9rem;
            }
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php">
                 Netralay Hospital
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" 
                    style="background: rgba(255, 255, 255, 0.3); border: none;">
                <span class="navbar-toggler-icon" style="filter: brightness(0) invert(1);"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="#home" style="font-size: 1.05rem;">
                             Home
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#doctors" style="font-size: 1.05rem;">
                             Doctors
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#about" style="font-size: 1.05rem;">
                             About
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#enquiry" style="font-size: 1.05rem;">
                            Enquiry
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="login.php" style="font-size: 1.05rem;">
                             Login
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section id="home" class="main-content">
        <div class="hero-section">
            <h1>Welcome to Netralay Hospital</h1>
            <p>Your trusted healthcare partner providing quality medical services with compassion and excellence.</p>
            <div class="hero-buttons">
                <a href="login.php" class="hero-btn btn-primary-custom">
                     Login to Portal
                </a>
                <a href="#doctors" class="hero-btn btn-primary-custom">
                     Our Doctors
                </a>
            </div>
        </div>
    </section>

    <!-- Doctors Section -->
    <section id="doctors" class="full-page-section" style="background: rgba(0, 0, 0, 0.3); color: white;">
        <div class="container-fluid px-5">
            <h2 class="section-title">Our Expert Doctors</h2>
            <div class="row g-5 justify-content-center align-items-stretch">
                <!-- Doctor Card 1 -->
                <div class="col-lg-3 col-md-6 col-sm-12">
                    <div class="doctor-card">
                        <div class="doctor-image">
                            <img src="#" alt="Doctor">
                        </div>
                        <div class="doctor-info">
                            <h4>Dr. John Smith</h4>
                            <p class="specialization">Cardiologist</p>
                            <p class="experience">15 years experience</p>
                        </div>
                    </div>
                </div>
                <!-- Doctor Card 2 -->
                <div class="col-lg-3 col-md-6 col-sm-12">
                    <div class="doctor-card">
                        <div class="doctor-image">
                            <img src="#" alt="Doctor" >
                        </div>
                        <div class="doctor-info">
                            <h4>Dr. Sarah Johnson</h4>
                            <p class="specialization">Neurologist</p>
                            <p class="experience">12 years experience</p>
                        </div>
                    </div>
                </div>
                <!-- Doctor Card 3 -->
                <div class="col-lg-3 col-md-6 col-sm-12">
                    <div class="doctor-card">
                        <div class="doctor-image">
                            <img src="#" alt="Doctor">
                        </div>
                        <div class="doctor-info">
                            <h4>Dr. Michael Chen</h4>
                            <p class="specialization">Orthopedic Surgeon</p>
                            <p class="experience">18 years experience</p>
                        </div>
                    </div>
                </div>
                <!-- Doctor Card 4 -->
                <div class="col-lg-3 col-md-6 col-sm-12">
                    <div class="doctor-card">
                        <div class="doctor-image">
                            <img src="#" alt="Doctor">
                        </div>
                        <div class="doctor-info">
                            <h4>Dr. Emily Brown</h4>
                            <p class="specialization">Pediatrician</p>
                            <p class="experience">10 years experience</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section id="about" class="full-page-section" style="background: rgba(0, 0, 0, 0.35); color: white;">
        <div class="container-fluid px-5">
            <div class="row justify-content-center">
                <div class="col-lg-11">
                    <div style="background: rgba(255, 255, 255, 0.15); border-radius: 25px; padding: 5rem 4rem; border: 1px solid rgba(255, 255, 255, 0.3); box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2); min-height: 500px;">
                        <h2 class="section-title" style="margin-bottom: 2.5rem;">About Netralay Hospital</h2>
                        <p style="font-size: 1.35rem; text-shadow: 1px 1px 4px rgba(0, 0, 0, 0.3); line-height: 1.9; margin-bottom: 2rem; text-align: center;">
                            Netralay Hospital is a leading healthcare institution dedicated to providing comprehensive medical services. 
                            Our state-of-the-art facilities, combined with experienced medical professionals, ensure that every patient 
                            receives the highest quality care. We offer a wide range of specialties including cardiology, neurology, 
                            orthopedics, pediatrics, and more.
                        </p>
                        <p style="font-size: 1.35rem; text-shadow: 1px 1px 4px rgba(0, 0, 0, 0.3); line-height: 1.9; text-align: center;">
                            Our patient management system allows for seamless appointment scheduling, medical history tracking, 
                            and efficient healthcare delivery.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Enquiry Section -->
    <section id="enquiry" class="full-page-section" style="background: rgba(0, 0, 0, 0.3); color: white;">
        <div class="container">
            <h2 class="section-title">Send Us an Enquiry</h2>
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div style="background: rgba(255, 255, 255, 0.25); border-radius: 25px; padding: 3rem; border: 1px solid rgba(255, 255, 255, 0.4); box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);">
                        <form action="enquiries.php" method="POST">
                            <div class="row">
                                <div class="col-md-6 mb-4">
                                    <label for="full_name" class="form-label" style="font-weight: 600; text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.3); font-size: 1.05rem; margin-bottom: 0.7rem;">Full Name *</label>
                                    <input type="text" class="form-control" id="full_name" name="full_name" required 
                                           style="background: rgba(255, 255, 255, 0.95); border: 2px solid rgba(255, 255, 255, 0.6); padding: 0.9rem; border-radius: 12px; font-size: 1rem; transition: all 0.3s ease;"
                                           onfocus="this.style.borderColor='white'; this.style.boxShadow='0 4px 15px rgba(0, 0, 0, 0.2)'"
                                           onblur="this.style.borderColor='rgba(255, 255, 255, 0.6)'; this.style.boxShadow='none'">
                                </div>
                                <div class="col-md-6 mb-4">
                                    <label for="email" class="form-label" style="font-weight: 600; text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.3); font-size: 1.05rem; margin-bottom: 0.7rem;">Email *</label>
                                    <input type="email" class="form-control" id="email" name="email" required 
                                           style="background: rgba(255, 255, 255, 0.95); border: 2px solid rgba(255, 255, 255, 0.6); padding: 0.9rem; border-radius: 12px; font-size: 1rem; transition: all 0.3s ease;"
                                           onfocus="this.style.borderColor='white'; this.style.boxShadow='0 4px 15px rgba(0, 0, 0, 0.2)'"
                                           onblur="this.style.borderColor='rgba(255, 255, 255, 0.6)'; this.style.boxShadow='none'">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-4">
                                    <label for="phone" class="form-label" style="font-weight: 600; text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.3); font-size: 1.05rem; margin-bottom: 0.7rem;">Phone *</label>
                                    <input type="tel" class="form-control" id="phone" name="phone" required 
                                           style="background: rgba(255, 255, 255, 0.95); border: 2px solid rgba(255, 255, 255, 0.6); padding: 0.9rem; border-radius: 12px; font-size: 1rem; transition: all 0.3s ease;"
                                           onfocus="this.style.borderColor='white'; this.style.boxShadow='0 4px 15px rgba(0, 0, 0, 0.2)'"
                                           onblur="this.style.borderColor='rgba(255, 255, 255, 0.6)'; this.style.boxShadow='none'">
                                </div>
                                <div class="col-md-6 mb-4">
                                    <label for="subject" class="form-label" style="font-weight: 600; text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.3); font-size: 1.05rem; margin-bottom: 0.7rem;">Subject *</label>
                                    <input type="text" class="form-control" id="subject" name="subject" required 
                                           style="background: rgba(255, 255, 255, 0.95); border: 2px solid rgba(255, 255, 255, 0.6); padding: 0.9rem; border-radius: 12px; font-size: 1rem; transition: all 0.3s ease;"
                                           onfocus="this.style.borderColor='white'; this.style.boxShadow='0 4px 15px rgba(0, 0, 0, 0.2)'"
                                           onblur="this.style.borderColor='rgba(255, 255, 255, 0.6)'; this.style.boxShadow='none'">
                                </div>
                            </div>
                            <div class="mb-4">
                                <label for="message" class="form-label" style="font-weight: 600; text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.3); font-size: 1.05rem; margin-bottom: 0.7rem;">Message *</label>
                                <textarea class="form-control" id="message" name="message" rows="5" required 
                                          style="background: rgba(255, 255, 255, 0.95); border: 2px solid rgba(255, 255, 255, 0.6); padding: 0.9rem; border-radius: 12px; font-size: 1rem; transition: all 0.3s ease; resize: vertical;"
                                          onfocus="this.style.borderColor='white'; this.style.boxShadow='0 4px 15px rgba(0, 0, 0, 0.2)'"
                                          onblur="this.style.borderColor='rgba(255, 255, 255, 0.6)'; this.style.boxShadow='none'"></textarea>
                            </div>
                            <div class="text-center">
                                <button type="submit" class="btn btn-lg" 
                                        style="background: linear-gradient(135deg, rgba(255, 255, 255, 0.95), rgba(255, 255, 255, 0.85)); color: #007bff; font-weight: 700; padding: 1rem 3.5rem; border-radius: 50px; border: none; box-shadow: 0 6px 25px rgba(0, 0, 0, 0.25); transition: all 0.4s ease; font-size: 1.1rem; letter-spacing: 0.5px;"
                                        onmouseover="this.style.background='white'; this.style.transform='translateY(-5px) scale(1.05)'; this.style.boxShadow='0 10px 35px rgba(0, 0, 0, 0.35)'; this.style.color='#0056b3'"
                                        onmouseout="this.style.background='linear-gradient(135deg, rgba(255, 255, 255, 0.95), rgba(255, 255, 255, 0.85))'; this.style.transform='translateY(0) scale(1)'; this.style.boxShadow='0 6px 25px rgba(0, 0, 0, 0.25)'; this.style.color='#007bff'">
                                    <i class="fas fa-paper-plane"></i> Send Enquiry
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer style="background: rgba(0, 0, 0, 0.6); padding: 1rem; color: white; text-align: center; border-top: 1px solid rgba(255, 255, 255, 0.3); box-shadow: 0 -4px 30px rgba(0, 0, 0, 0.2);">
        <p style="margin: 0; text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5); font-size: 1.05rem; font-weight: 500;">&copy; 2025 Netralay Hospital. All rights reserved.</p>
        <p style="margin: 0.7rem 0 0 0; font-size: 0.95rem; text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.4); color: rgba(255, 255, 255, 0.9);">Your trusted healthcare partner</p>
    </footer>

    <!-- Success Modal -->
    <div class="modal fade" id="successModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="background: linear-gradient(135deg, rgba(40, 167, 69, 0.95), rgba(25, 135, 84, 0.95)); backdrop-filter: blur(10px); border: 2px solid rgba(255, 255, 255, 0.3); border-radius: 20px; box-shadow: 0 8px 32px rgba(0, 0, 0, 0.4);">
                <div class="modal-body text-center py-5">
                    <div class="success-animation mb-4">
                        <i class="fas fa-check-circle" style="font-size: 5rem; color: white; animation: scaleIn 0.5s ease-out;"></i>
                    </div>
                    <h3 class="text-white fw-bold mb-3" style="text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);">Enquiry Submitted Successfully!</h3>
                    <p class="text-white mb-4" style="font-size: 1.1rem; text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.2);">Thank you for contacting us. We'll get back to you soon.</p>
                    <button type="button" class="btn btn-light btn-lg" data-bs-dismiss="modal" style="padding: 0.75rem 2.5rem; border-radius: 50px; font-weight: 600; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);">
                        <i class="fas fa-thumbs-up me-2"></i> Got it!
                    </button>
                </div>
            </div>
        </div>
    </div>

    <style>
        @keyframes scaleIn {
            0% {
                transform: scale(0);
                opacity: 0;
            }
            50% {
                transform: scale(1.2);
            }
            100% {
                transform: scale(1);
                opacity: 1;
            }
        }
        
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Check if enquiry was successfully submitted
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('enquiry') === 'success') {
            const successModal = new bootstrap.Modal(document.getElementById('successModal'));
            successModal.show();
            
            // Remove the query parameter from URL without reloading
            const newUrl = window.location.pathname;
            window.history.replaceState({}, document.title, newUrl);
            
            // Auto close after 5 seconds
            setTimeout(() => {
                successModal.hide();
            }, 5000);
        }
    </script>
</body>
</html>