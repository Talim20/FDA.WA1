<?php
require_once 'includes/auth.php';
require_once 'includes/functions.php';

$pageTitle = 'Dashboard';
$currentYear = getOperationalYear();
$stats = getDashboardStats($currentYear);

include 'includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="mb-0">Dashboard</h2>
        <p class="text-muted mb-0">Overview of surveillance operations for <?php echo $currentYear; ?></p>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-xl-3 col-md-6">
        <div class="stat-card">
            <div class="stat-icon bg-primary bg-opacity-10 text-primary">
                <i class="bi bi-geo-alt"></i>
            </div>
            <div class="stat-content">
                <h6 class="text-muted mb-1">Total Communities</h6>
                <h3 class="mb-0"><?php echo number_format($stats['total_communities']); ?></h3>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6">
        <div class="stat-card">
            <div class="stat-icon bg-success bg-opacity-10 text-success">
                <i class="bi bi-check-circle"></i>
            </div>
            <div class="stat-content">
                <h6 class="text-muted mb-1">Visited — <?php echo $currentYear; ?></h6>
                <h3 class="mb-0"><?php echo number_format($stats['visited_this_year']); ?></h3>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6">
        <div class="stat-card">
            <div class="stat-icon bg-warning bg-opacity-10 text-warning">
                <i class="bi bi-circle"></i>
            </div>
            <div class="stat-content">
                <h6 class="text-muted mb-1">Remaining</h6>
                <h3 class="mb-0"><?php echo number_format($stats['not_visited']); ?></h3>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6">
        <div class="stat-card">
            <div class="stat-icon bg-info bg-opacity-10 text-info">
                <i class="bi bi-pie-chart"></i>
            </div>
            <div class="stat-content">
                <h6 class="text-muted mb-1">Coverage</h6>
                <h3 class="mb-0"><?php echo $stats['coverage_percentage']; ?>%</h3>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-lg-3 col-md-6">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <i class="bi bi-map fs-1 text-primary"></i>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h6 class="text-muted mb-1">Districts</h6>
                        <h4 class="mb-0"><?php echo $stats['total_districts']; ?></h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <i class="bi bi-building fs-1 text-success"></i>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h6 class="text-muted mb-1">Municipalities</h6>
                        <h4 class="mb-0"><?php echo $stats['total_municipalities']; ?></h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <i class="bi bi-people fs-1 text-warning"></i>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h6 class="text-muted mb-1">Active Teams</h6>
                        <h4 class="mb-0"><?php echo $stats['active_teams']; ?></h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <i class="bi bi-clipboard-check fs-1 text-info"></i>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h6 class="text-muted mb-1">Total Visits (<?php echo $currentYear; ?>)</h6>
                        <h4 class="mb-0"><?php echo $stats['total_visits']; ?></h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-body">
        <h5 class="card-title mb-4">Quick Actions</h5>
        <div class="row g-3">
            <div class="col-md-3 col-6">
                <a href="communities/index.php" class="btn btn-outline-primary w-100 h-100 d-flex flex-column align-items-center justify-content-center py-3">
                    <i class="bi bi-geo-alt fs-2 mb-2"></i>
                    <span>View Communities</span>
                </a>
            </div>
            <div class="col-md-3 col-6">
                <a href="communities/add.php" class="btn btn-outline-success w-100 h-100 d-flex flex-column align-items-center justify-content-center py-3">
                    <i class="bi bi-plus-circle fs-2 mb-2"></i>
                    <span>Add Community</span>
                </a>
            </div>
            <div class="col-md-3 col-6">
                <a href="visits/record.php" class="btn btn-outline-warning w-100 h-100 d-flex flex-column align-items-center justify-content-center py-3">
                    <i class="bi bi-clipboard-check fs-2 mb-2"></i>
                    <span>Record Visit</span>
                </a>
            </div>
            <div class="col-md-3 col-6">
                <a href="reports/index.php" class="btn btn-outline-info w-100 h-100 d-flex flex-column align-items-center justify-content-center py-3">
                    <i class="bi bi-file-earmark-bar-graph fs-2 mb-2"></i>
                    <span>Generate Reports</span>
                </a>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
