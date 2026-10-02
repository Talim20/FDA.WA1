<?php
require_once '../includes/auth.php';
require_once '../includes/functions.php';
requireRole(['supervisor', 'administrator']);

$pageTitle = 'Edit Team';
$error = '';
$teamId = $_GET['id'] ?? 0;

$sql = "SELECT * FROM teams WHERE id = ?";
$team = fetchOne($sql, [$teamId]);

if (!$team) {
    $_SESSION['error'] = 'Team not found.';
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $teamName = trim($_POST['team_name'] ?? '');
    $teamIdentifier = trim($_POST['team_identifier'] ?? '');
    $supervisor = trim($_POST['supervisor'] ?? '');
    $status = $_POST['status'] ?? 'active';
    
    if (empty($teamName) || empty($teamIdentifier) || empty($supervisor)) {
        $error = 'Please fill in all required fields.';
    } else {
        try {
            $sql = "UPDATE teams SET team_name = ?, team_identifier = ?, supervisor = ?, status = ? WHERE id = ?";
            executeQuery($sql, [$teamName, $teamIdentifier, $supervisor, $status, $teamId]);
            logAudit(getCurrentUserId(), 'TEAM_UPDATED', 'team', $teamId, "Updated: $teamName");
            $_SESSION['success'] = 'Team successfully updated.';
            header('Location: index.php');
            exit;
        } catch (Exception $e) {
            $error = 'Error updating team.';
        }
    }
}

include '../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div><h2 class="mb-0">Edit Team</h2></div>
    <a href="index.php" class="btn btn-outline-secondary">Back</a>
</div>

<?php if ($error): ?>
<div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-body">
        <form method="POST" action="">
            <div class="mb-3">
                <label class="form-label">Team Name *</label>
                <input type="text" class="form-control" name="team_name" value="<?php echo htmlspecialchars($_POST['team_name'] ?? $team['team_name']); ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Team Identifier *</label>
                <input type="text" class="form-control" name="team_identifier" value="<?php echo htmlspecialchars($_POST['team_identifier'] ?? $team['team_identifier']); ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Supervisor *</label>
                <input type="text" class="form-control" name="supervisor" value="<?php echo htmlspecialchars($_POST['supervisor'] ?? $team['supervisor']); ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Status</label>
                <select class="form-select" name="status">
                    <option value="active" <?php echo (($_POST['status'] ?? $team['status'])) === 'active' ? 'selected' : ''; ?>>Active</option>
                    <option value="inactive" <?php echo (($_POST['status'] ?? $team['status'])) === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Update Team</button>
            <a href="index.php" class="btn btn-outline-secondary">Cancel</a>
        </form>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
