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
              <h5 class="card-title">Generate Reports</h5>
              <p>Admin can generate various reports based on date, city, or company and download them.</p>

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
                    <a href="download_report_xlsx.php?reportType=<?php echo htmlspecialchars($_POST['reportType'] ?? ''); ?>&startDate=<?php echo htmlspecialchars($_POST['startDate'] ?? ''); ?>&endDate=<?php echo htmlspecialchars($_POST['endDate'] ?? ''); ?>&selectCity=<?php echo htmlspecialchars($_POST['selectCity'] ?? ''); ?>&selectCompanyId=<?php echo htmlspecialchars($_POST['selectCompanyId'] ?? ''); ?>" class="btn btn-success ms-2" id="downloadReportBtn"><i class="bi bi-download"></i> Download Report (XLSX)</a>
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

<?php include('inc.footer.php'); ?>

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