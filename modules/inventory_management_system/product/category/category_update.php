<?php
session_start();
include("../../../../includes/cdn.html"); 
include("../../../../config/database.php");

// Check if the user is logged in and has either an Employee ID or an Admin ID in the session
if (!isset($_SESSION['EmpID']) && !isset($_SESSION['AdminID'])) {
    $_SESSION['alert'] = 'You must be logged in to access this page.';
    $_SESSION['alert_type'] = 'danger';
    header("Location: ../../../../login.php");
    exit;
}

// Check if an ID is provided in the URL
if (isset($_GET['id'])) {
    $category_id = $_GET['id'];

    // Fetch the existing category details
    $query = "SELECT * FROM ProductCategoryTb WHERE CategoryID = :category_id";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':category_id', $category_id, PDO::PARAM_INT);
    $stmt->execute();

    // Check if the category exists
    if ($stmt->rowCount() > 0) {
        $category = $stmt->fetch(PDO::FETCH_ASSOC);
    } else {
        $_SESSION['alert'] = 'Category not found.';
        $_SESSION['alert_type'] = 'danger';
        header("Location: ../../../../views/personnel_view.php#Products");
        exit;
    }
} else {
    $_SESSION['alert'] = 'Invalid category ID.';
    $_SESSION['alert_type'] = 'danger';
    header("Location: ../../../../views/personnel_view.php#Products");
    exit;
}

// Handle form submission for updates
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $new_category_name = $_POST['category_name'];

    // Update the category in the database
    $update_query = "UPDATE ProductCategoryTb SET CategoryName = :category_name WHERE CategoryID = :category_id";
    $update_stmt = $conn->prepare($update_query);
    $update_stmt->bindParam(':category_name', $new_category_name);
    $update_stmt->bindParam(':category_id', $category_id, PDO::PARAM_INT);

    if ($update_stmt->execute()) {
        // Success alert
        $_SESSION['alert'] = 'Category updated successfully!';
        $_SESSION['alert_type'] = 'success';
    } else {
        // Error alert
        $_SESSION['alert'] = 'Error: Could not update the category.';
        $_SESSION['alert_type'] = 'danger';
    }

    // Redirect to the category list
    header("Location:  category_read.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Product Category</title>
</head>
<body>
    <div class="container relative">
        <div class="sticky-top bg-light pb-2">
            <h3>Update Product Category</h3>
            <!-- Breadcrumb Navigation -->
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="../../../../views/personnel_view.php#Products">Home</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Update Product Category</li>
                </ol>
            </nav><hr>
        </div>

        <form method="POST" action="">
            <h6>Edit Category</h6>

            <div class="form-floating">
                <input type="text" class="form-control" name="category_name" value="<?php echo htmlspecialchars($category['CategoryName']); ?>" placeholder="Category" required>
                <label for="category_name">Category</label>
            </div>
            <br>

            <button class="btn btn-success w-100 mb-2" type="submit">Update</button>
            <a class="btn btn-secondary w-100 mb-2" href="category_read.php">Cancel</a>
        </form>
    </div>
</body>
</html>
