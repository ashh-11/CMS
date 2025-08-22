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

$_SESSION['user_id'] = 2; // Assuming user_id 2 is an agent
$_SESSION['role'] = 'agent';
$_SESSION['username'] = 'Agent (No Login)';
$_SESSION['location_id'] = 2; // TEMPORARY: Assuming agent is assigned to Lahore (location_id 2)

$page_title = "Manage My Branch Shipments";
$breadcrumbs = [
    ["Home", "agent_dashboard.php"],
    ["Shipments", "agent_manage_shipments.php"],
    ["My Branch", "#"]
];

include('inc.header.php');

$agent_location_id = $_SESSION['location_id'] ?? null;
$agent_location_name = 'Your Branch';

if ($agent_location_id) {
    $sql_loc = "SELECT city_name FROM locations WHERE location_id = " . mysqli_real_escape_string($conn, $agent_location_id);
    $result_loc = mysqli_query($conn, $sql_loc);
    if ($result_loc && mysqli_num_rows($result_loc) > 0) {
        $row_loc = mysqli_fetch_assoc($result_loc);
        $agent_location_name = $row_loc['city_name'];
        mysqli_free_result($result_loc);
    }
}

$shipments = [];
$success_message = '';
$error_message = '';

$sql_shipments_agent = "SELECT
                    s.shipment_id,
                    s.tracking_number,
                    s.current_status,
                    s.delivery_date,
                    sender.full_name AS sender_name,
                    receiver.full_name AS receiver_name,
                    from_loc.city_name AS from_city,
                    to_loc.city_name AS to_city,
                    cc.company_name
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
                    s.from_location_id = '" . mysqli_real_escape_string($conn, $agent_location_id) . "' OR
                    s.to_location_id = '" . mysqli_real_escape_string($conn, $agent_location_id) . "'
                ORDER BY
                    s.created_at DESC";

$result = mysqli_query($conn, $sql_shipments_agent);
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $shipments[] = $row;
    }
    mysqli_free_result($result);
} else {
    $error_message = "<div class='alert alert-danger'>Error fetching shipments for branch: " . mysqli_error($conn) . "</div>";
}
?>
  <?php include('inc/inc.sidebar.php'); ?>

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
      <div class="row">
        <div class="col-lg-12">

          <div class="card">
            <div class="card-body">
              <h5 class="card-title">Shipments For <?php echo htmlspecialchars($agent_location_name); ?> Branch</h5>
              <p>View, search, and update shipments originating from or destined to your branch (<?php echo htmlspecialchars($agent_location_name); ?>).</p>

              <?php echo $success_message; ?>
              <?php echo $error_message; ?>

              <div class="d-flex justify-content-end mb-3">
                  <a href="admin_create_update_shipment.php" class="btn btn-primary"><i class="bi bi-plus-circle"></i> Create New Bill</a>
              </div>

              <table class="table datatable">
                <thead>
                  <tr>
                    <th scope="col">#</th>
                    <th scope="col">Tracking No.</th>
                    <th scope="col">Sender</th>
                    <th scope="col">Receiver</th>
                    <th scope="col">Origin</th>
                    <th scope="col">Destination</th>
                    <th scope="col">Company</th>
                    <th scope="col">Status</th>
                    <th scope="col">Delivery Date</th>
                    <th scope="col">Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if (!empty($shipments)) {
                      $counter = 1;
                      foreach ($shipments as $shipment) { ?>
                        <tr>
                            <th scope="row"><?php echo htmlspecialchars($counter++); ?></th>
                            <td><?php echo htmlspecialchars($shipment['tracking_number']); ?></td>
                            <td><?php echo htmlspecialchars($shipment['sender_name']); ?></td>
                            <td><?php echo htmlspecialchars($shipment['receiver_name']); ?></td>
                            <td><?php echo htmlspecialchars($shipment['from_city']); ?></td>
                            <td><?php echo htmlspecialchars($shipment['to_city']); ?></td>
                            <td><?php echo htmlspecialchars($shipment['company_name']); ?></td>
                            <td><span class="badge <?php
                                switch($shipment['current_status']) {
                                    case 'delivered': echo 'bg-success'; break;
                                    case 'in_transit': echo 'bg-warning'; break;
                                    case 'out_for_delivery': echo 'bg-primary'; break;
                                    case 'shipment_booked': echo 'bg-secondary'; break;
                                    case 'on_hold': echo 'bg-danger'; break;
                                    case 'returned': echo 'bg-info'; break;
                                    default: echo 'bg-info'; break;
                                }
                            ?>"><?php echo htmlspecialchars(ucwords(str_replace('_', ' ', $shipment['current_status']))); ?></span></td>
                            <td><?php echo htmlspecialchars($shipment['delivery_date']); ?></td>
                            <td>
                              <a href="admin_create_update_shipment.php?id=<?php echo htmlspecialchars($shipment['shipment_id']); ?>" class="btn btn-sm btn-info text-white" title="Edit Shipment"><i class="bi bi-pencil"></i></a>
                              <a href="print_tracking_details.php?tracking_number=<?php echo htmlspecialchars($shipment['tracking_number']); ?>" target="_blank" class="btn btn-sm btn-secondary" title="Print Tracking"><i class="bi bi-printer"></i></a>
                            </td>
                        </tr>
                    <?php }
                  } else { ?>
                      <tr><td colspan="10">No shipments found for your branch.</td></tr>
                  <?php } ?>
                </tbody>
              </table>

            </div>
          </div>

        </div>
      </div>
    </section>

  </main>

<?php include('inc.footer.php'); ?>