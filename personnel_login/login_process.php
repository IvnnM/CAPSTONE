<?php
session_start();
include("../config/database.php");

class PersonnelLogin {
    private $conn;
    
    public function __construct($conn) {
        $this->conn = $conn;
    }
    
    public function processLogin($role, $email, $password) {
        try {
            // Validate input
            if (empty($email) || empty($password)) {
                return [false, "Please provide both email and password"];
            }
            
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return [false, "Invalid email format"];
            }

            // Admin login
            if ($role == 'admin') {
                $stmt = $this->conn->prepare("SELECT * FROM AdminTb WHERE AdminEmail = ?");
                $stmt->execute([$email]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                if (!$user || !password_verify($password, $user['AdminPassword'])) {
                    return [false, "Invalid admin credentials"];
                }

                $_SESSION['AdminID'] = $user['AdminID'];
                $_SESSION['AdminName'] = $user['AdminName'];
                return [true, "Admin login successful"];
            }
            // Employee login
            else if ($role == 'employee') {
                $stmt = $this->conn->prepare("SELECT * FROM EmpTb WHERE EmpEmail = ?");
                $stmt->execute([$email]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                if (!$user || !password_verify($password, $user['EmpPassword'])) {
                    return [false, "Invalid employee credentials"];
                }

                $_SESSION['EmpID'] = $user['EmpID'];
                $_SESSION['EmpName'] = $user['EmpName'];
                return [true, "Employee login successful"];
            }

            return [false, "Invalid role"];
            
        } catch (Exception $e) {
            return [false, "Login failed: " . $e->getMessage()];
        }
    }
}

// Process login request
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $login = new PersonnelLogin($conn);
    
    // Get and sanitize input
    $role = $_POST['role'] ?? '';
    $email = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'] ?? '';
    
    // Process login
    list($success, $message) = $login->processLogin($role, $email, $password);
    
    // Set alert message
    $_SESSION['alert'] = $message;
    $_SESSION['alert_type'] = $success ? 'success' : 'danger';
    
    // Redirect based on result
    if ($success) {
        if ($role == 'admin') {
            header("Location: ../views/personnel_view.php");
        } else {
            header("Location: ../views/personnel_view.php");
        }
    } else {
        header("Location: ./login_form.php");
    }
    exit;
} else {
    // If someone tries to access this file directly, redirect to login form
    header("Location: ./login_form.php");
    exit;
}
?>
