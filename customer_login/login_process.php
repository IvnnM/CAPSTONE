<?php
session_start();
include("../config/database.php");

class CustomerLogin {
    private $conn;
    
    public function __construct($conn) {
        $this->conn = $conn;
    }
    
    public function processLogin($email, $password) {
        try {
            // Validate input
            if (empty($email) || empty($password)) {
                return [false, "Please provide both email and password"];
            }
            
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return [false, "Invalid email format"];
            }
            
            // Get customer data
            $stmt = $this->conn->prepare("
                SELECT 
                    CustomerID,
                    Name,
                    Password,
                    ContactNumber,
                    IsVerified 
                FROM CustomerTb 
                WHERE Email = ?
            ");
            
            $stmt->execute([$email]);
            $customer = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Check if customer exists
            if (!$customer) {
                // Use a generic message for security
                return [false, "Invalid email or password"];
            }
            
            // Verify password
            if (!password_verify($password, $customer['Password'])) {
                return [false, "Invalid email or password"];
            }
            
            // Check if email is verified
            if (!$customer['IsVerified']) {
                // Send a new verification email
                $token = bin2hex(random_bytes(32));
                $expiration = date("Y-m-d H:i:s", strtotime("+24 hours"));
                
                $update_stmt = $this->conn->prepare("
                    UPDATE CustomerTb 
                    SET VerificationToken = ?,
                        TokenExpiration = ?
                    WHERE CustomerID = ?
                ");
                
                $update_stmt->execute([$token, $expiration, $customer['CustomerID']]);
                
                // Send verification email
                $this->sendVerificationEmail($email, $customer['Name'], $token);
                
                return [false, "Please verify your email first. A new verification link has been sent to your email."];
            }
            
            // Set session variables
            $_SESSION['customer_id'] = $customer['CustomerID'];
            $_SESSION['cust_name'] = $customer['Name'];
            $_SESSION['cust_email'] = $email;
            $_SESSION['cust_num'] = $customer['ContactNumber'];
            
            // Update last login timestamp (optional)
            $this->updateLastLogin($customer['CustomerID']);
            
            return [true, "Login successful"];
            
        } catch (Exception $e) {
            return [false, "Login failed: " . $e->getMessage()];
        }
    }
    
    private function updateLastLogin($customerId) {
        try {
            $stmt = $this->conn->prepare("
                UPDATE CustomerTb 
                SET LastLoginAt = CURRENT_TIMESTAMP 
                WHERE CustomerID = ?
            ");
            $stmt->execute([$customerId]);
        } catch (Exception $e) {
            // Silently fail as this is not critical
        }
    }
    
    private function sendVerificationEmail($email, $name, $token) {
        $verification_link = "https://dkat.dkat-trading.com/verify_email.php?token=" . $token;
        $subject = "Email Verification Required";
        $message = "
            <html>
            <body>
                <h2>Hello " . htmlspecialchars($name) . "!</h2>
                <p>Please verify your email address to access your account:</p>
                <p><a href='" . htmlspecialchars($verification_link) . "'>Verify Email Address</a></p>
                <p>Or copy and paste this link in your browser:</p>
                <p>" . htmlspecialchars($verification_link) . "</p>
                <p>This link will expire in 24 hours.</p>
                <p>If you didn't try to login, please ignore this email.</p>
            </body>
            </html>
        ";
        
        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        $headers .= "From: no-reply@dkat.dkat-trading.com";
        
        mail($email, $subject, $message, $headers);
    }
}

// Process login request
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $login = new CustomerLogin($conn);
    
    // Get and sanitize input
    $email = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'] ?? '';
    
    // Process login
    list($success, $message) = $login->processLogin($email, $password);
    
    // Set alert message
    $_SESSION['alert'] = $message;
    $_SESSION['alert_type'] = $success ? 'success' : 'danger';
    
    // Redirect based on result
    if ($success) {
        header("Location: /views/customer_view.php"); // Redirect to dashboard on success
    } else {
        header("Location: login_form.php"); // Redirect back to login form on failure
    }
    exit;
} else {
    // If someone tries to access this file directly, redirect to login form
    header("Location: login_form.php");
    exit;
}
?>