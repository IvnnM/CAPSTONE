<?php
session_start();
include("../../../../includes/cdn.php"); 
include("../../../../config/database.php");

// Check if the user is logged in and has an employee ID in the session
if (!isset($_SESSION['EmpID'])) {
    echo "<script>alert('You must be logged in to access this page.'); 
    window.location.href = '../../../../login.php';</script>";
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
    echo "<script>alert('Employee not found.'); window.history.back();</script>";
    exit;
}

// Handle form submission for updating employee details
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $old_password = $_POST['old_password'];
    
    // Verify the old password using password_verify
    if (!password_verify($old_password, $employee['EmpPassword'])) {
        echo "<script>alert('Old password is incorrect.');</script>";
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
                // If passwords don't match, show alert and exit
                echo "<script>alert('New passwords do not match.'); window.history.back();</script>";
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
            echo "<script>alert('Employee details updated successfully!'); window.history.back();</script>";
            exit;
        } else {
            echo "<script>alert('Error: Could not update employee details.');</script>";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Update Employee</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/water.css@2/out/water.css">
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
    <style>
        label, .form-control {
            font-size: small;
        }
    </style>
</head>
<body>
    <h3 class="mb-4">Update Your Employee Account</h3>
    <hr style="border-top: 1px solid white;">
    <form method="POST" action="" onsubmit="confirmUpdate(event)">
        <!-- Identification Section -->
        <h6>Identification</h6>
        <div class="row mb-3">
            <div class="col-md-6">
                <label for="emp_name">Employee Name:</label>
                <input type="text" name="emp_name" id="emp_name" class="form-control" value="<?= htmlspecialchars($employee['EmpName']) ?>" required>
            </div>
        </div>
        <hr style="border-top: 1px solid white;">
        
        <!-- Account Information Section -->
        <h6>Account Information</h6>
        <div class="row mb-3">
            <div class="col-md-6">
                <label for="emp_email">Employee Email:</label>
                <input type="email" name="emp_email" id="emp_email" class="form-control" value="<?= htmlspecialchars($employee['EmpEmail']) ?>" required>
            </div>
            <div class="col-md-6">
                <label for="old_password">Old Password:</label>
                <input type="password" name="old_password" id="old_password" class="form-control" required>
            </div>
        </div>

        <!-- Location Information Section -->
        <div class="row mb-3">
            <div class="col-md-6">
                <label for="province">Province:</label>
                <select id="province" name="province" class="form-control" required>
                    <option value="">Select Province</option>
                </select>
            </div>
            <div class="col-md-6">
                <label for="city">City:</label>
                <select id="city" name="city" class="form-control" required>
                    <option value="">Select City</option>
                </select>
            </div>
        </div>
        
        <input type="hidden" name="location_id" id="location_id" value="<?= htmlspecialchars($employee['LocationID']) ?>" required>
        
        <hr style="border-top: 1px solid white;">
        
        <!-- Password Update Section -->
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

    <a href="../../../../views/employee_view.php">Go to Dashboard</a>
</body>
</html>
