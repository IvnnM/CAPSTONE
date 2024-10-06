<?php
// inventory_update_levels.php
include("./../../../config/database.php");

function updateInventoryLevels($conn) {
    // Get the current quarter and year
    $currentYear = date("Y");
    $currentQuarter = ceil(date("n") / 3); // 1 to 4

    // Get the previous two years
    $previousYear1 = $currentYear - 1;
    $previousYear2 = $currentYear - 2;

    // Fetch historical transaction data for the same quarter in the last two years
    $sql = "
        SELECT I.InventoryID, I.ProductID, 
               COALESCE(SUM(CASE WHEN YEAR(T.TransactionDate) = $previousYear1 AND QUARTER(T.TransactionDate) = $currentQuarter THEN T.Quantity ELSE 0 END), 0) AS Sales_Year1,
               COALESCE(SUM(CASE WHEN YEAR(T.TransactionDate) = $previousYear2 AND QUARTER(T.TransactionDate) = $currentQuarter THEN T.Quantity ELSE 0 END), 0) AS Sales_Year2
        FROM InventoryTb I
        JOIN OnhandTb O ON I.InventoryID = O.InventoryID
        JOIN TransacTb T ON O.OnhandID = T.OnhandID
        WHERE (YEAR(T.TransactionDate) = $previousYear1 OR YEAR(T.TransactionDate) = $previousYear2) 
        AND QUARTER(T.TransactionDate) = $currentQuarter
        GROUP BY I.InventoryID
    ";

    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $inventory_levels = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Update the ReorderLevel and MaxStockLevel based on transaction data
    foreach ($inventory_levels as $item) {
        // Calculate average sales for dynamic safety stock
        $totalSales = $item['Sales_Year1'] + $item['Sales_Year2'];
        $averageSales = $totalSales / 2;

        // Calculate dynamic safety stock as 25% of average sales
        $safetyStock = max(5, ceil($averageSales * 0.25));
        // Calculate reorder and max stock levels
        $reorderLevel = calculateReorderLevel($item['Sales_Year1'], $item['Sales_Year2'], $safetyStock);
        $maxStockLevel = calculateMaxStockLevel($item['Sales_Year1'], $item['Sales_Year2'], $safetyStock);

        // Prepare update statement
        $update_sql = "
            UPDATE InventoryTb 
            SET ReorderLevel = :reorderLevel, MaxStockLevel = :maxStockLevel 
            WHERE InventoryID = :inventoryID
        ";

        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bindParam(':reorderLevel', $reorderLevel);
        $update_stmt->bindParam(':maxStockLevel', $maxStockLevel);
        $update_stmt->bindParam(':inventoryID', $item['InventoryID']);
        $update_stmt->execute();
    }
}

// Custom functions to determine ReorderLevel and MaxStockLevel
function calculateReorderLevel($sales_year1, $sales_year2, $safetyStock) {
    $avg = ($sales_year1 + $sales_year2) / 2;
    return max($safetyStock, ceil($avg * 0.1)); // Ensures at least safety stock
}

function calculateMaxStockLevel($sales_year1, $sales_year2, $safetyStock) {
    $avg = ($sales_year1 + $sales_year2) / 2;
    return max($safetyStock, ceil($avg * 1.5)); // Ensures at least safety stock
}

// Execute the update function
updateInventoryLevels($conn);
?>
