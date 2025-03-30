<?php
$current_page = basename($_SERVER['PHP_SELF']);
$user_type = $_SESSION['user_type'] ?? 'user';
$dashboardLink = match($user_type) {
    'admin' => 'admin_dashboard.php',
    'staff' => 'staff_dashboard.php',
    default => 'user_dashboard.php'
};
?>

<div class="sidebar">
    <h4>AU JAS</h4>
    
    <!-- Dashboard Link -->
    <a href="<?= $dashboardLink ?>" class="<?= ($current_page == $dashboardLink) ? 'active' : '' ?>">
        <i class="bi bi-house-door"></i> Dashboard
    </a>

    <!-- Event Calendar (common for all) -->
    <a href="event_calendar.php" class="<?= ($current_page == 'event_calendar.php') ? 'active' : '' ?>">
        <i class="bi bi-calendar"></i> Event Calendar
    </a>

    <?php if($user_type == 'admin') : ?>
        <!-- Admin-only links -->
        <a href="admin_Event management.php" class="<?= ($current_page == 'admin_Event_management.php') ? 'active' : '' ?>">
            <i class="bi bi-gear"></i> Event Management
        </a>
        <a href="admin_user management.php" class="<?= ($current_page == 'admin_user_management.php') ? 'active' : '' ?>">
            <i class="bi bi-people"></i> User Management
        </a>
    <?php elseif($user_type == 'staff') : ?>
        <!-- Staff-only links -->
        <a href="staff_event_management.php" class="<?= ($current_page == 'staff_event_management.php') ? 'active' : '' ?>">
            <i class="bi bi-ticket-perforated"></i> Event Submissions
        </a>
    <?php endif; ?>

    <!-- Questionnaires (common for all) -->
    <a href="admin_questionnaires.php" class="<?= ($current_page == 'admin_questionnaires.php') ? 'active' : '' ?>">
        <i class="bi bi-clipboard"></i> Questionnaires
    </a>

    <!-- Reports (common for all) -->
    <a href="admin_reports.php" class="<?= ($current_page == 'admin_reports.php') ? 'active' : '' ?>">
        <i class="bi bi-file-earmark-text"></i> Reports
    </a>
</div>