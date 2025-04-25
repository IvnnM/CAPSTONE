<?php
session_start();
include("../../../../includes/cdn.html");
include("../../../../config/database.php");

// Check if the admin is logged in and has an admin ID in the session
if (!isset($_SESSION['AdminID'])) {
    $_SESSION['alert'] = 'You must be logged in as admin to access this page.';
    $_SESSION['alert_type'] = 'danger';
    header("Location: ../../../../login.php");
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
    $_SESSION['alert'] = 'Admin not found.';
    $_SESSION['alert_type'] = 'danger';
    header("Location: ../../../../views/admin_view.php");
    exit;
}

// Handle form submission for updating admin details
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $old_password = $_POST['old_password'];
    
    // Verify the old password using password_verify
    if (!password_verify($old_password, $admin['AdminPassword'])) {
        $_SESSION['alert'] = 'Old password is incorrect.';
        $_SESSION['alert_type'] = 'danger';
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
                $_SESSION['alert'] = 'New passwords do not match.';
                $_SESSION['alert_type'] = 'danger';
                header("Location: admin_read.php");
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
            $_SESSION['alert'] = 'Admin details updated successfully!';
            $_SESSION['alert_type'] = 'success';
        } else {
            $_SESSION['alert'] = 'Error: Could not update admin details.';
            $_SESSION['alert_type'] = 'danger';
        }
        // Redirect back to the personnel view page
        header("Location: ../../../../views/personnel_view.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Admin</title>
    <link href="../../../../assets/css/form.css" rel="stylesheet">
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
</head>
<body>
    <div class="container relative">
        <div class="sticky-top pb-2">
            <h3>Manage Account</h3>
            <!-- Breadcrumb Navigation -->
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="../../../../views/personnel_view.php#Overview">Home</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Update Admin</li>
                </ol>
            </nav>
            <hr>
        </div>

        <form method="POST" action="" onsubmit="confirmUpdate(event)">
            <!-- Identification Section -->
            <h6>Identification</h6>
            <div class="form-floating mb-3">
                <input type="text" class="form-control" id="admin_name" name="admin_name" placeholder="Admin Name" value="<?= htmlspecialchars($admin['AdminName']) ?>" required>
                <label for="admin_name">Admin Name</label>
            </div>

            <!-- Account Information Section -->
            <h6>Account Information</h6>
            <div class="row mb-3">
                <div class="col-md-6">
                    <div class="form-floating">
                        <input type="email" class="form-control" id="admin_email" name="admin_email" placeholder="Admin Email" value="<?= htmlspecialchars($admin['AdminEmail']) ?>" required>
                        <label for="admin_email">Admin Email</label>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-floating">
                        <input type="password" class="form-control" id="old_password" name="old_password" placeholder="Old Password" required>
                        <label for="old_password">Old Password</label>
                    </div>
                </div>
            </div>

            <!-- Password Update Section -->
            <div class="row mb-3">
                <div class="col-md-6">
                    <div class="form-floating">
                        <input type="password" class="form-control" id="new_password" name="new_password" placeholder="New Password">
                        <label for="new_password">New Password</label>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-floating">
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="Confirm New Password">
                        <label for="confirm_password">Confirm New Password</label>
                    </div>
                </div>
                <small>Leave blank to keep your old password.</small>
            </div>

            <!-- Submit Button -->
            <button class="btn btn-success w-100 mb-2" type="submit">Update</button>
            <a class="btn btn-secondary w-100 mb-2" href="../../../../views/personnel_view.php#Overview">Cancel</a>
        </form>
    </div>
</body>

</html>
