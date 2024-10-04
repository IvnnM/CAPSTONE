let forecastChart;

function formatCurrency(amount) {
    return '₱' + parseFloat(amount).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
}

function renderForecastChart() {
    const provinceSelect = document.getElementById('province');
    const selectedProvince = provinceSelect.value;
    const url = `../modules/geographic_information_system/php/get_forecast_data.php?province=${selectedProvince || 'ALL'}`;

    fetch(url)
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (!data.data || data.data.length === 0) {
                console.error('No data available for forecasting');
                return;
            }

            const labels = [];
            const salesData = [];
            const monthlySales = {}; // Store total sales by month for seasonal index

            // Gather historical data
            data.data.forEach(item => {
                const monthYear = `${item.month}/${item.year}`;
                labels.push(monthYear);
                const sales = parseFloat(item.total_sales);
                salesData.push(sales);

                // Accumulate sales for each month (e.g., all sales in October over multiple years)
                if (!monthlySales[item.month]) {
                    monthlySales[item.month] = [];
                }
                monthlySales[item.month].push(sales);
            });

            // Calculate seasonal index: average sales for each month
            const seasonalIndex = {};
            for (const month in monthlySales) {
                const total = monthlySales[month].reduce((a, b) => a + b, 0);
                seasonalIndex[month] = total / monthlySales[month].length; // Average sales for this month
            }

            // Moving average forecast with seasonal adjustment
            const forecastedSales = [];
            const numPeriods = 3; // Moving average over 3 months
            const totalDataPoints = salesData.length;

            for (let i = 0; i < totalDataPoints; i++) {
                if (i >= numPeriods - 1) {
                    // Basic moving average
                    const average = salesData.slice(i - numPeriods + 1, i + 1).reduce((a, b) => a + b, 0) / numPeriods;
                    const month = labels[i].split('/')[0]; // Extract month from label
                    const adjustedForecast = average * (seasonalIndex[month] || 1); // Adjust by seasonal index
                    forecastedSales.push(adjustedForecast);
                } else {
                    forecastedSales.push(null); // Not enough data points for the average
                }
            }

            // Forecast for the next 3 months, applying seasonality
            const lastMonthYear = labels[labels.length - 1].split('/');
            let forecastMonth = parseInt(lastMonthYear[0], 10); // Current month
            let forecastYear = parseInt(lastMonthYear[1], 10); // Current year
            const forecastLabels = [];
            const nextSalesData = [];

            for (let i = 0; i < 3; i++) {
                forecastMonth++;
                if (forecastMonth > 12) {
                    forecastMonth = 1;
                    forecastYear++;
                }
                forecastLabels.push(`${forecastMonth}/${forecastYear}`);

                // Apply the seasonal index for the forecasted months
                const seasonalMultiplier = seasonalIndex[forecastMonth] || 1;
                const forecastValue = forecastedSales[forecastedSales.length - 1] * seasonalMultiplier;
                nextSalesData.push(forecastValue);
            }

            const ctx = document.getElementById('forecastChart').getContext('2d');
            if (forecastChart) {
                forecastChart.destroy();
            }

            // Create the chart with historical and forecast data, using different colors for forecasted months
            forecastChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels.concat(forecastLabels), // Historical + forecast labels
                    datasets: [
                        {
                            label: 'Historical Sales',
                            data: salesData, // Only historical data
                            backgroundColor: 'rgba(54, 162, 235, 0.2)',
                            borderColor: 'rgba(54, 162, 235, 1)', // Color for historical data
                            borderWidth: 2,
                            fill: false
                        },
                        {
                            label: 'Forecasted Sales',
                            data: forecastedSales.concat(nextSalesData), // Combined historical forecast and next months
                            backgroundColor: 'rgba(255, 99, 132, 0.2)',
                            borderColor: 'rgba(255, 99, 132, 1)', // Different color for forecasted data
                            borderDash: [5, 5], // Dashed line for forecasted data
                            borderWidth: 2,
                            fill: false
                        }
                    ]
                },
                options: {
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function (value) {
                                    return formatCurrency(value);
                                }
                            }
                        },
                        x: {
                            title: {
                                display: true,
                                text: 'Month/Year'
                            }
                        }
                    },
                    plugins: {
                        tooltip: {
                            callbacks: {
                                label: function (context) {
                                    const label = context.dataset.label || '';
                                    const value = formatCurrency(context.raw);
                                    return label + ': ' + value;
                                }
                            }
                        },
                        title: {
                            display: true,
                            text: selectedProvince ? `Sales Forecast for ${selectedProvince}` : 'Sales Forecast for All Provinces'
                        }
                    }
                }
            });
        })
        .catch(error => {
            console.error('Error fetching forecast data:', error);
        });
}

// Wait for the DOM to load before attaching event listeners
document.addEventListener('DOMContentLoaded', function () {
    const provinceSelect = document.getElementById('province');
    if (provinceSelect) {
        provinceSelect.addEventListener('change', function () {
            renderForecastChart(); // Call the function to render the chart when the province changes
        });
    }
    
    // Call to render the chart on page load
    renderForecastChart(); // Ensure the chart is rendered when the page loads
});
