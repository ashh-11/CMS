<?php
// inc.sidebar.php - Dynamic sidebar based on user role
// This file assumes that inc.connections.php (which starts the session and includes config.php)
// and inc.header.php have already been included in the main page.
// Therefore, $_SESSION variables and role constants (ROLE_ADMIN, ROLE_AGENT, etc.) are available.

// Ensure that role constants are defined. They should ideally come from config.php.
// These checks prevent "Undefined constant" errors if config.php isn't robustly linked.
if (!defined('ROLE_ADMIN')) define('ROLE_ADMIN', 'admin');
if (!defined('ROLE_AGENT')) define('ROLE_AGENT', 'agent');
if (!defined('ROLE_CUSTOMER')) define('ROLE_CUSTOMER', 'customer'); // Though customer might not see this sidebar


// Get the current user's role
$user_role = getUserRole();
// Get the current page's title (set in the main PHP file before inc.header.php)
$current_page_title = $page_title ?? ''; // Default to empty string if not set
?>

<!-- ======= Sidebar ======= -->
<aside id="sidebar" class="sidebar">

  <ul class="sidebar-nav" id="sidebar-nav">

    <!-- Dashboard Nav -->
    <li class="nav-item">
      <?php
      $dashboard_link = '#'; // Default fallback
      $is_dashboard_active = false;

      if ($user_role == ROLE_ADMIN) {
        $dashboard_link = 'admin_dashboard.php';
        $is_dashboard_active = ($current_page_title == "Admin Dashboard");
      } elseif ($user_role == ROLE_AGENT) {
        $dashboard_link = 'agent_dashboard.php';
        $is_dashboard_active = ($current_page_title == "Agent Dashboard");
      }
      ?>
      <a class="nav-link <?php echo $is_dashboard_active ? '' : 'collapsed'; ?>" href="<?php echo htmlspecialchars($dashboard_link); ?>">
        <i class="bi bi-grid"></i>
        <span>Dashboard</span>
      </a>
    </li><!-- End Dashboard Nav -->

    <!-- Shipments Nav (Visible for Admin & Agent) -->
    <?php if ($user_role == ROLE_ADMIN || $user_role == ROLE_AGENT) { ?>
    <li class="nav-item">
      <?php
      $is_shipments_menu_active = (
          strpos($current_page_title, 'Shipment') !== false ||
          strpos($current_page_title, 'Bill') !== false
      );
      ?>
      <a class="nav-link <?php echo $is_shipments_menu_active ? '' : 'collapsed'; ?>" data-bs-target="#shipments-nav" data-bs-toggle="collapse" href="#">
        <i class="bi bi-box"></i><span>Shipments</span><i class="bi bi-chevron-down ms-auto"></i>
      </a>
      <ul id="shipments-nav" class="nav-content collapse <?php echo $is_shipments_menu_active ? 'show' : ''; ?>" data-bs-parent="#sidebar-nav">
        <?php if ($user_role == ROLE_ADMIN) { ?>
          <li>
            <a href="admin_manage_shipments.php" class="<?php echo ($current_page_title == "Manage All Shipments") ? 'active' : ''; ?>">
              <i class="bi bi-circle"></i><span>Manage All Shipments</span>
            </a>
          </li>
        <?php } elseif ($user_role == ROLE_AGENT) { ?>
          <li>
            <a href="agent_manage_shipments.php" class="<?php echo ($current_page_title == "Manage My Branch Shipments") ? 'active' : ''; ?>">
              <i class="bi bi-circle"></i><span>Manage My Branch Shipments</span>
            </a>
          </li>
        <?php } ?>
        <li>
          <a href="admin_create_update_shipment.php" class="<?php echo ($current_page_title == "Create/Update Shipment") ? 'active' : ''; ?>">
            <i class="bi bi-circle"></i><span>Create New Bill</span>
          </a>
        </li>
        <li>
          <a href="track_shipment.php">
            <i class="bi bi-circle"></i><span>Track Shipment (Public)</span>
          </a>
        </li>
      </ul>
    </li><!-- End Shipments Nav -->
    <?php } ?>

    <!-- Customers Nav (Visible for Admin Only) -->
    <?php if ($user_role == ROLE_ADMIN) { ?>
    <li class="nav-item">
      <a class="nav-link <?php echo ($current_page_title == "Manage Customer Details") ? '' : 'collapsed'; ?>" href="admin_manage_customers.php">
        <i class="bi bi-people"></i>
        <span>Customers</span>
      </a>
    </li><!-- End Customers Nav -->
    <?php } ?>

    <!-- Agents Nav (Visible for Admin Only) -->
    <?php if ($user_role == ROLE_ADMIN) { ?>
    <li class="nav-item">
      <a class="nav-link <?php echo ($current_page_title == "Manage Agents") ? '' : 'collapsed'; ?>" href="admin_manage_agents.php">
        <i class="bi bi-person-badge"></i>
        <span>Agents</span>
      </a>
    </li><!-- End Agents Nav -->
    <?php } ?>

    <!-- Reports Nav (Visible for Admin Only) -->
    <?php if ($user_role == ROLE_ADMIN) { ?>
    <li class="nav-item">
      <a class="nav-link <?php echo ($current_page_title == "Reports") ? '' : 'collapsed'; ?>" href="admin_reports.php">
        <i class="bi bi-bar-chart"></i>
        <span>Reports</span>
      </a>
    </li><!-- End Reports Nav -->
    <?php } ?>

    <li class="nav-heading">System Pages</li>

    <li class="nav-item">
      <a class="nav-link collapsed" href="users-profile.html">
        <i class="bi bi-person"></i>
        <span>My Profile</span>
      </a>
    </li><!-- End Profile Page Nav -->
    
    <li class="nav-item">
      <a class="nav-link collapsed" href="users-profile.html">
        <i class="bi bi-person"></i>
        <span>My Profile</span>
      </a>
    </li><!-- End Profile Page Nav -->

    <li class="nav-item">
      <a class="nav-link collapsed" href="logout.php">
        <i class="bi bi-box-arrow-right"></i>
        <span>Logout</span>
      </a>
    </li><!-- End Logout Page Nav -->

  </ul>

</aside><!-- End Sidebar-->