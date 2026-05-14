<?php
require_once 'config.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header("Location: index.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];

$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM tickets WHERE user_id = :uid AND payment_status = 'paid'");
$stmt->execute(['uid' => $user_id]);
$total_bookings = $stmt->fetch()['total'];

$co2_saved = $total_bookings * 2.5;

$stmt = $pdo->prepare("SELECT * FROM tickets WHERE user_id = :uid ORDER BY created_at DESC LIMIT 5");
$stmt->execute(['uid' => $user_id]);
$recent_tickets = $stmt->fetchAll();

$stmt = $pdo->prepare("SELECT dest_station, COUNT(*) as count FROM tickets WHERE user_id = :uid GROUP BY dest_station ORDER BY count DESC LIMIT 5");
$stmt->execute(['uid' => $user_id]);
$chart_data = $stmt->fetchAll();
$labels = json_encode(array_column($chart_data, 'dest_station'));
$counts = json_encode(array_column($chart_data, 'count'));

?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard - MetroTick</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="dashboard-bg">
    <nav class="navbar navbar-expand-lg navbar-dark glass-nav fixed-top">
        <div class="container-fluid px-4">
            <a class="navbar-brand fw-bold gradient-text" href="user_dashboard.php">🚇 MetroTick</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item"><a class="nav-link active" href="user_dashboard.php">Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link" href="book_ticket.php">Book Ticket</a></li>
                    <li class="nav-item"><a class="nav-link" href="#" onclick="showTrainStatus()">Live Status</a></li>
                </ul>
                <div class="d-flex align-items-center">
                    <span class="text-light me-3">Welcome, <?php echo htmlspecialchars($username); ?></span>
                    <a href="logout.php" class="btn btn-outline-danger btn-sm glass-btn">Logout</a>
                </div>
            </div>
        </div>
    </nav>

    <div class="container mt-5 pt-5">
        <div class="row g-4 mb-4">
            <div class="col-md-4">
                <div class="glass-card p-4 text-center border-top border-primary border-4 h-100">
                    <h5 class="text-muted text-uppercase mb-3">Total Rides</h5>
                    <h2 class="display-4 fw-bold gradient-text mb-0"><?php echo $total_bookings; ?></h2>
                </div>
            </div>
            <div class="col-md-4">
                <div class="glass-card p-4 text-center border-top border-success border-4 h-100">
                    <h5 class="text-muted text-uppercase mb-3">🌱 CO2 Saved</h5>
                    <h2 class="display-4 fw-bold text-success mb-0"><?php echo number_format($co2_saved, 1); ?> <span class="fs-4">kg</span></h2>
                    <small class="text-muted">By taking the Metro instead of a car!</small>
                </div>
            </div>
            <div class="col-md-4">
                <div class="glass-card p-4 h-100">
                    <h5 class="text-muted text-uppercase mb-3 text-center">Top Destinations</h5>
                    <canvas id="travelChart" height="120"></canvas>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="glass-card p-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h4 class="mb-0">Recent Tickets</h4>
                        <a href="book_ticket.php" class="btn btn-primary btn-glass">+ New Booking</a>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-dark table-hover table-borderless align-middle">
                            <thead class="text-muted">
                                <tr>
                                    <th>Date</th>
                                    <th>Route</th>
                                    <th>Fare</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(count($recent_tickets) > 0): ?>
                                    <?php foreach($recent_tickets as $ticket): ?>
                                    <tr>
                                        <td><?php echo date('d M Y, h:i A', strtotime($ticket['created_at'])); ?></td>
                                        <td><?php echo htmlspecialchars($ticket['source_station'] . ' → ' . $ticket['dest_station']); ?></td>
                                        <td>₹<?php echo $ticket['fare']; ?></td>
                                        <td>
                                            <?php if($ticket['payment_status'] === 'paid'): ?>
                                                <span class="badge bg-success bg-opacity-75">Paid</span>
                                            <?php else: ?>
                                                <span class="badge bg-warning text-dark">Pending</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if($ticket['payment_status'] === 'paid'): ?>
                                                <a href="view_ticket.php?id=<?php echo $ticket['id']; ?>" class="btn btn-sm btn-outline-info">View QR</a>
                                            <?php else: ?>
                                                <a href="payment.php?ticket_id=<?php echo $ticket['id']; ?>" class="btn btn-sm btn-warning">Pay Now</a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan="5" class="text-center py-4">No tickets found. Time to travel!</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="trainStatusModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content glass-card border-0">
                <div class="modal-header border-0">
                    <h5 class="modal-title gradient-text">📡 Live Train Simulation</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center py-4" id="trainStatusBody">
                    <div class="spinner-border text-primary mb-3" role="status"></div>
                    <p>Fetching satellite data...</p>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const ctx = document.getElementById('travelChart');
        if (ctx) {
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: <?php echo $labels; ?>,
                    datasets: [{
                        label: 'Visits',
                        data: <?php echo $counts; ?>,
                        backgroundColor: 'rgba(94, 53, 177, 0.7)',
                        borderColor: '#5E35B1',
                        borderWidth: 1,
                        borderRadius: 5
                    }]
                },
                options: {
                    responsive: true,
                    scales: { y: { beginAtZero: true, grid: { color: 'rgba(255,255,255,0.1)' } }, x: { grid: { display: false } } },
                    plugins: { legend: { display: false } },
                    color: '#fff'
                }
            });
        }

        function showTrainStatus() {
            const modal = new bootstrap.Modal(document.getElementById('trainStatusModal'));
            modal.show();
            
            setTimeout(() => {
                const crowds = ['Low', 'Medium', 'High'];
                const mins = Math.floor(Math.random() * 10) + 1;
                const crowd = crowds[Math.floor(Math.random() * crowds.length)];
                let badgeClass = 'bg-success';

                if (crowd === 'High') {
                    badgeClass = 'bg-danger';
                } else if (crowd === 'Medium') {
                    badgeClass = 'bg-warning';
                }
                
                document.getElementById('trainStatusBody').innerHTML = `
                    <h3 class="mb-3">Next Train Arriving In:</h3>
                    <h1 class="display-1 fw-bold text-white mb-4">${mins} <small class="fs-4">mins</small></h1>
                    <h5>Expected Crowd Level: <span class="badge ${badgeClass}">${crowd}</span></h5>
                `;
            }, 1500);
        }
    </script>
</body>
</html>
