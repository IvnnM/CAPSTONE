<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Charts</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/4.6.0/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            background-color: #f8f9fa; /* Light background for better contrast */
        }
        .container {
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container position-relative bg-white text-dark rounded shadow p-4">
        <div class="row mb-3">
            <div class="col-md-4 p-2">
                <label for="yearDropdown" class="form-label">Select Year:</label>
                <select id="yearDropdown" class="form-select">
                    <!-- Year options will be populated here -->
                </select>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 p-2">
              <h2>Low Stock Products</h2>
              <canvas id="lowStockChart" class="border"></canvas> <!-- Canvas for low stock chart -->
            </div>
            <div class="col-md-6 p-2">
                <h2>Revenue by Product</h2>
                <canvas id="revenueProductChart" class="border"></canvas> <!-- Canvas for the revenue by product chart -->
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-6 p-2">  
                <h2>Revenue by Province</h2>
                <canvas id="revenueChart" class="border"></canvas> <!-- Canvas for the revenue by province chart -->
            </div>
            <div class="col-md-6 p-2">
                <h2>Sales Trend</h2>
                <canvas id="salesTrendChart" class="border"></canvas> <!-- Canvas for sales trend chart -->
            </div>
        </div>
        
        <div class="row">
            <div class="col-12 p-2">
                <h2>Quarterly Seasonal Sales Forecast</h2>
                <canvas id="forecastChart" class="border" width="400" height="200"></canvas><!-- Canvas for Forecast -->
            </div>
        </div>
    </div>

    <!-- Include the new JavaScript files -->
    <script src="../modules/geographic_information_system/charts/charts.js"></script>
    <script src="../modules/geographic_information_system/charts/forecast.js"></script>
</body>
</html>
