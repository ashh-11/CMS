<?php
$page_title = "Logout - TrackIt Courier";
$breadcrumbs = [
    ["Home", "user_dashboard.php"],
    ["Logout", "#"]
];
$is_public_landing_page = false;
include('inc.header.php');

$_SESSION = array();
session_destroy();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <title><?php echo htmlspecialchars($page_title); ?></title>
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

  <style>
    body {
      background-color: #819CDD;
      background-image: linear-gradient(135deg, #819CDD 0%, #ffffff 50%, #5A90D7 100%);
      display: flex;
      justify-content: center;
      align-items: center;
      min-height: 100vh;
      font-family: 'Open Sans', sans-serif;
      color: #3B428A;
      overflow: hidden; /* Prevent scrollbars from gradient stretching */
    }

    .section.logout-section {
      background: none;
      flex-grow: 1;
      display: flex;
      justify-content: center;
      align-items: center;
      width: 100%;
      position: relative; /* For z-index if needed */
      z-index: 2; /* Ensure it's above any subtle background elements */
    }

    .logout-container {
        background-color: #FFFFFF;
        border-radius: 15px;
        box-shadow: 0 15px 40px rgba(0, 0, 0, 0.2); /* More prominent shadow for depth */
        padding: 50px; /* Increased padding */
        max-width: 650px; /* Slightly wider */
        width: 90%;
        text-align: center;
        overflow: hidden;
        position: relative;
        z-index: 1;
    }

    .logo-container {
        margin-bottom: 30px;
    }
    .logo-container .logo-brand {
        display: inline-flex;
        align-items: center;
        text-decoration: none;
        gap: 8px;
    }
    .logo-container .logo-brand img {
        height: 80px;
        width: auto;
    }
    .logo-container .logo-brand .logo-text-trackit {
        font-family: "Nunito", sans-serif;
        font-size: 30px;
        font-weight: 800;
        color: #3B428A;
    }
    .logo-container .logo-brand .logo-text-couriers {
        font-family: "Nunito", sans-serif;
        font-size: 30px;
        font-weight: 800;
        color: #FE6A53;
    }
    .logo-container .logo-brand:hover .logo-text-trackit,
    .logo-container .logo-brand:hover .logo-text-couriers {
        color: #5A90D7;
    }

    .card-title {
      color: #3B428A;
      font-weight: 700;
      margin-bottom: 20px;
      font-size: 1.8em;
    }

    .small a {
      color: #7AAEEA;
      font-weight: 600;
    }
    .small a:hover {
        color: #5A90D7;
    }

    #header { display: none !important; }
    #footer { display: none !important; }
    .back-to-top { display: none !important; }
    .pagetitle { display: none !important; }

    /* Optional: Subtle background pattern for blending effect */
    body::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-image: radial-gradient(rgba(255,255,255,0.1) 1px, transparent 1px);
        background-size: 20px 20px;
        opacity: 0.3;
        z-index: 1;
    }
  </style>

</head>

<body>

  <main>
    <div class="container">

      <section class="section logout-section">
        <div class="container">
          <div class="row justify-content-center">
            <div class="col-lg-10 d-flex flex-column align-items-center justify-content-center">

              <div class="logout-container">
                <div class="logo-container">
                    <a href="index.html" class="logo-brand">
                      <img src="assets/img/logo.png" alt="TrackIt Couriers Logo">
                      <span class="logo-text-trackit">TrackIt</span>
                      <span class="logo-text-couriers">Couriers</span>
                    </a>
                </div>

                <div class="pt-4 pb-2">
                  <h5 class="card-title text-center pb-0 fs-4">You have been logged out successfully.</h5>
                  <p class="text-center small">Redirecting to the login page in 3 seconds...</p>
                  <p class="small mb-0">If you are not redirected, click <a href="login.php">here</a>.</p>
                </div>
              </div>

            </div>
          </div>
        </div>

      </section>

    </div>
  </main>

  <script src="assets/vendor/apexcharts/apexcharts.min.js"></script>
  <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="assets/vendor/chart.js/chart.umd.js"></script>
  <script src="assets/vendor/echarts/echarts.min.js"></script>
  <script src="assets/vendor/quill/quill.min.js"></script>
  <script src="assets/vendor/simple-datatables/simple-datatables.js"></script>
  <script src="assets/vendor/tinymce/tinymce.min.js"></script>
  <script src="assets/vendor/php-email-form/validate.js"></script>

  <script src="assets/js/main.js"></script>

</body>

</html>