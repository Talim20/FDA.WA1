<?php
require_once '../includes/auth.php';
require_once '../includes/functions.php';
requireRole(['administrator']);

$pageTitle = 'Edit User';
$error = '';
$userId = $_GET['id'] ?? 0;

$sql = "SELECT id, full_name, username, email, role, status FROM users WHERE id = ?";
$user = fetchOne($sql, [$userId]);

if (!$user) {
    $_SESSION['error'] = 'User not found.';
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName = trim($_POST['full_name'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? '';
    $status = $_POST['status'] ?? 'active';
    
    if (empty($fullName) || empty($username) || empty($email) || empty($role)) {
        $error = 'Please fill in all required fields.';
    } elseif (!empty($password) && strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } else {
        try {
            if (!empty($password)) {
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $sql = "UPDATE users SET full_name = ?, username = ?, email = ?, password = ?, role = ?, status = ? WHERE id = ?";
                executeQuery($sql, [$fullName, $username, $email, $hashedPassword, $role, $status, $userId]);
            } else {
                $sql = "UPDATE users SET full_name = ?, username = ?, email = ?, role = ?, status = ? WHERE id = ?";
                executeQuery($sql, [$fullName, $username, $email, $role, $status, $userId]);
            }
            logAudit(getCurrentUserId(), 'USER_UPDATED', 'user', $userId, "Updated: $username");
            $_SESSION['success'] = 'User successfully updated.';
            header('Location: index.php');
            exit;
        } catch (Exception $e) {
            $error = 'Error updating user.';
        }
    }
}

include '../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div><h2 class="mb-0">Edit User</h2></div>
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
                    <input type="text" class="form-control" name="full_name" value="<?php echo htmlspecialchars($_POST['full_name'] ?? $user['full_name']); ?>" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Username *</label>
                    <input type="text" class="form-control" name="username" value="<?php echo htmlspecialchars($_POST['username'] ?? $user['username']); ?>" required>
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">Email *</label>
                <input type="email" class="form-control" name="email" value="<?php echo htmlspecialchars($_POST['email'] ?? $user['email']); ?>" required>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">New Password (leave blank to keep current)</label>
                    <input type="password" class="form-control" name="password" minlength="6">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Role *</label>
                    <select class="form-select" name="role" required>
                        <option value="">Select Role</option>
                        <option value="administrator" <?php echo (($_POST['role'] ?? $user['role'])) === 'administrator' ? 'selected' : ''; ?>>Administrator</option>
                        <option value="supervisor" <?php echo (($_POST['role'] ?? $user['role'])) === 'supervisor' ? 'selected' : ''; ?>>Supervisor</option>
                        <option value="field_officer" <?php echo (($_POST['role'] ?? $user['role'])) === 'field_officer' ? 'selected' : ''; ?>>Field Officer</option>
                    </select>
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">Status</label>
                <select class="form-select" name="status">
                    <option value="active" <?php echo (($_POST['status'] ?? $user['status'])) === 'active' ? 'selected' : ''; ?>>Active</option>
                    <option value="inactive" <?php echo (($_POST['status'] ?? $user['status'])) === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Update User</button>
            <a href="index.php" class="btn btn-outline-secondary">Cancel</a>
        </form>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
