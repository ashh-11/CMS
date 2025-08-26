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
                  <a href="admin_create_update_shipment.php" class="btn custom-btn-primary"><i class="bi bi-plus-circle"></i> Create New Shipment</a>
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
                                    case 'delivered': echo 'status-badge-delivered'; break;
                                    case 'in_transit': echo 'status-badge-intransit'; break;
                                    case 'out_for_delivery': echo 'status-badge-outfordelivery'; break;
                                    case 'shipment_booked': echo 'status-badge-booked'; break;
                                    case 'on_hold': echo 'status-badge-onhold'; break;
                                    case 'returned': echo 'status-badge-returned'; break;
                                    default: echo 'status-badge-default'; break;
                                }
                            ?>"><?php echo htmlspecialchars(ucwords(str_replace('_', ' ', $shipment['current_status']))); ?></span></td>
                            <td><?php echo htmlspecialchars($shipment['delivery_date']); ?></td>
                            <td>
                              <a href="admin_create_update_shipment.php?id=<?php echo htmlspecialchars($shipment['shipment_id']); ?>" class="btn btn-sm custom-btn-action-edit" title="Edit Shipment"><i class="bi bi-pencil"></i></a>
                              <a href="?action=delete&id=<?php echo htmlspecialchars($shipment['shipment_id']); ?>" class="btn btn-sm custom-btn-action-delete" onclick="return confirm('Are you sure you want to delete this shipment?');" title="Delete Shipment"><i class="bi bi-trash"></i></a>
                              <a href="print_tracking_details.php?tracking_number=<?php echo htmlspecialchars($shipment['tracking_number']); ?>" target="_blank" class="btn btn-sm custom-btn-action-print" title="Print Tracking"><i class="bi bi-printer"></i></a>
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

<style>
  html, body {
    height: 100%;
    margin: 0;
    background-color: #E8EAEC; /* Off-White background from palette */
    font-family: 'Open Sans', sans-serif;
    color: #212B3F; /* Darkest color for main text */
  }

  body {
    display: flex;
    flex-direction: column;
  }

  #main {
    flex-grow: 1;
    padding: 30px;
    background-color: #FFFFFF; /* White background for the main content card */
    margin-top: 20px;
    margin-bottom: 20px;
    margin-right: 20px;
    margin-left: calc(280px + 30px); /* Sidebar width (approx 280px) + desired gap (30px) */
    border-radius: 20px;
    box-shadow: 0 10px 30px rgba(33, 43, 63, 0.08); /* Subtle shadow using darkest color */
    max-width: calc(100% - (280px + 30px + 20px)); /* Total width minus sidebar width, gap, and right margin */
  }

  /* Adjust #main margin when sidebar is collapsed */
  body.toggle-sidebar #main {
      margin-left: calc(80px + 30px); /* Collapsed sidebar width (approx 80px) + desired gap */
      max-width: calc(100% - (80px + 30px + 20px));
  }


  .pagetitle h1 {
    color: #212B3F; /* Dark text for title */
    font-weight: 700;
    text-align: left;
    margin-top: 15px;
    margin-bottom: 15px;
    font-size: 2em; /* Larger title */
  }

  .breadcrumb {
    justify-content: flex-start;
    background-color: transparent;
    padding: 0;
    margin-bottom: 25px;
    font-size: 0.9em;
  }
  .breadcrumb .breadcrumb-item a {
    color: #556F7A; /* Medium gray for breadcrumb links */
  }
  .breadcrumb .breadcrumb-item.active {
    color: #212B3F;
    font-weight: 600;
  }

  .card {
    border: none;
    box-shadow: 0 5px 15px rgba(33, 43, 63, 0.05); /* Lighter card shadow */
    border-radius: 15px;
    background-color: #FFFFFF;
  }

  .card-title {
    color: #212B3F;
    font-weight: 700;
    margin-bottom: 20px;
    font-size: 1.5em;
  }
  .card-body p {
    color: #556F7A;
    font-size: 1em;
  }

  /* Table Styling */
  .table {
    border-radius: 10px;
    overflow: hidden;
  }
  .table thead th {
    background-color: #8AD2B1; /* Light green from palette for table header */
    color: #212B3F;
    font-weight: 600;
    border-bottom: none;
    padding: 12px 15px;
    vertical-align: middle;
  }
  .table tbody tr {
    background-color: #FFFFFF;
    color: #212B3F;
  }
  .table-striped tbody tr:nth-of-type(odd) {
      background-color: #D8E6E8; /* Light blue/gray for striped rows */
  }
  .table tbody td {
      vertical-align: middle;
      padding: 10px 15px;
  }
  .table .datatable-selector, .table .datatable-search {
      margin-bottom: 15px;
  }
  .table .datatable-selector label, .table .datatable-search label {
      color: #556F7A;
      font-size: 0.95em;
  }
  .table .datatable-selector select, .table .datatable-search input {
      border: 1px solid #D8E6E8;
      border-radius: 5px;
      padding: 5px 10px;
      color: #212B3F;
  }
  .table .datatable-pagination {
      margin-top: 15px;
  }


  /* Custom Button Styles */
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

  /* Create New Shipment Button */
  .custom-btn-primary {
      background-color: #212B3F !important; /* Darkest blue for primary action */
      border-color: #212B3F !important;
      color: #FFFFFF !important;
      padding: 10px 20px;
      font-size: 1.05em;
      box-shadow: 0 4px 8px rgba(33, 43, 63, 0.15); /* Soft shadow for prominent button */
  }
  .custom-btn-primary:hover {
      background-color: #556F7A !important; /* Medium gray on hover */
      border-color: #556F7A !important;
      box-shadow: 0 6px 12px rgba(33, 43, 63, 0.2);
  }

  /* Action Buttons (Edit, Delete, Print) */
  .custom-btn-action-edit {
      background-color: #8AD2B1; /* Light green */
      border-color: #8AD2B1;
      color: #212B3F;
      padding: 4px 8px; /* Smaller padding for action icons */
      font-size: 0.85em;
  }
  .custom-btn-action-edit:hover {
      background-color: #556F7A;
      border-color: #556F7A;
      color: #FFFFFF;
  }

  .custom-btn-action-delete {
      background-color: #C26433; /* Brownish Orange */
      border-color: #C26433;
      color: #FFFFFF;
      padding: 4px 8px;
      font-size: 0.85em;
  }
  .custom-btn-action-delete:hover {
      background-color: #FE6A53; /* Coral */
      border-color: #FE6A53;
  }

  .custom-btn-action-print {
      background-color: #798086; /* Gray */
      border-color: #798086;
      color: #FFFFFF;
      padding: 4px 8px;
      font-size: 0.85em;
  }
  .custom-btn-action-print:hover {
      background-color: #556F7A;
      border-color: #556F7A;
  }

  /* Status Badges */
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


  /* Header styling using the new palette (as defined in inc.header.php) */
  #header {
      background-color: #212B3F;
      border-bottom: none;
      box-shadow: 0 2px 10px rgba(33, 43, 63, 0.1);
  }
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

  /* Sidebar styling (assumed to be included via inc.sidebar.php) */
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
      background-color: #2E6171; /* Dark Teal for active sidebar item */
  }
  .sidebar-nav .nav-link.collapsed {
      color: #556F7A;
  }
  .sidebar-nav .nav-link.collapsed:hover {
      color: #2E6171;
  }
  .sidebar-nav .nav-link.active i, .sidebar-nav .nav-link.collapsed:hover i {
      color: #8AD2B1; /* Light green for icons in active/hovered state */
  }
  .sidebar-nav .nav-content a {
      color: #556F7A;
  }
  .sidebar-nav .nav-content a:hover, .sidebar-nav .nav-content a.active {
      color: #2E6171;
      background-color: #E8EAEC;
  }
  .sidebar-nav .nav-heading {
      color: #798086; /* Gray for section headings */
  }
</style>