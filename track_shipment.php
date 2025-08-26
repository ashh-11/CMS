<?php
$page_title = "Track Your Shipment";
$breadcrumbs = [
    ["Home", "user_dashboard.php"],
    ["Track Shipment", "#"]
];
$is_public_landing_page = false;
include('inc.header.php');

$tracking_number = '';
$shipment_found = false;
$shipment_data = null;
$tracking_history = [];
$message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' || (isset($_GET['tracking_number']) && !empty($_GET['tracking_number']))) {
    $tracking_number = mysqli_real_escape_string($conn, trim($_POST['trackingNumber'] ?? $_GET['tracking_number']));

    if (empty($tracking_number)) {
        $message = "<div class='alert alert-warning'>Please enter a tracking number.</div>";
    } else {
        $sql_shipment = "SELECT
                            s.shipment_id,
                            s.tracking_number,
                            s.current_status,
                            s.delivery_date,
                            sender.full_name AS sender_name,
                            sender.address AS sender_address,
                            receiver.full_name AS receiver_name,
                            receiver.address AS receiver_address,
                            s.courier_type,
                            cc.company_name,
                            from_loc.city_name AS from_city,
                            to_loc.city_name AS to_city
                        FROM
                            shipments s
                        JOIN
                            customers sender ON s.sender_id = sender.customer_id
                        JOIN
                            customers receiver ON s.receiver_id = receiver.customer_id
                        JOIN
                            locations from_loc ON s.from_location_id = from_loc.location_id
                        JOIN
                            locations to_loc ON s.to_location_id = to_loc.location_id
                        JOIN
                            courier_companies cc ON s.company_id = cc.company_id
                        WHERE
                            s.tracking_number = '$tracking_number'";

        $result_shipment = mysqli_query($conn, $sql_shipment);

        if ($result_shipment && mysqli_num_rows($result_shipment) == 1) {
            $shipment_data = mysqli_fetch_assoc($result_shipment);
            $shipment_found = true;
            mysqli_free_result($result_shipment);

            $sql_history = "SELECT status_update, event_location, event_timestamp
                            FROM shipment_tracking_history
                            WHERE shipment_id = '" . mysqli_real_escape_string($conn, $shipment_data['shipment_id']) . "'
                            ORDER BY event_timestamp ASC";

            $result_history = mysqli_query($conn, $sql_history);
            if ($result_history) {
                while ($row_history = mysqli_fetch_assoc($result_history)) {
                    $tracking_history[] = $row_history;
                }
                mysqli_free_result($result_history);
            } else {
                $message = "<div class='alert alert-danger'>Error fetching tracking history: " . mysqli_error($conn) . "</div>";
            }

        } else {
            $message = "<div class='alert alert-warning'>No shipment found with tracking number: <strong>" . htmlspecialchars($tracking_number) . "</strong>. Please check and try again.</div>";
        }
    }
}
?>
  <main id="main" class="main">
    <div class="row align-items-center justify-content-center h-100">

      <div class="col-lg-6 main-content-left">
        <div class="pagetitle">
          <h1><?php echo htmlspecialchars($page_title); ?></h1>
          <nav>
            <ol class="breadcrumb">
              <?php foreach ($breadcrumbs as $crumb) { ?>
                <li class="breadcrumb-item"><a href="<?php echo htmlspecialchars($crumb[1]); ?>"><?php echo htmlspecialchars($crumb[0]); ?></a></li>
              <?php } ?>
            </ol>
          </nav>
        </div>

        <section class="section">
          <div class="card">
            <div class="card-body">
              <h5 class="card-title" style="color: #5A90D7;">Enter Tracking Number</h5>
              <form class="row g-3" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST">
                <div class="col-md-10">
                  <input type="text" class="form-control" id="trackingNumber" name="trackingNumber" placeholder="Enter Consignment Tracking Number" value="<?php echo htmlspecialchars($tracking_number); ?>" required>
                </div>
                <div class="col-md-2">
                  <button type="submit" class="btn w-200" style="background-color: #7EB4F2; color: white;">Track</button>
                </div>
              </form>

              <hr class="mt-4">

              <?php echo $message; ?>

              <?php if ($shipment_found) { ?>
              <div id="shipmentDetails">
                <h5 class="card-title" style="color: #5A90D7;">Shipment Details for: <span id="displayTrackingNumber"><?php echo htmlspecialchars($shipment_data['tracking_number']); ?></span></h5>
                <div class="row">
                  <div class="col-md-6">
                    <h6>Sender Info:</h6>
                    <p>Name: <span><?php echo htmlspecialchars($shipment_data['sender_name']); ?></span></p>
                    <p>Address: <span><?php echo htmlspecialchars($shipment_data['sender_address']); ?></span></p>
                  </div>
                  <div class="col-md-6">
                    <h6>Receiver Info:</h6>
                    <p>Name: <span><?php echo htmlspecialchars($shipment_data['receiver_name']); ?></span></p>
                    <p>Address: <span><?php echo htmlspecialchars($shipment_data['receiver_address']); ?></span></p>
                  </div>
                </div>
                <div class="row mt-3">
                  <div class="col-md-6">
                    <p><strong>Courier Type:</strong> <span><?php echo htmlspecialchars($shipment_data['courier_type']); ?></span></p>
                    <p><strong>Courier Company:</strong> <span><?php echo htmlspecialchars($shipment_data['company_name']); ?></span></p>
                  </div>
                  <div class="col-md-6">
                    <p><strong>From:</strong> <span><?php echo htmlspecialchars($shipment_data['from_city']); ?></span></p>
                    <p><strong>To:</strong> <span><?php echo htmlspecialchars($shipment_data['to_city']); ?></span></p>
                  </div>
                </div>
                <p class="mt-3"><strong>Current Status:</strong> <span class="badge <?php
                    switch($shipment_data['current_status']) {
                        case 'delivered': echo 'bg-success'; break;
                        case 'in_transit': echo 'bg-warning'; break;
                        case 'out_for_delivery': echo 'bg-primary'; break;
                        case 'shipment_booked': echo 'bg-secondary'; break;
                        case 'on_hold': echo 'bg-danger'; break;
                        default: echo 'bg-info'; break;
                    }
                ?>"><?php echo htmlspecialchars(ucwords(str_replace('_', ' ', $shipment_data['current_status']))); ?></span></p>
                <p><strong>Expected Delivery Date:</strong> <span><?php echo htmlspecialchars($shipment_data['delivery_date']); ?></span></p>

                <h6 class="mt-4">Tracking History:</h6>
                <div class="activity">
                  <?php if (!empty($tracking_history)) {
                      foreach ($tracking_history as $history_item) { ?>
                          <div class="activity-item d-flex">
                            <div class="activite-label"><?php echo htmlspecialchars(date('d M, Y H:i', strtotime($history_item['event_timestamp']))); ?></div>
                            <i class='bi bi-circle-fill activity-badge text-info align-self-start'></i>
                            <div class="activity-content">
                              <?php echo htmlspecialchars(ucwords(str_replace('_', ' ', $history_item['status_update']))) . (empty($history_item['event_location']) ? '' : ' - ' . htmlspecialchars($history_item['event_location'])); ?>
                            </div>
                          </div>
                      <?php }
                  } else { ?>
                      <p>No tracking history available yet.</p>
                  <?php } ?>
                </div>

                <div class="text-center mt-4">
                  <a href="print_tracking_details.php?tracking_number=<?php echo htmlspecialchars($shipment_data['tracking_number']); ?>" target="_blank" class="btn btn-secondary" style="background-color: #819CDD; color: white;">
                    <i class="bi bi-printer"></i> Print Tracking Details
                  </a>
                </div>
              </div>
              <?php } ?>

            </div>
          </div>
        </section>
      </div>

      <div class="col-lg-6 d-flex align-items-center justify-content-center main-content-right">
        <img src="assets/img/userdash.jpg" alt="Delivery Illustration" class="img-fluid" style="max-height: 80vh;">
      </div>

    </div>
  </main>

<?php include('inc.footer.php'); ?>

<style>
  html, body {
    height: 100%;
    margin: 0;
    background-color: white; /* Main page background is white */
  }

  body {
    display: flex;
    flex-direction: column;
  }

  #main {
    flex-grow: 1;
    display: flex; /* Make main a flex container for its row content */
    align-items: center; /* Vertically center content */
    padding: 30px; /* Overall padding for the main area */
  }

  .main-content-left {
    background-color: white; /* Left column background, explicit white */
    padding: 30px;
    border-radius: 15px;
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
    height: fit-content; /* Adjust height to content */
  }

  .main-content-right {
    display: flex;
    align-items: center;
    justify-content: center;
  }

  .pagetitle h1 {
    color: #5A90D7;
    font-weight: 700;
    text-align: left; /* Align title left */
    margin-bottom: 15px;
    font-size: 2.2em;
  }

  .breadcrumb {
    justify-content: flex-start; /* Align breadcrumbs left */
    background-color: transparent;
    padding: 0;
    margin-bottom: 20px;
  }
  .breadcrumb .breadcrumb-item a {
    color: #7AAEEA;
  }
  .breadcrumb .breadcrumb-item.active {
    color: #5A90D7;
    font-weight: 600;
  }

  .btn-primary {
      background-color: #7EB4F2 !important;
      border-color: #7EB4F2 !important;
      font-weight: 600;
  }
  .btn-primary:hover {
      background-color: #5A90D7 !important;
      border-color: #5A90D7 !important;
  }

  .btn-secondary {
      background-color: #819CDD !important;
      border-color: #819CDD !important;
      font-weight: 600;
  }
  .btn-secondary:hover {
      background-color: #5A90D7 !important;
      border-color: #5A90D7 !important;
  }

  #header .d-flex.align-items-center.justify-content-between .toggle-sidebar-btn {
      display: none !important;
  }
  #header .search-bar {
      display: none !important;
  }
  #header .header-nav .nav-item:not(.pe-3) {
      display: none !important;
  }
  #header .logo span {
      color: #5A90D7 !important;
      font-weight: bold;
  }
  #header .header-nav .nav-link {
      color: #5A90D7 !important;
      font-weight: 600;
  }
  #header .header-nav .nav-link:hover {
      color: #7AAEEA !important;
  }

  .header-nav .nav-item.d-block.d-lg-none { display: none !important; }
</style>