<?php
session_start();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Customer Registration</title>
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
        .form-container {
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
        .form-group .requirements {
            font-size: 0.9rem;
            color: #666;
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
        .btn-register {
            width: 100%;
            padding: 10px;
            background-color: #007bff;
            border: none;
            border-radius: 4px;
            color: white;
            font-size: 16px;
            cursor: pointer;
        }
        .btn-register:hover {
            background-color: #0056b3;
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
        .terms-container {
            font-size: 0.9rem;
            color: #333;
        }
        
        .terms-container a {
            text-decoration: underline;
            color: #007bff;
            cursor: pointer;
        }
        
        .terms-container a:hover {
            color: #0056b3;
        }
        
        .terms-container input[type="checkbox"] {
            width: 20px;
            height: 20px;
            margin: 0;
            accent-color: #007bff; /* Sets the checkbox color in modern browsers */
        }
        
        .d-flex {
            display: flex;
        }
        
        .align-items-center {
            align-items: center;
        }
        .me-2 {
            margin-right: 8px;
        }
        .terms-container a {
            margin-right: 10px;
        }
    </style>
</head>
<body>
    <div class="form-container shadow-lg">
        <!-- Back Button -->
        <a href="./login_form.php" class="back-button">
            <i class="bi bi-arrow-left"></i>
            Back
        </a><br>
        
        <h2 class="text-center">Customer Registration</h2>
        
        <?php if (isset($_SESSION['alert'])): ?>
            <div class="alert alert-<?php echo $_SESSION['alert_type']; ?>">
                <?php 
                echo $_SESSION['alert'];
                unset($_SESSION['alert']);
                unset($_SESSION['alert_type']);
                ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="sign_up_process.php">
            <input type="hidden" name="action" value="register">
            
            <div class="form-group">
                <label for="name">Full Name:</label>
                <input type="text" id="name" name="name" 
                       value="<?php 
                           // Clear name if it was invalid
                           echo (!isset($_SESSION['validation_errors']) || 
                                  !in_array('name', $_SESSION['validation_errors'])) 
                                  ? (isset($_SESSION['form_data']['name']) ? htmlspecialchars($_SESSION['form_data']['name']) : '') 
                                  : ''; 
                       ?>" 
                       required placeholder="Required, minimum 3 characters">
            </div>
            
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" 
                       value="<?php 
                           // Clear email if it was invalid
                           echo (!isset($_SESSION['validation_errors']) || 
                                  !in_array('email', $_SESSION['validation_errors'])) 
                                  ? (isset($_SESSION['form_data']['email']) ? htmlspecialchars($_SESSION['form_data']['email']) : '') 
                                  : ''; 
                       ?>" 
                       required placeholder="Valid email format, e.g., example@gmail.com">
            </div>
            
            <div class="form-group">
                <label for="contact">Contact Number:</label>
                <input type="tel" id="contact" name="contact" 
                       value="<?php 
                           // Clear contact if it was invalid
                           echo (!isset($_SESSION['validation_errors']) || 
                                  !in_array('contact', $_SESSION['validation_errors'])) 
                                  ? (isset($_SESSION['form_data']['contact']) ? htmlspecialchars($_SESSION['form_data']['contact']) : '') 
                                  : ''; 
                       ?>" 
                       minlength="11" maxlength="11" required placeholder="Required, 11 digits, e.g., 09123456789">
            </div>
            
            <div class="form-group">
                <label for="password">Password:</label>
                <div class="password-container">
                    <input type="password" id="password" name="password" 
                           value="<?php 
                               // Always clear password for security
                               echo ''; 
                           ?>" 
                           required minlength="8">
                    <button type="button" class="password-toggle" onclick="togglePassword()">
                        <i class="bi bi-eye"></i>
                    </button>
                </div>
                <span class="requirements">(At least 8 characters, include letters, numbers, and a special character)</span>
            </div>
            
            <div class="form-group terms-container">
                <label for="terms">
                    <input type="checkbox" id="terms" name="terms" required>
                    
                    I agree to the <a href="#" onclick="openTermsModal()">Terms & Conditions</a>
                    and <a href="#" onclick="openPrivacyModal()">Data Privacy Policy</a>
                </label>
            </div>
            
            <div class="form-group">
                <button type="submit" class="btn-register">Register</button>
            </div>
        </form>

        
        <div class="text-center mt-3">
            Already have an account? <a href="./login_form.php">Login here</a>
        </div>
    </div>

    <!-- Terms & Conditions Modal -->
    <div class="modal fade" id="termsModal" tabindex="-1" aria-labelledby="termsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="termsModalLabel">Terms & Conditions</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <h6>1. Acceptance of Terms</h6>
                    <p>By accessing or using the DKAT Chlorine and Swimming Pool Supply Trading System ("System"), you agree to be bound by these Terms and Conditions. If you do not agree to these terms, please do not use the System.</p>
    
                    <h6>2. User Accounts</h6>
                    <p>- Users are required to register an account to access certain features of the System.</p>
                    <p>- Users must provide accurate, complete, and updated information during registration.</p>
                    <p>- Users are responsible for maintaining the confidentiality of their account credentials.</p>
    
                    <h6>3. Permitted Use</h6>
                    <p>The System is intended for authorized users for managing inventory, sales, and logistics processes. Any misuse of the System, including unauthorized access or actions, is strictly prohibited.</p>
    
                    <h6>4. System Access and Availability</h6>
                    <p>The System is provided on an "as-is" and "as-available" basis. We reserve the right to modify, suspend, or discontinue the System at any time without notice.</p>
    
                    <h6>5. Limitation of Liability</h6>
                    <p>- DKAT and its developers are not liable for any loss or damages resulting from the use of the System.</p>
                    <p>- Users are solely responsible for ensuring the accuracy of data entered into the System.</p>
    
                    <h6>6. Intellectual Property</h6>
                    <p>The System and its content are the intellectual property of DKAT Chlorine and Swimming Pool Supply Trading. Users may not copy, distribute, or reproduce any part of the System without prior authorization.</p>
    
                    <h6>7. Updates and Modifications</h6>
                    <p>These Terms and Conditions may be updated periodically. Continued use of the System constitutes acceptance of the updated terms.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Data Privacy Policy Modal -->
    <div class="modal fade" id="privacyModal" tabindex="-1" aria-labelledby="privacyModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="privacyModalLabel">Data Privacy Policy</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <h6>1. Collection of Data</h6>
                    <p>- The System collects personal information, including names, email addresses, and contact details, during user registration.</p>
                    <p>- Additional data, such as inventory and transaction records, are collected to facilitate system operations.</p>
                    
                    <h6>2. Use of Data</h6>
                    <p>- Data is used to manage sales, inventory, and logistics, as well as for analytical purposes to improve system functionality.</p>
                    <p>- Personal information is used solely for account management and communication purposes.</p>
                    
                    <h6>3. Data Security</h6>
                    <p>- The System employs encryption and access controls to protect personal and transactional data.</p>
                    <p>- Regular security audits are conducted to safeguard against unauthorized access or breaches.</p>
                    
                    <h6>4. Data Sharing</h6>
                    <p>- Personal data will not be shared with third parties without user consent, except as required by law.</p>
                    
                    <h6>5. User Rights</h6>
                    <p>- Users have the right to access, update, or delete their personal information by contacting the System administrator.</p>
                    <p>- Users may request a copy of their stored data or withdraw their consent for data processing.</p>
                    
                    <h6>6. Retention of Data</h6>
                    <p>- User data is retained as long as the account remains active or as required to meet legal obligations.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
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

        function openTermsModal() {
            var termsModal = new bootstrap.Modal(document.getElementById('termsModal'));
            termsModal.show();
        }

        function openPrivacyModal() {
            var privacyModal = new bootstrap.Modal(document.getElementById('privacyModal'));
            privacyModal.show();
        }

        // Enable/disable register button based on Terms checkbox
        document.getElementById('terms').addEventListener('change', function() {
            const registerButton = document.querySelector('.btn-register');
            registerButton.disabled = !this.checked;
        });
        
        // Clear validation errors session after page load
        window.onload = function() {
            <?php 
            if (isset($_SESSION['validation_errors'])) {
                unset($_SESSION['validation_errors']);
            }
            if (isset($_SESSION['form_data'])) {
                unset($_SESSION['form_data']);
            }
            ?>
        }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js"></script>
</body>
</html>