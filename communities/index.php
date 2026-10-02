<?php
require_once '../includes/auth.php';
require_once '../includes/functions.php';

$pageTitle = 'Communities';
$currentYear = getOperationalYear();

$search = $_GET['search'] ?? '';
$areaFilter = $_GET['area'] ?? '';
$page = $_GET['page'] ?? 1;
$perPage = 20;
$offset = ($page - 1) * $perPage;

$sql = "SELECT c.*, aa.name as area_name, aa.type as area_type,
        (SELECT COUNT(*) FROM surveillance_visits sv WHERE sv.community_id = c.id AND sv.operational_year = ? AND sv.is_override = FALSE) as visit_count
        FROM communities c
        JOIN administrative_areas aa ON c.administrative_area_id = aa.id
        WHERE c.status = 'active'";

$params = [$currentYear];

if ($search) {
    $sql .= " AND (c.community_name LIKE ? OR aa.name LIKE ?)";
    $searchParam = "%$search%";
    $params[] = $searchParam;
    $params[] = $searchParam;
}

if ($areaFilter) {
    $sql .= " AND c.administrative_area_id = ?";
    $params[] = $areaFilter;
}

$sql .= " ORDER BY aa.name, c.community_name LIMIT $perPage OFFSET $offset";
$communities = fetchAll($sql, $params);

$areas = getAdministrativeAreas();

include '../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="mb-0">Communities</h2>
        <p class="text-muted mb-0">Manage registered communities</p>
    </div>
    <?php if (isSupervisorOrAdmin()): ?>
    <a href="add.php" class="btn btn-primary"><i class="bi bi-plus-circle me-2"></i>Add Community</a>
    <?php endif; ?>
</div>

<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Search</label>
                    <input type="text" class="form-control" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Community or district...">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Administrative Area</label>
                    <select class="form-select" name="area">
                        <option value="">All Areas</option>
                        <?php foreach ($areas as $area): ?>
                        <option value="<?php echo $area['id']; ?>" <?php echo $areaFilter == $area['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($area['name']); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">&nbsp;</label>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary"><i class="bi bi-search me-1"></i>Search</button>
                        <a href="index.php" class="btn btn-outline-secondary">Clear</a>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <?php if (empty($communities)): ?>
        <div class="text-center py-5">
            <i class="bi bi-geo-alt fs-1 text-muted"></i>
            <p class="text-muted mt-3">No communities found.</p>
        </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Community Name</th>
                        <th>Type</th>
                        <th>Administrative Area</th>
                        <th><?php echo $currentYear; ?> Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($communities as $community): ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($community['community_name']); ?></strong></td>
                        <td><?php echo ucfirst($community['locality_type']); ?></td>
                        <td><?php echo htmlspecialchars($community['area_name']); ?></td>
                        <td><?php echo getVisitStatusBadge($community['id'], $currentYear); ?></td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="view.php?id=<?php echo $community['id']; ?>" class="btn btn-outline-primary"><i class="bi bi-eye"></i></a>
                                <a href="../visits/record.php?community=<?php echo $community['id']; ?>" class="btn btn-outline-success"><i class="bi bi-clipboard-check"></i></a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
