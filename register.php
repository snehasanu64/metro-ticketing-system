<?php
require_once 'config.php';

if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] === 'admin') header("Location: admin_dashboard.php");
    else header("Location: user_dashboard.php");
    exit;
}

$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (!empty($username) && !empty($email) && !empty($password)) {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = "Invalid email format.";
        } else {
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = :email");
            $stmt->execute(['email' => $email]);
            if ($stmt->fetch()) {
                $error = "Email is already registered.";
            } else {
                $hashed_password = password_hash($password, PASSWORD_BCRYPT);
                $insert_stmt = $pdo->prepare("INSERT INTO users (username, email, password_hash, role) VALUES (:username, :email, :password_hash, 'user')");
                
                if ($insert_stmt->execute(['username' => $username, 'email' => $email, 'password_hash' => $hashed_password])) {
                    $success = "Account created! You can now <a href='index.php'>login</a>.";
                } else {
                    $error = "Registration failed. Please try again.";
                }
            }
        }
    } else {
        $error = "Please fill in all fields.";
    }
}
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Metro Ticketing System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="auth-bg">
    <div class="container d-flex justify-content-center align-items-center vh-100">
        <div class="glass-card auth-card p-5">
            <div class="text-center mb-4">
                <h2 class="fw-bold gradient-text">🚇 Join MetroTick</h2>
                <p class="text-muted">Create a passenger account</p>
            </div>
            
            <?php if(!empty($error)): ?>
                <div class="alert alert-danger py-2"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            <?php if(!empty($success)): ?>
                <div class="alert alert-success py-2"><?php echo $success; ?></div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="mb-3">
                    <label class="form-label fw-medium">Full Name</label>
                    <input type="text" class="form-control glass-input" name="username" required placeholder="John Doe">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-medium">Email Address</label>
                    <input type="email" class="form-control glass-input" name="email" required placeholder="name@example.com">
                </div>
                <div class="mb-4">
                    <label class="form-label fw-medium">Password</label>
                    <input type="password" class="form-control glass-input" id="pwdInput" name="password" required placeholder="Create a strong password">
                    
                    <div class="progress mt-2" style="height: 6px; background: rgba(255,255,255,0.1);">
                        <div class="progress-bar" id="pwdStrength" role="progressbar" style="width: 0%;"></div>
                    </div>
                    <small id="pwdFeedback" class="text-muted mt-1 d-block">Minimum 8 characters.</small>
                </div>
                <button type="submit" class="btn btn-primary w-100 btn-glass fw-bold">Register</button>
            </form>
            <div class="text-center mt-3">
                <p class="mb-0">Already have an account? <a href="index.php" class="text-decoration-none">Log In</a></p>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('pwdInput').addEventListener('input', function() {
            const pwd = this.value;
            const bar = document.getElementById('pwdStrength');
            const feedback = document.getElementById('pwdFeedback');
            
            let strength = 0;
            if(pwd.length >= 8) strength += 25;
            if(pwd.match(/[A-Z]/)) strength += 25;
            if(pwd.match(/[0-9]/)) strength += 25;
            if(pwd.match(/[^a-zA-Z0-9]/)) strength += 25;

            bar.style.width = strength + '%';
            
            if(strength <= 25) {
                bar.className = 'progress-bar bg-danger';
                feedback.innerText = 'Weak: Add numbers, uppercase, or symbols.';
                feedback.className = 'text-danger mt-1 d-block small';
            } else if(strength <= 75) {
                bar.className = 'progress-bar bg-warning';
                feedback.innerText = 'Moderate: Add a special symbol or uppercase letter.';
                feedback.className = 'text-warning mt-1 d-block small';
            } else {
                bar.className = 'progress-bar bg-success';
                feedback.innerText = 'Strong Password!';
                feedback.className = 'text-success mt-1 d-block small';
            }
            
            if(pwd.length === 0) {
                bar.style.width = '0%';
                feedback.innerText = 'Minimum 8 characters.';
                feedback.className = 'text-muted mt-1 d-block small';
            }
        });
    </script>
</body>
</html>
