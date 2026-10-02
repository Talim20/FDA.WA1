<?php
require_once '../includes/auth.php';
require_once '../includes/functions.php';
requireRole(['administrator']);

$pageTitle = 'Add User';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName = trim($_POST['full_name'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? '';
    $status = $_POST['status'] ?? 'active';
    
    if (empty($fullName) || empty($username) || empty($email) || empty($password) || empty($role)) {
        $error = 'Please fill in all required fields.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } else {
        try {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $sql = "INSERT INTO users (full_name, username, email, password, role, status) VALUES (?, ?, ?, ?, ?, ?)";
            executeQuery($sql, [$fullName, $username, $email, $hashedPassword, $role, $status]);
            logAudit(getCurrentUserId(), 'USER_CREATED', 'user', lastInsertId(), "Created: $username");
            $_SESSION['success'] = 'User successfully added.';
            header('Location: index.php');
            exit;
        } catch (Exception $e) {
            $error = 'Error adding user.';
        }
    }
}

include '../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div><h2 class="mb-0">Add User</h2></div>
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
                    <label class="form-label">Full Name *</label>
                    <input type="text" class="form-control" name="full_name" value="<?php echo htmlspecialchars($_POST['full_name'] ?? ''); ?>" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Username *</label>
                    <input type="text" class="form-control" name="username" value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>" required>
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">Email *</label>
                <input type="email" class="form-control" name="email" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Password *</label>
                    <input type="password" class="form-control" name="password" minlength="6" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Role *</label>
                    <select class="form-select" name="role" required>
                        <option value="">Select Role</option>
                        <option value="administrator" <?php echo ($_POST['role'] ?? '') === 'administrator' ? 'selected' : ''; ?>>Administrator</option>
                        <option value="supervisor" <?php echo ($_POST['role'] ?? '') === 'supervisor' ? 'selected' : ''; ?>>Supervisor</option>
                        <option value="field_officer" <?php echo ($_POST['role'] ?? '') === 'field_officer' ? 'selected' : ''; ?>>Field Officer</option>
                    </select>
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">Status</label>
                <select class="form-select" name="status">
                    <option value="active" <?php echo ($_POST['status'] ?? '') === 'active' ? 'selected' : ''; ?>>Active</option>
                    <option value="inactive" <?php echo ($_POST['status'] ?? '') === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Create User</button>
            <a href="index.php" class="btn btn-outline-secondary">Cancel</a>
        </form>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
