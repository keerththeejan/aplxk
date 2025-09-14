<?php
if (!isset($pageTitle)) { $pageTitle = SITE_NAME . ' - Home'; }
if (session_status() === PHP_SESSION_NONE) { session_start(); }
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="auto">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <meta name="theme-color" content="#253b2f" />
  <meta name="description" content="Logistip – Transport & Logistics HTML template" />
  <title><?php echo esc($pageTitle); ?></title>
  <link rel="icon" href="assets/images/fav-icon/favicon.ico" />

  <!-- Google Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet" />

  <!-- Core CSS (CDNs or your local assets/) -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.5.2/css/all.min.css" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/animate.css@4.1.1/animate.min.css" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.css" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/magnific-popup/dist/magnific-popup.css" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/leaflet.css" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/leaflet-routing-machine@3.2.12/dist/leaflet-routing-machine.css" />
  <!-- Theme CSS -->
  <link rel="stylesheet" href="assets/css/style.css" />
  <link rel="stylesheet" href="assets/css/theme.css" />
</head>
<body>
  <div class="wrapper">

    <!-- Header -->
    <header class="section-header header-1 sticky-navbar">
      <!-- Top bar -->
      <div class="top-bar small text-white" data-bs-theme="dark">
        <div class="container d-flex flex-wrap align-items-center justify-content-between py-2">
          <div class="d-flex flex-wrap gap-3 align-items-center">
            <span><i class="fa-solid fa-envelope me-2"></i>info@logistics.com</span>
            <span class="d-none d-md-inline"><i class="fa-solid fa-location-dot me-2"></i>265 New Ave, California, USA</span>
            <span class="d-none d-lg-inline"><i class="fa-solid fa-phone me-2"></i>(+0123) 2345 6789</span>
          </div>
          <div class="d-flex align-items-center gap-3">
            <span class="d-none d-md-inline">Follow Us:</span>
            <a class="text-white-50 hover-white" href="#"><i class="fab fa-facebook-f"></i></a>
            <a class="text-white-50 hover-white" href="#"><i class="fab fa-twitter"></i></a>
            <a class="text-white-50 hover-white" href="#"><i class="fab fa-instagram"></i></a>
            <a class="text-white-50 hover-white" href="#"><i class="fab fa-linkedin-in"></i></a>
            <span class="vr text-white-50 mx-2"></span>
            <?php if (!empty($_SESSION['is_admin'])): ?>
              <a class="text-white" href="admin/">Admin</a>
              <a class="text-white-50" href="admin/?logout=1">Logout</a>
            <?php else: ?>
              <a class="text-white-50" href="admin/">Admin Login</a>
            <?php endif; ?>
          </div>
        </div>
      </div>

      <!-- Main navbar -->
      <nav class="navbar navbar-expand-xl logistics-navbar">
        <div class="container">
          <a class="navbar-brand d-flex align-items-center" href="./">
            <?php $siteLogo = function_exists('getSetting') ? (getSetting('site_logo_url','') ?? '') : ''; ?>
            <?php if ($siteLogo): ?>
              <img src="<?php echo esc($siteLogo); ?>" alt="<?php echo esc(SITE_NAME); ?>" style="height:34px" class="me-2" />
            <?php else: ?>
              <span class="brand-mark me-2"><i class="fa-solid fa-truck-fast"></i></span>
              <span class="fw-bold">Logistip</span>
            <?php endif; ?>
          </a>

          <button class="navbar-toggler" type="button" data-bs-toggle="offcanvas" data-bs-target="#logisticsNavbar" aria-controls="logisticsNavbar" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
          </button>

          <div class="collapse navbar-collapse" id="desktopNavbar">
            <ul class="navbar-nav mx-auto mb-2 mb-xl-0">
              <li class="nav-item"><a class="nav-link active" href="/">Home</a></li>
              <li class="nav-item"><a class="nav-link" href="#about">About Us</a></li>
              <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#services" role="button" data-bs-toggle="dropdown" aria-expanded="false">Services</a>
                <ul class="dropdown-menu">
                  <li><a class="dropdown-item" href="#services">Air Freight</a></li>
                  <li><a class="dropdown-item" href="#services">Road Freight</a></li>
                  <li><a class="dropdown-item" href="#services">Ocean Freight</a></li>
                </ul>
              </li>
              <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">Pages</a>
                <ul class="dropdown-menu">
                  <li><a class="dropdown-item" href="#quote">Get Quote</a></li>
                  <li><a class="dropdown-item" href="#">Blog</a></li>
                  <li><a class="dropdown-item" href="#">Contact</a></li>
                </ul>
              </li>
            </ul>
            <div class="d-flex align-items-center gap-3">
              <a href="#" class="btn btn-sm btn-outline-primary d-none d-xl-inline">Login</a>
              <a href="#tracking" class="btn btn-primary btn-sm px-4 tracking-btn">Tracking</a>
            </div>
          </div>
        </div>
      </nav>
    </header>

    <!-- Mobile / Offcanvas -->
    <nav class="navbar logistics-navbar" aria-label="Offcanvas navbar large">
      <div class="offcanvas offcanvas-end" tabindex="-1" id="logisticsNavbar">
        <div class="offcanvas-header">
          <h5 class="offcanvas-title">Menu</h5>
          <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body">
          <ul class="navbar-nav justify-content-end flex-grow-1">
            <li class="nav-item"><a class="nav-link" href="./">Home</a></li>
            <li class="nav-item"><a class="nav-link" href="#about">About Us</a></li>
            <li class="nav-item"><a class="nav-link" href="#services">Services</a></li>
            <li class="nav-item"><a class="nav-link" href="#quote">Get Quote</a></li>
            <li class="nav-item mt-3"><a class="btn btn-primary w-100" href="#tracking">Tracking</a></li>
          </ul>
        </div>
      </div>
    </nav>
