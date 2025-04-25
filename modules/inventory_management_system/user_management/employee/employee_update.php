<?php
session_start();
include("../../../../includes/cdn.html"); 
include("../../../../config/database.php");

// Check if the user is logged in and has an employee ID in the session
if (!isset($_SESSION['EmpID'])) {
    $_SESSION['alert'] = 'You must be logged in to access this page.';
    $_SESSION['alert_type'] = 'danger';
    header("Location: ../../../../login.php");
    exit;
}

// Use the logged-in user's employee ID from the session
$emp_id = $_SESSION['EmpID'];

// Fetch employee details for display
$employee_query = "SELECT * FROM EmpTb WHERE EmpID = :emp_id";
$employee_stmt = $conn->prepare($employee_query);
$employee_stmt->bindParam(':emp_id', $emp_id, PDO::PARAM_INT);
$employee_stmt->execute();
$employee = $employee_stmt->fetch(PDO::FETCH_ASSOC);

if (!$employee) {
    $_SESSION['alert'] = 'Employee not found.';
    $_SESSION['alert_type'] = 'danger';
    header("Location: ../../../../views/personnel_view.php");
    exit;
}

// Handle form submission for updating employee details
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $old_password = $_POST['old_password'];
    
    // Verify the old password using password_verify
    if (!password_verify($old_password, $employee['EmpPassword'])) {
        $_SESSION['alert'] = 'Old password is incorrect.';
        $_SESSION['alert_type'] = 'danger';
        header("Location: ../../../../modules/inventory_management_system/user_management/employee/employee_update.php");
        exit;
    } else {
        $emp_name = $_POST['emp_name'];
        $location_id = $_POST['location_id'];
        $emp_email = $_POST['emp_email'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];

        // Check if new passwords match and are not empty
        if (!empty($new_password)) {
            if ($new_password === $confirm_password) {
                // Hash the new password
                $emp_password = password_hash($new_password, PASSWORD_DEFAULT);
            } else {
                $_SESSION['alert'] = 'New passwords do not match.';
                $_SESSION['alert_type'] = 'danger';
                header("Location: ../../../../modules/inventory_management_system/user_management/employee/employee_update.php");
                exit;
            }
        } else {
            // Retain old password if new password is not provided
            $emp_password = $employee['EmpPassword'];
        }

        // Prepare the update query
        $update_query = "UPDATE EmpTb SET EmpName = :emp_name, LocationID = :location_id, EmpEmail = :emp_email, EmpPassword = :emp_password WHERE EmpID = :emp_id";
        $update_stmt = $conn->prepare($update_query);
        $update_stmt->bindParam(':emp_name', $emp_name);
        $update_stmt->bindParam(':location_id', $location_id, PDO::PARAM_INT);
        $update_stmt->bindParam(':emp_email', $emp_email);
        $update_stmt->bindParam(':emp_password', $emp_password);
        $update_stmt->bindParam(':emp_id', $emp_id, PDO::PARAM_INT);

        // Execute the update query
        if ($update_stmt->execute()) {
            $_SESSION['alert'] = 'Your details updated successfully!';
            $_SESSION['alert_type'] = 'success';
        } else {
            $_SESSION['alert'] = 'Error: Could not update employee details.';
            $_SESSION['alert_type'] = 'danger';
        }

        // Redirect back to the personnel view page
        header("Location: ../../../../modules/inventory_management_system/user_management/employee/employee_update.php");
        exit;
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Employee</title>
    <link href="../../../../assets/css/form.css" rel="stylesheet">
    <script>
        $(document).ready(function() {
            // Fetch and populate province and city data
            $.ajax({
                url: "../../../../includes/get_location_data.php",
                method: "GET",
                dataType: "json",
                success: function(data) {
                    var provinces = data.provinces;
                    var cities = data.cities;
                    var provinceDropdown = $("#province");
                    var cityDropdown = $("#city");

                    // Populate province dropdown
                    provinces.forEach(function(province) {
                        provinceDropdown.append(
                            $("<option>").val(province.Province).text(province.Province)
                        );
                    });

                    // Set the selected province and city based on the current employee data
                    var currentProvince, currentCity;

                    cities.forEach(function(city) {
                        if (city.LocationID == <?= json_encode($employee['LocationID']) ?>) {
                            currentProvince = city.Province;
                            currentCity = city.City;
                        }
                    });

                    provinceDropdown.val(currentProvince);
                    updateCityDropdown(currentProvince, currentCity, cities);

                    // Event listener for province change
                    provinceDropdown.change(function() {
                        var selectedProvince = $(this).val();
                        updateCityDropdown(selectedProvince, null, cities);
                    });

                    // Function to update city dropdown
                    function updateCityDropdown(province, selectedCity, cities) {
                        cityDropdown.empty(); // Clear existing cities
                        cityDropdown.append("<option value=''>Select City</option>");
                        
                        // Filter and populate city dropdown based on selected province
                        cities.forEach(function(city) {
                            if (city.Province === province) {
                                var cityOption = $("<option>").val(city.LocationID).text(city.City);
                                if (city.City === selectedCity) {
                                    cityOption.prop("selected", true);
                                }
                                cityDropdown.append(cityOption);
                            }
                        });

                        // Update the LocationID based on selected city
                        cityDropdown.change(function() {
                            $("#location_id").val($(this).val());
                        });
                    }
                },
                error: function() {
                    alert("Error: Could not retrieve location data.");
                }
            });

            // Update LocationID based on selected city
            $("#city").change(function() {
                $("#location_id").val($(this).val());
            });
        });
    </script>

    <script>
        function confirmUpdate(event) {
            const newPassword = document.getElementById('new_password').value;
            const confirmPassword = document.getElementById('confirm_password').value;

            if (newPassword !== confirmPassword) {
                alert('New passwords do not match.');
                event.preventDefault(); // Prevent form submission if passwords do not match
                return;
            }

            if (!confirm('Are you sure you want to update this employee?')) {
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
                    <li class="breadcrumb-item"><a href="../../../../views/personnel_view.php">Home</a></li>
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
            <h6>Identification</h6>
            <div class="row mb-3">
                <div class="col-md-6">
                <div class="form-floating mb-3">
                    <input type="text" class="form-control" id="emp_name" name="emp_name" value="<?= htmlspecialchars($employee['EmpName']) ?>" placeholder="Employee Name" required>
                    <label for="emp_name">Employee Name</label>
                </div>
                </div>
                <div class="col-md-3">
                    <div class="form-floating">
                        <select id="province" name="province" class="form-control" required>
                            <option value="">Select Province</option>
                        </select>
                        <label for="province">Province</label>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-floating">
                        <select id="city" name="city" class="form-control" required>
                            <option value="">Select City</option>
                        </select>
                        <label for="city">City</label>
                    </div>
                </div>
            </div>
            <!-- Account Information Section -->
            <h6>Account Information</h6>
            <div class="row mb-3">
                <div class="col-md-6">
                    <div class="form-floating">
                        <input type="email" class="form-control" id="emp_email" name="emp_email" value="<?= htmlspecialchars($employee['EmpEmail']) ?>" placeholder="Employee Email" required>
                        <label for="emp_email">Employee Email</label>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-floating">
                        <input type="password" class="form-control" id="old_password" name="old_password" placeholder="Old Password" required>
                        <label for="old_password">Old Password</label>
                    </div>
                </div>
            </div>

            <input type="hidden" name="location_id" id="location_id" value="<?= htmlspecialchars($employee['LocationID']) ?>" required>

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
            <a class="btn btn-secondary w-100 mb-2" href="../../../../views/personnel_view.php">Cancel</a>
        </form>
    </div>
</body>

</html>
