<?php
require_once '../includes/auth.php';
require_once '../includes/functions.php';
requireRole(['supervisor', 'administrator']);

$pageTitle = 'Teams';
$teams = getTeams();

include '../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div><h2 class="mb-0">Teams</h2></div>
    <a href="add.php" class="btn btn-primary"><i class="bi bi-plus-circle me-2"></i>Add Team</a>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead><tr><th>Team Name</th><th>Identifier</th><th>Supervisor</th><th>Status</th><th>Actions</th></tr></thead>
                <tbody>
                    <?php foreach ($teams as $team): ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($team['team_name']); ?></strong></td>
                        <td><code><?php echo htmlspecialchars($team['team_identifier']); ?></code></td>
                        <td><?php echo htmlspecialchars($team['supervisor']); ?></td>
                        <td><span class="badge bg-<?php echo $team['status'] === 'active' ? 'success' : 'secondary'; ?>"><?php echo ucfirst($team['status']); ?></span></td>
                        <td><a href="edit.php?id=<?php echo $team['id']; ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
