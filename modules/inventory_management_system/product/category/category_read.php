<?php
session_start();
include("../../../../includes/cdn.html"); 
include("../../../../config/database.php");
// $_SESSION['EmpID']='1';
$_SESSION['AdminID']='1';
// Check if the user is logged in and has either an Employee ID or an Admin ID in the session
if (!isset($_SESSION['EmpID']) && !isset($_SESSION['AdminID'])) {
    echo "<script>alert('You must be logged in to access this page.'); 
    window.location.href = '../../../../login.php';</script>";
    exit;
}

// Determine the search value, either from URL or form submission
$search_value = '';
if (isset($_GET['search_value']) && !empty($_GET['search_value'])) {
    $search_value = trim($_GET['search_value']);
}

// Prepare the query to fetch categories with an optional search filter
$category_query = "SELECT * FROM ProductCategoryTb";

if (!empty($search_value)) {
    $category_query .= " WHERE CategoryName LIKE :search_value";
}

$stmt = $conn->prepare($category_query);

if (!empty($search_value)) {
    $search_param = '%' . $search_value . '%'; // Wildcard search for partial matches
    $stmt->bindParam(':search_value', $search_param);
}

$stmt->execute();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Category List</title>
    <script src="../../../../assets/js/checkUserType.js" defer></script>
</head>
<body>
    <input type="hidden" id="userType" value="<?= isset($_SESSION['AdminID']) ? 'AdminID' : 'User' ?>">

    <div class="container-fluid">
        <div class="sticky-top bg-light pb-2">
            <h3>Product Category List</h3>
            <!-- Breadcrumb Navigation -->
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="../../../../views/admin_view.php#Products">Home</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Product Category List</li>
                    <li class="breadcrumb-item"><a href="../../inventory/inventory_read.php">Product Inventory List</a></li>
                    <li class="breadcrumb-item"><a href="../../../sales_management_system/onhand/onhand_read.php">Product Onhand List</a></li>
                </ol>
            </nav><hr>
            <!-- <div class="d-flex justify-content-between">
                <button type="button" class="btn btn-secondary" onclick="window.location.href='category_create.php';">Back</button>
                 <h4>Record</h4>
                <button type="button" class="btn btn-success" onclick="window.location.href='category_create.php';">Add New Category</button>
            </div> -->
            <!-- Button Group for Navigation -->
            <div class="d-flex justify-content-end">
                <button type="button" class="btn btn-success" onclick="window.location.href='category_create.php';">Create New Category</button>
            </div>
        </div>
        <!-- Table to display product categories -->
        <div class="table-responsive">
            <table id="categoryTable" class="table table-light table-hover border-secondary pt-2">
                <thead class="table-info">
                    <tr>
                        <th class="col-auto">ID</th>  
                        <th class="col-auto">Category</th> 
                        <th class="adminActions col-auto" style="display:none;">Actions</th>  
                    </tr>
                </thead>
                <tbody>
                    <?php if ($stmt->rowCount() > 0): ?>
                        <?php while ($category = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
                            <tr>    
                                <td><?= htmlspecialchars($category['CategoryID']) ?></td>
                                <td><?= htmlspecialchars($category['CategoryName']) ?></td>
                                <!-- <td>
                                    <div class="d-inline-block text-truncate" style="max-width: 200px;" data-bs-toggle="tooltip" title="<?= htmlspecialchars($category['CategoryName']) ?>">
                                        <?= htmlspecialchars($category['CategoryName']) ?>
                                    </div>
                                </td> -->
                                <td class="adminActions d-flex justify-content-center" style="display:none;">
                                    <a href="category_update.php?id=<?= htmlspecialchars($category['CategoryID']) ?>" 
                                    class="btn btn-warning btn-sm w-50 me-2">Edit
                                    </a>
                                    <a href="category_delete.php?id=<?= htmlspecialchars($category['CategoryID']) ?>" 
                                    onclick="return confirm('Are you sure you want to delete this category?');" 
                                    class="btn btn-danger btn-sm w-50">Delete
                                    </a>
                                </td>

                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="3">No categories found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        // Initialize DataTables
        $(document).ready(function() {
            $('#categoryTable').DataTable({
                "paging": true,
                "lengthChange": true,
                "searching": true,
                "ordering": true,
                "info": true,
                "autoWidth": false,
                "pageLength": 5, // Default number of entries per page
                "lengthMenu": [5, 10, 25, 50, 100], // Options for number of entries
            });
        });
    </script>
</body>
</html>
