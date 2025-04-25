<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DKAT Pool Services</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="./assets/css/index.css">
    <link rel="stylesheet" href="./assets/css/responsive.css">
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg fixed-top">
        <div class="container">
            <a class="navbar-brand" href="../views/customer_view.php#Overview">DKAT Store<span>.</span></a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarsFurni" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item">
                        <a class="nav-link" href="https://www.facebook.com/profile.php?id=100092328014910">
                            <i class="bi bi-facebook me-2"></i>Facebook Page
                        </a>
                    </li>
                    <li class="nav-item ms-lg-3">
                        <a class="btn btn-nav" href="./personnel_login/login_form.php">Staff Login</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    <br><br>
    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6 mb-5 mb-lg-0">
                    <h1 class="hero-title">DKAT <span>Chlorine and Swimming Pool Supply Trading</span></h1>
                    <p class="hero-text">We're here to help with your swimming pool. We supply Pool chemicals and other Pool Products.</p>
                    <a href="./views/customer_view.php" class="btn-get-started">
                        View Website
                        <i class="bi bi-arrow-right ms-2"></i>
                    </a>
                </div>
                
                <div class="col-lg-6">
                    <div class="services-grid">
                        <div class="row g-4">
                            <div class="col-6">
                                <div class="image-card">
                                    <img src="./assets/images/Product1.png" alt="Pool Chemical Product" 
                                         class="img-fluid rounded hover-zoom" />
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="image-card">
                                    <img src="./assets/images/Product3.png" alt="Pool Supply Product" 
                                         class="img-fluid rounded hover-zoom" />
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="image-card">
                                    <img src="./assets/images/Product3.png" alt="Pool Maintenance Product" 
                                         class="img-fluid rounded hover-zoom" />
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="image-card">
                                    <img src="./assets/images/Product2.png" alt="Pool Equipment" 
                                         class="img-fluid rounded hover-zoom" />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>