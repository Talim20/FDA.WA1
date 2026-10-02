<?php
require_once '../includes/auth.php';
require_once '../includes/functions.php';
requireRole(['supervisor', 'administrator']);

$pageTitle = 'Administrative Areas';
$areas = getAdministrativeAreas();

include '../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div><h2 class="mb-0">Administrative Areas</h2></div>
    <a href="add.php" class="btn btn-primary"><i class="bi bi-plus-circle me-2"></i>Add Area</a>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead><tr><th>Name</th><th>Type</th><th>Capital</th><th>Communities</th><th>Actions</th></tr></thead>
                <tbody>
                    <?php foreach ($areas as $area): ?>
                    <?php
                    $count = fetchOne("SELECT COUNT(*) as count FROM communities WHERE administrative_area_id = ? AND status = 'active'", [$area['id']])['count'];
                    ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($area['name']); ?></strong></td>
                        <td><span class="badge bg-<?php echo $area['type'] === 'municipality' ? 'primary' : 'secondary'; ?>"><?php echo ucfirst($area['type']); ?></span></td>
                        <td><?php echo htmlspecialchars($area['capital']); ?></td>
                        <td><?php echo $count; ?></td>
                        <td><a href="edit.php?id=<?php echo $area['id']; ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
