<?php
$page_title = "Manage All Shipments";
$breadcrumbs = [
    ["Home", "admin_dashboard.php"],
    ["Shipments", "admin_manage_shipments.php"],
    ["Manage All", "#"]
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

$shipments = [];
$success_message = '';
$error_message = '';

if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $delete_id = mysqli_real_escape_string($conn, trim($_GET['id']));

    $sql_delete = "DELETE FROM shipments WHERE shipment_id = '$delete_id'";
    if (mysqli_query($conn, $sql_delete)) {
        $success_message = "<div class='alert alert-success'>Shipment deleted successfully.</div>";
    } else {
        $error_message = "<div class='alert alert-danger'>Error deleting shipment: " . mysqli_error($conn) . "</div>";
    }
}

$sql_shipments = "SELECT
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
                ORDER BY
                    s.created_at DESC";

$result = mysqli_query($conn, $sql_shipments);
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $shipments[] = $row;
    }
    mysqli_free_result($result);
} else {
    $error_message .= "<div class='alert alert-danger'>Error fetching shipments: " . mysqli_error($conn) . "</div>";
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
              <h5 class="card-title">All Courier Shipments</h5>
              <p>Admin can view, search, and manage all shipments across all locations.</p>

              <?php echo $success_message; ?>
              <?php echo $error_message; ?>

              <div class="d-flex justify-content-end mb-3">
                  <a href="admin_create_update_shipment.php" class="btn btn-primary"><i class="bi bi-plus-circle"></i> Create New Shipment</a>
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
                              <a href="?action=delete&id=<?php echo htmlspecialchars($shipment['shipment_id']); ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this shipment?');" title="Delete Shipment"><i class="bi bi-trash"></i></a>
                              <a href="print_tracking_details.php?tracking_number=<?php echo htmlspecialchars($shipment['tracking_number']); ?>" target="_blank" class="btn btn-sm btn-secondary" title="Print Tracking"><i class="bi bi-printer"></i></a>
                            </td>
                        </tr>
                    <?php }
                  } else { ?>
                      <tr><td colspan="10">No shipments found.</td></tr>
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