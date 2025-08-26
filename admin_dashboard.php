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
        '#3B428A',
        '#FE6A53',
        '#F5C3B3',
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
                    <div class="card-icon rounded-circle d-flex align-items-center justify-content-center" style="background: #F5C3B3;">
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
              <div class="card info-card revenue-card" style="border-left: 5px solid #3B428A;">
                <div class="card-body">
                  <h5 class="card-title" style="color: #3B428A;">Total Parcels</h5>
                  <div class="d-flex align-items-center">
                    <div class="card-icon rounded-circle d-flex align-items-center justify-content-center" style="background: #D7E1EE;">
                      <i class="bi bi-boxes" style="color: #3B428A;"></i>
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
              <div class="card info-card customers-card" style="border-left: 5px solid #B77253;">
                <div class="card-body">
                  <h5 class="card-title" style="color: #3B428A;">Total Staff</h5>
                  <div class="d-flex align-items-center">
                    <div class="card-icon rounded-circle d-flex align-items-center justify-content-center" style="background: #EDE6E0;">
                      <i class="bi bi-people" style="color: #B77253;"></i>
                    </div>
                    <div class="ps-3">
                      <h6><?php echo htmlspecialchars($total_staff); ?></h6>
                      <span class="text-muted small pt-2 ps-1">Staff Members</span>
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
<?php include('inc.footer.php'); ?>

<style>
  html, body {
    height: 100%;
    margin: 0;
    background-color: #E8EAEC;
    font-family: 'Open Sans', sans-serif;
    color: #212B3F;
  }

  body {
    display: flex;
    flex-direction: column;
  }

  #main {
    flex-grow: 1;
    padding: 30px;
    background-color: #FFFFFF;
    margin-top: 20px;
    margin-bottom: 20px;
    margin-right: 20px;
    margin-left: calc(280px + 30px);
    border-radius: 20px;
    box-shadow: 0 10px 30px rgba(33, 43, 63, 0.08);
    max-width: calc(100% - (280px + 30px + 20px));
  }

  body.toggle-sidebar #main {
      margin-left: calc(80px + 30px);
      max-width: calc(100% - (80px + 30px + 20px));
  }


  .pagetitle h1 {
    color: #212B3F;
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
    color: #556F7A;
  }
  .breadcrumb .breadcrumb-item.active {
    color: #212B3F;
    font-weight: 600;
  }

  .card {
    border: none;
    box-shadow: 0 5px 15px rgba(33, 43, 63, 0.05);
    border-radius: 15px;
    background-color: #FFFFFF;
  }

  .card-title {
    color: #212B3F;
    font-weight: 700;
    margin-bottom: 20px;
    font-size: 1.5em;
  }
  .card-body h5 {
      color: #212B3F;
  }
  .card-body h6 {
    color: #212B3F;
    font-weight: 600;
    margin-top: 15px;
    margin-bottom: 10px;
  }
  .card-body p {
    margin-bottom: 5px;
    font-size: 0.95em;
  }
  .card-body strong {
    color: #333;
  }

  hr {
    border-top: 1px solid #F5C3B3;
    margin-top: 30px;
    margin-bottom: 30px;
  }

  .table th, .table td {
      color: #212B3F;
  }
  .table-striped tbody tr:nth-of-type(odd) {
      background-color: #D8E6E8;
  }

  .btn {
    font-weight: 500;
    border-radius: 5px;
    padding: 6px 10px;
    margin-right: 5px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
  }
  .btn i {
    margin-right: 5px;
  }

  .custom-btn-primary {
      background-color: #212B3F !important;
      border-color: #212B3F !important;
      color: #FFFFFF !important;
      padding: 10px 20px;
      font-size: 1.05em;
      box-shadow: 0 4px 8px rgba(33, 43, 63, 0.15);
  }
  .custom-btn-primary:hover {
      background-color: #556F7A !important;
      border-color: #556F7A !important;
      box-shadow: 0 6px 12px rgba(33, 43, 63, 0.2);
  }

  .custom-btn-action-edit {
      background-color: #8AD2B1;
      border-color: #8AD2B1;
      color: #212B3F;
      padding: 4px 8px;
      font-size: 0.85em;
  }
  .custom-btn-action-edit:hover {
      background-color: #556F7A;
      border-color: #556F7A;
      color: #FFFFFF;
  }

  .custom-btn-action-delete {
      background-color: #C26433;
      border-color: #C26433;
      color: #FFFFFF;
      padding: 4px 8px;
      font-size: 0.85em;
  }
  .custom-btn-action-delete:hover {
      background-color: #FE6A53;
      border-color: #FE6A53;
  }

  .custom-btn-action-print {
      background-color: #798086;
      border-color: #798086;
      color: #FFFFFF;
      padding: 4px 8px;
      font-size: 0.85em;
  }
  .custom-btn-action-print:hover {
      background-color: #556F7A;
      border-color: #556F7A;
  }

  .badge {
    padding: 0.4em 0.7em;
    border-radius: 4px;
    font-weight: 600;
    font-size: 0.8em;
    text-transform: capitalize;
    color: #FFFFFF;
  }
  .status-badge-delivered { background-color: #8AD2B1; color: #212B3F; }
  .status-badge-intransit { background-color: #556F7A; }
  .status-badge-outfordelivery { background-color: #2E6171; }
  .status-badge-booked { background-color: #798086; }
  .status-badge-onhold { background-color: #FE6A53; }
  .status-badge-returned { background-color: #B79FAD; }
  .status-badge-default { background-color: #798086; }


  #header .logo span {
      color: #8AD2B1 !important;
      font-weight: bold;
  }
  #header .logo i {
      color: #8AD2B1;
  }
  #header .header-nav .nav-link {
      color: #E8EAEC !important;
      font-weight: 500;
  }
  #header .header-nav .nav-link:hover {
      color: #D4AFCD !important;
  }
  #header .nav-profile .dropdown-toggle {
      color: #8AD2B1 !important;
  }
  #header .dropdown-menu {
      background-color: #212B3F;
      border: 1px solid #556F7A;
  }
  #header .dropdown-menu .dropdown-header h6,
  #header .dropdown-menu .dropdown-header span {
      color: #D4AFCD;
  }
  #header .dropdown-menu .dropdown-item {
      color: #E8EAEC;
  }
  #header .dropdown-menu .dropdown-item:hover {
      background-color: #556F7A;
      color: #FFFFFF;
  }
  #header .dropdown-menu .dropdown-item i {
      color: #B79FAD;
  }

  .search-bar {
      border-radius: 8px;
      background-color: rgba(255, 255, 255, 0.1);
      border: 1px solid rgba(255, 255, 255, 0.2);
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
      color: #8AD2B1;
  }
  .search-bar button:hover {
      color: #D4AFCD;
  }

  .notifications .notification-item i {
      color: #B79FAD;
  }
  .notifications .notification-item h4 {
      color: #E8EAEC;
  }
  .notifications .notification-item p {
      color: #D4AFCD;
  }
  .notifications .dropdown-header {
      background-color: #556F7A;
      color: #FFFFFF;
  }
  .notifications .badge-number {
      background-color: #FE6A53 !important;
  }

  .sidebar {
      background-color: #FFFFFF;
      border-right: 1px solid #D8E6E8;
  }
  .sidebar-nav .nav-link {
      color: #212B3F;
  }
  .sidebar-nav .nav-link:hover {
      color: #2E6171;
      background-color: #D8E6E8;
  }
  .sidebar-nav .nav-link.active {
      color: #FFFFFF;
      background-color: #2E6171;
  }
  .sidebar-nav .nav-link.collapsed {
      color: #556F7A;
  }
  .sidebar-nav .nav-link.collapsed:hover {
      color: #2E6171;
  }
  .sidebar-nav .nav-link.active i, .sidebar-nav .nav-link.collapsed:hover i {
      color: #8AD2B1;
  }
  .sidebar-nav .nav-content a {
      color: #556F7A;
  }
  .sidebar-nav .nav-content a:hover, .sidebar-nav .nav-content a.active {
      color: #2E6171;
      background-color: #E8EAEC;
  }
  .sidebar-nav .nav-heading {
      color: #798086;
  }
</style>```