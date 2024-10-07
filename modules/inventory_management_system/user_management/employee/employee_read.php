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

// Determine the search value, either from URL or form submission
$search_value = '';
if (isset($_GET['search_value']) && !empty($_GET['search_value'])) {
    $search_value = trim($_GET['search_value']);
}

// Initialize the employees array
$employees = [];

// Fetch the employees only if there is a search value
if (!empty($search_value)) {
    $sql = "SELECT * FROM EmpTb WHERE EmpName LIKE :search_value OR EmpEmail LIKE :search_value";
    $stmt = $conn->prepare($sql);
    $search_param = '%' . $search_value . '%'; // Wildcard search for partial matches
    $stmt->bindParam(':search_value', $search_param);
    $stmt->execute();
    $employees = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    // Fetch all employees if no search value is provided
    $sql = "SELECT * FROM EmpTb";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $employees = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee List</title>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.24/css/jquery.dataTables.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.24/js/jquery.dataTables.min.js"></script>
    <link rel="stylesheet" href="../../../../assets/css/form.css"> <!-- Include your custom styles -->
    <style>

    </style>
</head>
<body>
    <div class="container">
        <h3>Employee List</h3>
        <!-- Breadcrumb Navigation -->
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="../../../../views/admin_view.php#Employee">Home</a></li>
                <li class="breadcrumb-item"><a href="employee_create.php">Add New Employee</a></li>
                <li class="breadcrumb-item active" aria-current="page">Employee List</li>
            </ol>
        </nav>
        <?php if (!empty($employees)): ?>
            <h4 class="mt-4">Employee Records</h4>
            <div class="container">
                <div class="table-responsive">
                    <table id="employeeTable" class="display table table-bordered table-striped table-hover fixed-table">
                        <thead>
                            <tr>
                                <th>Employee ID</th>
                                <th>Name</th>
                                <th>Location ID</th>
                                <th>Email</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($employees as $employee): ?>
                                <tr>
                                    <td><?= htmlspecialchars($employee['EmpID']) ?></td>
                                    <td><?= htmlspecialchars($employee['EmpName']) ?></td>
                                    <td><?= htmlspecialchars($employee['LocationID']) ?></td>
                                    <td><?= htmlspecialchars($employee['EmpEmail']) ?></td>
                                    <td>
                                        <div class="d-flex mb-2 justify-content-center">
                                            <a href="employee_delete.php?id=<?= htmlspecialchars($employee['EmpID']) ?>" class="btn btn-danger btn-sm me-2" onclick="return confirm('Are you sure you want to delete this employee?');">
                                                <i class="bi bi-trash"></i> <!-- Delete icon -->
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        <?php else: ?>
            <p class="mt-4">No employees found.</p>
        <?php endif; ?>
    </div>

    <script>
        // Initialize DataTables
        $(document).ready(function() {
            $('#employeeTable').DataTable({
                "paging": true,
                "lengthChange": true,
                "searching": true,
                "ordering": true,
                "info": true,
                "autoWidth": false,
                "pageLength": 10
            });
        });
    </script>
</body>
</html>
