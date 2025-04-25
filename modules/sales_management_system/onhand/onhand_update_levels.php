<?php
// inventory_update_levels.php
session_start();
include("./../../../config/database.php");

function updateOnhandLevels($conn) {
    // Get the current quarter and year
    $currentYear = date("Y");
    $currentQuarter = ceil(date("n") / 3); // 1 to 4

    // Get the previous two years
    $previousYear1 = $currentYear - 1;
    $previousYear2 = $currentYear - 2;

    // Fetch historical transaction data for the same quarter in the last two years
    $sql = "
        SELECT O.OnhandID, O.InventoryID, 
               COALESCE(SUM(CASE WHEN YEAR(T.TransactionDate) = $previousYear1 AND QUARTER(T.TransactionDate) = $currentQuarter THEN CR.Quantity ELSE 0 END), 0) AS Sales_Year1,
               COALESCE(SUM(CASE WHEN YEAR(T.TransactionDate) = $previousYear2 AND QUARTER(T.TransactionDate) = $currentQuarter THEN CR.Quantity ELSE 0 END), 0) AS Sales_Year2
        FROM OnhandTb O
        JOIN CartRecordTb CR ON O.OnhandID = CR.OnhandID
        JOIN TransacTb T ON CR.TransacID = T.TransacID
        WHERE (YEAR(T.TransactionDate) = $previousYear1 OR YEAR(T.TransactionDate) = $previousYear2) 
        AND QUARTER(T.TransactionDate) = $currentQuarter
        GROUP BY O.OnhandID
    ";

    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $onhand_levels = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Update the ReorderThreshold based on transaction data
    foreach ($onhand_levels as $item) {
        // Calculate average sales for dynamic safety stock
        $totalSales = $item['Sales_Year1'] + $item['Sales_Year2'];
        $averageSales = $totalSales / 2;

        // Calculate dynamic safety stock as 25% of average sales
        $safetyStock = max(5, ceil($averageSales * 0.25));
        
        // Calculate reorder level based on sales
        $reorderLevel = calculateReorderLevel($item['Sales_Year1'], $item['Sales_Year2'], $safetyStock);

        // Prepare update statement
        $update_sql = "
            UPDATE OnhandTb 
            SET RestockThreshold = :reorderLevel 
            WHERE OnhandID = :onhandID
        ";

        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bindParam(':reorderLevel', $reorderLevel);
        $update_stmt->bindParam(':onhandID', $item['OnhandID']);
        $update_stmt->execute();
    }
}

// Custom function to determine ReorderThreshold
function calculateReorderLevel($sales_year1, $sales_year2, $safetyStock) {
    $avg = ($sales_year1 + $sales_year2) / 2;
    return max($safetyStock, ceil($avg * 0.1)); // Ensures at least safety stock
}

// Execute the update function
try {
    updateOnhandLevels($conn);
    $_SESSION['alert'] = 'Restock Threshold levels updated successfully!';
    $_SESSION['alert_type'] = 'success';
} catch (Exception $e) {
    $_SESSION['alert'] = 'Error: Could not update onhand levels.';
    $_SESSION['alert_type'] = 'danger';
}

// Redirect to onhand view page
header("Location: onhand_read.php");
exit();
?>
