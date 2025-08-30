<?php
$page_title = "Admin Dashboard";
$breadcrumbs = [
    ["Home", "admin_dashboard.php"],
    ["Dashboard", "admin_dashboard.php"]
];

include('inc.header.php');
redirectToLoginIfNotAuthenticated();

if (getUserRole() !== ROLE_ADMIN) {
    if (getUserRole() == ROLE_AGENT) {
        header("location: agent_dashboard.php");
    } else {
        header("location: login.php");
    }
    exit;
}

$total_branches = 0;
$total_parcels = 0;
$total_staff = 0;

$sql_branches = "SELECT COUNT(*) FROM locations";
$result = mysqli_query($conn, $sql_branches);
if ($result) {
    $row = mysqli_fetch_row($result);
    $total_branches = $row[0];
    mysqli_free_result($result);
} else {
    $total_branches = 'Error';
}

$sql_parcels = "SELECT COUNT(*) FROM shipments";
$result = mysqli_query($conn, $sql_parcels);
if ($result) {
    $row = mysqli_fetch_row($result);
    $total_parcels = $row[0];
    mysqli_free_result($result);
} else {
    $total_parcels = 'Error';
}

$sql_staff = "SELECT COUNT(*) FROM users WHERE role = 'agent'";
$result = mysqli_query($conn, $sql_staff);
if ($result) {
    $row = mysqli_fetch_row($result);
    $total_staff = $row[0];
    mysqli_free_result($result);
} else {
    $total_staff = 'Error';
}

$dashboard_status_table = [];
$sql_status_counts = "SELECT current_status, COUNT(*) AS count FROM shipments GROUP BY current_status ORDER BY current_status ASC";
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
$sql_daily_activities = "SELECT status_update, COUNT(*) AS event_count
                         FROM shipment_tracking_history
                         WHERE DATE(event_timestamp) = '$today'
                         GROUP BY status_update";
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

if (empty(array_filter($chart_data_for_display))) {
    $chart_labels = ['No Daily Activity'];
    $chart_counts = [1];
    $chart_background_colors = ['#CCCCCC'];
} else {
    $chart_labels = array_keys($chart_data_for_display);
    $chart_counts = array_values(array_map('intval', $chart_data_for_display));
    $chart_background_colors = [
        '#5A90D7',
        '#FE6A53',
        '#7AAEEA',
        '#B77253',
        '#819CDD'
    ];
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

    <section class="section dashboard">
      <div class="row">

        <div class="col-lg-12">
          <div class="row">

            <div class="col-xxl-4 col-md-4">
              <div class="card info-card sales-card" style="border-left: 5px solid #FE6A53;">
                <div class="card-body">
                  <h5 class="card-title" style="color: #3B428A;">Total Branches</h5>
                  <div class="d-flex align-items-center">
                    <div class="card-icon rounded-circle d-flex align-items-center justify-content-center" style="background: #E5D1CF;">
                      <i class="bi bi-building" style="color: #FE6A53;"></i>
                    </div>
                    <div class="ps-3">
                      <h6><?php echo htmlspecialchars($total_branches); ?></h6>
                      <span class="text-muted small pt-2 ps-1">Branches</span>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div class="col-xxl-4 col-md-4">
              <div class="card info-card revenue-card" style="border-left: 5px solid #5A90D7;">
                <div class="card-body">
                  <h5 class="card-title" style="color: #3B428A;">Total Parcels</h5>
                  <div class="d-flex align-items-center">
                    <div class="card-icon rounded-circle d-flex align-items-center justify-content-center" style="background: #D7E1EE;">
                      <i class="bi bi-boxes" style="color: #5A90D7;"></i>
                    </div>
                    <div class="ps-3">
                      <h6><?php echo htmlspecialchars($total_parcels); ?></h6>
                      <span class="text-muted small pt-2 ps-1">Parcels</span>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div class="col-xxl-4 col-md-4">
              <div class="card info-card customers-card" style="border-left: 5px solid #819CDD;">
                <div class="card-body">
                  <h5 class="card-title" style="color: #3B428A;">Total Agents</h5>
                  <div class="d-flex align-items-center">
                    <div class="card-icon rounded-circle d-flex align-items-center justify-content-center" style="background: #E5D1CF;">
                      <i class="bi bi-people" style="color: #819CDD;"></i>
                    </div>
                    <div class="ps-3">
                      <h6><?php echo htmlspecialchars($total_staff); ?></h6>
                      <span class="text-muted small pt-2 ps-1">Agents</span>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div class="col-lg-6">
              <div class="card">
                <div class="card-body">
                  <h5 class="card-title" style="color: #3B428A;">Shipment Status Counts</h5>
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
              <div class="card">
                <div class="card-body">
                  <h5 class="card-title" style="color: #3B428A;">Daily Activities</h5>
                  <canvas id="dailyActivitiesPieChart" style="max-height: 400px;"></canvas>
                  <script>
                    document.addEventListener("DOMContentLoaded", () => {
                      new Chart(document.querySelector('#dailyActivitiesPieChart'), {
                        type: 'pie',
                        data: {
                          labels: <?php echo json_encode($chart_labels); ?>,
                          datasets: [{
                            label: 'Daily Activities',
                            data: <?php echo json_encode($chart_counts); ?>,
                            backgroundColor: <?php echo json_encode($chart_background_colors); ?>,
                            hoverOffset: 4
                          }]
                        }
                      });
                    });
                  </script>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
  </main>

  <?php include 'inc.footer.php'; ?>

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