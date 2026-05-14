<?php
require_once 'config.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit;
}

$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $base = $_POST['base_fare'];
    $per = $_POST['per_stop_fare'];
    $max = $_POST['max_fare'];
    
    $update = $pdo->prepare("UPDATE settings SET setting_value = :val WHERE setting_key = :key");
    $update->execute(['val' => $base, 'key' => 'base_fare']);
    $update->execute(['val' => $per, 'key' => 'per_stop_fare']);
    $update->execute(['val' => $max, 'key' => 'max_fare']);
    
    $msg = "Fare settings updated successfully!";
}

$settings = $pdo->query("SELECT setting_key, setting_value FROM settings")->fetchAll(PDO::FETCH_KEY_PAIR);
$base_fare = $settings['base_fare'] ?? 10;
$per_stop_fare = $settings['per_stop_fare'] ?? 2.5;
$max_fare = $settings['max_fare'] ?? 60;

?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fare Settings - MetroTick Admin</title>
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
                    <li class="nav-item"><a class="nav-link" href="manage_users.php">Manage Users</a></li>
                    <li class="nav-item"><a class="nav-link" href="manage_bookings.php">All Bookings</a></li>
                    <li class="nav-item"><a class="nav-link active" href="admin_settings.php">Fare Settings</a></li>
                </ul>
                <div class="d-flex ms-auto">
                    <a href="logout.php" class="btn btn-outline-danger btn-sm glass-btn">Logout</a>
                </div>
            </div>
        </div>
    </nav>

    <div class="container mt-5 pt-5 d-flex justify-content-center">
        <div class="glass-card p-5 mt-4" style="max-width: 500px; width: 100%;">
            <h3 class="mb-4 text-white">⚙️ Dynamic Fare Settings</h3>
            
            <?php if(!empty($msg)): ?>
                <div class="alert alert-success py-2"><?php echo $msg; ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="mb-3">
                    <label class="form-label text-muted">Base Fare (₹)</label>
                    <input type="number" step="0.5" class="form-control glass-input" name="base_fare" value="<?php echo htmlspecialchars($base_fare); ?>" required>
                    <small class="text-secondary">Fixed fee applied to every ticket.</small>
                </div>
                <div class="mb-3">
                    <label class="form-label text-muted">Fare Per Stop (₹)</label>
                    <input type="number" step="0.5" class="form-control glass-input" name="per_stop_fare" value="<?php echo htmlspecialchars($per_stop_fare); ?>" required>
                    <small class="text-secondary">Additional cost per station travelled.</small>
                </div>
                <div class="mb-4">
                    <label class="form-label text-muted">Maximum Fare Cap (₹)</label>
                    <input type="number" step="0.5" class="form-control glass-input" name="max_fare" value="<?php echo htmlspecialchars($max_fare); ?>" required>
                    <small class="text-secondary">The highest possible ticket price.</small>
                </div>
                <button type="submit" class="btn btn-primary w-100 btn-glass fw-bold">Update Fares</button>
            </form>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
