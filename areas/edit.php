<?php
require_once '../includes/auth.php';
require_once '../includes/functions.php';
requireRole(['supervisor', 'administrator']);

$pageTitle = 'Edit Area';
$error = '';
$areaId = $_GET['id'] ?? 0;

$sql = "SELECT * FROM administrative_areas WHERE id = ?";
$area = fetchOne($sql, [$areaId]);

if (!$area) {
    $_SESSION['error'] = 'Area not found.';
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $type = $_POST['type'] ?? '';
    $capital = trim($_POST['capital'] ?? '');
    
    if (empty($name) || empty($type) || empty($capital)) {
        $error = 'Please fill in all required fields.';
    } else {
        try {
            $sql = "UPDATE administrative_areas SET name = ?, type = ?, capital = ? WHERE id = ?";
            executeQuery($sql, [$name, $type, $capital, $areaId]);
            logAudit(getCurrentUserId(), 'AREA_UPDATED', 'administrative_area', $areaId, "Updated: $name");
            $_SESSION['success'] = 'Area successfully updated.';
            header('Location: index.php');
            exit;
        } catch (Exception $e) {
            $error = 'Error updating area.';
        }
    }
}

include '../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div><h2 class="mb-0">Edit Area</h2></div>
    <a href="index.php" class="btn btn-outline-secondary">Back</a>
</div>

<?php if ($error): ?>
<div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-body">
        <form method="POST" action="">
            <div class="mb-3">
                <label class="form-label">Name *</label>
                <input type="text" class="form-control" name="name" value="<?php echo htmlspecialchars($_POST['name'] ?? $area['name']); ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Type *</label>
                <select class="form-select" name="type" required>
                    <option value="">Select Type</option>
                    <option value="municipality" <?php echo (($_POST['type'] ?? $area['type'])) === 'municipality' ? 'selected' : ''; ?>>Municipality</option>
                    <option value="district" <?php echo (($_POST['type'] ?? $area['type'])) === 'district' ? 'selected' : ''; ?>>District</option>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Capital *</label>
                <input type="text" class="form-control" name="capital" value="<?php echo htmlspecialchars($_POST['capital'] ?? $area['capital']); ?>" required>
            </div>
            <button type="submit" class="btn btn-primary">Update Area</button>
            <a href="index.php" class="btn btn-outline-secondary">Cancel</a>
        </form>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
