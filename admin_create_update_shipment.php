<?php
$page_title = "Create/Update Shipment";
$breadcrumbs = [
    ["Home", "admin_dashboard.php"],
    ["Shipments", "admin_manage_shipments.php"],
    ["Create/Update Bill", "#"]
];

include('inc.header.php');
redirectToLoginIfNotAuthenticated();

$can_manage_shipments = (getUserRole() == ROLE_ADMIN || getUserRole() == ROLE_AGENT);
if (!$can_manage_shipments) {
    if (getUserRole() == ROLE_AGENT) {
        header("location: agent_dashboard.php");
    } else {
        header("location: login.php");
    }
    exit;
}

$shipment_id = null;
$tracking_number = '';
$sender_name = $sender_phone = $sender_address = $sender_city = '';
$receiver_name = $receiver_phone = $receiver_address = $receiver_city = '';
$courier_type = '';
$delivery_date = '';
$company_id = '';
$from_location_id = '';
$to_location_id = '';
$current_status = 'shipment_booked';

$form_title = "Create New Courier Bill";
$form_description = "Fill in the details to create a new courier bill.";
$submit_button_text = "Create Bill";

$success_message = '';
$error_message = '';

$locations = [];
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

$courier_companies = [];
$sql_companies = "SELECT company_id, company_name FROM courier_companies ORDER BY company_name ASC";
$result_comp = mysqli_query($conn, $sql_companies);
if ($result_comp) {
    while ($row_comp = mysqli_fetch_assoc($result_comp)) {
        $courier_companies[] = $row_comp;
    }
    mysqli_free_result($result_comp);
} else {
    $error_message .= "<div class='alert alert-danger'>Error loading courier companies: " . mysqli_error($conn) . "</div>";
}

if (isset($_GET['id']) && !empty(trim($_GET['id']))) {
    $shipment_id = mysqli_real_escape_string($conn, trim($_GET['id']));

    $form_title = "Update Courier Bill";
    $form_description = "Modify the details of the existing courier bill.";
    $submit_button_text = "Update Bill";

    $sql_fetch = "SELECT
                    s.shipment_id, s.tracking_number, s.sender_id, s.receiver_id,
                    s.courier_type, s.delivery_date, s.company_id,
                    s.from_location_id, s.to_location_id, s.current_status,
                    sender.full_name AS sender_name, sender.phone_number AS sender_phone, sender.address AS sender_address, sender.city AS sender_city,
                    receiver.full_name AS receiver_name, receiver.phone_number AS receiver_phone, receiver.address AS receiver_address, receiver.city AS receiver_city
                  FROM
                    shipments s
                  JOIN
                    customers sender ON s.sender_id = sender.customer_id
                  JOIN
                    customers receiver ON s.receiver_id = receiver.customer_id
                  WHERE
                    s.shipment_id = '$shipment_id'";

    $result_fetch = mysqli_query($conn, $sql_fetch);

    if ($result_fetch && mysqli_num_rows($result_fetch) == 1) {
        $shipment_data = mysqli_fetch_assoc($result_fetch);
        $shipment_id = $shipment_data['shipment_id'];
        $tracking_number = $shipment_data['tracking_number'];
        $courier_type = $shipment_data['courier_type'];
        $delivery_date = $shipment_data['delivery_date'];
        $company_id = $shipment_data['company_id'];
        $from_location_id = $shipment_data['from_location_id'];
        $to_location_id = $shipment_data['to_location_id'];
        $current_status = $shipment_data['current_status'];

        $sender_name = $shipment_data['sender_name'];
        $sender_phone = $shipment_data['sender_phone'];
        $sender_address = $shipment_data['sender_address'];
        $sender_city = $shipment_data['sender_city'];

        $receiver_name = $shipment_data['receiver_name'];
        $receiver_phone = $shipment_data['receiver_phone'];
        $receiver_address = $shipment_data['receiver_address'];
        $receiver_city = $shipment_data['receiver_city'];
    } else {
        $error_message .= "<div class='alert alert-danger'>Shipment not found or error fetching: " . mysqli_error($conn) . "</div>";
        $shipment_id = null;
    }
    mysqli_free_result($result_fetch);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $input_shipment_id = mysqli_real_escape_string($conn, trim($_POST['shipment_id'] ?? ''));
    $input_sender_name = mysqli_real_escape_string($conn, $_POST['sender_name']);
    $input_sender_phone = mysqli_real_escape_string($conn, $_POST['sender_phone']);
    $input_sender_address = mysqli_real_escape_string($conn, $_POST['sender_address']);
    $input_sender_city = mysqli_real_escape_string($conn, $_POST['sender_city']);
    $input_receiver_name = mysqli_real_escape_string($conn, $_POST['receiver_name']);
    $input_receiver_phone = mysqli_real_escape_string($conn, $_POST['receiver_phone']);
    $input_receiver_address = mysqli_real_escape_string($conn, $_POST['receiver_address']);
    $input_receiver_city = mysqli_real_escape_string($conn, $_POST['receiver_city']);
    $input_courier_type = mysqli_real_escape_string($conn, $_POST['courier_type']);
    $input_delivery_date = mysqli_real_escape_string($conn, $_POST['delivery_date']);
    $input_company_id = mysqli_real_escape_string($conn, $_POST['company_id']);
    $input_from_location_id = mysqli_real_escape_string($conn, $_POST['from_location_id']);
    $input_to_location_id = mysqli_real_escape_string($conn, $_POST['to_location_id']);
    $input_current_status = mysqli_real_escape_string($conn, $_POST['current_status']);

    $sender_name = $input_sender_name;
    $sender_phone = $input_sender_phone;
    $sender_address = $input_sender_address;
    $sender_city = $input_sender_city;
    $receiver_name = $input_receiver_name;
    $receiver_phone = $input_receiver_phone;
    $receiver_address = $input_receiver_address;
    $receiver_city = $input_receiver_city;
    $courier_type = $input_courier_type;
    $delivery_date = $input_delivery_date;
    $company_id = $input_company_id;
    $from_location_id = $input_from_location_id;
    $to_location_id = $input_to_location_id;
    $current_status = $input_current_status;

    if (empty($input_sender_name) || empty($input_sender_phone) || empty($input_receiver_name) || empty($input_receiver_phone) ||
        empty($input_courier_type) || empty($input_delivery_date) || empty($input_company_id) ||
        empty($input_from_location_id) || empty($input_to_location_id) || empty($input_current_status)) {
        $error_message = "<div class='alert alert-danger'>Please fill all required fields.</div>";
    } else {
        $sender_id_val = null;
        $sql_find_sender = "SELECT customer_id FROM customers WHERE phone_number = '$input_sender_phone'";
        $result_find_sender = mysqli_query($conn, $sql_find_sender);
        if ($result_find_sender && mysqli_num_rows($result_find_sender) > 0) {
            $row_sender = mysqli_fetch_assoc($result_find_sender);
            $sender_id_val = $row_sender['customer_id'];
            $sql_update_sender = "UPDATE customers SET full_name = '$input_sender_name', address = '$input_sender_address', city = '$input_sender_city' WHERE customer_id = '$sender_id_val'";
            if (!mysqli_query($conn, $sql_update_sender)) {
                $error_message .= "<div class='alert alert-danger'>Error updating sender: " . mysqli_error($conn) . "</div>";
            }
        } else {
            $sql_insert_sender = "INSERT INTO customers (full_name, phone_number, address, city) VALUES ('$input_sender_name', '$input_sender_phone', '$input_sender_address', '$input_sender_city')";
            if (mysqli_query($conn, $sql_insert_sender)) {
                $sender_id_val = mysqli_insert_id($conn);
            } else {
                $error_message .= "<div class='alert alert-danger'>Error adding sender: " . mysqli_error($conn) . "</div>";
            }
        }

        $receiver_id_val = null;
        $sql_find_receiver = "SELECT customer_id FROM customers WHERE phone_number = '$input_receiver_phone'";
        $result_find_receiver = mysqli_query($conn, $sql_find_receiver);
        if ($result_find_receiver && mysqli_num_rows($result_find_receiver) > 0) {
            $row_receiver = mysqli_fetch_assoc($result_find_receiver);
            $receiver_id_val = $row_receiver['customer_id'];
            $sql_update_receiver = "UPDATE customers SET full_name = '$input_receiver_name', address = '$input_receiver_address', city = '$input_receiver_city' WHERE customer_id = '$receiver_id_val'";
            if (!mysqli_query($conn, $sql_update_receiver)) {
                $error_message .= "<div class='alert alert-danger'>Error updating receiver: " . mysqli_error($conn) . "</div>";
            }
        } else {
            $sql_insert_receiver = "INSERT INTO customers (full_name, phone_number, address, city) VALUES ('$input_receiver_name', '$input_receiver_phone', '$input_receiver_address', '$input_receiver_city')";
            if (mysqli_query($conn, $sql_insert_receiver)) {
                $receiver_id_val = mysqli_insert_id($conn);
            } else {
                $error_message .= "<div class='alert alert-danger'>Error adding receiver: " . mysqli_error($conn) . "</div>";
            }
        }

        if ($sender_id_val && $receiver_id_val && empty($error_message)) {
            if (!empty($input_shipment_id)) {
                $sql_shipment = "UPDATE shipments SET
                                    sender_id = '$sender_id_val',
                                    receiver_id = '$receiver_id_val',
                                    courier_type = '$input_courier_type',
                                    delivery_date = '$input_delivery_date',
                                    company_id = '$input_company_id',
                                    from_location_id = '$input_from_location_id',
                                    to_location_id = '$input_to_location_id',
                                    current_status = '$input_current_status'
                                 WHERE shipment_id = '$input_shipment_id'";
                if (mysqli_query($conn, $sql_shipment)) {
                    $success_message = "<div class='alert alert-success'>Shipment updated successfully.</div>";
                    $tracking_number = $_POST['tracking_number_hidden'] ?? '';
                } else {
                    $error_message .= "<div class='alert alert-danger'>Error updating shipment: " . mysqli_error($conn) . "</div>";
                }
            } else {
                $generated_tracking_number = 'PKR' . date('Ymd') . strtoupper(bin2hex(random_bytes(3)));
                $sql_shipment = "INSERT INTO shipments (tracking_number, sender_id, receiver_id, courier_type, delivery_date, company_id, from_location_id, to_location_id, current_status, created_by_user_id)
                                 VALUES (
                                     '$generated_tracking_number',
                                     '$sender_id_val',
                                     '$receiver_id_val',
                                     '$input_courier_type',
                                     '$input_delivery_date',
                                     '$input_company_id',
                                     '$input_from_location_id',
                                     '$input_to_location_id',
                                     '$input_current_status',
                                     '" . $_SESSION['user_id'] . "'
                                 )";
                if (mysqli_query($conn, $sql_shipment)) {
                    $success_message = "<div class='alert alert-success'>Shipment created successfully. Tracking No: <strong>" . htmlspecialchars($generated_tracking_number) . "</strong></div>";
                    $sender_name = $sender_phone = $sender_address = $sender_city = '';
                    $receiver_name = $receiver_phone = $receiver_address = $receiver_city = '';
                    $courier_type = ''; $delivery_date = ''; $company_id = '';
                    $from_location_id = ''; $to_location_id = ''; $current_status = 'shipment_booked';
                    $tracking_number = $generated_tracking_number;
                } else {
                    $error_message .= "<div class='alert alert-danger'>Error creating shipment: " . mysqli_error($conn) . "</div>";
                }
            }
        } else {
            $error_message = "<div class='alert alert-danger'>Failed to process sender or receiver details due to a previous error.</div>";
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] != 'POST' && !$shipment_id) {
}
?>

<?php include('inc.sidebar.php'); ?>

<main id="main" class="main">
  <div class="pagetitle">
    <h1><?php echo htmlspecialchars($form_title); ?></h1>
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
            <h5 class="card-title"><?php echo htmlspecialchars($form_title); ?></h5>
            <p><?php echo htmlspecialchars($form_description); ?></p>
            <?php echo $success_message; ?>
            <?php echo $error_message; ?>

            <form class="row g-3" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST">
              <input type="hidden" name="shipment_id" value="<?php echo htmlspecialchars($shipment_id ?? ''); ?>">
              <input type="hidden" name="tracking_number_hidden" value="<?php echo htmlspecialchars($tracking_number ?? ''); ?>">

              <div class="col-md-6">
                <h6 class="mt-3">Sender Details</h6>
                <div class="form-floating mb-3">
                  <input type="text" class="form-control" id="senderName" name="sender_name" placeholder="Sender Name" value="<?php echo htmlspecialchars($sender_name); ?>" required>
                  <label for="senderName">Sender Name</label>
                </div>
                <div class="form-floating mb-3">
                  <input type="text" class="form-control" id="senderPhone" name="sender_phone" placeholder="Sender Phone" value="<?php echo htmlspecialchars($sender_phone); ?>" required>
                  <label for="senderPhone">Sender Phone</label>
                </div>
                <div class="form-floating mb-3">
                  <textarea class="form-control" placeholder="Sender Address" id="senderAddress" name="sender_address" style="height: 80px;" required><?php echo htmlspecialchars($sender_address); ?></textarea>
                  <label for="senderAddress">Sender Address</label>
                </div>
                <div class="form-floating mb-3">
                  <input type="text" class="form-control" id="senderCity" name="sender_city" placeholder="Sender City" value="<?php echo htmlspecialchars($sender_city); ?>" required>
                  <label for="senderCity">Sender City</label>
                </div>
                <div class="form-floating mb-3">
                  <select class="form-select" id="fromLocation" name="from_location_id" aria-label="From Location" required>
                    <option value="" disabled <?php echo empty($from_location_id) ? 'selected' : ''; ?>>Select Origin Branch</option>
                    <?php foreach ($locations as $loc) { ?>
                      <option value="<?php echo htmlspecialchars($loc['location_id']); ?>" <?php echo ($from_location_id == $loc['location_id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($loc['city_name']); ?>
                      </option>
                    <?php } ?>
                  </select>
                  <label for="fromLocation">From Location (Branch)</label>
                </div>
              </div>

              <div class="col-md-6">
                <h6 class="mt-3">Receiver Details</h6>
                <div class="form-floating mb-3">
                  <input type="text" class="form-control" id="receiverName" name="receiver_name" placeholder="Receiver Name" value="<?php echo htmlspecialchars($receiver_name); ?>" required>
                  <label for="receiverName">Receiver Name</label>
                </div>
                <div class="form-floating mb-3">
                  <input type="text" class="form-control" id="receiverPhone" name="receiver_phone" placeholder="Receiver Phone" value="<?php echo htmlspecialchars($receiver_phone); ?>" required>
                  <label for="receiverPhone">Receiver Phone</label>
                </div>
                <div class="form-floating mb-3">
                  <textarea class="form-control" placeholder="Receiver Address" id="receiverAddress" name="receiver_address" style="height: 80px;" required><?php echo htmlspecialchars($receiver_address); ?></textarea>
                  <label for="receiverAddress">Receiver Address</label>
                </div>
                <div class="form-floating mb-3">
                  <input type="text" class="form-control" id="receiverCity" name="receiver_city" placeholder="Receiver City" value="<?php echo htmlspecialchars($receiver_city); ?>" required>
                  <label for="receiverCity">Receiver City</label>
                </div>
                <div class="form-floating mb-3">
                  <select class="form-select" id="toLocation" name="to_location_id" aria-label="To Location" required>
                    <option value="" disabled <?php echo empty($to_location_id) ? 'selected' : ''; ?>>Select Destination Branch</option>
                    <?php foreach ($locations as $loc) { ?>
                      <option value="<?php echo htmlspecialchars($loc['location_id']); ?>" <?php echo ($to_location_id == $loc['location_id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($loc['city_name']); ?>
                      </option>
                    <?php } ?>
                  </select>
                  <label for="toLocation">To Location (Branch)</label>
                </div>
              </div>

              <div class="col-12">
                <hr>
                <h6 class="mt-3">Shipment Details</h6>
              </div>

              <div class="col-md-4">
                <div class="form-floating mb-3">
                  <select class="form-select" id="courierType" name="courier_type" aria-label="Courier Type" required>
                    <option value="" disabled <?php echo empty($courier_type) ? 'selected' : ''; ?>>Select Type</option>
                    <option value="Standard" <?php echo ($courier_type == 'Standard') ? 'selected' : ''; ?>>Standard</option>
                    <option value="Express" <?php echo ($courier_type == 'Express') ? 'selected' : ''; ?>>Express</option>
                    <option value="Document" <?php echo ($courier_type == 'Document') ? 'selected' : ''; ?>>Document</option>
                    <option value="Parcel" <?php echo ($courier_type == 'Parcel') ? 'selected' : ''; ?>>Parcel</option>
                  </select>
                  <label for="courierType">Courier Type</label>
                </div>
              </div>
              <div class="col-md-4">
                <div class="form-floating mb-3">
                  <input type="date" class="form-control" id="deliveryDate" name="delivery_date" value="<?php echo htmlspecialchars($delivery_date); ?>" required>
                  <label for="deliveryDate">Delivery Date</label>
                </div>
              </div>
              <div class="col-md-4">
                <div class="form-floating mb-3">
                  <select class="form-select" id="courierCompany" name="company_id" aria-label="Courier Company" required>
                    <option value="" disabled <?php echo empty($company_id) ? 'selected' : ''; ?>>Select Company</option>
                    <?php foreach ($courier_companies as $comp) { ?>
                      <option value="<?php echo htmlspecialchars($comp['company_id']); ?>" <?php echo ($company_id == $comp['company_id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($comp['company_name']); ?>
                      </option>
                    <?php } ?>
                  </select>
                  <label for="courierCompany">Courier Company</label>
                </div>
              </div>

              <div class="col-md-6">
                <div class="form-floating mb-3">
                  <input type="text" class="form-control" id="trackingNumber" name="tracking_number" placeholder="Auto-generated or Enter" value="<?php echo htmlspecialchars($tracking_number); ?>" readonly>
                  <label for="trackingNumber">Tracking Number (Auto-Generated)</label>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-floating mb-3">
                  <select class="form-select" id="currentStatus" name="current_status" aria-label="Current Status" required>
                    <option value="shipment_booked" <?php echo ($current_status == 'shipment_booked') ? 'selected' : ''; ?>>Shipment Booked</option>
                    <option value="in_transit" <?php echo ($current_status == 'in_transit') ? 'selected' : ''; ?>>In Transit</option>
                    <option value="out_for_delivery" <?php echo ($current_status == 'out_for_delivery') ? 'selected' : ''; ?>>Out for Delivery</option>
                    <option value="delivered" <?php echo ($current_status == 'delivered') ? 'selected' : ''; ?>>Delivered</option>
                    <option value="returned" <?php echo ($current_status == 'returned') ? 'selected' : ''; ?>>Returned</option>
                    <option value="on_hold" <?php echo ($current_status == 'on_hold') ? 'selected' : ''; ?>>On Hold</option>
                  </select>
                  <label for="currentStatus">Current Status</label>
                </div>
              </div>

              <div class="text-center mt-4">
                <button type="submit" class="btn btn-primary"><?php echo htmlspecialchars($submit_button_text); ?></button>
                <button type="reset" class="btn btn-secondary">Reset Form</button>
              </div>
            </form>

          </div>
        </div>
      </div>
    </div>
  </section>
</main>


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
