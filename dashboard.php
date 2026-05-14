<?php
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];

$stmt = $pdo->prepare("SELECT COUNT(*) as total_trips FROM travel_logs WHERE user_id = :uid");
$stmt->execute(['uid' => $user_id]);
$total_trips = $stmt->fetch()['total_trips'];

$stmt = $pdo->prepare("SELECT SUM(cost) as total_spent FROM travel_logs WHERE user_id = :uid");
$stmt->execute(['uid' => $user_id]);
$total_spent = $stmt->fetch()['total_spent'] ?? 0;

$co2_saved = $total_trips * 2.5;

$stmt = $pdo->prepare("SELECT source_station, dest_station, COUNT(*) as route_count FROM travel_logs WHERE user_id = :uid GROUP BY source_station, dest_station ORDER BY route_count DESC LIMIT 1");
$stmt->execute(['uid' => $user_id]);
$top_route_data = $stmt->fetch();
$top_route = $top_route_data ? $top_route_data['source_station'] . " → " . $top_route_data['dest_station'] : "No data yet";

$stmt = $pdo->prepare("SELECT HOUR(travel_time) as peak_hour, COUNT(*) as count FROM travel_logs WHERE user_id = :uid GROUP BY HOUR(travel_time) ORDER BY count DESC LIMIT 1");
$stmt->execute(['uid' => $user_id]);
$peak_hour_data = $stmt->fetch();
$peak_hour = $peak_hour_data ? $peak_hour_data['peak_hour'] : null;

$smart_suggestion = "Log more trips to receive smart travel suggestions!";
if ($peak_hour !== null) {
    if ($peak_hour >= 8 && $peak_hour <= 10) {
        $smart_suggestion = "You frequently travel during morning peak hours (" . $peak_hour . ":00). Consider traveling before 8 AM or after 10:30 AM to avoid the Namma Metro rush!";
    } elseif ($peak_hour >= 17 && $peak_hour <= 19) {
        $smart_suggestion = "You frequently travel during evening peak hours (" . $peak_hour . ":00). Consider shifting your commute slightly to avoid heavy crowds.";
    } else {
        $smart_suggestion = "Your typical travel time (" . $peak_hour . ":00) is generally outside extreme peak hours. Great job avoiding the rush!";
    }
}

$stmt = $pdo->prepare("SELECT DAYNAME(travel_date) as day_name, COUNT(*) as count FROM travel_logs WHERE user_id = :uid GROUP BY day_name ORDER BY FIELD(day_name, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday')");
$stmt->execute(['uid' => $user_id]);
$days_data = $stmt->fetchAll();
$chart_days_labels = json_encode(array_column($days_data, 'day_name'));
$chart_days_counts = json_encode(array_column($days_data, 'count'));

$stmt = $pdo->prepare("SELECT CONCAT(source_station, '-', dest_station) as route, COUNT(*) as count FROM travel_logs WHERE user_id = :uid GROUP BY route ORDER BY count DESC LIMIT 5");
$stmt->execute(['uid' => $user_id]);
$routes_data = $stmt->fetchAll();
$chart_routes_labels = json_encode(array_column($routes_data, 'route'));
$chart_routes_counts = json_encode(array_column($routes_data, 'count'));

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Namma Metro Analyzer</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <nav class="navbar">
        <div class="nav-brand">🚇 Namma Metro Analyzer</div>
        <div class="nav-links">
            <a href="dashboard.php" class="active">Dashboard</a>
            <a href="travel_logs.php">My Logs</a>
            <a href="logout.php" class="btn-logout">Logout (<?php echo htmlspecialchars($username); ?>)</a>
        </div>
    </nav>

    <div class="container">
        <h1 class="page-title">Welcome back, <?php echo htmlspecialchars($username); ?>!</h1>

        <div class="suggestion-panel">
            <div class="suggestion-icon">💡</div>
            <div class="suggestion-content">
                <strong>Smart Suggestion:</strong>
                <p><?php echo $smart_suggestion; ?></p>
            </div>
        </div>

        <div class="metrics-grid">
            <div class="metric-card">
                <h3>Total Trips</h3>
                <div class="value"><?php echo $total_trips; ?></div>
            </div>
            <div class="metric-card">
                <h3>Total Spent (₹)</h3>
                <div class="value">₹<?php echo number_format($total_spent, 2); ?></div>
            </div>
            <div class="metric-card">
                <h3>Most Frequent Route</h3>
                <div class="value route-value"><?php echo htmlspecialchars($top_route); ?></div>
            </div>
            <div class="metric-card eco-card">
                <h3>CO₂ Emissions Saved</h3>
                <div class="value eco-value">🌿 <?php echo number_format($co2_saved, 1); ?> kg</div>
            </div>
        </div>

        <div class="charts-grid">
            <div class="chart-container">
                <h3>Trips by Day</h3>
                <canvas id="barChart"></canvas>
            </div>
            <div class="chart-container">
                <h3>Top Routes Used</h3>
                <canvas id="pieChart"></canvas>
            </div>
        </div>
    </div>

    <input type="hidden" id="daysLabels" value='<?php echo $chart_days_labels; ?>'>
    <input type="hidden" id="daysCounts" value='<?php echo $chart_days_counts; ?>'>
    <input type="hidden" id="routesLabels" value='<?php echo $chart_routes_labels; ?>'>
    <input type="hidden" id="routesCounts" value='<?php echo $chart_routes_counts; ?>'>

    <script src="assets/js/charts.js"></script>
</body>
</html>
