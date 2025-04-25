<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
            margin: 0;
            padding: 20px;
        }
        .reset-container {
            max-width: 400px;
            margin: 40px auto;
            padding: 20px;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            position: relative;
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
        .password-container {
            position: relative;
        }
        .form-group input {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        .form-group input:focus {
            border-color: #007bff;
            outline: none;
            box-shadow: 0 0 0 2px rgba(0,123,255,.25);
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
        #new_password, #confirm_password {
            padding-right: 40px;
        }
        .btn-reset {
            width: 100%;
            padding: 10px;
            background-color: #007bff;
            border: none;
            border-radius: 4px;
            color: white;
            font-size: 16px;
            cursor: pointer;
        }
        .btn-reset:hover {
            background-color: #0056b3;
        }
        .text-center {
            text-align: center;
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
    <div class="reset-container">
        <!-- Back Button -->
        <!--<a href="./login_form.php" class="back-button">-->
        <!--    <i class="bi bi-arrow-left"></i>-->
        <!--    Back-->
        <!--</a><br>-->

        <h2 class="text-center">Reset Password</h2>
        
        <?php if (isset($_SESSION['alert'])): ?>
            <div class="alert alert-<?php echo $_SESSION['alert_type']; ?>">
                <?php 
                echo $_SESSION['alert'];
                unset($_SESSION['alert']);
                unset($_SESSION['alert_type']);
                ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_GET['token'])): ?>
            <form action="process_reset_password.php" method="post">
                <input type="hidden" name="token" value="<?= htmlspecialchars($_GET['token']) ?>">
                
                <div class="form-group">
                    <label for="new_password">New Password:</label>
                    <div class="password-container">
                        <input type="password" name="new_password" id="new_password" required minlength="8">
                        <button type="button" class="password-toggle" onclick="togglePassword('new_password')">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>
                </div>

                <div class="form-group">
                    <label for="confirm_password">Confirm Password:</label>
                    <div class="password-container">
                        <input type="password" name="confirm_password" id="confirm_password" required minlength="8">
                        <button type="button" class="password-toggle" onclick="togglePassword('confirm_password')">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>
                </div>

                <div class="form-group">
                    <button type="submit" class="btn-reset">Reset Password</button>
                </div>
            </form>
        <?php else: ?>
            <p class="text-danger text-center">Invalid password reset request.</p>
        <?php endif; ?>
    </div>

    <script>
        function togglePassword(inputId) {
            const passwordInput = document.getElementById(inputId);
            const toggleButton = passwordInput.nextElementSibling.querySelector('i');
            
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