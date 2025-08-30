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
$is_public_track_page = $is_public_track_page ?? false; // New flag for track_shipment

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
  <meta content="" name="description">
  <meta content="" name="keywords">
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

  <style>
    /* Global styles for the new palette */
    body {
      background-color: #E5D1CF; /* Light Pink/Beige for outer background */
      font-family: 'Open Sans', sans-serif;
      color: #3B428A; /* Main Blue for general text, slightly darker variant for contrast */
    }

    /* Header styling */
    #header {
        background-color: #5A90D7; /* Main Blue for header background */
        border-bottom: none;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }
    #header .logo {
        gap: 10px;
    }
    #header .logo svg {
        height: 30px; /* Adjust SVG height to fit header */
        width: auto;
    }
    #header .logo svg path,
    #header .logo svg rect {
        fill: #FFFFFF !important; /* White icon on blue header */
    }
    #header .logo svg text {
        fill: #FFFFFF !important; /* White text on blue header */
        font-family: 'Nunito', sans-serif;
    }
    #header .logo:hover svg path,
    #header .logo:hover svg rect,
    #header .logo:hover svg text {
        fill: #E5D1CF !important; /* Light Pink/Beige on hover */
    }

    #header .toggle-sidebar-btn {
        color: #FFFFFF; /* White toggle icon */
    }
    #header .toggle-sidebar-btn:hover {
        color: #E5D1CF;
    }

    /* Search Bar in Header */
    #header .search-bar {
        border-radius: 8px;
        background-color: rgba(255, 255, 255, 0.2); /* Slight transparency */
        border: 1px solid rgba(255, 255, 255, 0.3);
    }
    #header .search-bar input {
        color: #FFFFFF;
        background: transparent;
        border: none;
    }
    #header .search-bar input::placeholder {
        color: rgba(255, 255, 255, 0.7);
    }
    #header .search-bar button {
        background: transparent;
        border: none;
        color: #FFFFFF;
    }
    #header .search-bar button:hover {
        color: #E5D1CF;
    }

    /* Header Navigation (User Profile, Notifications etc.) */
    #header .header-nav .nav-link {
        color: #FFFFFF !important; /* White nav links */
        font-weight: 500;
    }
    #header .header-nav .nav-link:hover {
        color: #E5D1CF !important; /* Light Pink/Beige on hover */
    }
    #header .nav-profile .dropdown-toggle {
        color: #FFFFFF !important; /* White profile name */
    }
    #header .dropdown-menu {
        background-color: #5A90D7; /* Main Blue for dropdown background */
        border: 1px solid #819CDD;
    }
    #header .dropdown-menu .dropdown-header h6,
    #header .dropdown-menu .dropdown-header span {
        color: #E5D1CF; /* Light Pink/Beige for dropdown header text */
    }
    #header .dropdown-menu .dropdown-item {
        color: #FFFFFF; /* White dropdown items */
    }
    #header .dropdown-menu .dropdown-item:hover {
        background-color: #819CDD; /* Light Purple on hover */
        color: #FFFFFF;
    }
    #header .dropdown-menu .dropdown-item i {
        color: #7AAEEA; /* Lighter Blue for dropdown icons */
    }

    /* Notifications Badge */
    #header .notifications .badge-number {
        background-color: #FE6A53 !important; /* Coral for notification badge */
        color: #FFFFFF !important;
    }

    /* Sidebar Styling (if included) */
    .sidebar {
        background-color: #FFFFFF; /* White sidebar background */
        border-right: 1px solid #E5D1CF; /* Light Pink/Beige border */
    }
    .sidebar-nav .nav-link {
        color: #3B428A; /* Main Blue for sidebar links */
    }
    .sidebar-nav .nav-link:hover {
        color: #5A90D7; /* Lighter on hover */
        background-color: #F0F2F5; /* Light grey hover background */
    }
    .sidebar-nav .nav-link.active {
        color: #FFFFFF;
        background-color: #5A90D7; /* Main Blue for active item */
    }
    .sidebar-nav .nav-link.collapsed {
        color: #819CDD; /* Light Purple for collapsed items */
    }
    .sidebar-nav .nav-link.collapsed:hover {
        color: #5A90D7;
    }
    .sidebar-nav .nav-link.active i, .sidebar-nav .nav-link.collapsed:hover i {
        color: #E5D1CF; /* Light Pink/Beige for icons in active/hovered state */
    }
    .sidebar-nav .nav-content a {
        color: #819CDD;
    }
    .sidebar-nav .nav-content a:hover, .sidebar-nav .nav-content a.active {
        color: #5A90D7;
        background-color: #F0F2F5;
    }
    .sidebar-nav .nav-heading {
        color: #B77253; /* Brownish Orange for section headings */
    }

    /* General Main Content Styling */
    #main {
      background-color: #FFFFFF;
      margin: 20px auto;
      border-radius: 20px;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
      max-width: 1300px;
      padding: 30px;
      flex-grow: 1;
    }

    .pagetitle h1 {
      color: #3B428A;
      font-weight: 700;
      font-size: 2.5em;
    }
    .breadcrumb .breadcrumb-item a {
      color: #7AAEEA;
    }
    .breadcrumb .breadcrumb-item.active {
      color: #5A90D7;
      font-weight: 600;
    }

    /* Card Styling */
    .card {
      border: none;
      box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
      border-radius: 15px;
    }
    .card-title {
      color: #3B428A;
      font-weight: 700;
      font-size: 1.5em;
    }

    /* Button Styling */
    .btn {
      font-weight: 600;
      border-radius: 8px;
      padding: 10px 20px;
    }
    .btn-primary {
      background-color: #7AAEEA !important;
      border-color: #7AAEEA !important;
      color: #FFFFFF !important;
      box-shadow: 0 4px 8px rgba(90, 144, 215, 0.2);
    }
    .btn-primary:hover {
      background-color: #5A90D7 !important;
      border-color: #5A90D7 !important;
      box-shadow: 0 6px 12px rgba(90, 144, 215, 0.3);
    }
    .btn-secondary {
        background-color: #819CDD !important;
        border-color: #819CDD !important;
        color: #FFFFFF !important;
        box-shadow: 0 4px 8px rgba(129, 156, 221, 0.2);
    }
    .btn-secondary:hover {
        background-color: #5A90D7 !important;
        border-color: #5A90D7 !important;
        box-shadow: 0 6px 12px rgba(90, 144, 215, 0.3);
    }

    /* Table Styling */
    .table thead th {
        background-color: #5A90D7;
        color: #FFFFFF;
        font-weight: 700;
    }
    .table-striped tbody tr:nth-of-type(odd) {
        background-color: #F0F2F5;
    }
    .table tbody tr {
        color: #3B428A;
    }

    /* Badge colors (adjust as needed for specific pages) */
    .badge {
      font-weight: 600;
      text-transform: capitalize;
    }
    .bg-success { background-color: #7AAEEA !important; } /* Lighter Blue for success */
    .bg-warning { background-color: #FE6A53 !important; } /* Coral for warning */
    .bg-primary { background-color: #5A90D7 !important; } /* Main Blue for primary status */
    .bg-secondary { background-color: #B77253 !important; } /* Brownish Orange for secondary status */
    .bg-danger { background-color: #FE6A53 !important; } /* Reusing Coral for danger/on hold */
    .bg-info { background-color: #819CDD !important; } /* Light Purple for info */

    /* Specific overrides for layout elements for different pages */
    /* Handled individually in each PHP file's <style> block or via inc.header.php flags */
  </style>
</head>

<body>
  <header id="header" class="header fixed-top d-flex align-items-center">
    <div class="d-flex align-items-center justify-content-between">
      <a href="<?php echo htmlspecialchars($default_dashboard_link); ?>" class="logo d-flex align-items-center">
        <!-- SVG Logo -->
        <svg width="180" height="35" viewBox="0 0 180 35" fill="none" xmlns="http://www.w3.org/2000/svg">
          <path d="M15.9 0C7.16 0 0 7.02 0 15.6C0 24.18 15.9 35 15.9 35S31.8 24.18 31.8 15.6C31.8 7.02 24.64 0 15.9 0ZM15.9 21.28A5.68 5.68 0 1 1 15.9 10.12 5.68 5.68 0 0 1 15.9 21.28Z" fill="#3B428A"/>
          <rect x="12" y="12" width="8" height="8" fill="#FE6A53"/>
          <text x="40" y="23" font-family="Nunito, sans-serif" font-size="22" font-weight="700" fill="#3B428A">TrackIt</text>
          <text x="115" y="23" font-family="Nunito, sans-serif" font-size="18" fill="#FE6A53">Couriers</text>
        </svg>
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

        <?php if (!$is_public_landing_page && !$is_public_track_page) { ?>
        <li class="nav-item d-block d-lg-none">
          <a class="nav-link nav-icon search-bar-toggle " href="#">
            <i class="bi bi-search"></i>
          </a>
        </li>
        <?php } ?>

        <?php if (!$is_public_landing_page && !$is_public_track_page &&isLoggedIn()) { ?>
        <li class="nav-item dropdown">
          <a class="nav-link nav-icon" href="#" data-bs-toggle="dropdown">
            <i class="bi bi-bell"></i>
            <span class="badge bg-primary badge-number">4</span>
          </a>
          <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow notifications">
            <li class="dropdown-header">You have 4 new notifications<a href="#"><span class="badge rounded-pill bg-primary p-2 ms-2">View all</span></a></li>
            <li><hr class="dropdown-divider"></li>
            <li class="notification-item"><i class="bi bi-exclamation-circle text-warning"></i><div><h4>Shipment Update</h4><p>Quae dolorem earum veritatis oditseno</p><p>30 min. ago</p></div></li>
            <li><hr class="dropdown-divider"></li>
            <li class="notification-item"><i class="bi bi-check-circle text-success"></i><div><h4>Sit rerum fuga</h4><p>Quae dolorem earum veritatis oditseno</p><p>2 hrs. ago</p></div></li>
            <li><hr class="dropdown-divider"></li>
            <li class="dropdown-footer"><a href="#">Show all notifications</a></li>
          </ul>
        </li>
        <?php } ?>

        <li class="nav-item dropdown pe-3">
          <?php if (isLoggedIn()) { ?>
            <a class="nav-link nav-profile d-flex align-items-center pe-0" href="#" data-bs-toggle="dropdown">
              <img src="assets/img/profile-img.jpg" alt="Profile" class="rounded-circle">
              <span class="d-none d-md-block dropdown-toggle ps-2"><?php echo htmlspecialchars($_SESSION['username'] ?? 'User'); ?></span>
            </a>
            <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow profile">
              <li class="dropdown-header"><h6><?php echo htmlspecialchars($_SESSION['username'] ?? 'User'); ?></h6><span><?php echo htmlspecialchars(ucfirst(getUserRole() ?? '')); ?></span></li>
              <li><hr class="dropdown-divider"></li>
              <li><a class="dropdown-item d-flex align-items-center" href="logout.php"><i class="bi bi-box-arrow-right"></i><span>Sign Out</span></a></li>
            </ul>
          <?php } else { ?>
            <a class="nav-link nav-icon" href="login.php">
              <i class="bi bi-box-arrow-in-right"></i> Login
            </a>
          <?php } ?>
        </li>
      </ul>
    </nav>
  </header>