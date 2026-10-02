<?php
require_once '../includes/auth.php';
require_once '../includes/functions.php';

requireRole(['supervisor', 'administrator']);

$pageTitle = 'Add Community';
$error = '';
$areas = getAdministrativeAreas();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['community_name'] ?? '');
    $localityType = $_POST['locality_type'] ?? '';
    $areaId = $_POST['administrative_area_id'] ?? '';
    $latitude = $_POST['latitude'] ?? '';
    $longitude = $_POST['longitude'] ?? '';
    $description = trim($_POST['description'] ?? '');
    
    if (empty($name) || empty($localityType) || empty($areaId)) {
        $error = 'Please fill in all required fields.';
    } elseif (isDuplicateCommunity($name, $areaId)) {
        $error = 'Community already exists in this area.';
    } else {
        try {
            $sql = "INSERT INTO communities (community_name, locality_type, administrative_area_id, latitude, longitude, description)
                    VALUES (?, ?, ?, ?, ?, ?)";
            executeQuery($sql, [$name, $localityType, $areaId, $latitude ?: null, $longitude ?: null, $description ?: null]);
            
            $newId = lastInsertId();
            logAudit(getCurrentUserId(), 'COMMUNITY_CREATED', 'community', $newId, "Created: $name");
            
            $_SESSION['success'] = 'Community successfully added.';
            header('Location: view.php?id=' . $newId);
            exit;
        } catch (Exception $e) {
            $error = 'Error adding community.';
        }
    }
}

include '../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div><h2 class="mb-0">Add Community</h2></div>
    <a href="index.php" class="btn btn-outline-secondary">Back</a>
</div>

<?php if ($error): ?>
<div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-body">
        <form method="POST" action="">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Community Name *</label>
                    <input type="text" class="form-control" name="community_name" value="<?php echo htmlspecialchars($_POST['community_name'] ?? ''); ?>" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Locality Type *</label>
                    <select class="form-select" name="locality_type" required>
                        <option value="">Select Type</option>
                        <option value="community" <?php echo ($_POST['locality_type'] ?? '') === 'community' ? 'selected' : ''; ?>>Community</option>
                        <option value="village" <?php echo ($_POST['locality_type'] ?? '') === 'village' ? 'selected' : ''; ?>>Village</option>
                        <option value="town" <?php echo ($_POST['locality_type'] ?? '') === 'town' ? 'selected' : ''; ?>>Town</option>
                    </select>
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">Administrative Area *</label>
                <select class="form-select" name="administrative_area_id" required>
                    <option value="">Select Area</option>
                    <?php foreach ($areas as $area): ?>
                    <option value="<?php echo $area['id']; ?>" <?php echo ($_POST['administrative_area_id'] ?? '') == $area['id'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($area['name'] . ' - ' . $area['capital']); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Latitude</label>
                    <input type="text" class="form-control" name="latitude" value="<?php echo htmlspecialchars($_POST['latitude'] ?? ''); ?>">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Longitude</label>
                    <input type="text" class="form-control" name="longitude" value="<?php echo htmlspecialchars($_POST['longitude'] ?? ''); ?>">
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">Description</label>
                <textarea class="form-control" name="description" rows="3"><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Save Community</button>
            <a href="index.php" class="btn btn-outline-secondary">Cancel</a>
        </form>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
