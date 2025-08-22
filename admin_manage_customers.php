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
              <h5 class="card-title">All Customer Details</h5>
              <p>Admin can view, search, and manage all registered customer details.</p>

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

<?php include('inc.footer.php'); ?>

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