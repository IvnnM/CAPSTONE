<?php
session_start();
include("./../../../includes/cdn.php"); 
include("./../../../config/database.php");

// Use PDO to fetch data
$sql = "SELECT o.OnhandID, o.OnhandQty, o.RetailPrice, o.PromoPrice, p.ProductImage, p.ProductName 
        FROM OnhandTb o 
        JOIN InventoryTb i ON o.InventoryID = i.InventoryID 
        JOIN ProductTb p ON i.ProductID = p.ProductID";

$stmt = $conn->prepare($sql);
$stmt->execute();
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
  <title>Available Products List</title>
</head>
<body>
  <div class="container">
      <div class="row">
          <?php 
          // Define the base URL for images if necessary
          $base_url = '/1CAPSTONE/modules/inventory_management_system/product/';
          foreach($products as $row): 
              // Construct the image path based on the stored ProductImage value
              $image_path = $base_url . htmlspecialchars($row['ProductImage']);
          ?>
              <div class="col-lg-3 mt-3 mb-3">
                  <div class="card border-info p-2">
                      <!-- Ensure the path to the image is correct -->
                      <img src="<?= $image_path ?>" alt="<?= htmlspecialchars($row['ProductName']) ?>" class="card-img-top" style="width:100%; height: 200px; object-fit: cover;">
                      <div class="card-body">
                          <h5 class="card-title"><?= htmlspecialchars($row['ProductName']) ?></h5>
                          <p class="card-text">Retail Price: <?= htmlspecialchars($row['RetailPrice']) ?> <br>
                          Promo Price: <?= htmlspecialchars($row['PromoPrice']) ?> <br>
                          Available Quantity: <?= htmlspecialchars($row['OnhandQty']) ?></p>
                          <a href="transac_create_retail.php?onhand_id=<?= htmlspecialchars($row['OnhandID']) ?>" class="btn btn-primary">Buy in Retail</a>
                          <a href="transac_create_promo.php?onhand_id=<?= htmlspecialchars($row['OnhandID']) ?>" class="btn btn-warning">Buy in Promo</a>
                      </div>
                  </div>
              </div>
          <?php endforeach; ?>
      </div>
  </div>
</body>
</html>