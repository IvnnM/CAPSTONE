<?php
session_start();
include("../../../../includes/cdn.php"); 
include("../../../../config/database.php");

// Check if the user is logged in and has either an Employee ID or an Admin ID in the session
if (!isset($_SESSION['EmpID']) && !isset($_SESSION['AdminID'])) {
    echo "<script>alert('You must be logged in to access this page.'); 
    window.location.href = '../../../../login.php';</script>";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get the category name from the form
    $category_name = $_POST['category_name'];

    // Check if the category name is not empty
    if (!empty($category_name)) {
        // Prepare and execute the insert query using PDO
        $query = "INSERT INTO ProductCategoryTb (CategoryName) VALUES (:category_name)";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':category_name', $category_name);

        if ($stmt->execute()) {
            // Success alert
            echo "<script>alert('Product Category Created Successfully');</script>";
        } else {
            // Error alert
            echo "<script>alert('Error: Could not create product category');</script>";
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Create Product Category</title>
</head>
<body>
    <h3>Create Product Category</h3>
    <form method="POST" action="">
        <label for="category_name">Category Name:</label>
        <input type="text" name="category_name" required><br>
        <button type="submit">Create</button>
    </form>
    <br>
    <a href="category_read.php">Back to Category List</a>
</body>
</html>
