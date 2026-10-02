<?php
require_once '../includes/auth.php';
require_once '../includes/functions.php';

$pageTitle = 'Visit History';

$yearFilter = $_GET['year'] ?? getOperationalYear();
$areaFilter = $_GET['area'] ?? '';
$teamFilter = $_GET['team'] ?? '';

$sql = "SELECT sv.*, c.community_name, aa.name as area_name, vo.outcome_name, t.team_name, u.full_name as officer_name
        FROM surveillance_visits sv
        JOIN communities c ON sv.community_id = c.id
        JOIN administrative_areas aa ON c.administrative_area_id = aa.id
        JOIN visit_outcomes vo ON sv.inspection_outcome_id = vo.id
        JOIN teams t ON sv.team_id = t.id
        JOIN users u ON sv.officer_id = u.id
        WHERE 1=1";

$params = [];
if ($yearFilter) { $sql .= " AND sv.operational_year = ?"; $params[] = $yearFilter; }
if ($areaFilter) { $sql .= " AND c.administrative_area_id = ?"; $params[] = $areaFilter; }
if ($teamFilter) { $sql .= " AND sv.team_id = ?"; $params[] = $teamFilter; }

$sql .= " ORDER BY sv.visit_date DESC";
$visits = fetchAll($sql, $params);

$areas = getAdministrativeAreas();
$teams = getTeams();
$years = fetchAll("SELECT DISTINCT operational_year FROM surveillance_visits ORDER BY operational_year DESC");

include '../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div><h2 class="mb-0">Visit History</h2></div>
    <a href="record.php" class="btn btn-primary"><i class="bi bi-clipboard-check me-2"></i>Record New Visit</a>
</div>

<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Year</label>
                    <select class="form-select" name="year">
                        <option value="">All Years</option>
                        <?php foreach ($years as $yr): ?>
                        <option value="<?php echo $yr['operational_year']; ?>" <?php echo $yearFilter == $yr['operational_year'] ? 'selected' : ''; ?>><?php echo $yr['operational_year']; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Area</label>
                    <select class="form-select" name="area">
                        <option value="">All Areas</option>
                        <?php foreach ($areas as $area): ?>
                        <option value="<?php echo $area['id']; ?>" <?php echo $areaFilter == $area['id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($area['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Team</label>
                    <select class="form-select" name="team">
                        <option value="">All Teams</option>
                        <?php foreach ($teams as $team): ?>
                        <option value="<?php echo $team['id']; ?>" <?php echo $teamFilter == $team['id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($team['team_name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">&nbsp;</label>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">Filter</button>
                        <a href="history.php" class="btn btn-outline-secondary">Clear</a>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <?php if (empty($visits)): ?>
        <p class="text-muted">No visits found.</p>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-striped">
                <thead><tr><th>Date</th><th>Time</th><th>Year</th><th>Community</th><th>Area</th><th>Team</th><th>Officer</th><th>Outcome</th></tr></thead>
                <tbody>
                    <?php foreach ($visits as $visit): ?>
                    <tr>
                        <td><?php echo formatDate($visit['visit_date']); ?></td>
                        <td><?php echo formatTime($visit['visit_time']); ?></td>
                        <td><?php echo $visit['operational_year']; ?></td>
                        <td><a href="../communities/view.php?id=<?php echo $visit['community_id']; ?>"><?php echo htmlspecialchars($visit['community_name']); ?></a></td>
                        <td><?php echo htmlspecialchars($visit['area_name']); ?></td>
                        <td><?php echo htmlspecialchars($visit['team_name']); ?></td>
                        <td><?php echo htmlspecialchars($visit['officer_name']); ?></td>
                        <td><?php echo htmlspecialchars($visit['outcome_name']); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
