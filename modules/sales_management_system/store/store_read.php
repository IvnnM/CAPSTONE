<?php
// store_read.php
session_start();
include("./../../../includes/cdn.html"); 
include("./../../../config/database.php");

// Check if user is logged in
if (!isset($_SESSION['EmpID']) && !isset($_SESSION['AdminID'])) {
    echo "<script>alert('You must be logged in to access this page.'); 
    window.location.href = './../../../login.php';</script>";
    exit;
}

// Fetch store information
$store_query = "
    SELECT s.*, l.Province, l.City 
    FROM StoreInfoTb s
    JOIN LocationTb l ON s.LocationID = l.LocationID
";
$store_stmt = $conn->prepare($store_query);
$store_stmt->execute();
$stores = $store_stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Store Information</title>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.24/css/jquery.dataTables.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.24/js/jquery.dataTables.min.js"></script>
</head>
<body>
<?php include("../../../includes/personnel/header.php"); ?>
<?php include("../../../includes/personnel/navbar.php"); ?>
    <div class="container-fluid"><hr>
        <div class="sticky-top bg-light pb-2">
            <h3>Store Information</h3>
            <!--<nav aria-label="breadcrumb">-->
            <!--    <ol class="breadcrumb">-->
            <!--        <li class="breadcrumb-item"><a href="../../../views/personnel_view.php#Store">Home</a></li>-->
            <!--        <li class="breadcrumb-item active" aria-current="page">Store Information</li>-->
            <!--    </ol>-->
            <!--</nav>-->
            <!--<hr>-->
            
            <?php if (isset($_SESSION['AdminID'])): ?>
                <div class="text-end mb-3">
                    <a href="store_create.php" class="btn btn-success">Create Store</a>
                </div>
            <?php endif; ?>
        </div>

        <div class="table-responsive">
            <table id="storeTable" class="table table-light table-hover border-secondary">
                <thead class="table-info">
                    <tr>
                        <th>ID</th>
                        <th>Province</th>
                        <th>City</th>
                        <th>GCash Number</th>
                        <th>GCash QR</th>
                        <th>Delivery Fee</th>
                        <th>Coordinates</th>
                        <?php if (isset($_SESSION['AdminID'])): ?>
                            <th>Actions</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($stores as $store): ?>
                        <tr>
                            <td><?= htmlspecialchars($store['StoreInfoID']) ?></td>
                            <td><?= htmlspecialchars($store['Province']) ?></td>
                            <td><?= htmlspecialchars($store['City']) ?></td>
                            <td><?= htmlspecialchars($store['StoreGcashNum']) ?></td>
                            <td>
                                <?php if ($store['StoreGcashQR']): ?>
                                    <img src="data:image/png;base64,<?= base64_encode($store['StoreGcashQR']) ?>" 
                                         alt="GCash QR" style="max-width: 100px;">
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($store['StoreDeliveryFee']) ?></td>
                            <td><?= htmlspecialchars($store['StoreExactCoordinates']) ?></td>
                            <?php if (isset($_SESSION['AdminID'])): ?>
                                <td>
                                    <div class="d-flex justify-content-center">
                                        <a href="store_update.php?store_id=<?= $store['StoreInfoID'] ?>" 
                                           class="btn btn-warning btn-sm me-2">Update</a>
                                        <a href="store_delete.php?store_id=<?= $store['StoreInfoID'] ?>" 
                                           class="btn btn-danger btn-sm"
                                           onclick="return confirm('Are you sure you want to delete this store?');">Delete</a>
                                    </div>
                                </td>
                            <?php endif; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            $('#storeTable').DataTable({
                "paging": true,
                "lengthChange": true,
                "searching": true,
                "ordering": true,
                "info": true,
                "autoWidth": false,
                "pageLength": 5,
                "lengthMenu": [5, 10, 25, 50, 100]
            });
        });
    </script>
</body>
</html>