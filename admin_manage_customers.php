<?php
$page_title = "Manage Customer Details";
$breadcrumbs = [
    ["Home", "admin_dashboard.php"],
    ["Customers", "admin_manage_customers.php"],
    ["Manage", "#"]
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

$customers = [];
$success_message = '';
$error_message = '';

if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $delete_id = mysqli_real_escape_string($conn, trim($_GET['id']));

    $sql_delete = "DELETE FROM customers WHERE customer_id = '$delete_id'";
    if (mysqli_query($conn, $sql_delete)) {
        $success_message = "<div class='alert alert-success'>Customer deleted successfully.</div>";
    } else {
        $error_message = "<div class='alert alert-danger'>Error deleting customer: " . mysqli_error($conn) . "</div>";
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['form_type']) && $_POST['form_type'] == 'customer_modal') {
    $customer_id_val = mysqli_real_escape_string($conn, trim($_POST['modalCustomerId'] ?? ''));
    $full_name = mysqli_real_escape_string($conn, $_POST['modalCustomerName']);
    $phone_number = mysqli_real_escape_string($conn, $_POST['modalCustomerPhone']);
    $email = mysqli_real_escape_string($conn, $_POST['modalCustomerEmail']);
    $address = mysqli_real_escape_string($conn, $_POST['modalCustomerAddress']);
    $city = mysqli_real_escape_string($conn, $_POST['modalCustomerCity']);

    if (empty($full_name) || empty($phone_number) || empty($address) || empty($city)) {
        $error_message = "<div class='alert alert-danger'>All required customer fields (Name, Phone, Address, City) must be filled.</div>";
    } else {
        if (!empty($customer_id_val)) {
            $sql_customer = "UPDATE customers SET full_name = '$full_name', phone_number = '$phone_number', email = '$email', address = '$address', city = '$city', updated_at = CURRENT_TIMESTAMP WHERE customer_id = '$customer_id_val'";
            if (mysqli_query($conn, $sql_customer)) {
                $success_message = "<div class='alert alert-success'>Customer updated successfully.</div>";
            } else {
                $error_message = "<div class='alert alert-danger'>Error updating customer: " . mysqli_error($conn) . "</div>";
            }
        } else {
            $sql_customer = "INSERT INTO customers (full_name, phone_number, email, address, city) VALUES ('$full_name', '$phone_number', '$email', '$address', '$city')";
            if (mysqli_query($conn, $sql_customer)) {
                $success_message = "<div class='alert alert-success'>Customer added successfully.</div>";
            } else {
                $error_message = "<div class='alert alert-danger'>Error adding customer: " . mysqli_error($conn) . "</div>";
            }
        }
    }
}

$sql_customers = "SELECT customer_id, full_name, phone_number, email, address, city FROM customers ORDER BY full_name ASC";
$result = mysqli_query($conn, $sql_customers);
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $customers[] = $row;
    }
    mysqli_free_result($result);
} else {
    $error_message .= "<div class='alert alert-danger'>Error fetching customers: " . mysqli_error($conn) . "</div>";
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

              <?php echo $success_message; ?>
              <?php echo $error_message; ?>

              <div class="d-flex justify-content-end mb-3">
                  <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#customerModal" data-customer-id="" title="Add New Customer"><i class="bi bi-plus-circle"></i> Add New Customer</button>
              </div>

              <table class="table datatable">
                <thead>
                  <tr>
                    <th scope="col">#</th>
                    <th scope="col">Name</th>
                    <th scope="col">Phone</th>
                    <th scope="col">Email</th>
                    <th scope="col">Address</th>
                    <th scope="col">City</th>
                    <th scope="col">Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if (!empty($customers)) {
                      $counter = 1;
                      foreach ($customers as $customer) { ?>
                        <tr>
                            <th scope="row"><?php echo htmlspecialchars($counter++); ?></th>
                            <td><?php echo htmlspecialchars($customer['full_name']); ?></td>
                            <td><?php echo htmlspecialchars($customer['phone_number']); ?></td>
                            <td><?php echo htmlspecialchars($customer['email']); ?></td>
                            <td><?php echo htmlspecialchars($customer['address']); ?></td>
                            <td><?php echo htmlspecialchars($customer['city']); ?></td>
                            <td>
                              <button type="button" class="btn btn-sm btn-info text-white" data-bs-toggle="modal" data-bs-target="#customerModal" data-customer-id="<?php echo htmlspecialchars($customer['customer_id']); ?>"
                                data-customer-name="<?php echo htmlspecialchars($customer['full_name']); ?>"
                                data-customer-phone="<?php echo htmlspecialchars($customer['phone_number']); ?>"
                                data-customer-email="<?php echo htmlspecialchars($customer['email']); ?>"
                                data-customer-address="<?php echo htmlspecialchars($customer['address']); ?>"
                                data-customer-city="<?php echo htmlspecialchars($customer['city']); ?>"
                                title="Edit Customer"><i class="bi bi-pencil"></i></button>
                              <a href="?action=delete&id=<?php echo htmlspecialchars($customer['customer_id']); ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this customer? This may affect linked shipments.');" title="Delete Customer"><i class="bi bi-trash"></i></a>
                            </td>
                        </tr>
                    <?php }
                  } else { ?>
                      <tr><td colspan="7">No customers found.</td></tr>
                  <?php } ?>
                </tbody>
              </table>

            </div>
          </div>

        </div>
      </div>
    </section>

    <div class="modal fade" id="customerModal" tabindex="-1">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Add/Edit Customer</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <form class="row g-3" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST">
              <input type="hidden" name="form_type" value="customer_modal">
              <input type="hidden" id="modalCustomerId" name="modalCustomerId">
              <div class="col-md-12">
                <label for="modalCustomerName" class="form-label">Full Name</label>
                <input type="text" class="form-control" id="modalCustomerName" name="modalCustomerName" required>
              </div>
              <div class="col-md-6">
                <label for="modalCustomerPhone" class="form-label">Phone Number</label>
                <input type="text" class="form-control" id="modalCustomerPhone" name="modalCustomerPhone" required>
              </div>
              <div class="col-md-6">
                <label for="modalCustomerEmail" class="form-label">Email</label>
                <input type="email" class="form-control" id="modalCustomerEmail" name="modalCustomerEmail">
              </div>
              <div class="col-12">
                <label for="modalCustomerAddress" class="form-label">Address</label>
                <textarea class="form-control" id="modalCustomerAddress" name="modalCustomerAddress" rows="3" required></textarea>
              </div>
              <div class="col-md-6">
                <label for="modalCustomerCity" class="form-label">City</label>
                <input type="text" class="form-control" id="modalCustomerCity" name="modalCustomerCity" required>
              </div>
              <div class="col-12 text-center mt-4">
                <button type="submit" class="btn btn-primary">Save Changes</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>

  </main>

<script>
    document.addEventListener('DOMContentLoaded', function() {
      const customerModal = document.getElementById('customerModal');
      customerModal.addEventListener('show.bs.modal', function (event) {
        const button = event.relatedTarget;
        const customerId = button.getAttribute('data-customer-id');

        const modalTitle = customerModal.querySelector('.modal-title');
        const modalForm = customerModal.querySelector('form');
        const modalCustomerId = modalForm.querySelector('#modalCustomerId');
        const modalCustomerName = modalForm.querySelector('#modalCustomerName');
        const modalCustomerPhone = modalForm.querySelector('#modalCustomerPhone');
        const modalCustomerEmail = modalForm.querySelector('#modalCustomerEmail');
        const modalCustomerAddress = modalForm.querySelector('#modalCustomerAddress');
        const modalCustomerCity = modalForm.querySelector('#modalCustomerCity');

        if (customerId) {
          modalTitle.textContent = 'Edit Customer Details';
          modalCustomerId.value = customerId;
          modalCustomerName.value = button.getAttribute('data-customer-name');
          modalCustomerPhone.value = button.getAttribute('data-customer-phone');
          modalCustomerEmail.value = button.getAttribute('data-customer-email');
          modalCustomerAddress.value = button.getAttribute('data-customer-address');
          modalCustomerCity.value = button.getAttribute('data-customer-city');
        } else {
          modalTitle.textContent = 'Add New Customer';
          modalCustomerId.value = '';
          modalCustomerName.value = '';
          modalCustomerPhone.value = '';
          modalCustomerEmail.value = '';
          modalCustomerAddress.value = '';
          modalCustomerCity.value = '';
        }
      });
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