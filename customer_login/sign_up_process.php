<?php
session_start();
include("../config/database.php");

class CustomerAuth {
    private $conn;
    
    public function __construct($conn) {
        $this->conn = $conn;
    }
    
    public function register($name, $email, $contact, $password) {
        try {
            $errors = [];
            
            // Validate name
            if (empty($name)) {
                $errors[] = "name";
                return [false, "Name cannot be empty", $errors];
            }
            
            if (strlen($name) < 2 || strlen($name) > 50) {
                $errors[] = "name";
                return [false, "Name must be between 2 and 50 characters long", $errors];
            }
            
            // Validate name format (allows letters, spaces, and hyphens)
            if (!preg_match("/^[a-zA-Z\s-]+$/", $name)) {
                $errors[] = "name";
                return [false, "Name can only contain letters, spaces, and hyphens", $errors];
            }
            
            // Validate email
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = "email";
                return [false, "Invalid email format", $errors];
            }
            
            // Additional email domain validation (optional)
            $email_domain = substr(strrchr($email, "@"), 1);
            $allowed_domains = ['gmail.com', 'yahoo.com', 'hotmail.com', 'outlook.com']; // Add your allowed domains
            if (!in_array(strtolower($email_domain), $allowed_domains)) {
                $errors[] = "email";
                return [false, "Please use a valid email domain", $errors];
            }
            
            // Validate contact number
            if (empty($contact)) {
                $errors[] = "contact";
                return [false, "Contact number cannot be empty", $errors];
            }
            
            // Validate Philippine mobile number format
            if (!preg_match("/^(09)\d{9}$/", $contact)) {
                $errors[] = "contact";
                return [false, "Invalid mobile number. Please use format 09XXXXXXXXX (11 digits starting with 09)", $errors];
            }
            
            // Password validation
            if (empty($password)) {
                $errors[] = "password";
                return [false, "Password cannot be empty", $errors];
            }
            
            if (strlen($password) < 8) {
                $errors[] = "password";
                return [false, "Password must be at least 8 characters long", $errors];
            }
            
            // Complex password requirements
            $password_checks = [
                'lowercase' => preg_match("/[a-z]/", $password),
                'uppercase' => preg_match("/[A-Z]/", $password),
                'number' => preg_match("/[0-9]/", $password),
                'special' => preg_match("/[!@#$%^&*()_+\-=\[\]{};':\"\\|,.<>\/?]/", $password)
            ];
            
            $missing_requirements = array_keys(array_filter($password_checks, function($v) { return !$v; }));
            if (!empty($missing_requirements)) {
                $requirement_messages = [
                    'lowercase' => 'at least one lowercase letter',
                    'uppercase' => 'at least one uppercase letter', 
                    'number' => 'at least one number',
                    'special' => 'at least one special character'
                ];
                
                $missing_req_text = implode(', ', array_map(function($req) use ($requirement_messages) {
                    return $requirement_messages[$req];
                }, $missing_requirements));
                
                $errors[] = "password";
                return [false, "Password must contain $missing_req_text", $errors];
            }
            
            // Check if password is too common (optional - you might want to maintain a list of common passwords)
            $common_passwords = ['password123', '12345678', 'qwerty', 'letmein'];
            if (in_array(strtolower($password), $common_passwords)) {
                $errors[] = "password";
                return [false, "Please choose a stronger password", $errors];
            }
            
            // Check if email already exists
            $check_stmt = $this->conn->prepare("SELECT CustomerID FROM CustomerTb WHERE Email = ?");
            $check_stmt->execute([$email]);
            
            if ($check_stmt->fetch()) {
                return [false, "Email already registered"];
            }
            
            // Generate verification token
            $verification_token = bin2hex(random_bytes(32));
            $token_expiration = date("Y-m-d H:i:s", strtotime("+24 hours"));
            
            // Begin transaction
            $this->conn->beginTransaction();
            
            // Insert new customer
            $insert_stmt = $this->conn->prepare("
                INSERT INTO CustomerTb (
                    Name, 
                    Email, 
                    ContactNumber, 
                    Password, 
                    VerificationToken, 
                    TokenExpiration, 
                    IsVerified
                ) VALUES (?, ?, ?, ?, ?, ?, 0)
            ");
            
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);
            
            $insert_stmt->execute([
                $name,
                $email,
                $contact,
                $hashed_password,
                $verification_token,
                $token_expiration
            ]);
            
            // Send verification email
            $verification_link = "https://dkat.dkat-trading.com/customer_login/verify_email.php?token=" . $verification_token;
            $subject = "Verify Your Email Address";
            $message = "
                <html>
                <body>
                    <h2>Welcome to DKAT, " . htmlspecialchars($name) . "!</h2>
                    <p>Thank you for registering. Please click the link below to verify your email address:</p>
                    <p><a href='" . htmlspecialchars($verification_link) . "'>Verify Email Address</a></p>
                    <p>Or copy and paste this link in your browser:</p>
                    <p>" . htmlspecialchars($verification_link) . "</p>
                    <p>This link will expire in 24 hours.</p>
                    <p>If you didn't create an account, please ignore this email.</p>
                </body>
                </html>
            ";
            
            $headers = "MIME-Version: 1.0" . "\r\n";
            $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
            $headers .= "From: no-reply@dkat.dkat-trading.com";
            
            if (!mail($email, $subject, $message, $headers)) {
                throw new Exception("Failed to send verification email");
            }
            
            $this->conn->commit();
            return [true, "Registration successful! Please check your email to verify your account."];
            
        } catch (Exception $e) {
            $this->conn->rollBack();
            return [false, "Registration failed: " . $e->getMessage()];
        }
    }
    
    public function verifyEmail($token) {
        try {
            $stmt = $this->conn->prepare("
                UPDATE CustomerTb 
                SET IsVerified = 1, 
                    VerificationToken = NULL 
                WHERE VerificationToken = ? 
                AND TokenExpiration > NOW() 
                AND IsVerified = 0
            ");
            
            $stmt->execute([$token]);
            
            if ($stmt->rowCount() > 0) {
                return [true, "Email verified successfully! You can now login."];
            } else {
                return [false, "Invalid or expired verification token"];
            }
            
        } catch (Exception $e) {
            return [false, "Verification failed: " . $e->getMessage()];
        }
    }
    
    public function login($email, $password) {
        try {
            $stmt = $this->conn->prepare("
                SELECT CustomerID, Password, IsVerified, Name 
                FROM CustomerTb 
                WHERE Email = ?
            ");
            
            $stmt->execute([$email]);
            $customer = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$customer) {
                return [false, "Invalid email or password"];
            }
            
            if (!password_verify($password, $customer['Password'])) {
                return [false, "Invalid email or password"];
            }
            
            if (!$customer['IsVerified']) {
                return [false, "Please verify your email before logging in"];
            }
            
            // Set session variables
            $_SESSION['customer_id'] = $customer['CustomerID'];
            $_SESSION['customer_name'] = $customer['Name'];
            
            return [true, "Login successful"];
            
        } catch (Exception $e) {
            return [false, "Login failed: " . $e->getMessage()];
        }
    }
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $auth = new CustomerAuth($conn);
    
    if (isset($_POST['action']) && $_POST['action'] == 'register') {
        if (isset($_POST['name'], $_POST['email'], $_POST['contact'], $_POST['password'])) {
            // Save form data into session
            $_SESSION['form_data'] = [
                'name' => trim($_POST['name']),
                'email' => trim($_POST['email']),
                'contact' => trim($_POST['contact'])
            ];

            list($success, $message, $errors) = $auth->register(
                $_SESSION['form_data']['name'],
                $_SESSION['form_data']['email'],
                $_SESSION['form_data']['contact'],
                $_POST['password']
            );

            $_SESSION['alert'] = $message;
            $_SESSION['alert_type'] = $success ? 'success' : 'danger';
            $_SESSION['validation_errors'] = $errors;

            if ($success) {
                unset($_SESSION['form_data']);
            }

            header("Location: sign_up_form.php");
            exit;
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $_SESSION['form_data'] = $_POST;

    list($success, $message) = $auth->register(
        trim($_POST['name']),
        trim($_POST['email']),
        trim($_POST['contact']),
        $_POST['password']
    );

    $_SESSION['alert'] = $message;
    $_SESSION['alert_type'] = $success ? 'success' : 'danger';

    if (!$success) {
        header("Location: sign_up_form.php");
        exit;
    }
}

?>