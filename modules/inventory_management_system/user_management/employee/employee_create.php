<?php
session_start();
include("../../../../includes/cdn.html"); 
include("../../../../config/database.php");

// Check if the admin is logged in and has an admin ID in the session
if (!isset($_SESSION['AdminID'])) {
    echo "<script>alert('You must be logged in to access this page.'); 
    window.location.href = '../../../../login.php';</script>";
    exit;
}

// Handle form submission for creating employee
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $empName = trim($_POST['emp_name']);
    $locationID = trim($_POST['location_id']);
    $empEmail = trim($_POST['emp_email']);
    $empPassword = trim($_POST['emp_password']);
    $errorMessage = '';
    $successMessage = '';

    // Input validation
    if (empty($empName) || empty($locationID) || empty($empEmail) || empty($empPassword)) {
        $errorMessage = "All fields are required.";
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
            $successMessage = "Employee created successfully.";
        } else {
            $errorMessage = "Error: Could not create employee.";
        }
    }
}
?>
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

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Employee</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/water.css@2/out/water.css">

</head>
<body>
    <h1 class="mb-4">Employee Form</h1>

    <?php if (isset($errorMessage) && !empty($errorMessage)): ?>
        <div style="color: red;"><?= htmlspecialchars($errorMessage) ?></div>
    <?php endif; ?>
    <?php if (isset($successMessage) && !empty($successMessage)): ?>
        <div style="color: green;"><?= htmlspecialchars($successMessage) ?></div>
    <?php endif; ?>

    <form method="POST" action="">
        <hr style="border-top: 1px solid white;">
        <h6>Personal Information</h6>
        <div class="row mb-3">
            <div class="col-md-12">
                <label for="emp_name">Employee Name:</label>
                <input type="text" class="form-control" name="emp_name" required>
            </div>
            <div class="col-md-6">
                <label for="province">Province:</label>
                <select id="province" name="province" class="form-control" required>
                    <option value="">Select Province</option>
                    <!-- Add your provinces dynamically here -->
                </select>
            </div>
            <div class="col-md-6">
                <label for="city">City:</label>
                <select id="city" name="city" class="form-control" required>
                    <option value="">Select City</option>
                    <!-- Add your cities dynamically here -->
                </select>
            </div>
        </div>

        <hr style="border-top: 1px solid white;">
        <h6>Set Up Account</h6>
        <div class="row mb-3">
            <div class="col-md-6">
                <label for="emp_email">Employee Email:</label>
                <input type="email" class="form-control" name="emp_email" required>
            </div>
            <div class="col-md-6">
                <label for="emp_password">Password:</label>
                <input type="password" class="form-control" name="emp_password" required>
            </div>
            <!-- Hidden field to store the selected LocationID -->
            <input type="hidden" name="location_id" id="location_id" required>
        </div>

        
        <button class="btn btn-success" type="submit">Create</button>
    </form>



    <br>
    <a href="employee_read.php">Go to Employee List</a>
</body>
</html>
