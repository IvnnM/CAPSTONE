<?php
session_start();
include("../../../../includes/cdn.html"); 
include("../../../../config/database.php");

// Check if the admin is logged in and has an admin ID in the session
if (!isset($_SESSION['AdminID'])) {
    $_SESSION['alert'] = 'You must be logged in to access this page.';
    $_SESSION['alert_type'] = 'danger';
    header("Location: ../../../../login.php");
    exit;
}

// Handle form submission for creating employee
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $empName = trim($_POST['emp_name']);
    $locationID = trim($_POST['location_id']);
    $empEmail = trim($_POST['emp_email']);
    $empPassword = trim($_POST['emp_password']);

    // Input validation
    if (empty($empName) || empty($locationID) || empty($empEmail) || empty($empPassword)) {
        $_SESSION['alert'] = 'All fields are required.';
        $_SESSION['alert_type'] = 'danger';
    } else {
        // Prepare the insert query
        $insert_query = "INSERT INTO EmpTb (EmpName, LocationID, EmpEmail, EmpPassword) VALUES (:emp_name, :location_id, :emp_email, :emp_password)";
        $insert_stmt = $conn->prepare($insert_query);
        
        // Hash the password
        $hashedPassword = password_hash($empPassword, PASSWORD_DEFAULT);
        
        // Bind parameters
        $insert_stmt->bindParam(':emp_name', $empName);
        $insert_stmt->bindParam(':location_id', $locationID);
        $insert_stmt->bindParam(':emp_email', $empEmail);
        $insert_stmt->bindParam(':emp_password', $hashedPassword);

        // Execute the insert query
        if ($insert_stmt->execute()) {
            $_SESSION['alert'] = 'Employee Account created successfully.';
            $_SESSION['alert_type'] = 'success';
        } else {
            $_SESSION['alert'] = 'Error: Could not create employee.';
            $_SESSION['alert_type'] = 'danger';
        }
    }

    // Redirect to the relevant page after handling form submission
    header("Location: employee_read.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Employee</title>
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

                    // Event listener for province change
                    provinceDropdown.change(function() {
                        var selectedProvince = $(this).val();
                        cityDropdown.empty(); // Clear existing cities
                        cityDropdown.append("<option value=''>Select City</option>");
                        
                        // Filter and populate city dropdown based on selected province
                        cities.forEach(function(city) {
                            if (city.Province === selectedProvince) {
                                cityDropdown.append(
                                    $("<option>").val(city.LocationID).text(city.City)
                                );
                            }
                        });
                    });
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
            const empPassword = document.getElementById('emp_password').value;
            const confirmPassword = document.getElementById('confirm_password').value;

            if (empPassword !== confirmPassword) {
                alert('Passwords do not match.');
                event.preventDefault(); // Prevent form submission if passwords do not match
                return;
            }

            if (!confirm('Are you sure you want to create this account?')) {
                event.preventDefault(); // Prevent form submission if user cancels confirmation
            }
        }
    </script>
</head>
<body>
    <div class="container relative">
        <div class="sticky-top bg-light pb-2">
            <h3>Add New Employee</h3>
            <!-- Breadcrumb Navigation -->
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="../../../../views/personnel_view.php#Employee">Home</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Create Employee</li>
                </ol>
            </nav>
            <hr>
        </div>

        <form method="POST" action="" onsubmit="confirmUpdate(event)">
            <!-- Personal Information Section -->
            <h6>Personal Information</h6>
            <div class="row mb-3">
                <div class="col-md-6">
                    <div class="form-floating">
                        <input type="text" class="form-control" id="emp_name" name="emp_name" placeholder="Employee Name" required>
                        <label for="emp_name">Employee Name</label>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-floating">
                        <select id="province" name="province" class="form-control" required>
                            <option value="">Select Province</option>
                            <!-- Add provinces dynamically here -->
                        </select>
                        <label for="province">Province</label>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-floating">
                        <select id="city" name="city" class="form-control" required>
                            <option value="">Select City</option>
                            <!-- Add cities dynamically here -->
                        </select>
                        <label for="city">City</label>
                    </div>
                </div>
            </div>
            <!-- Set Up Account Section -->
            <h6>Set Up Account</h6>
            <div class="row mb-3">
                <div class="col-md-6">
                    <div class="form-floating">
                        <input type="email" class="form-control" id="emp_email" name="emp_email" placeholder="Employee Email" required>
                        <label for="emp_email">Employee Email</label>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-floating">
                        <input type="password" class="form-control" id="emp_password" name="emp_password" placeholder="Password" required>
                        <label for="emp_password">Password</label>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-floating">
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="Confirm Password" required>
                        <label for="confirm_password">Confirm Password</label>
                    </div>
                </div>
                <input type="hidden" name="location_id" id="location_id" required>
            </div>

            <!-- Submit Button -->
            <button class="btn btn-success w-100 mb-2" type="submit">Create</button>
            <a class="btn btn-secondary w-100 mb-2" href="employee_read.php">Cancel</a>
        </form>
    </div>
</body>

</html>
