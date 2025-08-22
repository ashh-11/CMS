<?php
include('inc.header.php');

$_SESSION = array();

session_destroy();

$page_title = "Logout";
$breadcrumbs = [
    ["Home", "admin_dashboard.php"],
    ["Logout", "#"]
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <title><?php echo htmlspecialchars($page_title); ?> - Courier Management System</title>
  <meta content="User logout page for the Courier Management System." name="description">
  <meta content="courier, logout, session" name="keywords">

  <meta http-equiv="refresh" content="3;url=login.php">

  <link href="assets/img/favicon.png" rel="icon">
  <link href="assets/img/apple-touch-icon.png" rel="apple-touch-icon">
  <link href="https://fonts.gstatic.com" rel="preconnect">
  <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i|Nunito:300,300i,400,400i,600,600i,700,700i|Poppins:300,300i,400,400i,500,500i,600,600i,700,700i" rel="stylesheet">
  <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
  <link href="assets/vendor/boxicons/css/boxicons.min.css" rel="stylesheet">
  <link href="assets/vendor/quill/quill.snow.css" rel="stylesheet">
  <link href="assets/vendor/quill/quill.bubble.css" rel="stylesheet">
  <link href="assets/vendor/remixicon/remixicon.css" rel="stylesheet">
  <link href="assets/vendor/simple-datatables/style.css" rel="stylesheet">
  <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
  <header id="header" class="header fixed-top d-flex align-items-center">
    <div class="d-flex align-items-center justify-content-between">
      <a href="admin_dashboard.php" class="logo d-flex align-items-center">
        <img src="assets/img/logo.png" alt="">
        <span class="d-none d-lg-block">Courier System</span>
      </a>
      <i class="bi bi-list toggle-sidebar-btn"></i>
    </div>
    <div class="search-bar">
      <form class="search-form d-flex align-items-center" method="POST" action="#">
        <input type="text" name="query" placeholder="Search" title="Enter search keyword">
        <button type="submit" title="Search"><i class="bi bi-search"></i></button>
      </form>
    </div>
    <nav class="header-nav ms-auto">
      <ul class="d-flex align-items-center">
        <li class="nav-item d-block d-lg-none">
          <a class="nav-link nav-icon search-bar-toggle " href="#">
            <i class="bi bi-search"></i>
          </a>
        </li>
        <li class="nav-item dropdown">
          <a class="nav-link nav-icon" href="#" data-bs-toggle="dropdown">
            <i class="bi bi-bell"></i>
            <span class="badge bg-primary badge-number">0</span>
          </a>
          <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow notifications">
            <li class="dropdown-header">You have 0 new notifications<a href="#"><span class="badge rounded-pill bg-primary p-2 ms-2">View all</span></a></li>
            <li><hr class="dropdown-divider"></li>
            <li class="dropdown-footer"><a href="#">Show all notifications</a></li>
          </ul>
        </li>
        <li class="nav-item dropdown pe-3">
          <a class="nav-link nav-profile d-flex align-items-center pe-0" href="#" data-bs-toggle="dropdown">
            <img src="assets/img/profile-img.jpg" alt="Profile" class="rounded-circle">
            <span class="d-none d-md-block dropdown-toggle ps-2">Guest</span>
          </a>
          <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow profile">
            <li class="dropdown-header"><h6>Guest</h6><span>Logged Out</span></li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item d-flex align-items-center" href="users-profile.html"><i class="bi bi-person"></i><span>My Profile</span></a></li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item d-flex align-items-center" href="login.php"><i class="bi bi-box-arrow-in-right"></i><span>Login</span></a></li>
          </ul>
        </li>
      </ul>
    </nav>
  </header>

  <main id="main" class="main">

    <div class="pagetitle">
      <h1><?php echo htmlspecialchars($page_title); ?></h1>
      <nav>
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="admin_dashboard.php">Home</a></li>
          <li class="breadcrumb-item active">Logout</li>
        </ol>
      </nav>
    </div>

    <section class="section">
      <div class="row justify-content-center">
        <div class="col-lg-6 text-center">
          <div class="card">
            <div class="card-body pt-3">
              <h5 class="card-title">You have been logged out successfully.</h5>
              <p>Redirecting to the login page in 3 seconds...</p>
              <p>If you are not redirected, click <a href="login.php">here</a>.</p>
            </div>
          </div>
        </div>
      </div>
    </section>

  </main>

<?php include('inc.footer.php'); ?>