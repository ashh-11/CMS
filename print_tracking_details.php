<?php
$page_title = "Print Tracking Details";
$breadcrumbs = [
    ["Home", "user_dashboard.php"],
    ["Track Shipment", "track_shipment.php"],
    ["Print", "#"]
];
$is_public_landing_page = true;
include('inc.header.php');

$tracking_number = mysqli_real_escape_string($conn, trim($_GET['tracking_number'] ?? ''));
$shipment_data = null;
$tracking_history = [];
$error_message = '';

if (empty($tracking_number)) {
    $error_message = "No tracking number provided.";
} else {
    $sql_shipment = "SELECT
                        s.shipment_id,
                        s.tracking_number,
                        s.current_status,
                        s.delivery_date,
                        sender.full_name AS sender_name,
                        sender.phone_number AS sender_phone,
                        sender.address AS sender_address,
                        receiver.full_name AS receiver_name,
                        receiver.phone_number AS receiver_phone,
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
            $error_message = "Error fetching tracking history: " . mysqli_error($conn);
        }

    } else {
        $error_message = "Shipment with tracking number " . htmlspecialchars($tracking_number) . " not found.";
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
        <div class="col-lg-10">
          <div class="card p-4">
            <?php if (!empty($error_message)) { ?>
                <div class="alert alert-danger no-print" role="alert">
                    <?php echo $error_message; ?>
                </div>
            <?php } elseif ($shipment_data) { ?>
                <div class-="print-area">
                    <div class="row">
                        <div class="col-12 text-center mb-4">
                            <img src="assets/img/logo.png" alt="Courier System Logo" style="height: 60px;">
                            <h2 class="mt-2" style="color: #5A90D7;">Courier System - Shipment Tracking</h2>
                            <hr style="border-top: 2px solid #E5D1CF;">
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-12 text-center">
                            <h3 class="section-title" style="color: #5A90D7;">Tracking Number: <strong style="color: #012970;"><?php echo htmlspecialchars($shipment_data['tracking_number']); ?></strong></h3>
                        </div>
                    </div>

                    <div class="row info-section mb-4 p-3" style="background-color: #F8F9FA; border-radius: 8px;">
                        <div class="col-md-6">
                            <h5 style="color: #5A90D7;">Sender Information:</h5>
                            <p><strong>Name:</strong> <?php echo htmlspecialchars($shipment_data['sender_name']); ?></p>
                            <p><strong>Address:</strong> <?php echo htmlspecialchars($shipment_data['sender_address']); ?></p>
                            <p><strong>Contact:</strong> <?php echo htmlspecialchars($shipment_data['sender_phone']); ?></p>
                        </div>
                        <div class="col-md-6">
                            <h5 style="color: #5A90D7;">Receiver Information:</h5>
                            <p><strong>Name:</strong> <?php echo htmlspecialchars($shipment_data['receiver_name']); ?></p>
                            <p><strong>Address:</strong> <?php echo htmlspecialchars($shipment_data['receiver_address']); ?></p>
                            <p><strong>Contact:</strong> <?php echo htmlspecialchars($shipment_data['receiver_phone']); ?></p>
                        </div>
                    </div>

                    <div class="row info-section mb-4 p-3" style="background-color: #F8F9FA; border-radius: 8px;">
                        <div class="col-md-6">
                            <p><strong>Courier Type:</strong> <?php echo htmlspecialchars($shipment_data['courier_type']); ?></p>
                            <p><strong>Courier Company:</strong> <?php echo htmlspecialchars($shipment_data['company_name']); ?></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Origin:</strong> <?php echo htmlspecialchars($shipment_data['from_city']); ?></p>
                            <p><strong>Destination:</strong> <?php echo htmlspecialchars($shipment_data['to_city']); ?></p>
                        </div>
                    </div>

                    <div class="row info-section mb-4 p-3" style="background-color: #F8F9FA; border-radius: 8px;">
                        <div class="col-12">
                            <h5 style="color: #5A90D7;">Current Status: <span class="badge <?php
                                switch($shipment_data['current_status']) {
                                    case 'delivered': echo 'bg-success'; break;
                                    case 'in_transit': echo 'bg-warning'; break;
                                    case 'out_for_delivery': echo 'bg-primary'; break;
                                    case 'shipment_booked': echo 'bg-secondary'; break;
                                    case 'on_hold': echo 'bg-danger'; break;
                                    default: echo 'bg-info'; break;
                                }
                            ?>"><?php echo htmlspecialchars(ucwords(str_replace('_', ' ', $shipment_data['current_status']))); ?></span></h5>
                            <p><strong>Expected Delivery Date:</strong> <?php echo htmlspecialchars($shipment_data['delivery_date']); ?></p>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12">
                            <h5 style="color: #5A90D7;">Tracking History:</h5>
                            <div class="activity ps-3" style="border-left: 2px solid #819CDD;">
                                <?php if (!empty($tracking_history)) {
                                    foreach ($tracking_history as $history_item) { ?>
                                        <div class="activity-item d-flex">
                                            <div class="activite-label pe-3"><?php echo htmlspecialchars(date('d M, Y H:i', strtotime($history_item['event_timestamp']))); ?></div>
                                            <i class='bi bi-circle-fill activity-badge align-self-start' style="color: #7AAEEA;"></i>
                                            <div class="activity-content ps-2">
                                                <?php echo htmlspecialchars(ucwords(str_replace('_', ' ', $history_item['status_update']))) . (empty($history_item['event_location']) ? '' : ' - ' . htmlspecialchars($history_item['event_location'])); ?>
                                            </div>
                                        </div>
                                    <?php }
                                } else { ?>
                                    <p>No tracking history available yet.</p>
                                <?php } ?>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mt-5 no-print">
                    <div class="col-12 text-center">
                        <button class="btn btn-primary me-2" onclick="window.print()" style="background-color: #7EB4F2; color: white;">
                            <i class="bi bi-printer"></i> Print This Page
                        </button>
                        <button class="btn btn-secondary" onclick="window.close()" style="background-color: #819CDD; color: white;">
                            <i class="bi bi-x-circle"></i> Close
                        </button>
                    </div>
                </div>
            <?php } ?>
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
    background-color: #F0F2F5;
  }

  body {
    display: flex;
    flex-direction: column;
    font-family: 'Open Sans', sans-serif;
    color: #333;
  }

  #main {
    flex-grow: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 30px;
    background-color: #F0F2F5;
  }

  .pagetitle h1 {
    color: #5A90D7;
    font-weight: 700;
    text-align: left;
    margin-bottom: 15px;
    font-size: 2.2em;
  }

  .breadcrumb {
    justify-content: flex-start;
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

  .card {
    border: none;
    box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
    border-radius: 15px;
  }

  .card-title {
    color: #5A90D7;
    font-weight: 700;
    margin-bottom: 20px;
  }
  .card-body h6 {
    color: #012970;
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
    border-top: 1px solid #E5D1CF;
    margin-top: 30px;
    margin-bottom: 30px;
  }

  .activity {
    padding-left: 15px;
  }
  .activity-item {
    padding-left: 25px;
    position: relative;
    margin-bottom: 10px;
  }
  .activity-item .activite-label {
    font-size: 0.85em;
    color: #819CDD;
    position: absolute;
    left: 0;
    top: 2px;
  }
  .activity-item .activity-badge {
    position: absolute;
    left: -10px;
    top: 5px;
    width: 10px;
    height: 10px;
    background-color: #7AAEEA;
    border-radius: 50%;
    border: 1px solid #5A90D7;
  }
  .activity-item .activity-content {
    margin-left: 60px;
    line-height: 1.4;
  }

  .btn-primary {
      background-color: #7EB4F2 !important;
      border-color: #7EB4F2 !important;
      font-weight: 600;
      padding: 8px 25px;
  }
  .btn-primary:hover {
      background-color: #5A90D7 !important;
      border-color: #5A90D7 !important;
  }

  .btn-secondary {
      background-color: #819CDD !important;
      border-color: #819CDD !important;
      font-weight: 600;
      padding: 8px 25px;
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

  .right-aligned-image-container {
    padding: 20px;
    text-align: center;
  }
  .right-aligned-image {
    max-width: 100%;
    height: auto;
    border-radius: 15px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.1);
  }

  @media print {
    body {
      background: none !important;
      opacity: 1 !important;
    }
    .main {
      box-shadow: none !important;
      margin: 0 !important;
      padding: 0 !important;
      background-color: transparent !important;
      width: 100% !important;
      max-width: none !important;
    }
    .no-print {
      display: none !important;
    }
    .main-content-right {
      display: none !important;
    }
    .main-content-left {
      width: 100% !important;
      flex: 0 0 100% !important;
      max-width: 100% !important;
      box-shadow: none !important;
      padding: 0 !important;
      border-radius: 0 !important;
    }
    .card {
        box-shadow: none !important;
    }
    .pagetitle h1, .breadcrumb { text-align: left !important; justify-content: flex-start !important; }
  }
</style>