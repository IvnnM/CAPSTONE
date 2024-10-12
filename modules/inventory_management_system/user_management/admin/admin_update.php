<?php
session_start();
include("../../../../includes/cdn.html");
include("../../../../config/database.php");

// Check if the admin is logged in and has an admin ID in the session
if (!isset($_SESSION['AdminID'])) {
    echo "<script>alert('You must be logged in as admin to access this page.'); 
    window.location.href = '../../../../login.php';</script>";
    exit;
}

// Use the logged-in admin's ID from the session
$admin_id = $_SESSION['AdminID'];

// Fetch admin details for display
$admin_query = "SELECT * FROM AdminTb WHERE AdminID = :admin_id";
$admin_stmt = $conn->prepare($admin_query);
$admin_stmt->bindParam(':admin_id', $admin_id, PDO::PARAM_INT);
$admin_stmt->execute();
$admin = $admin_stmt->fetch(PDO::FETCH_ASSOC);

if (!$admin) {
    echo "<script>alert('Admin not found.'); window.history.back();</script>";
    exit;
}

// Handle form submission for updating admin details
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $old_password = $_POST['old_password'];
    
    // Verify the old password using password_verify
    if (!password_verify($old_password, $admin['AdminPassword'])) {
        echo "<script>alert('Old password is incorrect.');</script>";
    } else {
        $admin_name = $_POST['admin_name'];
        $admin_email = $_POST['admin_email'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];

        // Check if new passwords match and are not empty
        if (!empty($new_password)) {
            if ($new_password === $confirm_password) {
                // Hash the new password
                $admin_password = password_hash($new_password, PASSWORD_DEFAULT);
            } else {
                // If passwords don't match, show alert and exit
                echo "<script>alert('New passwords do not match.'); window.history.back();</script>";
                exit;
            }
        } else {
            // Retain old password if new password is not provided
            $admin_password = $admin['AdminPassword'];
        }

        // Prepare the update query
        $update_query = "UPDATE AdminTb SET AdminName = :admin_name, AdminEmail = :admin_email, AdminPassword = :admin_password WHERE AdminID = :admin_id";
        $update_stmt = $conn->prepare($update_query);
        $update_stmt->bindParam(':admin_name', $admin_name);
        $update_stmt->bindParam(':admin_email', $admin_email);
        $update_stmt->bindParam(':admin_password', $admin_password);
        $update_stmt->bindParam(':admin_id', $admin_id, PDO::PARAM_INT);

        // Execute the update query
        if ($update_stmt->execute()) {
            echo "<script>alert('Admin details updated successfully!'); window.location.href = 'admin_read.php';</script>";
            exit;
        } else {
            echo "<script>alert('Error: Could not update admin details.');</script>";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Update Admin</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/water.css@2/out/water.css">
    <script>
        function confirmUpdate(event) {
            const newPassword = document.getElementById('new_password').value;
            const confirmPassword = document.getElementById('confirm_password').value;

            if (newPassword !== confirmPassword) {
                alert('New passwords do not match.');
                event.preventDefault(); // Prevent form submission if passwords do not match
                return;
            }

            if (!confirm('Are you sure you want to update your account details?')) {
                event.preventDefault(); // Prevent form submission if user cancels confirmation
            }
        }
    </script>
    <style>
        label, .form-control {
            font-size: small;
        }
    </style>
</head>
<body>
    <h1 class="mb-4">Admin Form</h1>
    <hr style="border-top: 1px solid white;">
    <form method="POST" action="" onsubmit="confirmUpdate(event)">
        <h6>Identification</h6>
        <div class="row mb-3">
            <div class="col-md-6">
                <label for="admin_name">Admin Name:</label>
                <input type="text" name="admin_name" id="admin_name" class="form-control" value="<?= htmlspecialchars($admin['AdminName']) ?>" required>
            </div>
        </div>
        <hr style="border-top: 1px solid white;">
        <h6>Account Information</h6>
        <div class="row mb-3">
            <div class="col-md-6">
                <label for="admin_email">Admin Email:</label>
                <input type="email" name="admin_email" id="admin_email" class="form-control" value="<?= htmlspecialchars($admin['AdminEmail']) ?>" required>
            </div>

            <div class="col-md-6">
                <label for="old_password">Old Password:</label>
                <input type="password" name="old_password" id="old_password" class="form-control" required>
            </div>
        </div>
        <hr style="border-top: 1px solid white;">
        <h6>Set New Password <span><label>Note: Leave blank to keep your old password.</label></span></h6>
        
        <div class="row mb-3">
            <div class="col-md-6">
                <label for="new_password">New Password:</label>
                <input type="password" name="new_password" id="new_password" class="form-control">
            </div>
            <div class="col-md-6">
                <label for="confirm_password">Confirm New Password:</label>
                <input type="password" name="confirm_password" id="confirm_password" class="form-control">
            </div>
        </div>

        <button class="btn btn-success" type="submit">Update</button>
    </form>
    <br>
    <a href="../../../../views/admin_view.php#Profile">Go to Dashboard</a>
</body>
</html>
