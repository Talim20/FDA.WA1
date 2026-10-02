<?php
require_once '../includes/auth.php';
require_once '../includes/functions.php';
requireRole(['supervisor', 'administrator']);

$pageTitle = 'Add Team';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $teamName = trim($_POST['team_name'] ?? '');
    $teamIdentifier = trim($_POST['team_identifier'] ?? '');
    $supervisor = trim($_POST['supervisor'] ?? '');
    
    if (empty($teamName) || empty($teamIdentifier) || empty($supervisor)) {
        $error = 'Please fill in all required fields.';
    } else {
        try {
            $sql = "INSERT INTO teams (team_name, team_identifier, supervisor) VALUES (?, ?, ?)";
            executeQuery($sql, [$teamName, $teamIdentifier, $supervisor]);
            logAudit(getCurrentUserId(), 'TEAM_CREATED', 'team', lastInsertId(), "Created: $teamName");
            $_SESSION['success'] = 'Team successfully added.';
            header('Location: index.php');
            exit;
        } catch (Exception $e) {
            $error = 'Error adding team.';
        }
    }
}

include '../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div><h2 class="mb-0">Add Team</h2></div>
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
                <input type="text" class="form-control" name="team_name" value="<?php echo htmlspecialchars($_POST['team_name'] ?? ''); ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Team Identifier *</label>
                <input type="text" class="form-control" name="team_identifier" value="<?php echo htmlspecialchars($_POST['team_identifier'] ?? ''); ?>" placeholder="e.g., TM-001" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Supervisor *</label>
                <input type="text" class="form-control" name="supervisor" value="<?php echo htmlspecialchars($_POST['supervisor'] ?? ''); ?>" required>
            </div>
            <button type="submit" class="btn btn-primary">Save Team</button>
            <a href="index.php" class="btn btn-outline-secondary">Cancel</a>
        </form>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
