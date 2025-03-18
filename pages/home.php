<!-- Hero Section -->
<div class="hero-section bg-light py-5">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <h1 class="display-4 fw-bold mb-4">Your Digital Health Companion</h1>
                <p class="lead mb-4">Streamline your healthcare journey with our comprehensive EHR system. Access your medical records instantly, connect with healthcare providers, and manage your health information securely - all in one place.</p>
                <?php if (!$isLoggedIn): ?>
                    <div class="d-grid gap-2 d-md-flex justify-content-md-start">
                        <a href="index.php?page=login" class="btn btn-primary btn-lg px-4 me-md-2">Sign In</a>
                        <a href="index.php?page=register" class="btn btn-outline-primary btn-lg px-4">Get Started</a>
                    </div>
                <?php else: ?>
                    <div class="d-grid gap-2 d-md-flex justify-content-md-start">
                        <a href="index.php?page=dashboard" class="btn btn-primary btn-lg px-4">View Dashboard</a>
                    </div>
                <?php endif; ?>
            </div>
            <div class="col-lg-6">
                <img src="assets/images/hero-image.svg" alt="EHR System Illustration" class="img-fluid">
            </div>
        </div>
    </div>
</div>

<!-- Features Section -->
<div class="features-section py-5">
    <div class="container">
        <h2 class="text-center mb-5">Smart Healthcare Solutions</h2>
        <div class="row g-4">
            <div class="col-md-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body text-center">
                        <i class="fas fa-qrcode fa-3x text-primary mb-3"></i>
                        <h3 class="h5">Instant Access</h3>
                        <p>Scan QR codes to instantly access your complete medical history, prescriptions, and test results at any healthcare facility</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body text-center">
                        <i class="fas fa-shield-alt fa-3x text-primary mb-3"></i>
                        <h3 class="h5">Bank-Level Security</h3>
                        <p>Your health data is protected with military-grade encryption and HIPAA-compliant security protocols</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body text-center">
                        <i class="fas fa-user-md fa-3x text-primary mb-3"></i>
                        <h3 class="h5">Seamless Care</h3>
                        <p>Healthcare providers can instantly access your records, update information, and coordinate care efficiently</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Benefits Section -->
<div class="benefits-section py-5 bg-light">
    <div class="container">
        <h2 class="text-center mb-5">Transform Your Healthcare Experience</h2>
        <div class="row g-4">
            <div class="col-lg-6">
                <div class="d-flex align-items-start mb-4">
                    <i class="fas fa-check-circle text-success me-3 mt-1"></i>
                    <div>
                        <h4 class="h5">24/7 Access</h4>
                        <p>View your medical records, upcoming appointments, and medication schedules anytime, from any device</p>
                    </div>
                </div>
                <div class="d-flex align-items-start mb-4">
                    <i class="fas fa-check-circle text-success me-3 mt-1"></i>
                    <div>
                        <h4 class="h5">Smart Updates</h4>
                        <p>Receive instant notifications when your healthcare providers update your records or prescribe new medications</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="d-flex align-items-start mb-4">
                    <i class="fas fa-check-circle text-success me-3 mt-1"></i>
                    <div>
                        <h4 class="h5">Complete Health Profile</h4>
                        <p>Access your full medical history, including allergies, chronic conditions, and family health records</p>
                    </div>
                </div>
                <div class="d-flex align-items-start mb-4">
                    <i class="fas fa-check-circle text-success me-3 mt-1"></i>
                    <div>
                        <h4 class="h5">Data Privacy</h4>
                        <p>Your health information is protected with end-to-end encryption and strict access controls</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- CTA Section -->
<div class="cta-section py-5">
    <div class="container text-center">
        <h2 class="mb-4">Take Control of Your Health Journey</h2>
        <p class="lead mb-4">Join thousands of patients and healthcare providers who trust our EHR system for secure and efficient healthcare management.</p>
        <?php if (!$isLoggedIn): ?>
            <a href="index.php?page=register" class="btn btn-primary btn-lg px-4">Create Your Account</a>
        <?php endif; ?>
    </div>
</div> 