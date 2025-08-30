<?php
$page_title = "Reports";
$breadcrumbs = [
    ["Home", "admin_dashboard.php"],
    ["Reports", "#"]
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

$locations = [];
$courier_companies_dropdown = [];
$report_data = [];
$report_generated = false;
$error_message = '';

$sql_locations = "SELECT location_id, city_name FROM locations ORDER BY city_name ASC";
$result_loc = mysqli_query($conn, $sql_locations);
if ($result_loc) {
    while ($row_loc = mysqli_fetch_assoc($result_loc)) {
        $locations[] = $row_loc;
    }
    mysqli_free_result($result_loc);
} else {
    $error_message .= "<div class='alert alert-danger'>Error loading locations: " . mysqli_error($conn) . "</div>";
}

$sql_companies = "SELECT company_id, company_name FROM courier_companies ORDER BY company_name ASC";
$result_comp = mysqli_query($conn, $sql_companies);
if ($result_comp) {
    while ($row_comp = mysqli_fetch_assoc($result_comp)) {
        $courier_companies_dropdown[] = $row_comp;
    }
    mysqli_free_result($result_comp);
} else {
    $error_message .= "<div class='alert alert-danger'>Error loading courier companies: " . mysqli_error($conn) . "</div>";
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['generate_report'])) {
    $report_type = mysqli_real_escape_string($conn, $_POST['reportType'] ?? '');
    $start_date = mysqli_real_escape_string($conn, $_POST['startDate'] ?? '');
    $end_date = mysqli_real_escape_string($conn, $_POST['endDate'] ?? '');
    $selected_city_id = mysqli_real_escape_string($conn, $_POST['selectCity'] ?? '');
    $selected_company_id = mysqli_real_escape_string($conn, $_POST['selectCompanyId'] ?? '');

    $sql_report = "";
    $where_clause = [];

    if (!empty($start_date) && !empty($end_date) && strtotime($start_date) > strtotime($end_date)) {
        $error_message = "<div class='alert alert-danger'>Start date cannot be after end date.</div>";
    }

    if (empty($error_message)) {
        $sql_report_base = "SELECT
                                s.tracking_number, s.current_status, s.delivery_date, s.courier_type,
                                sender.full_name AS sender_name,
                                receiver.full_name AS receiver_name,
                                from_loc.city_name AS from_city,
                                to_loc.city_name AS to_city,
                                cc.company_name
                            FROM shipments s
                            JOIN customers sender ON s.sender_id = sender.customer_id
                            JOIN customers receiver ON s.receiver_id = receiver.customer_id
                            JOIN locations from_loc ON s.from_location_id = from_loc.location_id
                            JOIN locations to_loc ON s.to_location_id = to_loc.location_id
                            JOIN courier_companies cc ON s.company_id = cc.company_id";

        if (!empty($start_date) && !empty($end_date)) {
            $where_clause[] = "s.created_at BETWEEN '$start_date 00:00:00' AND '$end_date 23:59:59'";
        }

        switch ($report_type) {
            case 'date_wise':
                if (empty($start_date) || empty($end_date)) {
                    $error_message = "<div class='alert alert-danger'>Start and End dates are required for Date-Wise report.</div>";
                }
                break;
            case 'city_wise':
                if (empty($selected_city_id)) {
                    $error_message = "<div class='alert alert-danger'>Please select a city for City-Wise report.</div>";
                } else {
                    $where_clause[] = "(s.from_location_id = '$selected_city_id' OR s.to_location_id = '$selected_city_id')";
                }
                break;
            case 'company_wise':
                if (empty($selected_company_id)) {
                    $error_message = "<div class='alert alert-danger'>Please select a courier company for Company-Wise report.</div>";
                } else {
                    $where_clause[] = "s.company_id = '$selected_company_id'";
                }
                break;
            default:
                $error_message = "<div class='alert alert-danger'>Please select a valid Report Type.</div>";
                break;
        }

        if (empty($error_message)) {
            $sql_report = $sql_report_base;
            if (!empty($where_clause)) {
                $sql_report .= " WHERE " . implode(" AND ", $where_clause);
            }
            $sql_report .= " ORDER BY s.created_at ASC";

            $result_report = mysqli_query($conn, $sql_report);

            if ($result_report) {
                while ($row = mysqli_fetch_assoc($result_report)) {
                    $report_data[] = $row;
                }
                mysqli_free_result($result_report);
                $report_generated = true;

                $log_type = "Report Generation";
                $log_params = mysqli_real_escape_string($conn, json_encode($_POST));
                $log_user_id = $_SESSION['user_id'];
                $sql_log = "INSERT INTO reports_log (generated_by_user_id, report_type, report_params) VALUES ('$log_user_id', '$report_type', '$log_params')";
                mysqli_query($conn, $sql_log);

            } else {
                $error_message = "<div class='alert alert-danger'>Error generating report: " . mysqli_error($conn) . "</div>";
            }
        }
    }
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
              
              <?php echo $error_message; ?>

              <form class="row g-3" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST">
                <input type="hidden" name="generate_report" value="1">

                <div class="col-md-4">
                  <label for="reportType" class="form-label">Report Type</label>
                  <select id="reportType" name="reportType" class="form-select">
                    <option value="" disabled <?php echo empty($_POST['reportType']) ? 'selected' : ''; ?>>Select a Report Type</option>
                    <option value="date_wise" <?php echo (isset($_POST['reportType']) && $_POST['reportType'] == 'date_wise') ? 'selected' : ''; ?>>Date-Wise Summary</option>
                    <option value="city_wise" <?php echo (isset($_POST['reportType']) && $_POST['reportType'] == 'city_wise') ? 'selected' : ''; ?>>City-Wise Shipments</option>
                    <option value="company_wise" <?php echo (isset($_POST['reportType']) && $_POST['reportType'] == 'company_wise') ? 'selected' : ''; ?>>Courier Company Performance</option>
                  </select>
                </div>

                <div class="col-md-4" id="dateRangeFields">
                  <label for="startDate" class="form-label">Start Date</label>
                  <input type="date" class="form-control" id="startDate" name="startDate" value="<?php echo htmlspecialchars($_POST['startDate'] ?? ''); ?>">
                </div>
                <div class="col-md-4" id="endDateField">
                  <label for="endDate" class="form-label">End Date</label>
                  <input type="date" class="form-control" id="endDate" name="endDate" value="<?php echo htmlspecialchars($_POST['endDate'] ?? ''); ?>">
                </div>

                <div class="col-md-4" id="citySelectionField" style="display: none;">
                  <label for="selectCity" class="form-label">Select City</label>
                  <select id="selectCity" name="selectCity" class="form-select">
                    <option value="" selected>Select City</option>
                    <?php foreach ($locations as $loc) { ?>
                        <option value="<?php echo htmlspecialchars($loc['location_id']); ?>" <?php echo (isset($_POST['selectCity']) && $_POST['selectCity'] == $loc['location_id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($loc['city_name']); ?>
                        </option>
                    <?php } ?>
                  </select>
                </div>

                <div class="col-md-4" id="companySelectionField" style="display: none;">
                  <label for="selectCompanyId" class="form-label">Select Company</label>
                  <select id="selectCompanyId" name="selectCompanyId" class="form-select">
                    <option value="" selected>Select Company</option>
                    <?php foreach ($courier_companies_dropdown as $comp) { ?>
                        <option value="<?php echo htmlspecialchars($comp['company_id']); ?>" <?php echo (isset($_POST['selectCompanyId']) && $_POST['selectCompanyId'] == $comp['company_id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($comp['company_name']); ?>
                        </option>
                    <?php } ?>
                  </select>
                </div>


                <div class="col-12 text-center mt-4">
                  <button type="submit" class="btn btn-primary" id="generateReportBtn"><i class="bi bi-file-earmark-bar-graph"></i> Generate Report</button>
                  <?php if ($report_generated && !empty($report_data)) { ?>
                    <a href="download_report.php?reportType=<?php echo htmlspecialchars($_POST['reportType'] ?? ''); ?>&startDate=<?php echo htmlspecialchars($_POST['startDate'] ?? ''); ?>&endDate=<?php echo htmlspecialchars($_POST['endDate'] ?? ''); ?>&selectCity=<?php echo htmlspecialchars($_POST['selectCity'] ?? ''); ?>&selectCompanyId=<?php echo htmlspecialchars($_POST['selectCompanyId'] ?? ''); ?>" class="btn btn-success ms-2" id="downloadReportBtn"><i class="bi bi-download"></i> Download Report (XLSX)</a>
                  <?php } ?>
                </div>
              </form>

              <hr class="mt-5">

              <h5 class="card-title">Generated Report Preview</h5>
              <div id="reportPreview" class="mt-3">
                <?php if ($report_generated && !empty($report_data)) { ?>
                    <table class="table table-bordered table-striped" id="reportTable">
                        <thead>
                            <tr>
                                <th>Tracking No.</th>
                                <th>Sender</th>
                                <th>Receiver</th>
                                <th>Origin</th>
                                <th>Destination</th>
                                <th>Current Status</th>
                                <th>Delivery Date</th>
                                <th>Courier Type</th>
                                <th>Company</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($report_data as $row) { ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['tracking_number']); ?></td>
                                    <td><?php echo htmlspecialchars($row['sender_name']); ?></td>
                                    <td><?php echo htmlspecialchars($row['receiver_name']); ?></td>
                                    <td><?php echo htmlspecialchars($row['from_city']); ?></td>
                                    <td><?php echo htmlspecialchars($row['to_city']); ?></td>
                                    <td><?php echo htmlspecialchars(ucwords(str_replace('_', ' ', $row['current_status']))); ?></td>
                                    <td><?php echo htmlspecialchars($row['delivery_date']); ?></td>
                                    <td><?php echo htmlspecialchars($row['courier_type']); ?></td>
                                    <td><?php echo htmlspecialchars($row['company_name']); ?></td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                <?php } elseif ($report_generated && empty($report_data)) { ?>
                    <div class="alert alert-warning" role="alert">
                        No data found for the selected report criteria.
                    </div>
                <?php } else { ?>
                    <div class="alert alert-info" role="alert">
                        Select report criteria and click "Generate Report" to see a preview here.
                    </div>
                <?php } ?>
              </div>

            </div>
          </div>

        </div>
      </div>
    </section>

  </main>

<script>
    document.addEventListener('DOMContentLoaded', function() {
      const reportTypeSelect = document.getElementById('reportType');
      const dateRangeFields = document.getElementById('dateRangeFields');
      const endDateField = document.getElementById('endDateField');
      const citySelectionField = document.getElementById('citySelectionField');
      const companySelectionField = document.getElementById('companySelectionField');

      function toggleReportFields() {
        const selectedType = reportTypeSelect.value;
        dateRangeFields.style.display = 'none';
        endDateField.style.display = 'none';
        citySelectionField.style.display = 'none';
        companySelectionField.style.display = 'none';

        if (selectedType === 'date_wise') {
          dateRangeFields.style.display = 'block';
          endDateField.style.display = 'block';
        } else if (selectedType === 'city_wise') {
          dateRangeFields.style.display = 'block';
          endDateField.style.display = 'block';
          citySelectionField.style.display = 'block';
        } else if (selectedType === 'company_wise') {
          dateRangeFields.style.display = 'block';
          endDateField.style.display = 'block';
          companySelectionField.style.display = 'block';
        }
      }

      reportTypeSelect.addEventListener('change', toggleReportFields);
      toggleReportFields();
    });
</script>
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