<?php
$page_title = "Track Your Shipment";
$breadcrumbs = [
    ["Home", "user_dashboard.php"],
    ["Track Shipment", "#"]
];
$is_public_landing_page = true;

include('inc.header.php');

$tracking_number = '';
$shipment_found = false;
$shipment_data = null;
$tracking_history = [];
$message = '';

// --- existing PHP logic remains unchanged ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' || (isset($_GET['trackingNumber']) && !empty($_GET['trackingNumber']))) {
    $tracking_number = mysqli_real_escape_string($conn, trim($_POST['trackingNumber'] ?? $_GET['trackingNumber']));

    if (empty($tracking_number)) {
        $message = "<div class='alert alert-warning text-center'>Please enter a tracking number.</div>";
    } else {
        $sql_shipment = "..."; // your existing shipment query
        // (keep existing code here, unchanged)
    }
}
?>

<main id="main" class="main">
  <div class="container py-5">
    <div class="row justify-content-center align-items-center">

      <!-- LEFT CONTENT -->
      <div class="col-lg-6 col-md-10 mb-4">
        <div class="pagetitle text-center mb-4">
          <h1><?php echo htmlspecialchars($page_title); ?></h1>
        </div>

        <section class="section">
          <div class="card shadow-lg border-0 rounded-4">
            <div class="card-body p-4">
              <h5 class="card-title text-center mb-4" style="color: #5A90D7;">Enter Tracking Number</h5>
              
              <!-- Tracking Form -->
              <form class="row g-3 justify-content-center" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST">
                <div class="col-12 mb-3">
                  <input type="text" class="form-control form-control-lg" id="trackingNumber" name="trackingNumber" placeholder="Enter Consignment Tracking Number" value="<?php echo htmlspecialchars($tracking_number); ?>" required>
                </div>
                <div class="col-12 text-center">
  <a href="print_tracking_details.php" 
     class="btn btn-lg px-5" 
     style="background-color: #7EB4F2; color: white; display: inline-block; text-decoration: none;">
    Track
  </a>
</div>

              </form>

              <div class="mt-4">
                <?php echo $message; ?>
              </div>

              <!-- Shipment Results -->
              <?php if ($shipment_found) { ?>
                <!-- keep your existing shipment details block -->
              <?php } ?>

            </div>
          </div>
        </section>
      </div>

      <!-- RIGHT CONTENT -->
      <div class="col-lg-6 col-md-10 d-flex align-items-center justify-content-center">
        <img src="assets/img/userdash.jpg" alt="Delivery Illustration" class="img-fluid rounded-4 shadow-lg" style="max-height: 75vh;">
      </div>
    </div>
  </div>
</main>

<?php include('inc.footer.php'); ?>


<style>
body {
  background: #f8faff;
}

#main {
  margin-top: 80px;   /* space below header */
  margin-bottom: 40px; /* space above footer */
  padding: 20px 0;
}

.pagetitle h1 {
  color: #5A90D7;
  font-weight: 700;
  font-size: 2.4em;
}

.card {
  background: #fff;
  border-radius: 20px;
}

.form-control-lg {
  padding: 14px 18px;
  border-radius: 12px;
  border: 1px solid #d0d7e2;
  font-size: 1.1em;
}

.form-control-lg:focus {
  border-color: #5A90D7;
  box-shadow: 0 0 8px rgba(90, 144, 215, 0.3);
}

.btn {
  border-radius: 12px;
  font-weight: 600;
  letter-spacing: 0.5px;
}

.btn:hover {
  background-color: #5A90D7 !important;
}

.alert {
  border-radius: 10px;
}
</style>
