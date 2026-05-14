<?php
require_once 'config.php';

if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] === 'admin') header("Location: admin_dashboard.php");
    else header("Location: user_dashboard.php");
    exit;
}

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (!empty($email) && !empty($password)) {
        $stmt = $pdo->prepare("SELECT id, password_hash, username, role FROM users WHERE email = :email AND role = 'admin'");
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            
            header("Location: admin_dashboard.php");
            exit;
        } else {
            $error = "Access Denied. Invalid admin credentials.";
        }
    } else {
        $error = "Please fill in all fields.";
    }
}
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - MetroTick</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="admin-bg">
    <div class="container d-flex justify-content-center align-items-center vh-100">
        <div class="glass-card auth-card p-5" style="max-width: 450px; width: 100%; border-top: 4px solid #e74c3c;">
            <div class="text-center mb-4">
                <h2 class="fw-bold text-danger">🛠️ Metro Admin</h2>
                <p class="text-muted">Staff Management Portal</p>
            </div>
            
            <?php if(!empty($error)): ?>
                <div class="alert alert-danger py-2"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="mb-3">
                    <label class="form-label fw-medium text-light">Admin Email</label>
                    <input type="email" class="form-control glass-input" name="email" required placeholder="admin@bengaluru.com">
                </div>
                <div class="mb-4">
                    <label class="form-label fw-medium text-light">Passcode</label>
                    <input type="password" class="form-control glass-input" name="password" required placeholder="••••••••">
                </div>
                <button type="submit" class="btn btn-danger w-100 btn-glass fw-bold">Secure Login</button>
            </form>
            
            <div class="text-center mt-4 pt-3 border-top border-secondary">
                <a href="index.php" class="text-muted small text-decoration-none">← Back to Passenger Portal</a>
            </div>
            
            <div class="demo-creds text-center small text-muted mt-4">
                <strong>Demo Admin:</strong> admin@bengaluru.com<br><strong>Password:</strong> password
            </div>
        </div>
    </div>
</body>
</html>
