<?php
require_once 'config.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit;
}

$username = $_SESSION['username'];

$total_users = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'user'")->fetchColumn();
$total_revenue = $pdo->query("SELECT SUM(fare) FROM tickets WHERE payment_status = 'paid'")->fetchColumn() ?? 0;
$total_bookings = $pdo->query("SELECT COUNT(*) FROM tickets WHERE payment_status = 'paid'")->fetchColumn();

$revenue_data = $pdo->query("SELECT DATE(created_at) as d, SUM(fare) as r FROM tickets WHERE payment_status = 'paid' GROUP BY d ORDER BY d DESC LIMIT 7")->fetchAll();
$rev_labels = json_encode(array_reverse(array_column($revenue_data, 'd')));
$rev_amounts = json_encode(array_reverse(array_column($revenue_data, 'r')));
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - MetroTick</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
                    <li class="nav-item"><a class="nav-link active" href="admin_dashboard.php">Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link" href="manage_users.php">Manage Users</a></li>
                    <li class="nav-item"><a class="nav-link" href="manage_bookings.php">All Bookings</a></li>
                    <li class="nav-item"><a class="nav-link" href="admin_settings.php">Fare Settings</a></li>
                </ul>
                <div class="d-flex align-items-center">
                    <span class="text-light me-3 text-uppercase badge bg-danger">ADMIN: <?php echo htmlspecialchars($username); ?></span>
                    <a href="logout.php" class="btn btn-outline-danger btn-sm glass-btn">Logout</a>
                </div>
            </div>
        </div>
    </nav>

    <div class="container mt-5 pt-5">
        <div class="row g-4 mb-5">
            <div class="col-md-4">
                <div class="glass-card p-4 text-center border-top border-info border-4">
                    <h5 class="text-muted text-uppercase mb-3">Total Users</h5>
                    <h2 class="display-4 fw-bold text-info mb-0"><?php echo $total_users; ?></h2>
                </div>
            </div>
            <div class="col-md-4">
                <div class="glass-card p-4 text-center border-top border-success border-4">
                    <h5 class="text-muted text-uppercase mb-3">Total Revenue</h5>
                    <h2 class="display-4 fw-bold text-success mb-0">₹<?php echo number_format($total_revenue, 2); ?></h2>
                </div>
            </div>
            <div class="col-md-4">
                <div class="glass-card p-4 text-center border-top border-warning border-4">
                    <h5 class="text-muted text-uppercase mb-3">Total Tickets Sold</h5>
                    <h2 class="display-4 fw-bold text-warning mb-0"><?php echo $total_bookings; ?></h2>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="glass-card p-4">
                    <h5 class="text-muted text-uppercase mb-4">Revenue Trends (Last 7 Days)</h5>
                    <canvas id="revenueChart" height="80"></canvas>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const ctx = document.getElementById('revenueChart').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: <?php echo $rev_labels; ?>,
                datasets: [{
                    label: 'Revenue (₹)',
                    data: <?php echo $rev_amounts; ?>,
                    borderColor: '#2ECC71',
                    backgroundColor: 'rgba(46, 204, 113, 0.2)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { display: false } },
                scales: { 
                    y: { beginAtZero: true, grid: { color: 'rgba(255,255,255,0.1)' } },
                    x: { grid: { color: 'rgba(255,255,255,0.05)' } }
                },
                color: '#fff'
            }
        });
    </script>
</body>
</html>
