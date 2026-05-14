<?php
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT travel_date, travel_time, source_station, dest_station, cost FROM travel_logs WHERE user_id = :uid ORDER BY travel_date DESC, travel_time DESC");
$stmt->execute(['uid' => $user_id]);
$logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

$filename = "metro_travel_history_" . date('Ymd') . ".csv";

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');

$output = fopen('php://output', 'w');
fputcsv($output, array('Date', 'Time', 'Source Station', 'Destination Station', 'Cost (INR)'));

foreach ($logs as $row) {
    fputcsv($output, $row);
}
fclose($output);
exit;
?>
