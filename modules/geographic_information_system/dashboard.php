<?php
// /modules/geographic_information_system/dashboard.php
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Geographic Information System</title>
    <!-- Leaflet CSS and JS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/leaflet.css" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/leaflet.js"></script>
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- Your JavaScript Files -->
    <script src="../modules/geographic_information_system/js/map/gis_map.js"></script>
    <script src="../modules/geographic_information_system/js/dropdown_functions.js"></script>
    <script src="../modules/geographic_information_system/js/map/map_functions.js"></script>
    <script src="../modules/geographic_information_system/js/charts/total_transactions_line_graph.js"></script>
    <script src="../modules/geographic_information_system/js/charts/total_transactions_bar_graph.js"></script>
    <script src="../modules/geographic_information_system/js/charts/forecast_graph.js"></script>
    <script src="../modules/geographic_information_system/js/charts/product_demand_graph.js"></script>

    <!-- Additional styling -->
    <style>
        body {
            color: white;
        }
        .p-2 {
            padding: 1rem;
        }
        #map {
            width: 100%;
            height: 400px; /* Adjust height as necessary */
        }
        .col-6, .col-12 {
            border: solid white;
            border-radius: 14px;
        }
        .dropdown-container {
            margin-bottom: 10px;
        }

        /* Custom styles to ensure responsive height */
        #forecastChart {
            height: 300px; /* Default height for smaller screens */
        }

        @media (min-width: 768px) {
            #forecastChart {
                max-height: 400px; /* Height for larger screens */
            }
        }
    </style>
</head>
<body>

<div class="container-fluid text-center p-0">
  
  <div class="row g-2">
    
    <!-- Two columns at the top -->
    <div class="col-md-5 col-12">
      <div class="p-2">
      </div> <!-- Left side: blank -->
    </div>
    <div class="col-md-7 col-12">
      <div class="p-2">
        <canvas id="allTransactionsChart" width="400px" height="200px"></canvas>
      </div>
    </div>

    <!-- Two columns in the middle -->
    <div class="col-md-7 col-12">
      <div class="p-2">
        <!-- Map container with two rows -->
        <label>CALABARZON</label>
        <div id="map" style="height: 450px; border-radius: 12px;"></div> <!-- Map -->
        <hr style="border-top: 1px solid white;">
        <div>
          <!-- Dropdowns for Province and City -->
          <div class="dropdown-container">
            <label for="province">Select Province:</label>
            <select id="province" class="form-control">
              <option value="">--Select Province--</option>
            </select>
          </div>
          <div class="dropdown-container">
            <label for="city">Select City:</label>
            <select id="city" class="form-control">
              <option value="">--Select City--</option>
            </select>
          </div>
        </div>
      </div>
    </div>
    <div class="col-md-5 col-12">
      <div class="p-2">
        <div>
          <div class="dropdown-container">
            <label for="year">Select Year:</label>
            <select id="year" class="form-control">
                <option value="">--Select Year--</option>
            </select>
          </div>
        </div>
        <hr style="border-top: 1px solid white;">
        <div><canvas id="productDemandChart" width="400" height="250"></canvas></div>
        <hr style="border-top: 1px solid white;">
        <div> <canvas id="transactionsChart" width="400" height="250"></canvas></div>
      </div>
    </div>
    <!-- One column at the bottom -->
    <div class="col-12">
      <div class="p-2">
        <label>Sales Forecast</label>
        <div class="row">
          <div class="col-md-8 offset-md-2"> <!-- Center the chart on larger screens -->
            <canvas id="forecastChart" class="w-100" style="height: 400px;"></canvas> <!-- Full width and responsive height -->
          </div>
        </div>
      </div> 
    </div>




  </div>
</div>

<script>
  // Call the function to populate years when the page loads
  document.addEventListener('DOMContentLoaded', function() {
      const yearDropdown = document.getElementById('year'); // Assuming you have an element with id='year'
      fetchAndPopulateYears(yearDropdown); // Pass the dropdown element to the function
  });

  // Function to reset all dropdowns to default
  function resetDropdowns() {
      document.getElementById('year').selectedIndex = 0; // Reset year dropdown
      document.getElementById('province').selectedIndex = 0; // Reset province dropdown
      document.getElementById('city').selectedIndex = 0; // Reset city dropdown
      renderTotalTransactionsLineGraph(null); // Reset chart
  }

  document.getElementById('year').addEventListener('change', function () {
      const selectedYear = this.value; // Get the selected year
      console.log('Selected Year:', selectedYear); // Log the selected year
      
      // Reset all dropdowns if the default option is selected
      if (selectedYear === '') { // Assuming empty string is the default
          resetDropdowns();
          return;
      }

      renderTotalTransactionsLineGraph(selectedYear); // Call your graph rendering function
  });

  document.getElementById('province').addEventListener('change', function () {
      const selectedProvince = this.value; // Get the selected province
      
      // Reset year dropdown to default option whenever a province is selected
      document.getElementById('year').selectedIndex = 0; // Reset year dropdown

      // Reset all dropdowns if the default option is selected
      if (selectedProvince === '') { // Assuming empty string is the default
          resetDropdowns();
          return;
      }

      const selectedYear = document.getElementById('year').value; // Get the selected year
      renderTotalTransactionsLineGraph(selectedYear); // Update chart based on new province selection
  });

</script>


</body>
</html>
