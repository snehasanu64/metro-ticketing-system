<?php
require_once 'config.php';

if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] === 'admin') header("Location: admin_dashboard.php");
    else header("Location: user_dashboard.php");
    exit;
}

$error = '';
$msg = $_GET['msg'] ?? '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (!empty($email) && !empty($password)) {
        $stmt = $pdo->prepare("SELECT id, password_hash, username, role FROM users WHERE email = :email AND role = 'user'");
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            
            header("Location: user_dashboard.php");
            exit;
        } else {
            $error = "Invalid email or password.";
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
    <title>Passenger Login - MetroTick</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="auth-bg">
    <div class="container d-flex justify-content-center align-items-center vh-100">
        <div class="glass-card auth-card p-5" style="max-width: 450px; width: 100%;">
            <div class="text-center mb-4">
                <h2 class="fw-bold gradient-text">🚇 MetroTick</h2>
                <p class="text-muted">Passenger Portal Login</p>
            </div>
            
            <?php if(!empty($error)): ?>
                <div class="alert alert-danger py-2"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            <?php if(!empty($msg)): ?>
                <div class="alert alert-warning py-2"><?php echo htmlspecialchars($msg); ?></div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="mb-3">
                    <label class="form-label fw-medium">Email Address</label>
                    <input type="email" class="form-control glass-input" name="email" required placeholder="name@example.com">
                </div>
                <div class="mb-4">
                    <label class="form-label fw-medium">Password</label>
                    <input type="password" class="form-control glass-input" name="password" required placeholder="••••••••">
                </div>
                <button type="submit" class="btn btn-primary w-100 btn-glass fw-bold">Log In</button>
            </form>
            <div class="text-center mt-3">
                <p class="mb-0">Don't have an account? <a href="register.php" class="text-decoration-none">Register</a></p>
                <div class="mt-3 pt-3 border-top border-secondary">
                    <a href="admin_login.php" class="text-muted small text-decoration-none">🛠️ Admin Staff? Login Here</a>
                </div>
            </div>
            
            <div class="demo-creds text-center small text-muted mt-4">
                <strong>Demo User:</strong> test@bengaluru.com<br><strong>Password:</strong> password
            </div>
        </div>
    </div>
</body>
</html>
