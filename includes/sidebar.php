<?php
$currentRole = $_SESSION['role'] ?? '';
?>
<nav class="sidebar">
    <div class="p-3 border-bottom border-secondary">
        <h5 class="text-white mb-0"><i class="bi bi-list-check me-2"></i>Menu</h5>
    </div>
    
    <ul class="nav flex-column py-2">
        <li class="nav-item">
            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>" href="dashboard.php">
                <i class="bi bi-speedometer2 me-2"></i>Dashboard
            </a>
        </li>
        
        <li class="nav-item">
            <a class="nav-link <?php echo strpos(basename($_SERVER['PHP_SELF']), 'communities') !== false ? 'active' : ''; ?>" href="communities/index.php">
                <i class="bi bi-geo-alt me-2"></i>Communities
            </a>
        </li>
        
        <li class="nav-item">
            <a class="nav-link <?php echo strpos(basename($_SERVER['PHP_SELF']), 'visits') !== false ? 'active' : ''; ?>" href="visits/record.php">
                <i class="bi bi-clipboard-check me-2"></i>Record Visit
            </a>
        </li>
        
        <li class="nav-item">
            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'visits/history.php' ? 'active' : ''; ?>" href="visits/history.php">
                <i class="bi bi-clock-history me-2"></i>Visit History
            </a>
        </li>
        
        <?php if (in_array($currentRole, ['administrator', 'supervisor'])): ?>
        <li class="nav-item">
            <a class="nav-link <?php echo strpos(basename($_SERVER['PHP_SELF']), 'areas') !== false ? 'active' : ''; ?>" href="areas/index.php">
                <i class="bi bi-map me-2"></i>Administrative Areas
            </a>
        </li>
        
        <li class="nav-item">
            <a class="nav-link <?php echo strpos(basename($_SERVER['PHP_SELF']), 'teams') !== false ? 'active' : ''; ?>" href="teams/index.php">
                <i class="bi bi-people me-2"></i>Teams
            </a>
        </li>
        <?php endif; ?>
        
        <?php if ($currentRole == 'administrator'): ?>
        <li class="nav-item">
            <a class="nav-link <?php echo strpos(basename($_SERVER['PHP_SELF']), 'users') !== false ? 'active' : ''; ?>" href="users/index.php">
                <i class="bi bi-person-gear me-2"></i>Users
            </a>
        </li>
        <?php endif; ?>
        
        <li class="nav-item">
            <a class="nav-link <?php echo strpos(basename($_SERVER['PHP_SELF']), 'reports') !== false ? 'active' : ''; ?>" href="reports/index.php">
                <i class="bi bi-file-earmark-bar-graph me-2"></i>Reports
            </a>
        </li>
    </ul>
    
    <div class="p-3 border-top border-secondary mt-auto">
        <div class="text-white small text-center">
            <i class="bi bi-shield-check me-1"></i>FDA Upper West Region
        </div>
    </div>
</nav>
