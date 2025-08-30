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
<style>
  html, body {
    height: 100%;
    margin: 0;
    background-color: white; /* No background image, solid white */
    font-family: 'Open Sans', sans-serif;
    color: #3B428A;
  }

  body {
    display: flex;
    flex-direction: column;
  }

  #main {
    flex-grow: 1;
    padding: 30px;
    background-color: #FFFFFF;
    margin-top: calc(70px + 30px); /* Height of header (approx 70px) + desired gap (30px) */
    margin-bottom: 20px;
    margin-right: 20px;
    margin-left: calc(280px + 30px);
    border-radius: 20px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
    max-width: calc(100% - (280px + 30px + 20px));
  }

  body.toggle-sidebar #main {
      margin-left: calc(80px + 30px);
      max-width: calc(100% - (80px + 30px + 20px));
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
  .bg-success { background-color: #7AAEEA !important; }
  .bg-warning { background-color: #FE6A53 !important; }
  .bg-primary { background-color: #5A90D7 !important; }
  .bg-secondary { background-color: #B77253 !important; }
  .bg-danger { background-color: #FE6A53 !important; }
  .bg-info { background-color: #819CDD !important; }


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
      background-color: #FFFFFF;
      border-right: 1px solid #E5D1CF;
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
  .sidebar-nav .nav-link.active i, .sidebar-nav .nav-link.collapsed:hover i {
      color: #E5D1CF;
  }
  .sidebar-nav .nav-content a {
      color: #819CDD;
  }
  .sidebar-nav .nav-content a:hover, .sidebar-nav .nav-content a.active {
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
</style>