<?php
require_once 'config.php';

echo "<h2>System Diagnosis & Fix</h2>";

try {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = 'test@bengaluru.com'");
    $stmt->execute();
    $user = $stmt->fetch();

    if (!$user) {
        echo "<p style='color:red;'>User 'test@bengaluru.com' DOES NOT EXIST in the database!</p>";
        echo "<p>Attempting to create the user...</p>";
        
        $hash = password_hash('password', PASSWORD_DEFAULT);
        $insert = $pdo->prepare("INSERT INTO users (username, email, password_hash) VALUES ('NammaCommuter', 'test@bengaluru.com', :hash)");
        $insert->execute(['hash' => $hash]);
        
        echo "<p style='color:green;'>User created successfully with password: <strong>password</strong></p>";
    } else {
        echo "<p style='color:green;'>User 'test@bengaluru.com' found in database.</p>";

        $hash = password_hash('password', PASSWORD_DEFAULT);
        $update = $pdo->prepare("UPDATE users SET password_hash = :hash WHERE email = 'test@bengaluru.com'");
        $update->execute(['hash' => $hash]);
        
        echo "<p style='color:green;'>Password explicitly reset to: <strong>password</strong></p>";
    }

    $stmt = $pdo->prepare("SELECT id, password_hash, username FROM users WHERE email = 'test@bengaluru.com'");
    $stmt->execute();
    $testUser = $stmt->fetch();
    
    if (password_verify('password', $testUser['password_hash'])) {
        echo "<h3 style='color:green;'>✅ Login test passed programmatically!</h3>";

        echo "<form action='index.php' method='POST'>
                <input type='hidden' name='email' value='test@bengaluru.com'>
                <input type='hidden' name='password' value='password'>
                <button type='submit' style='padding: 10px 20px; background: #5E35B1; color: white; border: none; border-radius: 5px; cursor: pointer;'>Click here to Auto-Login</button>
              </form>";
    } else {
        echo "<h3 style='color:red;'>❌ Login test failed programmatically. Something is wrong with PHP's password_verify.</h3>";
    }

} catch(Exception $e) {
    echo "<p style='color:red;'>Database Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>
