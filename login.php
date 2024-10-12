<?php 
session_start();
include("./includes/cdn.html");
include("./config/database.php");

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $role = $_POST['role'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    
    if ($role == 'admin') {
        // Admin login
        $query = "SELECT * FROM AdminTb WHERE AdminEmail = :email";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':email', $email); // Using bindParam with named placeholders
        $stmt->execute();
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($admin && password_verify($password, $admin['AdminPassword'])) {
            // Successful login
            $_SESSION['AdminID'] = $admin['AdminID'];
            $_SESSION['AdminName'] = $admin['AdminName'];
            header('Location: ./views/admin_view.php');
            exit();
        } else {
            echo "<script>alert('Invalid admin credentials');</script>";
        }

    } else if ($role == 'employee') {
        // Employee login
        $query = "SELECT * FROM EmpTb WHERE EmpEmail = :email";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':email', $email); // Using bindParam with named placeholders
        $stmt->execute();
        $employee = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($employee && password_verify($password, $employee['EmpPassword'])) {
            // Successful login
            $_SESSION['EmpID'] = $employee['EmpID'];
            $_SESSION['EmpName'] = $employee['EmpName'];
            header('Location: ./views/admin_view.php');
            exit();
        } else {
            echo "<script>alert('Invalid employee credentials');</script>";
        }
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Personnel Login</title>
  <link rel="stylesheet" href="./assets/css/style.css">     
</head>
<body>

  <div class="page p-3 mt-3">
    <h1>Personnel Login</h1>
    <section>
        <form method="POST" action="" class="row g-3">
            <div class="col-md-12">
                <div class="form-floating mb-3">
                    <select name="role" id="role" class="form-select" required>
                        <option value='' disabled selected>Select your role</option>
                        <option value="employee">Employee</option>
                        <option value="admin">Admin</option>
                    </select>
                    <label for="role">Role</label>
                </div>
            </div>
            <div class="col-md-12">
                <div class="form-floating mb-3">
                    <input type="email" class="form-control" id="email" name="email" placeholder="Email" required>
                    <label for="email">Email</label>
                </div>
            </div>
            <div class="col-md-12">
                <div class="form-floating mb-3">
                    <input type="password" class="form-control" id="password" name="password" placeholder="Password" required>
                    <label for="password">Password</label>
                </div>
            </div>
            <div class="col-12 text-end">
                <button type="button" class="btn btn-secondary" onclick="goBack()">Back</button>
                <button type="submit" class="btn btn-primary">Login</button>
            </div>
        </form>
    </section>
  </div>
  <?php include("./includes/personnel/footer.php"); ?>
</body>
</html>
