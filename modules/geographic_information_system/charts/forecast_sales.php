<?php
session_start();
include("../../../config/database.php");

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json'); // Ensure only JSON is output

try {
    // Get historical sales data for the last two years
    $query = "
        SELECT YEAR(t.TransactionDate) AS Year, MONTH(t.TransactionDate) AS Month, SUM(t.TotalPrice) AS Revenue
        FROM TransacTb t
        WHERE t.Status = 'Delivered' AND YEAR(t.TransactionDate) IN (YEAR(CURDATE()), YEAR(CURDATE()) - 1)
        GROUP BY Year, Month
        ORDER BY Year, Month
    ";

    // Prepare and execute the query
    $stmt = $conn->prepare($query);
    $stmt->execute();

    // Fetch historical data
    $historicalData = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!$historicalData) {
        echo json_encode(["error" => "No historical data found."]);
        exit;
    }

    // Prepare historical sales for forecasting
    $monthlyRevenue = array_fill(1, 12, [0, 0]); // Placeholder for 12 months with last year's and this year's revenue

    foreach ($historicalData as $data) {
        $monthlyRevenue[$data['Month']][$data['Year'] == date('Y') ? 1 : 0] = $data['Revenue'];
    }

    // Calculate average seasonal trend
    $seasonalTrend = [];
    for ($month = 1; $month <= 12; $month++) {
        $seasonalTrend[$month] = ($monthlyRevenue[$month][0] + $monthlyRevenue[$month][1]) / 2;
    }

    // Forecast for the next three months based on seasonal trend
    $forecastedData = [];
    for ($i = 1; $i <= 3; $i++) {
        $nextMonth = (date('n') + $i) % 12; // Forecast next month
        if ($nextMonth === 0) $nextMonth = 12; // Adjust for month 0 (December)
        
        // Check if the seasonal trend for the next month exists before calculating
        if (isset($seasonalTrend[$nextMonth])) {
            $forecastedData[$nextMonth] = round($seasonalTrend[$nextMonth] * 1.1, 2); // Increase by 10% for forecasting
        } else {
            $forecastedData[$nextMonth] = 0; // Default to 0 if not set
        }
    }

    // Array of month names
    $monthNames = [
        1 => 'January', 2 => 'February', 3 => 'March',
        4 => 'April', 5 => 'May', 6 => 'June',
        7 => 'July', 8 => 'August', 9 => 'September',
        10 => 'October', 11 => 'November', 12 => 'December'
    ];

    // Combine historical and forecasted data
    $result = [];
    
    // Add all months to the result, including months with no historical data
    foreach ($monthNames as $monthNumber => $monthName) {
        $historicalRevenue = $monthlyRevenue[$monthNumber][1] ?? 0; // Current year's revenue or 0 if not set
        $revenueType = $historicalRevenue > 0 ? 'Historical' : 'No Data';

        // Add current year revenue or no data for historical months
        $result[] = [
            'Month' => $monthName,
            'Revenue' => $historicalRevenue,
            'Type' => $revenueType
        ];
    }

    // Add forecasted data to the result
    foreach ($forecastedData as $month => $revenue) {
        $result[$month - 1]['Revenue'] = $revenue; // Replace the revenue for forecasted months
        $result[$month - 1]['Type'] = 'Forecast'; // Mark as forecast
    }

    // Send JSON response
    echo json_encode($result); // Output JSON response

} catch (PDOException $e) {
    // Log the error message for debugging
    error_log($e->getMessage());
    // Send a JSON response with error
    echo json_encode(["error" => "Database error occurred."]);
    exit; // Ensure nothing else is sent after this
}
