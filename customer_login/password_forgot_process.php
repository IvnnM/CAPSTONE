<?php
session_start();
include("../config/database.php");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);

    // Check if email exists in CustomerTb
    $stmt = $conn->prepare("SELECT CustomerID, Name FROM CustomerTb WHERE Email = :email");
    $stmt->execute(['email' => $email]);
    $customer = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($customer) {
        // Generate a secure token
        $token = bin2hex(random_bytes(32)); // 64 characters
        $expiration = date("Y-m-d H:i:s", strtotime("+1 hour")); // Token valid for 1 hour

        // Insert token into PasswordResetTb table with CustomerID
        $insert_stmt = $conn->prepare("INSERT INTO PasswordResetTb (CustomerID, Token, Expiration) VALUES (:customer_id, :token, :expiration)");
        $insert_stmt->execute([
            'customer_id' => $customer['CustomerID'],
            'token' => $token,
            'expiration' => $expiration
        ]);

        // Send email with the reset link
        $reset_link = "https://dkat.dkat-trading.com/customer_login/password_reset.php?token=" . $token;
        $subject = "Password Reset Request";
        $message = "Hi " . $customer['Name'] . ",\n\nClick the following link to reset your password:\n\n" . $reset_link . "\n\nThis link will expire in 1 hour.\n\nIf you did not request this, please ignore this email.";
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

    header("Location: /customer_login/login_form.php");
    exit;
}
?>
