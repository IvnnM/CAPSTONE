<?php
session_start();
include("../../../../includes/cdn.html"); 
include("../../../../config/database.php");

// Check if the user is logged in and has a customer ID in the session
if (!isset($_SESSION['customer_id'])) {
    $_SESSION['alert'] = 'You must be logged in to access this page.';
    $_SESSION['alert_type'] = 'danger';
    header("Location: ../../../../login.php");
    exit;
}

// Use the logged-in user's customer ID from the session
$customer_id = $_SESSION['customer_id'];

// Fetch customer details for display
$customer_query = "SELECT * FROM CustomerTb WHERE CustomerID = :customer_id";
$customer_stmt = $conn->prepare($customer_query);
$customer_stmt->bindParam(':customer_id', $customer_id, PDO::PARAM_INT);
$customer_stmt->execute();
$customer = $customer_stmt->fetch(PDO::FETCH_ASSOC);

if (!$customer) {
    $_SESSION['alert'] = 'Customer not found.';
    $_SESSION['alert_type'] = 'danger';
    header("Location: ../../../../views/customer_view.php#Orders");
    exit;
}

// Handle form submission for updating customer details
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $old_password = $_POST['old_password'];
    
    // Verify the old password using password_verify
    if (!password_verify($old_password, $customer['Password'])) {
        $_SESSION['alert'] = 'Old password is incorrect.';
        $_SESSION['alert_type'] = 'danger';
        header("Location: ../../../../modules/inventory_management_system/user_management/customer/customer_update.php");
        exit;
    } else {
        $name = $_POST['name'];
        $email = $_POST['email'];
        $contact_number = $_POST['contact_number'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];

        // Check if new passwords match and are not empty
        if (!empty($new_password)) {
            if ($new_password === $confirm_password) {
                // Hash the new password
                $password = password_hash($new_password, PASSWORD_DEFAULT);
            } else {
                $_SESSION['alert'] = 'New passwords do not match.';
                $_SESSION['alert_type'] = 'danger';
                header("Location: ../../../../modules/inventory_management_system/user_management/customer/customer_update.php");
                exit;
            }
        } else {
            // Retain old password if new password is not provided
            $password = $customer['Password'];
        }

        // Prepare the update query
        $update_query = "UPDATE CustomerTb SET Name = :name, Email = :email, ContactNumber = :contact_number, Password = :password WHERE CustomerID = :customer_id";
        $update_stmt = $conn->prepare($update_query);
        $update_stmt->bindParam(':name', $name);
        $update_stmt->bindParam(':email', $email);
        $update_stmt->bindParam(':contact_number', $contact_number);
        $update_stmt->bindParam(':password', $password);
        $update_stmt->bindParam(':customer_id', $customer_id, PDO::PARAM_INT);

        // Execute the update query
        if ($update_stmt->execute()) {
            $_SESSION['alert'] = 'Your details updated successfully!';
            $_SESSION['alert_type'] = 'success';
        } else {
            $_SESSION['alert'] = 'Error: Could not update customer details.';
            $_SESSION['alert_type'] = 'danger';
        }

        // Redirect back to the index page
        header("Location: ../../../../modules/inventory_management_system/user_management/customer/customer_update.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Account</title>
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

            if (!confirm('Are you sure you want to update your account?')) {
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
                    <li class="breadcrumb-item"><a href="../../../../views/customer_view.php#Overview">Home</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Update Account</li>
                </ol>
            </nav>
            <hr>
        </div>
        <!-- Alert Display Section -->
        <?php if(isset($_SESSION['alert'])): ?>
        <div class="alert alert-<?= htmlspecialchars($_SESSION['alert_type']) ?> alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($_SESSION['alert']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php 
        // Unset the alert after displaying to prevent it from showing again
        unset($_SESSION['alert']);
        unset($_SESSION['alert_type']);
        endif; 
        ?>
        <form method="POST" action="" onsubmit="confirmUpdate(event)">
            <!-- Identification Section -->
            <h6>Personal Information</h6>
            <div class="row mb-3">
                <div class="col-md-6">
                    <div class="form-floating mb-3">
                        <input type="text" class="form-control" id="name" name="name" value="<?= htmlspecialchars($customer['Name']) ?>" placeholder="Full Name" required>
                        <label for="name">Full Name</label>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-floating">
                        <input type="tel" class="form-control" id="contact_number" name="contact_number" value="<?= htmlspecialchars($customer['ContactNumber']) ?>" placeholder="Contact Number" required>
                        <label for="contact_number">Contact Number</label>
                    </div>
                </div>
            </div>

            <!-- Account Information Section -->
            <h6>Account Information</h6>
            <div class="row mb-3">
                <div class="col-md-6">
                    <div class="form-floating">
                        <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($customer['Email']) ?>" placeholder="Email" required>
                        <label for="email">Email</label>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-floating">
                        <input type="password" class="form-control" id="old_password" name="old_password" placeholder="Current Password" required>
                        <label for="old_password">Current Password</label>
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

            <button class="btn btn-primary w-100 mb-2" type="submit">Update</button>
            <a class="btn btn-secondary w-100 mb-2" href="../../../../views/customer_view.php#Profile">Cancel</a>
        </form>
    </div>
</body>
</html>