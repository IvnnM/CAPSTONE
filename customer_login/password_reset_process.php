<?php
session_start();
include("../config/database.php");

function validatePassword($password) {
    // Check if password is empty
    if (empty($password)) {
        return [false, "Password cannot be empty"];
    }
    
    // Check password length
    if (strlen($password) < 8) {
        return [false, "Password must be at least 8 characters long"];
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
        
        return [false, "Password must contain $missing_req_text"];
    }
    
    // Check if password is too common
    $common_passwords = ['password123', '12345678', 'qwerty', 'letmein'];
    if (in_array(strtolower($password), $common_passwords)) {
        return [false, "Please choose a stronger password"];
    }
    
    return [true, ""];
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        // Validate input
        if (!isset($_POST['token'], $_POST['new_password'], $_POST['confirm_password'])) {
            throw new Exception("Missing required fields");
        }

        $token = $_POST['token'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];

        // Check if passwords match
        if ($new_password !== $confirm_password) {
            throw new Exception("Passwords do not match");
        }

        // Validate password
        list($is_valid_password, $password_error) = validatePassword($new_password);
        if (!$is_valid_password) {
            throw new Exception($password_error);
        }

        // Hash the password
        $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);

        // Check if the token is valid for Customer
        $stmt = $conn->prepare("SELECT CustomerID FROM PasswordResetTb WHERE Token = :token AND Expiration > NOW()");
        $stmt->execute(['token' => $token]);
        $reset_request = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$reset_request) {
            throw new Exception("Invalid or expired token");
        }

        // Begin transaction
        $conn->beginTransaction();

        // Update the customer's password
        $update_stmt = $conn->prepare("UPDATE CustomerTb SET Password = :new_password WHERE CustomerID = :customer_id");
        $update_stmt->execute([
            'new_password' => $hashed_password, 
            'customer_id' => $reset_request['CustomerID']
        ]);

        // Delete the token after the password update
        $delete_stmt = $conn->prepare("DELETE FROM PasswordResetTb WHERE Token = :token");
        $delete_stmt->execute(['token' => $token]);

        // Commit transaction
        $conn->commit();

        // Set success session alert
        $_SESSION['alert'] = "Password has been successfully updated. Please login with your new password.";
        $_SESSION['alert_type'] = "success";
        header("Location: /customer_login/login_form.php");
        exit;

    } catch (Exception $e) {
        // Rollback transaction if needed
        if ($conn->inTransaction()) {
            $conn->rollBack();
        }

        // Set error session alert
        $_SESSION['alert'] = $e->getMessage();
        $_SESSION['alert_type'] = "danger";
        header("Location: /customer_login/password_reset.php?token=" . urlencode($token));
        exit;
    }
} else {
    // Redirect if accessed without POST
    header("Location: /customer_login/login_form.php");
    exit;
}
?>