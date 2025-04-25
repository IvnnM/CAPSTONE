<?php
session_start();
include("../config/database.php");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);

    // Check if email exists in AdminTb
    $stmt = $conn->prepare("SELECT AdminID, AdminName FROM AdminTb WHERE AdminEmail = :email");
    $stmt->execute(['email' => $email]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($admin) {
        // Generate a secure token
        $token = bin2hex(random_bytes(32)); // 64 characters
        $expiration = date("Y-m-d H:i:s", strtotime("+1 hour")); // Token valid for 1 hour

        // Insert token into PasswordResetTb for admin
        $insert_stmt = $conn->prepare("INSERT INTO PasswordResetTb (AdminID, Token, Expiration) VALUES (:admin_id, :token, :expiration)");
        $insert_stmt->execute([
            'admin_id' => $admin['AdminID'],
            'token' => $token,
            'expiration' => $expiration
        ]);

        // Send email with the reset link
        $reset_link = "https://dkat.dkat-trading.com/personnel_login/reset_password.php?token=" . $token;
        $subject = "Password Reset Request";
        $message = "Hi " . $admin['AdminName'] . ",\n\nClick the following link to reset your password:\n\n" . $reset_link . "\n\nThis link will expire in 1 hour.\n\nIf you did not request this, please ignore this email.";
        $headers = "From: no-reply@dkat.dkat-trading.com";

        if (mail($email, $subject, $message, $headers)) {
            $_SESSION['alert'] = "Password reset link has been sent to your email.";
            $_SESSION['alert_type'] = "success";
        } else {
            $_SESSION['alert'] = "Failed to send password reset link. Please try again.";
            $_SESSION['alert_type'] = "danger";
        }
    } else {
        // Check if email exists in EmpTb (Employee table)
        $stmt = $conn->prepare("SELECT EmpID, EmpName FROM EmpTb WHERE EmpEmail = :email");
        $stmt->execute(['email' => $email]);
        $employee = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($employee) {
            // Generate a secure token
            $token = bin2hex(random_bytes(32)); // 64 characters
            $expiration = date("Y-m-d H:i:s", strtotime("+1 hour")); // Token valid for 1 hour

            // Insert token into PasswordResetTb for employee
            $insert_stmt = $conn->prepare("INSERT INTO PasswordResetTb (EmpID, Token, Expiration) VALUES (:emp_id, :token, :expiration)");
            $insert_stmt->execute([
                'emp_id' => $employee['EmpID'],
                'token' => $token,
                'expiration' => $expiration
            ]);

            // Send email with the reset link
            $reset_link = "https://dkat.dkat-trading.com/personnel_login/reset_password.php?token=" . $token;
            $subject = "Password Reset Request";
            $message = "Hi " . $employee['EmpName'] . ",\n\nClick the following link to reset your password:\n\n" . $reset_link . "\n\nThis link will expire in 1 hour.\n\nIf you did not request this, please ignore this email.";
            $headers = "From: no-reply@dkat.dkat-trading.com";

            if (mail($email, $subject, $message, $headers)) {
                $_SESSION['alert'] = "Password reset link has been sent to your email.";
                $_SESSION['alert_type'] = "success";
            } else {
                $_SESSION['alert'] = "Failed to send password reset link. Please try again.";
                $_SESSION['alert_type'] = "danger";
            }
        } else {
            $_SESSION['alert'] = "No account found with that email address.";
            $_SESSION['alert_type'] = "danger";
        }
    }

    header("Location: /personnel_login/login_form.php");
    exit;
}
?>
