<?php
require_once 'config.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header("Location: index.php");
    exit;
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

$settings = $pdo->query("SELECT setting_key, setting_value FROM settings")->fetchAll(PDO::FETCH_KEY_PAIR);
$base_fare = (float)($settings['base_fare'] ?? 10);
$per_stop = (float)($settings['per_stop_fare'] ?? 2.5);
$max_fare = (float)($settings['max_fare'] ?? 60);

function renderStationOptions(array $stations): string {
    $options = '';

    foreach ($stations as $station) {
        $safeStation = htmlspecialchars($station);
        $options .= '<option value="' . $safeStation . '">' . $safeStation . '</option>';
    }

    return $options;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $src = $_POST['source_station'];
    $dest = $_POST['dest_station'];
    
    if ($src === $dest) {
        $error = "Source and Destination cannot be identical.";
    } else {
        $src_idx = array_search($src, $stations);
        $dest_idx = array_search($dest, $stations);
        $distance = abs($src_idx - $dest_idx);
        
        $fare = ceil($base_fare + ($distance * $per_stop));
        if ($fare > $max_fare) {
            $fare = $max_fare;
        }
        
        $stmt = $pdo->prepare("INSERT INTO tickets (user_id, source_station, dest_station, fare, payment_status) VALUES (:uid, :src, :dest, :fare, 'pending')");
        if ($stmt->execute(['uid' => $_SESSION['user_id'], 'src' => $src, 'dest' => $dest, 'fare' => $fare])) {
            $ticket_id = $pdo->lastInsertId();
            header("Location: payment.php?ticket_id=" . $ticket_id);
            exit;
        } else {
            $error = "Failed to initiate booking.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Ticket - MetroTick</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .select2-container--default .select2-selection--single {
            background-color: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            height: 40px;
            color: #fff;
        }
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            color: #fff;
            line-height: 40px;
            padding-left: 12px;
        }
        .select2-dropdown {
            background-color: #2b2b2b;
            border: 1px solid rgba(255,255,255,0.1);
            color: #fff;
        }
        .select2-container--default .select2-results__option--highlighted.select2-results__option--selectable {
            background-color: #5E35B1;
            color: white;
        }
        .select2-search--dropdown .select2-search__field {
            background-color: #1a1a1a;
            color: white;
            border: 1px solid rgba(255,255,255,0.2);
        }
    </style>
</head>
<body class="dashboard-bg">
    <nav class="navbar navbar-expand-lg navbar-dark glass-nav fixed-top">
        <div class="container-fluid px-4">
            <a class="navbar-brand fw-bold gradient-text" href="user_dashboard.php">🚇 MetroTick</a>
            <div class="d-flex align-items-center ms-auto">
                <a href="user_dashboard.php" class="btn btn-outline-light btn-sm glass-btn me-2">Back to Dashboard</a>
            </div>
        </div>
    </nav>

    <div class="container mt-5 pt-5 d-flex justify-content-center">
        <div class="glass-card p-5 mt-4" style="width: 100%; max-width: 600px;">
            <h2 class="text-center mb-4 gradient-text">Book Metro Ticket</h2>
            
            <?php if(!empty($error)): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <form method="POST" id="bookingForm">
                <div class="mb-4">
                    <label class="form-label text-light">From Station</label>
                    <select name="source_station" id="src" class="form-select glass-input" required>
                        <option value="">Select Origin...</option>
                        <?php echo renderStationOptions($stations); ?>
                    </select>
                </div>
                
                <div class="text-center text-muted mb-4 fs-4">
                    <span style="transform: rotate(90deg); display: inline-block;">⇄</span>
                </div>

                <div class="mb-4">
                    <label class="form-label text-light">To Station</label>
                    <select name="dest_station" id="dest" class="form-select glass-input" required>
                        <option value="">Select Destination...</option>
                        <?php echo renderStationOptions($stations); ?>
                    </select>
                </div>

                <div class="glass-card p-3 mb-4 text-center d-none" id="farePreviewBox" style="background: rgba(46, 204, 113, 0.1); border: 1px solid rgba(46, 204, 113, 0.3);">
                    <h6 class="text-success mb-1">Estimated Fare</h6>
                    <h2 class="mb-0 text-white" id="farePreviewAmount">₹--</h2>
                </div>

                <button type="submit" class="btn btn-primary w-100 btn-glass btn-lg mt-2">Proceed to Payment</button>
            </form>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
        $(document).ready(function() {
            $('#src').select2({ placeholder: "Type to search Origin..." });
            $('#dest').select2({ placeholder: "Type to search Destination..." });

            $('#src, #dest').on('change', calculateFare);
        });

        const stations = <?php echo json_encode($stations); ?>;
        const base_fare = <?php echo $base_fare; ?>;
        const per_stop = <?php echo $per_stop; ?>;
        const max_fare = <?php echo $max_fare; ?>;
        
        const box = document.getElementById('farePreviewBox');
        const amount = document.getElementById('farePreviewAmount');

        function calculateFare() {
            const srcVal = $('#src').val();
            const destVal = $('#dest').val();
            
            if (srcVal && destVal && srcVal !== destVal) {
                const sIdx = stations.indexOf(srcVal);
                const dIdx = stations.indexOf(destVal);
                const dist = Math.abs(sIdx - dIdx);
                let fare = Math.ceil(base_fare + (dist * per_stop));

                if (fare > max_fare) {
                    fare = max_fare;
                }
                
                amount.innerText = '₹' + fare + '.00';
                box.classList.remove('d-none');
            } else {
                box.classList.add('d-none');
            }
        }
    </script>
</body>
</html>
