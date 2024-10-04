// File: /modules/geographic_information_system/js/charts/forecast_graph.js

console.log("Forecast graph script loaded");

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
            console.log('Data fetched:', data); // Log the fetched data

            if (!data.data || data.data.length === 0) {
                console.error('No data available for forecasting');
                // Optionally, display a message on the chart area or handle no data case
                return;
            }

            const labels = [];
            const salesData = [];

            data.data.forEach(item => {
                const monthYear = `${item.month}/${item.year}`;
                labels.push(monthYear);
                salesData.push(parseFloat(item.total_sales));
            });

            const forecastedSales = [];
            const numPeriods = 3; // Moving average over 3 months
            for (let i = 0; i < salesData.length - numPeriods + 1; i++) {
                const average = salesData.slice(i, i + numPeriods).reduce((a, b) => a + b, 0) / numPeriods;
                forecastedSales.push(average);
            }

            const lastAverage = forecastedSales[forecastedSales.length - 1];
            forecastedSales.push(lastAverage, lastAverage, lastAverage); // Forecast next 3 months

            const ctx = document.getElementById('forecastChart').getContext('2d');
            if (forecastChart) {
                forecastChart.destroy();
            }

            // Update the chart with dynamic title based on selected province
            forecastChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels.concat(['Forecast Month 1', 'Forecast Month 2', 'Forecast Month 3']),
                    datasets: [{
                        label: 'Total Sales',
                        data: salesData.concat([lastAverage, lastAverage, lastAverage]),
                        backgroundColor: 'rgba(54, 162, 235, 0.2)',
                        borderColor: 'rgba(54, 162, 235, 1)',
                        borderWidth: 2,
                        fill: true
                    }]
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
