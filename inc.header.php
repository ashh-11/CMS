<?php
require_once 'inc.connections.php';

function isLoggedIn() {
  return isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true;
}

function getUserRole() {
  return isset($_SESSION['role']) ? $_SESSION['role'] : null;
}

function getUserLocationId() {
  return isset($_SESSION['location_id']) ? $_SESSION['location_id'] : null;
}

function redirectToLoginIfNotAuthenticated() {
  if (!isLoggedIn()) {
    header('location: login.php');
    exit;
  }
}

$page_title = $page_title ?? "Dashboard";
$breadcrumbs = $breadcrumbs ?? [];
$is_public_landing_page = $is_public_landing_page ?? false;
$is_public_track_page   = $is_public_track_page ?? false;

$default_dashboard_link = 'login.php';
if (isLoggedIn()) {
  if (getUserRole() == ROLE_ADMIN) {
    $default_dashboard_link = 'admin_dashboard.php';
  } elseif (getUserRole() == ROLE_AGENT) {
    $default_dashboard_link = 'agent_dashboard.php';
  } else {
    $default_dashboard_link = 'track_shipment.php';
  }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <title><?php echo htmlspecialchars($page_title); ?> - TrackIt Couriers</title>

  <link href="assets/img/logo.png" rel="icon">
  <link href="assets/img/apple-touch-icon.png" rel="apple-touch-icon">
  <link href="https://fonts.gstatic.com" rel="preconnect">
  <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700|Nunito:400,600,700,800|Poppins:400,500,600,700" rel="stylesheet">
  <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
  <link href="assets/vendor/boxicons/css/boxicons.min.css" rel="stylesheet">
  <link href="assets/vendor/remixicon/remixicon.css" rel="stylesheet">
  <link href="assets/css/style.css" rel="stylesheet">

  <style>
    body {
      background-color: #E5D1CF;
      font-family: 'Open Sans', sans-serif;
      color: #3B428A;
    }
    #header {
      background-color: #5A90D7;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }
    #header .logo-brand {
      display: inline-flex !important;
      align-items: center;
      gap: 8px;
      text-decoration: none;
    }
    #header .logo-brand img {
      height: 50px;
      width: auto;
    }
    #header .logo-text-trackit {
      font-family: "Nunito", sans-serif;
      font-size: 22px;
      font-weight: 800;
      color: #FFFFFF;
    }
    #header .logo-text-couriers {
      font-family: "Nunito", sans-serif;
      font-size: 22px;
      font-weight: 800;
      color: #FE6A53;
    }
    #main {
      background-color: #FFFFFF;
      margin: 20px auto;
      border-radius: 20px;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
      max-width: 1300px;
      padding: 30px;
      flex-grow: 1;
    }
  </style>
</head>

<body>
<header id="header" class="header fixed-top d-flex align-items-center">
  <div class="d-flex align-items-center justify-content-between">
    <a href="<?php echo htmlspecialchars($default_dashboard_link); ?>" class="logo logo-brand">
      <img src="assets/img/logo.png" alt="TrackIt Couriers Logo">
      <span class="logo-text-trackit">TrackIt</span>
      <span class="logo-text-couriers">Couriers</span>
    </a>

    <?php if (!$is_public_landing_page && !$is_public_track_page) { ?>
      <i class="bi bi-list toggle-sidebar-btn"></i>
    <?php } ?>
  </div>

  <?php if (!$is_public_landing_page && !$is_public_track_page) { ?>
    <div class="search-bar">
      <form class="search-form d-flex align-items-center" method="POST" action="#">
        <input type="text" name="query" placeholder="Search" title="Enter search keyword">
        <button type="submit" title="Search"><i class="bi bi-search"></i></button>
      </form>
    </div>
  <?php } ?>

  <nav class="header-nav ms-auto">
    <ul class="d-flex align-items-center">
      <?php if (!$is_public_landing_page && !$is_public_track_page && isLoggedIn()) { ?>
        <li class="nav-item dropdown pe-3">
          <a class="nav-link nav-profile d-flex align-items-center pe-0" href="#" data-bs-toggle="dropdown">
            <img src="assets/img/proimg.png" alt="Profile" class="rounded-circle">
            <span class="d-none d-md-block dropdown-toggle ps-2">
              <?php echo htmlspecialchars($_SESSION['username'] ?? 'User'); ?>
            </span>
          </a>
          <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow profile">
            <li class="dropdown-header">
              <h6><?php echo htmlspecialchars($_SESSION['username'] ?? 'User'); ?></h6>
              <span><?php echo htmlspecialchars(ucfirst(getUserRole() ?? '')); ?></span>
            </li>
            <li><hr class="dropdown-divider"></li>
            <li>
              <a class="dropdown-item d-flex align-items-center" href="logout.php">
                <i class="bi bi-box-arrow-right"></i><span>Sign Out</span>
              </a>
            </li>
          </ul>
        </li>
      <?php } else if (!$is_public_landing_page) { ?>
        <li class="nav-item"><a class="nav-link nav-icon" href="login.php"><i class="bi bi-box-arrow-in-right"></i> Login</a></li>
      <?php } ?>
    </ul>
  </nav>
</header>
