<?php
require_once 'config.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit;
}

$query = "SELECT t.*, u.username, u.email FROM tickets t JOIN users u ON t.user_id = u.id ORDER BY t.created_at DESC";
$tickets = $pdo->query($query)->fetchAll();

if (isset($_GET['export']) && $_GET['export'] == 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="metro_bookings_' . date('Ymd') . '.csv"');
    $output = fopen('php://output', 'w');
    fputcsv($output, ['Ticket ID', 'Passenger', 'Email', 'Source', 'Destination', 'Fare (INR)', 'Method', 'Status', 'Date']);
    foreach ($tickets as $t) {
        fputcsv($output, [$t['id'], $t['username'], $t['email'], $t['source_station'], $t['dest_station'], $t['fare'], $t['payment_method'], $t['payment_status'], $t['created_at']]);
    }
    fclose($output);
    exit;
}

?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Bookings - MetroTick Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
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
                    <li class="nav-item"><a class="nav-link active" href="manage_bookings.php">All Bookings</a></li>
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
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3 class="mb-0 text-white">All System Bookings</h3>
                <div>
                    <a href="manage_bookings.php?export=csv" class="btn btn-success btn-sm me-2">📤 Export CSV</a>
                    <button onclick="exportPDF()" class="btn btn-danger btn-sm">📄 Export PDF</button>
                </div>
            </div>
            
            <input type="text" id="searchInput" class="form-control glass-input mb-4" placeholder="🔍 Search by ID, Passenger, or Station...">
            
            <div class="table-responsive" id="pdfTable">
                <h4 class="d-none text-dark mb-3" id="pdfTitle">MetroTick - Global Booking Report</h4>
                <table class="table table-dark table-hover table-borderless align-middle" id="bookingTable">
                    <thead class="text-muted border-bottom border-secondary">
                        <tr>
                            <th>Tkt ID</th>
                            <th>Passenger</th>
                            <th>Route</th>
                            <th>Fare</th>
                            <th>Method</th>
                            <th>Status</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($tickets as $t): ?>
                        <tr>
                            <td>#<?php echo $t['id']; ?></td>
                            <td><?php echo htmlspecialchars($t['username']); ?><br><small class="text-muted"><?php echo htmlspecialchars($t['email']); ?></small></td>
                            <td><?php echo htmlspecialchars($t['source_station'] . ' → ' . $t['dest_station']); ?></td>
                            <td class="text-success fw-bold">₹<?php echo $t['fare']; ?></td>
                            <td><?php echo $t['payment_method'] ?? '-'; ?></td>
                            <td>
                                <?php if($t['payment_status'] === 'paid'): ?>
                                    <span class="badge bg-success">Paid</span>
                                <?php else: ?>
                                    <span class="badge bg-warning text-dark">Pending</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo date('d/m/Y H:i', strtotime($t['created_at'])); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        function exportPDF() {
            const element = document.getElementById('pdfTable');
            const title = document.getElementById('pdfTitle');
            const table = document.getElementById('bookingTable');
            
            title.classList.remove('d-none');
            table.classList.remove('table-dark');
            table.classList.add('table-light');
            
            const opt = {
                margin:       0.5,
                filename:     'MetroTick_Bookings_Report.pdf',
                image:        { type: 'jpeg', quality: 0.98 },
                html2canvas:  { scale: 2 },
                jsPDF:        { unit: 'in', format: 'a4', orientation: 'landscape' }
            };

            html2pdf().set(opt).from(element).save().then(() => {
                title.classList.add('d-none');
                table.classList.remove('table-light');
                table.classList.add('table-dark');
            });
        }

        document.getElementById('searchInput').addEventListener('keyup', function() {
            let filter = this.value.toLowerCase();
            let rows = document.querySelectorAll('#bookingTable tbody tr');
            
            rows.forEach(row => {
                let text = row.textContent.toLowerCase();
                row.style.display = text.includes(filter) ? '' : 'none';
            });
        });
    </script>
</body>
</html>
