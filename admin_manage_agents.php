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
              <h5 class="card-title">All Agent Logins</h5>
              <p>Admin can add, edit, or delete agent accounts and assign their branch locations.</p>

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

<?php include('inc.footer.php'); ?>

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