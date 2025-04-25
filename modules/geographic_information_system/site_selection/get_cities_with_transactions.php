<?php
// Include the database connection file
include_once('../../../config/database.php');

// Function to sanitize and validate province input
function getValidProvinces($provinceParam) {
    return array_filter(array_map('trim', explode(',', $provinceParam)));
}

// Function to execute a prepared query and fetch results
function fetchQueryResults($conn, $query, $params = []) {
    $stmt = $conn->prepare($query);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Function to calculate average yearly values
function calculateYearlyAverages($yearlyData, $isRevenue = true) {
    if (empty($yearlyData)) return 0; // Return 0 if no data

    $total = array_sum($yearlyData);
    $count = count($yearlyData);
    return $count > 0 ? round($total / $count, $isRevenue ? 2 : 0) : 0; // Avoid division by zero
}

try {
    // Check if the province parameter is set and valid
    if (isset($_GET['province']) && !empty($_GET['province'])) {
        $provinceArray = getValidProvinces($_GET['province']);

        if (empty($provinceArray)) {
            throw new Exception("No valid provinces found.");
        }

        // Prepare placeholders for the query
        $placeholders = rtrim(str_repeat('?,', count($provinceArray)), ',');

        // Query for cities, their LatLng, total revenue, and transaction count
        $query = "
            SELECT L.City, L.LatLng, SUM(T.TotalPrice) AS TotalRevenue, COUNT(T.TransacID) AS TransactionCount
            FROM LocationTb L
            JOIN TransacTb T ON L.LocationID = T.LocationID
            WHERE L.Province IN ($placeholders) 
            AND T.Status = 'Delivered'
            GROUP BY L.City, L.LatLng
            ORDER BY L.City
        ";

        // Fetch cities data
        $cities = fetchQueryResults($conn, $query, $provinceArray);

        if (empty($cities)) {
            throw new Exception("No cities found for the selected provinces.");
        }

        // Prepare to store forecast results for each city
        foreach ($cities as &$city) {
            // Get historical sales data for the specific city
            $historicalQuery = "
                SELECT YEAR(t.TransactionDate) AS Year, SUM(t.TotalPrice) AS Revenue, COUNT(t.TransacID) AS TransactionCount
                FROM TransacTb t
                JOIN LocationTb l ON t.LocationID = l.LocationID
                WHERE t.Status = 'Delivered' AND l.City = ? 
                AND YEAR(t.TransactionDate) >= (YEAR(CURDATE()) - 3) 
                GROUP BY Year
                ORDER BY Year
            ";

            // Fetch historical data for the city
            $historicalData = fetchQueryResults($conn, $historicalQuery, [$city['City']]);

            // Prepare historical sales data for forecasting
            $yearlyRevenue = [];
            $yearlyTransactions = [];
            foreach ($historicalData as $data) {
                $yearlyRevenue[$data['Year']] = $data['Revenue'];
                $yearlyTransactions[$data['Year']] = $data['TransactionCount'];
            }

            // Calculate averages for yearly revenue and transactions
            $averageYearlyRevenue = calculateYearlyAverages($yearlyRevenue);
            $averageYearlyTransactions = calculateYearlyAverages($yearlyTransactions, false);

            // Forecasting for the next 3 years
            $forecastedRevenue = [];
            $forecastedTransactions = [];
            $lastYear = date('Y');

            for ($i = 1; $i <= 3; $i++) {
                $nextYear = $lastYear + $i;
                $lastYearRevenue = $yearlyRevenue[$lastYear - $i] ?? 0;
                $lastYearTransactions = $yearlyTransactions[$lastYear - $i] ?? 0;

                // Example growth rates
                $revenueGrowthRate = 0.1; // 10%
                $transactionGrowthRate = 0.05; // 5%

                $forecastedRevenue[$nextYear] = round($lastYearRevenue * (1 + $revenueGrowthRate), 2);
                $forecastedTransactions[$nextYear] = round($lastYearTransactions * (1 + $transactionGrowthRate));
            }

            // Add forecasted values and historical data to the city
            $city['ForecastedRevenue'] = $forecastedRevenue;
            $city['ForecastedTransactions'] = $forecastedTransactions;
            $city['HistoricalRevenue'] = $yearlyRevenue; // Add historical revenue
            $city['HistoricalTransactions'] = $yearlyTransactions; // Add historical transactions
        }

        // Prepare result data
        $result = [
            'cities' => $cities,
            'totalRevenue' => array_sum(array_column($cities, 'TotalRevenue')),
            'totalTransactions' => array_sum(array_column($cities, 'TransactionCount')),
        ];

        // Return data as JSON
        header('Content-Type: application/json');
        echo json_encode($result);
    } else {
        echo json_encode(["error" => "Province parameter is missing or invalid."]);
    }
} catch (PDOException $e) {
    echo json_encode(["error" => "Database error: " . $e->getMessage()]);
} catch (Exception $e) {
    echo json_encode(["error" => $e->getMessage()]);
} finally {
    $conn = null;
}
?>
