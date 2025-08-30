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
$_SESSION['username'] = 'Agent Lahore';
$_SESSION['location_id'] = 2; // TEMPORARY: Assuming agent is assigned to Lahore (location_id 2)

$page_title = "Agent Dashboard";
$breadcrumbs = [
  ["Home", "agent_dashboard.php"],
  ["Dashboard", "agent_dashboard.php"]
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

$total_shipments = 0;
$in_progress_shipments = 0;
$delivered_shipments = 0;

$sql_total = "SELECT COUNT(*) FROM shipments WHERE from_location_id = '" . mysqli_real_escape_string($conn, $agent_location_id) . "' OR to_location_id = '" . mysqli_real_escape_string($conn, $agent_location_id) . "'";
$result_total = mysqli_query($conn, $sql_total);
if ($result_total) {
  $row = mysqli_fetch_row($result_total);
  $total_shipments = $row[0];
  mysqli_free_result($result_total);
} else {
  $total_shipments = 'Error';
}

$sql_in_progress = "SELECT COUNT(*) FROM shipments WHERE (from_location_id = '" . mysqli_real_escape_string($conn, $agent_location_id) . "' OR to_location_id = '" . mysqli_real_escape_string($conn, $agent_location_id) . "') AND current_status IN ('shipment_booked', 'in_transit', 'out_for_delivery', 'on_hold')";
$result_in_progress = mysqli_query($conn, $sql_in_progress);
if ($result_in_progress) {
  $row = mysqli_fetch_row($result_in_progress);
  $in_progress_shipments = $row[0];
  mysqli_free_result($result_in_progress);
} else {
  $in_progress_shipments = 'Error';
}

$sql_delivered = "SELECT COUNT(*) FROM shipments WHERE (from_location_id = '" . mysqli_real_escape_string($conn, $agent_location_id) . "' OR to_location_id = '" . mysqli_real_escape_string($conn, $agent_location_id) . "') AND current_status = 'delivered'";
$result_delivered = mysqli_query($conn, $sql_delivered);
if ($result_delivered) {
  $row = mysqli_fetch_row($result_delivered);
  $delivered_shipments = $row[0];
  mysqli_free_result($result_delivered);
} else {
  $delivered_shipments = 'Error';
}

$dashboard_status_table = [];
$sql_status_counts = "SELECT current_status, COUNT(*) AS count FROM shipments WHERE from_location_id = '" . mysqli_real_escape_string($conn, $agent_location_id) . "' OR to_location_id = '" . mysqli_real_escape_string($conn, $agent_location_id) . "' GROUP BY current_status ORDER BY current_status ASC";
$result = mysqli_query($conn, $sql_status_counts);
$status_raw_counts = [];
if ($result) {
  while ($row = mysqli_fetch_assoc($result)) {
    $status_raw_counts[$row['current_status']] = $row['count'];
  }
  mysqli_free_result($result);
}

$dashboard_status_table = [
  'Item Accepted by Courier' => $status_raw_counts['shipment_booked'] ?? 0,
  'Collected' => 0,
  'Shipped' => 0,
  'In-Transit' => $status_raw_counts['in_transit'] ?? 0,
  'Out of Delivery' => $status_raw_counts['out_for_delivery'] ?? 0,
];

$chart_labels = [];
$chart_counts = [];

$today = date('Y-m-d');
$sql_daily_activities = "SELECT sth.status_update, COUNT(*) AS event_count
                         FROM shipment_tracking_history sth
                         JOIN shipments s ON sth.shipment_id = s.shipment_id
                         WHERE (s.from_location_id = '" . mysqli_real_escape_string($conn, $agent_location_id) . "' OR s.to_location_id = '" . mysqli_real_escape_string($conn, $agent_location_id) . "')
                         AND DATE(sth.event_timestamp) = '$today'
                         GROUP BY sth.status_update";
$result_daily = mysqli_query($conn, $sql_daily_activities);

$raw_daily_counts = [];
if ($result_daily) {
  while ($row = mysqli_fetch_assoc($result_daily)) {
    $raw_daily_counts[$row['status_update']] = $row['event_count'];
  }
  mysqli_free_result($result_daily);
}

$chart_data_for_display = [
  'Item Accepted by Courier' => ($raw_daily_counts['shipment_booked'] ?? 0),
  'Collected'                => ($raw_daily_counts['collected'] ?? (($raw_daily_counts['shipment_booked'] ?? 0) / 2)),
  'Shipped'                  => ($raw_daily_counts['in_transit'] ?? 0) > 0 ? ceil(($raw_daily_counts['in_transit'] ?? 0) / 2) : 0,
  'In-Transit'               => ($raw_daily_counts['in_transit'] ?? 0) > 0 ? floor(($raw_daily_counts['in_transit'] ?? 0) / 2) : 0,
  'Out of Delivery'          => ($raw_daily_counts['out_for_delivery'] ?? 0),
];

$chart_labels = array_keys($chart_data_for_display);
$chart_counts = array_values($chart_data_for_display);

mysqli_close($conn);
?>
<?php include('inc.sidebar.php'); ?>

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

  <section class="section dashboard">
    <div class="row">

      <div class="col-lg-12">
        <div class="row">

          <div class="col-xxl-4 col-md-4">
            <div class="card info-card sales-card" style="border-left: 5px solid #ff6699;">
              <div class="card-body">
                <h5 class="card-title">Total Shipments <span>| <?php echo htmlspecialchars($agent_location_name); ?></span></h5>
                <div class="d-flex align-items-center">
                  <div class="card-icon rounded-circle d-flex align-items-center justify-content-center" style="background: #ffe6ee;">
                    <i class="bi bi-box-seam" style="color: #ff6699;"></i>
                  </div>
                  <div class="ps-3">
                    <h6><?php echo htmlspecialchars($total_shipments); ?></h6>
                    <span class="text-muted small pt-2 ps-1">Shipments</span>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="col-xxl-4 col-md-4">
            <div class="card info-card revenue-card" style="border-left: 5px solid #28a745;">
              <div class="card-body">
                <h5 class="card-title">In Progress <span>| <?php echo htmlspecialchars($agent_location_name); ?></span></h5>
                <div class="d-flex align-items-center">
                  <div class="card-icon rounded-circle d-flex align-items-center justify-content-center" style="background: #e6ffe6;">
                    <i class="bi bi-hourglass-split" style="color: #28a745;"></i>
                  </div>
                  <div class="ps-3">
                    <h6><?php echo htmlspecialchars($in_progress_shipments); ?></h6>
                    <span class="text-muted small pt-2 ps-1">Parcels</span>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="col-xxl-4 col-md-4">
            <div class="card info-card customers-card" style="border-left: 5px solid #007bff;">
              <div class="card-body">
                <h5 class="card-title">Delivered <span>| <?php echo htmlspecialchars($agent_location_name); ?></span></h5>
                <div class="d-flex align-items-center">
                  <div class="card-icon rounded-circle d-flex align-items-center justify-content-center" style="background: #e6f2ff;">
                    <i class="bi bi-check-circle" style="color: #007bff;"></i>
                  </div>
                  <div class="ps-3">
                    <h6><?php echo htmlspecialchars($delivered_shipments); ?></h6>
                    <span class="text-muted small pt-2 ps-1">Parcels</span>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="col-lg-6">
            <div class="card">
              <div class="card-body">
                <h5 class="card-title">Shipment Status Counts <span>| <?php echo htmlspecialchars($agent_location_name); ?></span></h5>
                <table class="table table-striped">
                  <thead>
                    <tr>
                      <th scope="col">Sr.No</th>
                      <th scope="col">Status</th>
                      <th scope="col">Count</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php $s_no = 1; ?>
                    <?php foreach ($dashboard_status_table as $status_name => $count) { ?>
                      <tr>
                        <th scope="row"><?php echo htmlspecialchars($s_no++); ?></th>
                        <td><?php echo htmlspecialchars($status_name); ?></td>
                        <td><?php echo htmlspecialchars($count); ?></td>
                      </tr>
                    <?php } ?>
                  </tbody>
                </table>
              </div>
            </div>
          </div>

          <div class="col-lg-6">

          </div>
        </div>
      </div>
    </div>
  </section>

</main>

<?php include('inc.footer.php'); ?>
<style>
  html,
  body {
    height: 100%;
    margin: 0;
    background-color: white;
    /* No background image, solid white */
    font-family: 'Open Sans', sans-serif;
    color: #3B428A;
  }

  body {
    display: flex;
    flex-direction: column;
  }

  #main {
    margin-left: 280px;
    /* equal to sidebar width */
    margin-top: 70px;
    /* equal to header height */
    padding: 30px;
    background-color: #FFFFFF;
    border-radius: 20px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
    flex: 1;
    /* let content grow and push footer down */
    max-width: calc(100% - 300px);
    transition: all 0.3s ease;
  }

  body.toggle-sidebar #main {
    margin-left: 80px;
    /* collapsed sidebar */
    max-width: calc(100% - 100px);
  }


  .pagetitle h1 {
    color: #3B428A;
    font-weight: 700;
    text-align: left;
    margin-bottom: 15px;
    font-size: 2.5em;
  }

  .breadcrumb {
    justify-content: flex-start;
    background-color: transparent;
    padding: 0;
    margin-bottom: 25px;
    font-size: 0.9em;
  }

  .breadcrumb .breadcrumb-item a {
    color: #7AAEEA;
  }

  .breadcrumb .breadcrumb-item.active {
    color: #5A90D7;
    font-weight: 600;
  }

  .card {
    border: none;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
    border-radius: 15px;
    background-color: #FFFFFF;
  }

  .card-title {
    color: #3B428A;
    font-weight: 700;
    margin-bottom: 20px;
    font-size: 1.5em;
  }

  .card-body h5 {
    color: #3B428A;
  }

  .card-body h6 {
    color: #3B428A;
    font-weight: 600;
    margin-top: 15px;
    margin-bottom: 10px;
  }

  .card-body p {
    color: #819CDD;
    font-size: 0.95em;
  }

  .card-body strong {
    color: #333;
  }

  hr {
    border-top: 1px solid #E5D1CF;
    margin-top: 30px;
    margin-bottom: 30px;
  }

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

  .badge {
    font-weight: 600;
    text-transform: capitalize;
  }

  .bg-success {
    background-color: #7AAEEA !important;
  }

  .bg-warning {
    background-color: #FE6A53 !important;
  }

  .bg-primary {
    background-color: #5A90D7 !important;
  }

  .bg-secondary {
    background-color: #B77253 !important;
  }

  .bg-danger {
    background-color: #FE6A53 !important;
  }

  .bg-info {
    background-color: #819CDD !important;
  }


  #header .logo svg path,
  #header .logo svg rect {
    fill: #FFFFFF !important;
  }

  #header .logo svg text {
    fill: #FFFFFF !important;
  }

  #header .logo:hover svg path,
  #header .logo:hover svg rect,
  #header .logo:hover svg text {
    fill: #E5D1CF !important;
  }

  #header .toggle-sidebar-btn {
    color: #FFFFFF;
  }

  #header .toggle-sidebar-btn:hover {
    color: #E5D1CF;
  }

  .search-bar input {
    color: #FFFFFF;
    background: transparent;
    border: none;
  }

  .search-bar input::placeholder {
    color: rgba(255, 255, 255, 0.7);
  }

  .search-bar button {
    background: transparent;
    border: none;
    color: #E5D1CF;
  }

  .search-bar button:hover {
    color: #FFFFFF;
  }

  #header .header-nav .nav-link {
    color: #FFFFFF !important;
  }

  #header .header-nav .nav-link:hover {
    color: #E5D1CF !important;
  }

  #header .nav-profile .dropdown-toggle {
    color: #FFFFFF !important;
  }

  #header .dropdown-menu {
    background-color: #5A90D7;
    border: 1px solid #819CDD;
  }

  #header .dropdown-menu .dropdown-header h6,
  #header .dropdown-menu .dropdown-header span {
    color: #E5D1CF;
  }

  #header .dropdown-menu .dropdown-item {
    color: #FFFFFF;
  }

  #header .dropdown-menu .dropdown-item:hover {
    background-color: #819CDD;
    color: #FFFFFF;
  }

  #header .dropdown-menu .dropdown-item i {
    color: #7AAEEA;
  }

  .notifications .badge-number {
    background-color: #FE6A53 !important;
  }

  .sidebar {
    width: 280px;
    /* fixed width */
    position: fixed;
    /* stick to the left */
    top: 70px;
    /* below header (adjust if header height differs) */
    left: 0;
    bottom: 0;
    overflow-y: auto;
    padding-top: 20px;
    z-index: 100;
  }

  .sidebar-nav .nav-link {
    color: #3B428A;
  }

  .sidebar-nav .nav-link:hover {
    color: #5A90D7;
    background-color: #F0F2F5;
  }

  .sidebar-nav .nav-link.active {
    color: #FFFFFF;
    background-color: #5A90D7;
  }

  .sidebar-nav .nav-link.collapsed {
    color: #819CDD;
  }

  .sidebar-nav .nav-link.collapsed:hover {
    color: #5A90D7;
  }

  .sidebar-nav .nav-link.active i,
  .sidebar-nav .nav-link.collapsed:hover i {
    color: #E5D1CF;
  }

  .sidebar-nav .nav-content a {
    color: #819CDD;
  }

  .sidebar-nav .nav-content a:hover,
  .sidebar-nav .nav-content a.active {
    color: #5A90D7;
    background-color: #F0F2F5;
  }

  .sidebar-nav .nav-heading {
    color: #B77253;
  }

  .card.sales-card .card-icon {
    background: #E5D1CF !important;
  }

  .card.sales-card .card-icon i {
    color: #FE6A53 !important;
  }

  .card.revenue-card .card-icon {
    background: rgba(90, 144, 215, 0.2) !important;
  }

  .card.revenue-card .card-icon i {
    color: #5A90D7 !important;
  }

  .card.customers-card .card-icon {
    background: rgba(129, 156, 221, 0.2) !important;
  }

  .card.customers-card .card-icon i {
    color: #819CDD !important;
  }

  .footer {
    flex-shrink: 0;
    /* keeps footer from being overlapped */
    margin-left: 280px;
    /* align with sidebar */
  }
</style>