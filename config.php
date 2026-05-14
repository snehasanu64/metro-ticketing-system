<?php
session_start();

$inactive = 1800; 
if (isset($_SESSION['timeout'])) {
    $session_life = time() - $_SESSION['timeout'];
    if ($session_life > $inactive) {
        session_unset();
        session_destroy();
        header("Location: index.php?msg=Session expired due to inactivity.");
        exit;
    }
}
$_SESSION['timeout'] = time();

$host = 'localhost';
$dbname = 'metro_analyzer';
$username = 'root'; 
$password = ''; 

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    $pdo->exec("CREATE TABLE IF NOT EXISTS settings (
        setting_key VARCHAR(50) PRIMARY KEY,
        setting_value VARCHAR(255) NOT NULL
    )");
    
    $stmt = $pdo->query("SELECT COUNT(*) FROM settings");
    if ($stmt->fetchColumn() == 0) {
        $pdo->exec("INSERT INTO settings (setting_key, setting_value) VALUES 
            ('base_fare', '10.00'),
            ('per_stop_fare', '2.50'),
            ('max_fare', '60.00')
        ");
    }

} catch(PDOException $e) {
    die("Database Connection failed: " . $e->getMessage() . "<br>Please run database.sql");
}
?>
