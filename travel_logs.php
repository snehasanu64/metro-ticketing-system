<?php
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];

function renderStationOptions(array $stations): string {
    $options = '';

    foreach ($stations as $station) {
        $safeStation = htmlspecialchars($station);
        $options .= '<option value="' . $safeStation . '">' . $safeStation . '</option>';
    }

    return $options;
}

$stations = [
    "Whitefield (Kadugodi)", "Hopefarm Channasandra", "Kadugodi Tree Park", "Pattandur Agrahara", 
    "Sri Sathya Sai Hospital", "Nallurhalli", "Kundalahalli", "Seetharampalya", "Hoodi", 
    "Garudacharapalya", "Singayyanapalya", "Krishnarajapura (K.R. Pura)", "Benniganahalli", 
    "Baiyappanahalli", "Swami Vivekananda Road", "Indiranagar", "Halasuru", "Trinity", 
    "MG Road", "Cubbon Park", "Dr. B.R. Ambedkar Station, Vidhana Soudha", 
    "Sir M. Visveshwaraya Station, Central College", "Nadaprabhu Kempegowda Station, Majestic", 
    "KSR Bengaluru City Railway Station", "Magadi Road", 
    "Sri Balagangadharanatha Swamiji Station, Hosahalli", "Vijayanagar", "Attiguppe", 
    "Deepanjali Nagar", "Mysuru Road", "Pantharapalya - Nayandahalli", "Rajarajeshwari Nagar", 
    "Jnanabharathi", "Pattanagere", "Kengeri Bus Terminal", "Kengeri", "Challaghatta",
    "Madavara", "Chikkabidarakallu", "Manjunathanagara", "Nagasandra", "Dasarahalli", "Jalahalli", 
    "Peenya Industry", "Peenya", "Goraguntepalya", "Yeshwanthpur", "Sandal Soap Factory", 
    "Mahalakshmi", "Rajajinagar", "Mahakavi Kuvempu Road", "Srirampura", 
    "Mantri Square Sampige Road", "Chickpete", "Krishna Rajendra Market (K.R. Market)", 
    "National College", "Lalbagh", "South End Circle", "Jayanagar", 
    "Rashtriya Vidyalaya Road (R.V. Road)", "Banashankari", "Jaya Prakash Nagar (J.P. Nagar)", 
    "Yelachenahalli", "Konanakunte Cross", "Doddakallasandra", "Vajarahalli", "Thalaghattapura", 
    "Silk Institute"
];
sort($stations);

$stmt = $pdo->prepare("SELECT * FROM travel_logs WHERE user_id = :uid ORDER BY travel_date DESC, travel_time DESC");
$stmt->execute(['uid' => $user_id]);
$logs = $stmt->fetchAll();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Logs - Namma Metro Analyzer</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <nav class="navbar">
        <div class="nav-brand">🚇 Namma Metro Analyzer</div>
        <div class="nav-links">
            <a href="dashboard.php">Dashboard</a>
            <a href="travel_logs.php" class="active">My Logs</a>
            <a href="logout.php" class="btn-logout">Logout (<?php echo htmlspecialchars($username); ?>)</a>
        </div>
    </nav>

    <div class="container">
        <div class="logs-header">
            <h1 class="page-title">Travel History</h1>
            <div style="display: flex; gap: 10px;">
                <a href="export_csv.php" class="btn btn-secondary">📥 Export to CSV</a>
                <button class="btn btn-primary" id="openModalBtn">+ Add New Trip</button>
            </div>
        </div>

        <?php
        if (isset($_SESSION['msg'])) {
            echo "<div class='alert alert-success'>" . $_SESSION['msg'] . "</div>";
            unset($_SESSION['msg']);
        }
        if (isset($_SESSION['error'])) {
            echo "<div class='alert alert-danger'>" . $_SESSION['error'] . "</div>";
            unset($_SESSION['error']);
        }
        ?>

        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Source Station</th>
                        <th>Destination Station</th>
                        <th>Cost (₹)</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($logs) > 0): ?>
                        <?php foreach ($logs as $log): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($log['travel_date']); ?></td>
                            <td><?php echo htmlspecialchars($log['travel_time']); ?></td>
                            <td><?php echo htmlspecialchars($log['source_station']); ?></td>
                            <td><?php echo htmlspecialchars($log['dest_station']); ?></td>
                            <td>₹<?php echo number_format($log['cost'], 2); ?></td>
                            <td>
                                <form method="POST" action="process_log.php" onsubmit="return confirm('Are you sure you want to delete this log?');" style="display:inline;">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="log_id" value="<?php echo $log['log_id']; ?>">
                                    <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 2rem;">No travel logs found. Add a trip to get started!</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div id="addLogModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Log a New Trip</h2>
            <form method="POST" action="process_log.php">
                <input type="hidden" name="action" value="add">
                
                <div class="form-group">
                    <label>Source Station</label>
                    <select name="source_station" required>
                        <option value="">Select Station...</option>
                        <?php echo renderStationOptions($stations); ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Destination Station</label>
                    <select name="dest_station" required>
                        <option value="">Select Station...</option>
                        <?php echo renderStationOptions($stations); ?>
                    </select>
                </div>

                <div class="form-row">
                    <div class="form-group half">
                        <label>Date</label>
                        <input type="date" name="travel_date" required max="<?php echo date('Y-m-d'); ?>">
                    </div>
                    <div class="form-group half">
                        <label>Time</label>
                        <input type="time" name="travel_time" required>
                    </div>
                </div>

                <div class="form-group">
                    <label>Ticket Cost (₹)</label>
                    <input type="number" step="0.01" min="0" name="cost" required placeholder="e.g. 35.00">
                </div>

                <button type="submit" class="btn btn-primary btn-block">Save Trip</button>
            </form>
        </div>
    </div>

    <script>
        const modal = document.getElementById("addLogModal");
        const btn = document.getElementById("openModalBtn");
        const span = document.getElementsByClassName("close")[0];

        btn.onclick = function() {
            modal.style.display = "block";
        };
        span.onclick = function() {
            modal.style.display = "none";
        };
        window.onclick = function(event) {
            if (event.target == modal) {
                modal.style.display = "none";
            }
        };
    </script>
</body>
</html>
