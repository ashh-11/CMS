<?php
$page_title = "Manage Agents";
$breadcrumbs = [
    ["Home", "admin_dashboard.php"],
    ["Users", "#"],
    ["Agents", "#"]
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

$agents = [];
$locations = [];
$success_message = '';
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

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['form_type']) && $_POST['form_type'] == 'agent_modal') {
    $agent_user_id = mysqli_real_escape_string($conn, trim($_POST['modalAgentId'] ?? ''));
    $username = mysqli_real_escape_string($conn, $_POST['modalAgentUsername']);
    $password = mysqli_real_escape_string($conn, $_POST['modalAgentPassword']);
    $location_id = mysqli_real_escape_string($conn, $_POST['modalAgentLocation']);
    $status = mysqli_real_escape_string($conn, $_POST['modalAgentStatus']);

    if (empty($username) || empty($location_id) || empty($status)) {
        $error_message = "<div class='alert alert-danger'>Username, Location, and Status are required.</div>";
    } else {
        $hashed_password = null;
        if (!empty($password)) {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        }

        if (!empty($agent_user_id)) {
            $sql_agent = "UPDATE users SET username = '$username', location_id = '$location_id', status = '$status'";
            if ($hashed_password !== null) {
                $sql_agent .= ", password_hash = '$hashed_password'";
            }
            $sql_agent .= " WHERE user_id = '$agent_user_id' AND role = 'agent'";

            if (mysqli_query($conn, $sql_agent)) {
                $success_message = "<div class='alert alert-success'>Agent updated successfully.</div>";
            } else {
                $error_message = "<div class='alert alert-danger'>Error updating agent: " . mysqli_error($conn) . "</div>";
            }
        } else {
            if (empty($password)) {
                $error_message = "<div class='alert alert-danger'>Password is required for new agents.</div>";
            } else {
                $hashed_password_for_new_agent = password_hash($password, PASSWORD_DEFAULT);
                $sql_agent = "INSERT INTO users (username, password_hash, role, location_id, status) VALUES ('$username', '$hashed_password_for_new_agent', 'agent', '$location_id', '$status')";
                if (mysqli_query($conn, $sql_agent)) {
                    $success_message = "<div class='alert alert-success'>Agent added successfully.</div>";
                } else {
                    $error_message = "<div class='alert alert-danger'>Error adding agent: " . mysqli_error($conn) . "</div>";
                }
            }
        }
    }
}

if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $delete_id = mysqli_real_escape_string($conn, trim($_GET['id']));

    $sql_delete = "DELETE FROM users WHERE user_id = '$delete_id' AND role = 'agent'";
    if (mysqli_query($conn, $sql_delete)) {
        $success_message = "<div class='alert alert-success'>Agent deleted successfully.</div>";
    } else {
        $error_message = "<div class='alert alert-danger'>Error deleting agent: " . mysqli_error($conn) . "</div>";
    }
}

$sql_agents = "SELECT u.user_id, u.username, l.city_name, u.status, u.created_at, u.location_id
               FROM users u
               JOIN locations l ON u.location_id = l.location_id
               WHERE u.role = 'agent'
               ORDER BY u.username ASC";
$result = mysqli_query($conn, $sql_agents);
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $agents[] = $row;
    }
    mysqli_free_result($result);
} else {
    $error_message .= "<div class='alert alert-danger'>Error fetching agents: " . mysqli_error($conn) . "</div>";
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
                  <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#agentModal" data-agent-id="" title="Add New Agent"><i class="bi bi-person-plus"></i> Add New Agent</button>
              </div>

              <table class="table datatable">
                <thead>
                  <tr>
                    <th scope="col">#</th>
                    <th scope="col">Username</th>
                    <th scope="col">Assigned Location</th>
                    <th scope="col">Status</th>
                    <th scope="col">Created At</th>
                    <th scope="col">Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if (!empty($agents)) {
                      $counter = 1;
                      foreach ($agents as $agent) { ?>
                        <tr>
                            <th scope="row"><?php echo htmlspecialchars($counter++); ?></th>
                            <td><?php echo htmlspecialchars($agent['username']); ?></td>
                            <td><?php echo htmlspecialchars($agent['city_name']); ?></td>
                            <td><span class="badge <?php echo ($agent['status'] == 'active') ? 'bg-success' : 'bg-warning'; ?>"><?php echo htmlspecialchars(ucfirst($agent['status'])); ?></span></td>
                            <td><?php echo htmlspecialchars($agent['created_at']); ?></td>
                            <td>
                              <button type="button" class="btn btn-sm btn-info text-white" data-bs-toggle="modal" data-bs-target="#agentModal" data-agent-id="<?php echo htmlspecialchars($agent['user_id']); ?>"
                                data-agent-username="<?php echo htmlspecialchars($agent['username']); ?>"
                                data-agent-location-id="<?php echo htmlspecialchars($agent['location_id']); ?>"
                                data-agent-status="<?php echo htmlspecialchars($agent['status']); ?>"
                                title="Edit Agent"><i class="bi bi-pencil"></i></button>
                              <a href="?action=delete&id=<?php echo htmlspecialchars($agent['user_id']); ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this agent?');" title="Delete Agent"><i class="bi bi-trash"></i></a>
                            </td>
                        </tr>
                    <?php }
                  } else { ?>
                      <tr><td colspan="6">No agents found.</td></tr>
                  <?php } ?>
                </tbody>
              </table>

            </div>
          </div>

        </div>
      </div>
    </section>

    <div class="modal fade" id="agentModal" tabindex="-1">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Add/Edit Agent Login</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <form class="row g-3" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST">
              <input type="hidden" name="form_type" value="agent_modal">
              <input type="hidden" id="modalAgentId" name="modalAgentId">
              <div class="col-md-12">
                <label for="modalAgentUsername" class="form-label">Username</label>
                <input type="text" class="form-control" id="modalAgentUsername" name="modalAgentUsername" required>
              </div>
              <div class="col-md-12">
                <label for="modalAgentPassword" class="form-label">Password</label>
                <input type="password" class="form-control" id="modalAgentPassword" name="modalAgentPassword" placeholder="Leave blank to keep current password">
                <small class="text-muted">Only fill this if you want to change the password.</small>
              </div>
              <div class="col-md-12">
                <label for="modalAgentLocation" class="form-label">Assigned Location</label>
                <select class="form-select" id="modalAgentLocation" name="modalAgentLocation" required>
                  <option value="" disabled selected>Select Branch City</option>
                  <?php foreach ($locations as $loc) { ?>
                    <option value="<?php echo htmlspecialchars($loc['location_id']); ?>">
                      <?php echo htmlspecialchars($loc['city_name']); ?>
                    </option>
                  <?php } ?>
                </select>
              </div>
              <div class="col-md-12">
                <label for="modalAgentStatus" class="form-label">Account Status</label>
                <select class="form-select" id="modalAgentStatus" name="modalAgentStatus" required>
                  <option value="active">Active</option>
                  <option value="inactive">Inactive</option>
                </select>
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
      const agentModal = document.getElementById('agentModal');
      agentModal.addEventListener('show.bs.modal', function (event) {
        const button = event.relatedTarget;
        const agentId = button.getAttribute('data-agent-id');

        const modalTitle = agentModal.querySelector('.modal-title');
        const modalForm = agentModal.querySelector('form');
        const modalAgentId = modalForm.querySelector('#modalAgentId');
        const modalAgentUsername = modalForm.querySelector('#modalAgentUsername');
        const modalAgentPassword = modalForm.querySelector('#modalAgentPassword');
        const modalAgentLocation = modalForm.querySelector('#modalAgentLocation');
        const modalAgentStatus = modalForm.querySelector('#modalAgentStatus');

        modalAgentPassword.value = '';

        if (agentId) {
          modalTitle.textContent = 'Edit Agent Login';
          modalAgentId.value = agentId;
          modalAgentUsername.value = button.getAttribute('data-agent-username');
          modalAgentLocation.value = button.getAttribute('data-agent-location-id');
          modalAgentStatus.value = button.getAttribute('data-agent-status');
        } else {
          modalTitle.textContent = 'Add New Agent';
          modalAgentId.value = '';
          modalAgentUsername.value = '';
          modalAgentLocation.value = '';
          modalAgentStatus.value = 'active';
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