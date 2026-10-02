<?php
require_once '../includes/auth.php';
require_once '../includes/functions.php';
requireRole(['administrator']);

$pageTitle = 'User Management';
$users = getUsers();

include '../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div><h2 class="mb-0">User Management</h2></div>
    <a href="add.php" class="btn btn-primary"><i class="bi bi-person-plus me-2"></i>Add User</a>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead><tr><th>Full Name</th><th>Username</th><th>Email</th><th>Role</th><th>Status</th><th>Actions</th></tr></thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($user['full_name']); ?></strong></td>
                        <td><?php echo htmlspecialchars($user['username']); ?></td>
                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                        <td><span class="badge bg-<?php echo $user['role'] === 'administrator' ? 'danger' : ($user['role'] === 'supervisor' ? 'warning' : 'info'); ?>"><?php echo ucfirst($user['role']); ?></span></td>
                        <td><span class="badge bg-<?php echo $user['status'] === 'active' ? 'success' : 'secondary'; ?>"><?php echo ucfirst($user['status']); ?></span></td>
                        <td><a href="edit.php?id=<?php echo $user['id']; ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
