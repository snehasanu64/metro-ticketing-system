<?php
require_once 'config.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header("Location: index.php");
    exit;
}

if (!isset($_GET['id'])) {
    header("Location: user_dashboard.php");
    exit;
}

$ticket_id = $_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM tickets WHERE id = :tid AND user_id = :uid AND payment_status = 'paid'");
$stmt->execute(['tid' => $ticket_id, 'uid' => $_SESSION['user_id']]);
$ticket = $stmt->fetch();

if (!$ticket) {
    die("Invalid ticket or payment not completed.");
}

$qr_url = "https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=" . urlencode($ticket['qr_data']);

?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E-Ticket - MetroTick</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
</head>
<body class="dashboard-bg">
    <nav class="navbar navbar-expand-lg navbar-dark glass-nav fixed-top">
        <div class="container-fluid px-4">
            <a class="navbar-brand fw-bold gradient-text" href="user_dashboard.php">🚇 MetroTick</a>
            <div class="d-flex align-items-center ms-auto">
                <a href="user_dashboard.php" class="btn btn-outline-light btn-sm glass-btn">Back to Dashboard</a>
            </div>
        </div>
    </nav>

    <div class="container mt-5 pt-5 d-flex justify-content-center">
        <div class="ticket-card mt-4" id="printableTicket" style="width: 100%; max-width: 400px; background: #fff; border-radius: 20px; overflow: hidden; box-shadow: 0 20px 40px rgba(0,0,0,0.3);">
            <div class="bg-primary text-white text-center py-3" style="background: linear-gradient(135deg, #5E35B1, #00897B) !important;">
                <h4 class="mb-0">Namma Metro E-Ticket</h4>
            </div>
            <div class="p-4 text-dark text-center">
                <img src="<?php echo $qr_url; ?>" alt="QR Code" class="img-fluid mb-4 rounded border p-2">
                
                <h6 class="text-muted text-uppercase mb-1">Ticket ID</h6>
                <p class="fw-bold fs-5"><?php echo htmlspecialchars($ticket['qr_data']); ?></p>
                
                <div class="d-flex justify-content-between text-start mt-4 mb-3 border-top pt-3">
                    <div>
                        <small class="text-muted d-block">Source</small>
                        <strong><?php echo htmlspecialchars($ticket['source_station']); ?></strong>
                    </div>
                </div>
                <div class="d-flex justify-content-between text-start mb-3 border-top pt-3">
                    <div>
                        <small class="text-muted d-block">Destination</small>
                        <strong><?php echo htmlspecialchars($ticket['dest_station']); ?></strong>
                    </div>
                </div>
                
                <div class="row border-top pt-3 text-start">
                    <div class="col-6">
                        <small class="text-muted d-block">Fare Paid</small>
                        <strong>₹<?php echo $ticket['fare']; ?></strong>
                    </div>
                    <div class="col-6">
                        <small class="text-muted d-block">Date & Time</small>
                        <strong><?php echo date('d M Y', strtotime($ticket['created_at'])); ?></strong>
                    </div>
                </div>
            </div>
            <div class="bg-light p-3 text-center border-top" data-html2canvas-ignore="true">
                <button class="btn btn-primary w-100" onclick="downloadPDF()">Download as PDF</button>
            </div>
        </div>
    </div>
    
    <script>
        function downloadPDF() {
            const element = document.getElementById('printableTicket');
            const opt = {
                margin:       0.2,
                filename:     'Metro_Ticket_<?php echo htmlspecialchars($ticket['qr_data']); ?>.pdf',
                image:        { type: 'jpeg', quality: 0.98 },
                html2canvas:  { scale: 2, useCORS: true },
                jsPDF:        { unit: 'in', format: 'letter', orientation: 'portrait' }
            };
            html2pdf().set(opt).from(element).save();
        }
    </script>
</body>
</html>
