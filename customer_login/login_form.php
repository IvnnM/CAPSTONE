<?php
session_start();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Customer Login</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f0f0f0;
            margin: 0;
            padding: 20px;
        }
        .login-container {
            max-width: 400px;
            margin: 40px auto;
            padding: 20px;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            position: relative; /* Added for back button positioning */
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #333;
        }
        .form-group input, .form-group select {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        .form-group input:focus, .form-group select:focus {
            border-color: #007bff;
            outline: none;
            box-shadow: 0 0 0 2px rgba(0,123,255,.25);
        }
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .btn-login {
            width: 100%;
            padding: 10px;
            background-color: #007bff;
            border: none;
            border-radius: 4px;
            color: white;
            font-size: 16px;
            cursor: pointer;
        }
        .btn-login:hover {
            background-color: #0056b3;
        }
        .text-center {
            text-align: center;
        }
        /* Password toggle styles */
        .password-container {
            position: relative;
        }
        .password-toggle {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            border: none;
            background: none;
            cursor: pointer;
            color: #666;
            padding: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 30px;
            height: 30px;
        }
        .password-toggle:hover {
            color: #333;
        }
        .password-toggle i {
            font-size: 1.2rem;
        }
        #password {
            padding-right: 40px;
        }
        /* Back button styles */
        .back-button {
            position: absolute;
            top: 20px;
            left: 20px;
            color: #666;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 5px;
            font-size: 0.9rem;
        }
        .back-button:hover {
            color: #333;
        }
        .back-button i {
            font-size: 1.2rem;
        }
        /* Adjust header margin to accommodate back button */
        h2.text-center {
            margin-top: 15px;
        }
    </style>
</head>
<body>
    <div class="login-container shadow-lg">
                <!-- Back Button -->
                <a href="../views/customer_view.php#Overview" class="back-button">
            <i class="bi bi-arrow-left"></i>
            Back
        </a><br>
        <h2 class="text-center">Customer Login</h2>
        
        <?php if (isset($_SESSION['alert'])): ?>
            <div class="alert alert-<?php echo $_SESSION['alert_type']; ?>">
                <?php 
                echo $_SESSION['alert'];
                unset($_SESSION['alert']);
                unset($_SESSION['alert_type']);
                ?>
            </div>
        <?php endif; ?>

        <form action="login_process.php" method="POST">
            <div class="form-group">
                <label for="email">Email Address:</label>
                <input type="email" id="email" name="email" required>
            </div>
            
            <div class="form-group">
                <label for="password">Password:</label>
                <div class="password-container">
                    <input type="password" id="password" name="password" required>
                    <button type="button" class="password-toggle" onclick="togglePassword()">
                        <i class="bi bi-eye"></i>
                    </button>
                </div>
            </div>
            <!-- Forgot Password Link -->
            <div class="forgot-pass-link form-group text-end">
                <a href="./password_forgot.php" class="text-muted">Forgot Password?</a>
            </div>
            <div class="form-group">
                <button type="submit" class="btn-login">Login</button>
            </div>
        </form>
        
        <div class="register-link">
            Don't have an account? <a href="./sign_up_form.php">Register here</a>
        </div>
    </div>

    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const toggleButton = document.querySelector('.password-toggle i');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleButton.className = 'bi bi-eye-slash';
            } else {
                passwordInput.type = 'password';
                toggleButton.className = 'bi bi-eye';
            }
        }
    </script>
</body>
</html>