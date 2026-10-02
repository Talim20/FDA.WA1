<?php
require_once '../includes/auth.php';
require_once '../includes/functions.php';

$pageTitle = 'Record Visit';
$error = '';
$showOverride = false;
$existingVisit = null;

$communityId = $_GET['community'] ?? $_POST['community_id'] ?? '';
$teams = getTeams();
$outcomes = getVisitOutcomes();
$communities = fetchAll("SELECT c.*, aa.name as area_name FROM communities c JOIN administrative_areas aa ON c.administrative_area_id = aa.id WHERE c.status = 'active' ORDER BY aa.name, c.community_name");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $communityId = $_POST['community_id'] ?? '';
    $teamId = $_POST['team_id'] ?? '';
    $visitDate = $_POST['visit_date'] ?? '';
    $visitTime = $_POST['visit_time'] ?? '';
    $operationalYear = $_POST['operational_year'] ?? getOperationalYear($visitDate);
    $outcomeId = $_POST['inspection_outcome_id'] ?? '';
    $notes = trim($_POST['notes'] ?? '');
    $latitude = $_POST['latitude'] ?? '';
    $longitude = $_POST['longitude'] ?? '';
    $isOverride = isset($_POST['is_override']) && $_POST['is_override'] === '1';
    $overrideReason = trim($_POST['override_reason'] ?? '');
    
    if (empty($communityId) || empty($teamId) || empty($visitDate) || empty($visitTime) || empty($outcomeId)) {
        $error = 'Please fill in all required fields.';
    } elseif ($isOverride && !isSupervisorOrAdmin()) {
        $error = 'You are not authorized to use override.';
    } elseif ($isOverride && empty($overrideReason)) {
        $error = 'Please provide override reason.';
    } else {
        $existingVisit = getCommunityVisitForYear($communityId, $operationalYear);
        
        if ($existingVisit && !$isOverride) {
            $showOverride = true;
            $error = 'This community has already been visited in ' . $operationalYear . '.';
        } else {
            try {
                $sql = "INSERT INTO surveillance_visits 
                        (community_id, team_id, officer_id, visit_date, visit_time, operational_year, 
                         inspection_outcome_id, notes, latitude, longitude, is_override, override_reason, override_authorized_by)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $overrideAuthorizedBy = $isOverride ? getCurrentUserId() : null;
                executeQuery($sql, [$communityId, $teamId, getCurrentUserId(), $visitDate, $visitTime, $operationalYear, 
                                $outcomeId, $notes ?: null, $latitude ?: null, $longitude ?: null, 
                                $isOverride ? 1 : 0, $overrideReason ?: null, $overrideAuthorizedBy]);
                
                $visitId = lastInsertId();
                logAudit(getCurrentUserId(), 'VISIT_RECORDED', 'surveillance_visit', $visitId, "Community: $communityId, Year: $operationalYear");
                
                $_SESSION['success'] = 'Visit successfully recorded.';
                header('Location: history.php?community=' . $communityId);
                exit;
            } catch (Exception $e) {
                $error = 'Error recording visit.';
            }
        }
    }
}

include '../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div><h2 class="mb-0">Record Surveillance Visit</h2></div>
    <a href="../communities/index.php" class="btn btn-outline-secondary">Back</a>
</div>

<?php if ($error): ?>
<div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>

<?php if ($existingVisit && $showOverride): ?>
<div class="alert alert-warning">
    <h5>Duplicate Visit Detected</h5>
    <p>This community was visited on <?php echo formatDate($existingVisit['visit_date']); ?> by <?php echo htmlspecialchars($existingVisit['team_name']); ?></p>
    <?php if (isSupervisorOrAdmin()): ?>
    <hr>
    <p>Authorized users may use the override option below.</p>
    <?php endif; ?>
</div>
<?php endif; ?>

<div class="card">
    <div class="card-body">
        <form method="POST" action="">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Community *</label>
                    <select class="form-select" name="community_id" required>
                        <option value="">Select Community</option>
                        <?php foreach ($communities as $comm): ?>
                        <option value="<?php echo $comm['id']; ?>" <?php echo $communityId == $comm['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($comm['community_name'] . ' - ' . $comm['area_name']); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Team *</label>
                    <select class="form-select" name="team_id" required>
                        <option value="">Select Team</option>
                        <?php foreach ($teams as $team): ?>
                        <option value="<?php echo $team['id']; ?>" <?php echo ($_POST['team_id'] ?? '') == $team['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($team['team_name']); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">Visit Date *</label>
                    <input type="date" class="form-control" name="visit_date" value="<?php echo htmlspecialchars($_POST['visit_date'] ?? date('Y-m-d')); ?>" required onchange="document.getElementById('operational_year').value = new Date(this.value).getFullYear()">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Visit Time *</label>
                    <input type="time" class="form-control" name="visit_time" value="<?php echo htmlspecialchars($_POST['visit_time'] ?? date('H:i')); ?>" required>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Operational Year *</label>
                    <input type="number" class="form-control" id="operational_year" name="operational_year" value="<?php echo htmlspecialchars($_POST['operational_year'] ?? getOperationalYear()); ?>" readonly>
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">Inspection Outcome *</label>
                <select class="form-select" name="inspection_outcome_id" required>
                    <option value="">Select Outcome</option>
                    <?php foreach ($outcomes as $outcome): ?>
                    <option value="<?php echo $outcome['id']; ?>" <?php echo ($_POST['inspection_outcome_id'] ?? '') == $outcome['id'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($outcome['outcome_name']); ?>
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
                <label class="form-label">Notes</label>
                <textarea class="form-control" name="notes" rows="3"><?php echo htmlspecialchars($_POST['notes'] ?? ''); ?></textarea>
            </div>
            <?php if ($showOverride && isSupervisorOrAdmin()): ?>
            <div class="card bg-warning bg-opacity-10 mb-3">
                <div class="card-body">
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="is_override" name="is_override" value="1" onchange="document.getElementById('override_reason_div').style.display = this.checked ? 'block' : 'none'">
                        <label class="form-check-label fw-bold" for="is_override">Authorize Override Visit</label>
                    </div>
                    <div id="override_reason_div" style="display: none;">
                        <label class="form-label">Override Reason *</label>
                        <textarea class="form-control" name="override_reason" rows="2"></textarea>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            <button type="submit" class="btn btn-primary">Record Visit</button>
            <a href="../communities/index.php" class="btn btn-outline-secondary">Cancel</a>
        </form>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
