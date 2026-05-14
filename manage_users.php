<?php
require_once 'config.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit;
}

if (isset($_GET['delete'])) {
    $del_id = $_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = :id AND role = 'user'");
    if($stmt->execute(['id' => $del_id])) {
        $msg = "User deleted successfully.";
    }
}

$users = $pdo->query("SELECT * FROM users WHERE role = 'user' ORDER BY created_at DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - MetroTick Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="dashboard-bg admin-bg">
    <nav class="navbar navbar-expand-lg navbar-dark glass-nav fixed-top">
        <div class="container-fluid px-4">
            <a class="navbar-brand fw-bold gradient-text" href="admin_dashboard.php">🛠️ Metro Admin</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item"><a class="nav-link" href="admin_dashboard.php">Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link active" href="manage_users.php">Manage Users</a></li>
                    <li class="nav-item"><a class="nav-link" href="manage_bookings.php">All Bookings</a></li>
                    <li class="nav-item"><a class="nav-link" href="admin_settings.php">Fare Settings</a></li>
                </ul>
                <div class="d-flex ms-auto">
                    <a href="logout.php" class="btn btn-outline-danger btn-sm glass-btn">Logout</a>
                </div>
            </div>
        </div>
    </nav>

    <div class="container mt-5 pt-5">
        <div class="glass-card p-5 mt-4">
            <h3 class="mb-4 text-white">Registered Users</h3>
            
            <?php if(isset($msg)) echo "<div class='alert alert-success'>$msg</div>"; ?>
            
            <div class="table-responsive">
                <table class="table table-dark table-hover table-borderless align-middle">
                    <thead class="text-muted">
                        <tr>
                            <th>ID</th>
                            <th>Username</th>
                            <th>Email Address</th>
                            <th>Joined On</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($users as $u): ?>
                        <tr>
                            <td>#<?php echo $u['id']; ?></td>
                            <td class="fw-bold"><?php echo htmlspecialchars($u['username']); ?></td>
                            <td><?php echo htmlspecialchars($u['email']); ?></td>
                            <td><?php echo date('d M Y', strtotime($u['created_at'])); ?></td>
                            <td>
                                <a href="manage_users.php?delete=<?php echo $u['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this user? All their tickets will also be deleted.')">Remove</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
