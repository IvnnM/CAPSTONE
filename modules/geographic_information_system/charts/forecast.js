const forecastChartCtx = document.getElementById('forecastChart').getContext('2d');
let forecastChart;

// Function to update the forecast chart
function updateForecastChart() {
    // Fetch data from forecast_sales.php
    fetch('../modules/geographic_information_system/charts/forecast_sales.php')
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json(); // Expecting JSON response
        })
        .then(data => {
            // Check if the data is an array
            if (!Array.isArray(data)) {
                throw new TypeError('Expected data to be an array');
            }

            console.log(data); // Log the raw data to check for issues

            const months = []; // For month names
            const revenues = []; // Combined historical and forecasted revenue data
            const backgroundColors = []; // For different background colors

            // Separate historical and forecast data
            data.forEach(item => {
                months.push(item.Month); // Add month names
                revenues.push(item.Revenue); // Add both historical and forecast revenue

                // Set the color for each data point
                if (item.Type === 'Historical') {
                    backgroundColors.push('rgba(75, 192, 192, 0.2)'); // Historical color
                } else if (item.Type === 'Forecast') {
                    backgroundColors.push('rgba(255, 99, 132, 0.2)'); // Forecast color
                }
            });

            // Debugging: Check the lengths of the revenue arrays
            console.log("Revenues:", revenues);
            console.log("Months:", months);

            // Create or update the chart
            if (forecastChart) {
                forecastChart.destroy(); // Destroy the existing chart if it exists
            }

            forecastChart = new Chart(forecastChartCtx, {
                type: 'line',
                data: {
                    labels: months, // Month labels for both historical and forecast
                    datasets: [
                        {
                            label: 'Revenue',
                            data: revenues, // Combined data for historical and forecast
                            borderColor: function(context) {
                                const index = context.dataIndex;
                                const revenueType = data[index]?.Type; // Use optional chaining
                                return revenueType === 'Forecast' ? 'rgba(255, 99, 132, 1)' : 'rgba(75, 192, 192, 1)';
                            },
                            backgroundColor: backgroundColors, // Use the background colors array
                            fill: true, // Fill under the line for better visibility
                            borderDash: function(context) {
                                const index = context.dataIndex;
                                return data[index]?.Type === 'Forecast' ? [5, 5] : []; // Dotted line for forecast
                            },
                            pointRadius: 5, // Show points for clarity
                        }
                    ]
                },
                options: {
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    },
                    elements: {
                        line: {
                            tension: 0.4 // Smooth the line between points
                        }
                    }
                }
            });
        })
        .catch(error => console.error('Error fetching forecast data:', error));
}

// Call to update charts on page load
document.addEventListener('DOMContentLoaded', function() {
    updateForecastChart(); // Load forecast chart on page load
});
