<?php
require_once 'inc.connections.php';

$report_type = mysqli_real_escape_string($conn, $_GET['reportType'] ?? '');
$start_date = mysqli_real_escape_string($conn, $_GET['startDate'] ?? '');
$end_date = mysqli_real_escape_string($conn, $_GET['endDate'] ?? '');
$selected_city_id = mysqli_real_escape_string($conn, $_GET['selectCity'] ?? '');
$selected_company_id = mysqli_real_escape_string($conn, $_GET['selectCompanyId'] ?? '');

$sql_report = "";
$where_clause = [];
$filename = "shipments_report_" . date('Ymd_His');
$error_message = "";

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
    if (strtotime($start_date) > strtotime($end_date)) {
        $error_message = "Start date cannot be after end date.";
    } else {
        $where_clause[] = "s.created_at BETWEEN '$start_date 00:00:00' AND '$end_date 23:59:59'";
        $filename .= "_from_" . $start_date . "_to_" . $end_date;
    }
}

if (empty($error_message)) {
    switch ($report_type) {
        case 'date_wise':
            if (empty($start_date) || empty($end_date)) {
                $error_message = "Start and End dates are required for Date-Wise report.";
            }
            $filename .= "_date_wise";
            break;
        case 'city_wise':
            if (empty($selected_city_id)) {
                $error_message = "Please select a city for City-Wise report.";
            } else {
                $where_clause[] = "(s.from_location_id = '$selected_city_id' OR s.to_location_id = '$selected_city_id')";
                $city_name_query = mysqli_query($conn, "SELECT city_name FROM locations WHERE location_id = '$selected_city_id'");
                $city_row = mysqli_fetch_assoc($city_name_query);
                $filename .= "_city_" . ($city_row['city_name'] ?? 'unknown');
            }
            break;
        case 'company_wise':
            if (empty($selected_company_id)) {
                $error_message = "Please select a courier company for Company-Wise report.";
            } else {
                $where_clause[] = "s.company_id = '$selected_company_id'";
                $company_name_query = mysqli_query($conn, "SELECT company_name FROM courier_companies WHERE company_id = '$selected_company_id'");
                $company_row = mysqli_fetch_assoc($company_name_query);
                $filename .= "_company_" . ($company_row['company_name'] ?? 'unknown');
            }
            break;
        default:
            $error_message = "Please select a valid Report Type.";
            break;
    }
}

if (empty($error_message)) {
    $sql_report = $sql_report_base;
    if (!empty($where_clause)) {
        $sql_report .= " WHERE " . implode(" AND ", $where_clause);
    }
    $sql_report .= " ORDER BY s.created_at ASC";

    $result_report = mysqli_query($conn, $sql_report);

    if ($result_report) {
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '.csv"');

        $output = fopen('php://output', 'w');

        fputcsv($output, [
            'Tracking Number', 'Current Status', 'Aesthetic Delivery Date', 'Courier Type',
            'Sender Name', 'Receiver Name', 'Origin City', 'Destination City', 'Courier Company'
        ]);

        while ($row = mysqli_fetch_assoc($result_report)) {
            $row['current_status'] = ucwords(str_replace('_', ' ', $row['current_status']));
            fputcsv($output, [
                $row['tracking_number'],
                $row['current_status'],
                $row['delivery_date'],
                $row['courier_type'],
                $row['sender_name'],
                $row['receiver_name'],
                $row['from_city'],
                $row['to_city'],
                $row['company_name']
            ]);
        }
        mysqli_free_result($result_report);
        fclose($output);
        mysqli_close($conn);
        exit;
    } else {
        $error_message = "Error generating report: " . mysqli_error($conn);
    }
}

// If an error occurred before headers were sent, output a simple error page
mysqli_close($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Report Download Error</title>
  <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      font-family: 'Open Sans', sans-serif;
      background-color: #E8EAEC;
      color: #212B3F;
      display: flex;
      justify-content: center;
      align-items: center;
      min-height: 100vh;
      margin: 0;
    }
    .error-container {
      background-color: #FFFFFF;
      padding: 30px;
      border-radius: 15px;
      box-shadow: 0 5px 20px rgba(33, 43, 63, 0.1);
      max-width: 600px;
      text-align: center;
    }
    .error-container h1 {
      color: #C26433;
      font-weight: 700;
      margin-bottom: 20px;
    }
    .error-container p {
      color: #556F7A;
      margin-bottom: 20px;
    }
    .error-container .btn {
      background-color: #2E6171;
      border-color: #2E6171;
      color: #FFFFFF;
      padding: 10px 20px;
      border-radius: 5px;
      font-weight: 600;
    }
    .error-container .btn:hover {
      background-color: #556F7A;
      border-color: #556F7A;
    }
  </style>
</head>
<body>
  <div class="error-container">
    <h1>Report Download Failed!</h1>
    <p>We encountered an issue while trying to generate your report.</p>
    <?php if (!empty($error_message)) { ?>
      <div class="alert alert-danger" role="alert"><?php echo htmlspecialchars($error_message); ?></div>
    <?php } ?>
    <p>Please go back and try again, ensuring all fields are correctly selected.</p>
    <a href="admin_reports.php" class="btn">Go Back to Reports</a>
  </div>
</body>
</html>