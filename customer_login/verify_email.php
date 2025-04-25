<?php
session_start();
include("../config/database.php");

class EmailVerification {
    private $conn;
    
    public function __construct($conn) {
        $this->conn = $conn;
    }
    
    public function verifyEmail($token) {
        try {
            // Check if token exists and is valid
            $stmt = $this->conn->prepare("
                SELECT CustomerID, Name, Email, TokenExpiration 
                FROM CustomerTb 
                WHERE VerificationToken = ? 
                AND IsVerified = 0
            ");
            $stmt->execute([$token]);
            $customer = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$customer) {
                return [false, "Invalid verification token. Please check your email link."];
            }
            
            // Check if token has expired
            if (strtotime($customer['TokenExpiration']) < time()) {
                // Generate new token and send new email
                $new_token = bin2hex(random_bytes(32));
                $new_expiration = date("Y-m-d H:i:s", strtotime("+24 hours"));
                
                $update_stmt = $this->conn->prepare("
                    UPDATE CustomerTb 
                    SET VerificationToken = ?,
                        TokenExpiration = ?
                    WHERE CustomerID = ?
                ");
                $update_stmt->execute([$new_token, $new_expiration, $customer['CustomerID']]);
                
                // Send new verification email
                $this->sendNewVerificationEmail(
                    $customer['Email'],
                    $customer['Name'],
                    $new_token
                );
                
                return [false, "Verification link has expired. A new verification link has been sent to your email."];
            }
            
            // Verify the email
            $verify_stmt = $this->conn->prepare("
                UPDATE CustomerTb 
                SET IsVerified = 1,
                    VerificationToken = NULL,
                    TokenExpiration = NULL
                WHERE CustomerID = ?
            ");
            $verify_stmt->execute([$customer['CustomerID']]);
            
            return [true, "Email verified successfully! You can now login to your account."];
            
        } catch (Exception $e) {
            return [false, "Verification failed: " . $e->getMessage()];
        }
    }
    
    private function sendNewVerificationEmail($email, $name, $token) {
        $verification_link = "https://dkat.dkat-trading.com/verify_email.php?token=" . $token;
        $subject = "New Email Verification Link";
        $message = "
            <html>
            <body>
                <h2>Hello " . htmlspecialchars($name) . "!</h2>
                <p>Your previous verification link has expired. Please use this new link to verify your email address:</p>
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
        
        mail($email, $subject, $message, $headers);
    }
}

// Handle the verification request
if (isset($_GET['token'])) {
    $verification = new EmailVerification($conn);
    list($success, $message) = $verification->verifyEmail($_GET['token']);
    
    $_SESSION['alert'] = $message;
    $_SESSION['alert_type'] = $success ? 'success' : 'danger';
} else {
    $_SESSION['alert'] = "Invalid verification attempt.";
    $_SESSION['alert_type'] = 'danger';
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Email Verification</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container {
            max-width: 600px;
            margin: 40px auto;
            padding: 20px;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
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
        .text-center {
            text-align: center;
        }
        .button {
            display: inline-block;
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            margin-top: 20px;
        }
        .button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="text-center">Email Verification</h1>
        
        <?php if (isset($_SESSION['alert'])): ?>
            <div class="alert alert-<?php echo $_SESSION['alert_type']; ?>">
                <?php echo $_SESSION['alert']; ?>
            </div>
        <?php endif; ?>
        
        <div class="text-center">
            <a href="login_form.php" class="button">Go to Login</a>
        </div>
    </div>
</body>
</html>