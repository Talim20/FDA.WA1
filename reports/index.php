<?php
require_once '../includes/auth.php';
require_once '../includes/functions.php';

$pageTitle = 'Reports';

$yearFilter = $_GET['year'] ?? getOperationalYear();
$areaFilter = $_GET['area'] ?? '';
$teamFilter = $_GET['team'] ?? '';
$outcomeFilter = $_GET['outcome'] ?? '';

$sql = "SELECT sv.*, c.community_name, c.locality_type, aa.name as area_name,
        vo.outcome_name, t.team_name, u.full_name as officer_name
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
if ($outcomeFilter) { $sql .= " AND sv.inspection_outcome_id = ?"; $params[] = $outcomeFilter; }

$sql .= " ORDER BY sv.visit_date DESC";
$visits = fetchAll($sql, $params);

$totalCommunities = fetchOne("SELECT COUNT(*) as count FROM communities WHERE status = 'active'")['count'];
$visitedCommunities = count(array_unique(array_column($visits, 'community_id')));
$unvisitedCommunities = $totalCommunities - $visitedCommunities;
$coveragePercentage = $totalCommunities > 0 ? round(($visitedCommunities / $totalCommunities) * 100, 1) : 0;

$areas = getAdministrativeAreas();
$teams = getTeams();
$outcomes = getVisitOutcomes();
$years = fetchAll("SELECT DISTINCT operational_year FROM surveillance_visits ORDER BY operational_year DESC");

include '../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div><h2 class="mb-0">Reports</h2></div>
    <div class="btn-group">
        <button type="button" class="btn btn-outline-success" onclick="exportCSV()">
            <i class="bi bi-file-earmark-excel me-2"></i>Export CSV
        </button>
        <button type="button" class="btn btn-outline-secondary" onclick="window.print()">
            <i class="bi bi-printer me-2"></i>Print
        </button>
    </div>
</div>

<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="">
            <div class="row g-3">
                <div class="col-md-2">
                    <label class="form-label">Year</label>
                    <select class="form-select" name="year">
                        <option value="">All Years</option>
                        <?php foreach ($years as $yr): ?>
                        <option value="<?php echo $yr['operational_year']; ?>" <?php echo $yearFilter == $yr['operational_year'] ? 'selected' : ''; ?>><?php echo $yr['operational_year']; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Area</label>
                    <select class="form-select" name="area">
                        <option value="">All Areas</option>
                        <?php foreach ($areas as $area): ?>
                        <option value="<?php echo $area['id']; ?>" <?php echo $areaFilter == $area['id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($area['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Team</label>
                    <select class="form-select" name="team">
                        <option value="">All Teams</option>
                        <?php foreach ($teams as $team): ?>
                        <option value="<?php echo $team['id']; ?>" <?php echo $teamFilter == $team['id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($team['team_name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Outcome</label>
                    <select class="form-select" name="outcome">
                        <option value="">All Outcomes</option>
                        <?php foreach ($outcomes as $outcome): ?>
                        <option value="<?php echo $outcome['id']; ?>" <?php echo $outcomeFilter == $outcome['id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($outcome['outcome_name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">&nbsp;</label>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">Generate</button>
                        <a href="index.php" class="btn btn-outline-secondary">Clear</a>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <h6 class="card-title">Total Communities</h6>
                <h3 class="mb-0"><?php echo number_format($totalCommunities); ?></h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-success text-white">
            <div class="card-body">
                <h6 class="card-title">Visited</h6>
                <h3 class="mb-0"><?php echo number_format($visitedCommunities); ?></h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-warning text-dark">
            <div class="card-body">
                <h6 class="card-title">Unvisited</h6>
                <h3 class="mb-0"><?php echo number_format($unvisitedCommunities); ?></h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-info text-white">
            <div class="card-body">
                <h6 class="card-title">Coverage %</h6>
                <h3 class="mb-0"><?php echo $coveragePercentage; ?>%</h3>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">Surveillance Visit Report</h5>
    </div>
    <div class="card-body">
        <?php if (empty($visits)): ?>
        <p class="text-muted">No visits found matching your criteria.</p>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-striped" id="reportTable">
                <thead><tr><th>Date</th><th>Time</th><th>Year</th><th>Community</th><th>Area</th><th>Team</th><th>Officer</th><th>Outcome</th><th>Notes</th></tr></thead>
                <tbody>
                    <?php foreach ($visits as $visit): ?>
                    <tr>
                        <td><?php echo formatDate($visit['visit_date']); ?></td>
                        <td><?php echo formatTime($visit['visit_time']); ?></td>
                        <td><?php echo $visit['operational_year']; ?></td>
                        <td><?php echo htmlspecialchars($visit['community_name']); ?></td>
                        <td><?php echo htmlspecialchars($visit['area_name']); ?></td>
                        <td><?php echo htmlspecialchars($visit['team_name']); ?></td>
                        <td><?php echo htmlspecialchars($visit['officer_name']); ?></td>
                        <td><?php echo htmlspecialchars($visit['outcome_name']); ?></td>
                        <td><?php echo htmlspecialchars($visit['notes'] ?? '-'); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div class="mt-3"><strong>Total Visits:</strong> <?php echo count($visits); ?></div>
        <?php endif; ?>
    </div>
</div>

<?php include '../includes/footer.php'; ?>

<script>
function exportCSV() {
    const table = document.getElementById('reportTable');
    if (!table) {
        alert('No data to export');
        return;
    }
    
    let csv = [];
    const rows = table.querySelectorAll('tr');
    
    for (let i = 0; i < rows.length; i++) {
        const row = [], cols = rows[i].querySelectorAll('td, th');
        for (let j = 0; j < cols.length; j++) {
            row.push('"' + cols[j].innerText.replace(/"/g, '""') + '"');
        }
        csv.push(row.join(','));
    }
    
    const csvFile = new Blob([csv.join('\n')], { type: 'text/csv' });
    const downloadLink = document.createElement('a');
    downloadLink.download = 'surveillance_report_<?php echo date('Y-m-d'); ?>.csv';
    downloadLink.href = window.URL.createObjectURL(csvFile);
    downloadLink.style.display = 'none';
    document.body.appendChild(downloadLink);
    downloadLink.click();
    document.body.removeChild(downloadLink);
}
</script>
