<?php
session_start();

$hostname = 'localhost';
$username_db = 'root';
$password_db = '';
$database_name = 'courier_system';

$conn = mysqli_connect($hostname, $username_db, $password_db, $database_name);

if ($conn === false) {
    die("Database connection FAILED! Error: " . mysqli_connect_error());
}

mysqli_set_charset($conn, "utf8mb4");

$page_title = "Track Your Shipment";
$breadcrumbs = [
    ["Home", "admin_dashboard.php"],
    ["Track Shipment", "#"]
];

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
      <div class="row justify-content-center">
        <div class="col-lg-8">
          <div class="card">
            <div class="card-body">
              <h5 class="card-title">Enter Tracking Number</h5>
              <form class="row g-3" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST">
                <div class="col-md-10">
                  <input type="text" class="form-control" id="trackingNumber" name="trackingNumber" placeholder="Enter Consignment Tracking Number" value="<?php echo htmlspecialchars($tracking_number); ?>" required>
                </div>
                <div class="col-md-2">
                  <button type="submit" class="btn btn-primary w-100">Track</button>
                </div>
              </form>

              <hr class="mt-4">

              <?php echo $message; ?>

              <?php if ($shipment_found) { ?>
              <div id="shipmentDetails">
                <h5 class="card-title">Shipment Details for: <span id="displayTrackingNumber"><?php echo htmlspecialchars($shipment_data['tracking_number']); ?></span></h5>
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
                  <a href="print_tracking_details.php?tracking_number=<?php echo htmlspecialchars($shipment_data['tracking_number']); ?>" target="_blank" class="btn btn-secondary">
                    <i class="bi bi-printer"></i> Print Tracking Details
                  </a>
                </div>
              </div>
              <?php } ?>

            </div>
          </div>
        </div>
      </div>
    </section>

  </main>

<?php include('inc.footer.php'); ?>