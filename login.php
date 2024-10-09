<?php 
session_start();
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
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <!-- Bootstrap Bundle with Popper.js -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>

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
