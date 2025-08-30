<?php
$user_role = getUserRole();
$current_page_title = $page_title ?? '';
?>

<aside id="sidebar" class="sidebar">

  <ul class="sidebar-nav" id="sidebar-nav">

    <li class="nav-item">
      <?php
      $dashboard_link = '#';
      $is_dashboard_active = false;

      if ($user_role == ROLE_ADMIN) {
        $dashboard_link = 'admin_dashboard.php';
        $is_dashboard_active = ($current_page_title == "Admin Dashboard");
      } elseif ($user_role == ROLE_AGENT) {
        $dashboard_link = 'agent_dashboard.php';
        $is_dashboard_active = ($current_page_title == "Agent Dashboard");
      } else {
          $dashboard_link = 'track_shipment.php';
      }
      ?>
      <a class="nav-link <?php echo $is_dashboard_active ? '' : 'collapsed'; ?>" href="<?php echo htmlspecialchars($dashboard_link); ?>">
        <i class="bi bi-grid"></i>
        <span>Dashboard</span>
      </a>
    </li>

    <?php if ($user_role == ROLE_ADMIN || $user_role == ROLE_AGENT) { ?>
    <li class="nav-item">
      <?php
      $is_shipments_menu_active = (
          strpos($current_page_title, 'Shipment') !== false ||
          strpos($current_page_title, 'Bill') !== false
      );
      ?>
      <!-- FIX: Added '#' to data-bs-target -->
      <a class="nav-link <?php echo $is_shipments_menu_active ? '' : 'collapsed'; ?>" data-bs-target="#shipments-nav" data-bs-toggle="collapse" href="#">
        <i class="bi bi-box"></i><span>Shipments</span><i class="bi bi-chevron-down ms-auto"></i>
      </a>
      <ul id="shipments-nav" class="nav-content collapse <?php echo $is_shipments_menu_active ? 'show' : ''; ?>" data-bs-parent="#sidebar-nav">
        <?php if ($user_role == ROLE_ADMIN) { ?>
          <li>
            <!-- FIX: Changed admin_manage_shipment.php to admin_manage_shipments.php -->
            <a href="admin_manage_shipments.php" class="<?php echo ($current_page_title == "Manage All Shipments") ? 'active' : ''; ?>">
              <i class="bi bi-circle"></i><span>Manage All Shipments</span>
            </a>
          </li>
        <?php } elseif ($user_role == ROLE_AGENT) { ?>
          <li>
            <!-- FIX: Changed agent_manage_shipment.php to agent_manage_shipments.php -->
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
    </li>
    <?php } ?>

    <?php if ($user_role == ROLE_ADMIN) { ?>
    <li class="nav-item">
      <a class="nav-link <?php echo ($current_page_title == "Manage Customer Details") ? '' : 'collapsed'; ?>" href="admin_manage_customers.php">
        <i class="bi bi-people"></i>
        <span>Customers</span>
      </a>
    </li>
    <?php } ?>

    <?php if ($user_role == ROLE_ADMIN) { ?>
    <li class="nav-item">
      <a class="nav-link <?php echo ($current_page_title == "Manage Agents") ? '' : 'collapsed'; ?>" href="admin_manage_agents.php">
        <i class="bi bi-person-badge"></i>
        <span>Agents</span>
      </a>
    </li>
    <?php } ?>

    <?php if ($user_role == ROLE_ADMIN) { ?>
    <li class="nav-item">
      <a class="nav-link <?php echo ($current_page_title == "Reports") ? '' : 'collapsed'; ?>" href="admin_reports.php">
        <i class="bi bi-bar-chart"></i>
        <span>Reports</span>
      </a>
    </li>
    <?php } ?>

    <li class="nav-heading">System Pages</li>


    <li class="nav-item">
      <a class="nav-link collapsed" href="logout.php">
        <i class="bi bi-box-arrow-right"></i>
        <span>Logout</span>
      </a>
    </li>

  </ul>

</aside>