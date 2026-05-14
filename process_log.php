<?php
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $action = $_POST['action'] ?? '';

    if ($action == 'add') {
        $source = $_POST['source_station'];
        $dest = $_POST['dest_station'];
        $date = $_POST['travel_date'];
        $time = $_POST['travel_time'];
        $cost = $_POST['cost'];

        if ($source === $dest) {
            $_SESSION['error'] = "Source and Destination cannot be the same.";
        } else {
            $stmt = $pdo->prepare("INSERT INTO travel_logs (user_id, source_station, dest_station, travel_date, travel_time, cost) VALUES (:uid, :src, :dest, :dt, :tm, :cost)");
            if ($stmt->execute(['uid' => $_SESSION['user_id'], 'src' => $source, 'dest' => $dest, 'dt' => $date, 'tm' => $time, 'cost' => $cost])) {
                $_SESSION['msg'] = "Trip logged successfully!";
            } else {
                $_SESSION['error'] = "Failed to log trip.";
            }
        }
    } elseif ($action == 'delete') {
        $log_id = $_POST['log_id'];

        $stmt = $pdo->prepare("DELETE FROM travel_logs WHERE log_id = :log_id AND user_id = :uid");
        if ($stmt->execute(['log_id' => $log_id, 'uid' => $_SESSION['user_id']])) {
            $_SESSION['msg'] = "Log deleted successfully.";
        } else {
            $_SESSION['error'] = "Failed to delete log.";
        }
    }
    
    header("Location: travel_logs.php");
    exit;
}
?>
