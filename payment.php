<?php
require_once 'config.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header("Location: index.php");
    exit;
}

if (!isset($_GET['ticket_id'])) {
    header("Location: user_dashboard.php");
    exit;
}

$ticket_id = $_GET['ticket_id'];
$stmt = $pdo->prepare("SELECT * FROM tickets WHERE id = :tid AND user_id = :uid AND payment_status = 'pending'");
$stmt->execute(['tid' => $ticket_id, 'uid' => $_SESSION['user_id']]);
$ticket = $stmt->fetch();

if (!$ticket) {
    die("Invalid ticket or already paid.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'pay') {
    $method = $_POST['method'];

    $qr_data = "TKT-" . $ticket_id . "-" . strtoupper(substr(md5(uniqid()), 0, 8));
    
    $update = $pdo->prepare("UPDATE tickets SET payment_status = 'paid', payment_method = :method, qr_data = :qr WHERE id = :tid");
    $update->execute(['method' => $method, 'qr' => $qr_data, 'tid' => $ticket_id]);
    
    echo json_encode(['status' => 'success', 'ticket_id' => $ticket_id]);
    exit;
}
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Secure Payment - MetroTick</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
</head>
<body class="dashboard-bg">
    <div class="container d-flex justify-content-center align-items-center vh-100">
        <div class="glass-card p-5" style="width: 100%; max-width: 500px;" id="paymentCard">
            <h3 class="text-center mb-4">Complete Payment</h3>
            
            <div class="bg-dark bg-opacity-50 p-3 rounded mb-4 text-center border border-secondary">
                <p class="mb-1 text-muted">Journey Route</p>
                <h6 class="text-white"><?php echo htmlspecialchars($ticket['source_station']); ?> <br><span class="text-primary">↓</span><br> <?php echo htmlspecialchars($ticket['dest_station']); ?></h6>
                <hr class="border-secondary my-2">
                <h2 class="text-success mb-0">₹<?php echo $ticket['fare']; ?></h2>
            </div>

            <div class="mb-4">
                <label class="form-label text-muted">Select Payment Method</label>
                <select class="form-select glass-input" id="paymentMethod">
                    <option value="UPI">UPI (GPay, PhonePe, Paytm)</option>
                    <option value="Debit Card">Debit / Credit Card</option>
                    <option value="Net Banking">Net Banking</option>
                </select>
            </div>

            <button class="btn btn-success w-100 btn-lg btn-glass" id="payBtn" onclick="processPayment()">
                Pay Securely
            </button>
            <a href="user_dashboard.php" class="btn btn-link text-muted w-100 mt-2 text-decoration-none">Cancel</a>
        </div>

        <div class="text-center d-none" id="processingScreen">
            <div class="spinner-border text-primary" style="width: 4rem; height: 4rem;" role="status"></div>
            <h4 class="mt-4 text-white">Processing Payment...</h4>
            <p class="text-muted">Please do not refresh the page</p>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
    <script>
        function processPayment() {
            const method = document.getElementById('paymentMethod').value;
            const card = document.getElementById('paymentCard');
            const processing = document.getElementById('processingScreen');

            card.classList.add('d-none');
            processing.classList.remove('d-none');

            setTimeout(() => {
                fetch('payment.php?ticket_id=<?php echo $ticket_id; ?>', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: 'action=pay&method=' + encodeURIComponent(method)
                })
                .then(res => res.json())
                .then(data => {
                    if(data.status === 'success') {
                        Toastify({
                            text: "Payment Successful! Generating Ticket...",
                            duration: 3000,
                            gravity: "top",
                            position: "center",
                            style: { background: "linear-gradient(to right, #00b09b, #96c93d)" }
                        }).showToast();
                        
                        setTimeout(() => {
                            window.location.href = 'view_ticket.php?id=' + data.ticket_id;
                        }, 1500);
                    }
                });
            }, 2500);
        }
    </script>
</body>
</html>
