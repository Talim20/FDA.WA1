<?php
require_once '../includes/auth.php';
require_once '../includes/functions.php';

$pageTitle = 'Community Details';
$communityId = $_GET['id'] ?? 0;

if (!$communityId) {
    $_SESSION['error'] = 'Invalid community ID.';
    header('Location: index.php');
    exit;
}

$sql = "SELECT c.*, aa.name as area_name, aa.type as area_type, aa.capital
        FROM communities c
        JOIN administrative_areas aa ON c.administrative_area_id = aa.id
        WHERE c.id = ?";
$community = fetchOne($sql, [$communityId]);

if (!$community) {
    $_SESSION['error'] = 'Community not found.';
    header('Location: index.php');
    exit;
}

$currentYear = getOperationalYear();
$currentVisit = getCommunityVisitForYear($communityId, $currentYear);
$visitHistory = getCommunityVisitHistory($communityId);

include '../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="mb-0"><?php echo htmlspecialchars($community['community_name']); ?></h2>
        <p class="text-muted mb-0"><?php echo ucfirst($community['locality_type']); ?> Details</p>
    </div>
    <a href="index.php" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-2"></i>Back</a>
</div>

<div class="card mb-4">
    <div class="card-header bg-primary text-white">
        <h5 class="card-title mb-0"><i class="bi bi-info-circle me-2"></i>Community Information</h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6 mb-3"><strong>Community Name:</strong> <?php echo htmlspecialchars($community['community_name']); ?></div>
            <div class="col-md-6 mb-3"><strong>Locality Type:</strong> <?php echo ucfirst($community['locality_type']); ?></div>
            <div class="col-md-6 mb-3"><strong>Administrative Area:</strong> <?php echo htmlspecialchars($community['area_name']); ?></div>
            <div class="col-md-6 mb-3"><strong>Capital:</strong> <?php echo htmlspecialchars($community['capital']); ?></div>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header bg-info text-white">
        <h5 class="card-title mb-0"><?php echo $currentYear; ?> Status</h5>
    </div>
    <div class="card-body">
        <div class="mb-3"><strong>Status:</strong> <?php echo getVisitStatusBadge($communityId, $currentYear); ?></div>
        <?php if ($currentVisit): ?>
        <div class="mb-3"><strong>Last Visit:</strong> <?php echo formatDate($currentVisit['visit_date']); ?> at <?php echo formatTime($currentVisit['visit_time']); ?></div>
        <div class="mb-3"><strong>Team:</strong> <?php echo htmlspecialchars($currentVisit['team_name']); ?></div>
        <div class="mb-3"><strong>Outcome:</strong> <?php echo htmlspecialchars($currentVisit['outcome_name']); ?></div>
        <?php else: ?>
        <div class="alert alert-info">This community has not been visited during <?php echo $currentYear; ?>.</div>
        <?php endif; ?>
    </div>
</div>

<div class="card">
    <div class="card-header bg-dark text-white">
        <h5 class="card-title mb-0"><i class="bi bi-clock-history me-2"></i>Visit History</h5>
    </div>
    <div class="card-body">
        <?php if (empty($visitHistory)): ?>
        <p class="text-muted">No visit history recorded.</p>
        <?php else: ?>
        <table class="table table-striped">
            <thead><tr><th>Year</th><th>Date</th><th>Time</th><th>Team</th><th>Officer</th><th>Outcome</th></tr></thead>
            <tbody>
                <?php foreach ($visitHistory as $visit): ?>
                <tr>
                    <td><?php echo $visit['operational_year']; ?></td>
                    <td><?php echo formatDate($visit['visit_date']); ?></td>
                    <td><?php echo formatTime($visit['visit_time']); ?></td>
                    <td><?php echo htmlspecialchars($visit['team_name']); ?></td>
                    <td><?php echo htmlspecialchars($visit['officer_name']); ?></td>
                    <td><?php echo htmlspecialchars($visit['outcome_name']); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
