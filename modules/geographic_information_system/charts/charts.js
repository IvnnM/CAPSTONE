const yearDropdown = document.getElementById('yearDropdown');
let revenueChart;
let revenueProductChart;
let lowStockChart;
let salesTrendChart;

// Fetch available years from the database
fetch('../modules/geographic_information_system/charts/get_available_years.php')
    .then(response => response.json())
    .then(data => {
        // Populate dropdown with years
        data.forEach(year => {
            const option = document.createElement('option');
            option.value = year.Year; // Assuming your JSON has a 'Year' key
            option.textContent = year.Year;
            yearDropdown.appendChild(option);
        });

        // Initial load for the default year (if needed)
        updateCharts(yearDropdown.value);
    })
    .catch(error => console.error('Error fetching years:', error));

// Add event listener to dropdown to update charts on year change
yearDropdown.addEventListener('change', function() {
    const selectedYear = this.value;
    updateCharts(selectedYear);
});

function updateCharts(year) {
    // Update the revenue by province chart
    fetch(`../modules/geographic_information_system/charts/get_revenue_data.php?year=${year}`)
    .then(response => response.json())
    .then(data => {
        const labels = data.map(item => item.Province);
        const percentageData = data.map(item => item.Percentage); // Use Percentage data instead of Revenue
        const ctx = document.getElementById('revenueChart').getContext('2d');

        // Destroy the existing chart if it exists
        if (revenueChart) {
            revenueChart.destroy();
        }

        // Generate unique, professional-looking colors for each province
        const colors = [
            '#4e79a7', '#f28e2b', '#e15759', '#76b7b2', '#59a14f', '#edc948',
            '#b07aa1', '#ff9da7', '#9c755f', '#bab0ac', '#86bcdb', '#c79fef'
        ];

        // Create a new chart
        revenueChart = new Chart(ctx, {
            type: 'pie',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Revenue by Province (%)',
                    data: percentageData, // Pass the percentage data to the chart
                    backgroundColor: colors.slice(0, labels.length), // Use only as many colors as needed
                    borderColor: '#ffffff',
                    borderWidth: 2,
                    hoverOffset: 8
                }]
            },
            options: {
                plugins: {
                    legend: {
                        position: 'right',
                        labels: {
                            boxWidth: 15,
                            padding: 20,
                            font: {
                                family: 'Arial',
                                size: 14,
                                weight: 'bold'
                            },
                            color: '#333'
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const percentage = context.raw.toFixed(2); // Format percentage to 2 decimal places
                                return `${context.label}: ${percentage}%`;
                            }
                        }
                    }
                }
            }
        });
    })
    .catch(error => console.error('Error fetching revenue data:', error));

    // Update the revenue by product chart
    fetch(`../modules/geographic_information_system/charts/get_revenue_by_product.php?year=${year}`)
        .then(response => response.json())
        .then(data => {
            const labels = data.map(item => item.ProductName);
            const revenueData = data.map(item => item.Revenue);
            const ctx = document.getElementById('revenueProductChart').getContext('2d');

            // Destroy the existing chart if it exists
            if (revenueProductChart) {
                revenueProductChart.destroy();
            }

            // Create a new chart
            revenueProductChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Revenue by Product',
                        data: revenueData,
                        backgroundColor: 'rgba(153, 102, 255, 0.2)',
                        borderColor: 'rgba(153, 102, 255, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        })
        .catch(error => console.error('Error fetching product revenue data:', error));
    // Update the low stock chart
    fetch('../modules/geographic_information_system/charts/get_low_stock.php')
    .then(response => response.json())
    .then(data => {
        const labels = data.map(item => item.ProductName);
        const stockData = data.map(item => item.OnhandQty);
        const ctx = document.getElementById('lowStockChart').getContext('2d');

        // Destroy the existing chart if it exists
        if (lowStockChart) {
            lowStockChart.destroy();
        }

        // Create a new chart
        lowStockChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Low Stock Quantity',
                    data: stockData,
                    backgroundColor: 'rgba(255, 99, 132, 0.2)',
                    borderColor: 'rgba(255, 99, 132, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    })
    .catch(error => console.error('Error fetching low stock data:', error));

    // Update the sales trend chart
fetch(`../modules/geographic_information_system/charts/get_sales_trend.php?year=${year}`)
.then(response => {
    if (!response.ok) {
        throw new Error('Network response was not ok');
    }
    return response.json();
})
.then(data => {
    // Check if data is empty
    if (!Array.isArray(data) || data.length === 0) {
        console.warn('No sales trend data available for the selected year.');
        // Optionally, you could handle the empty case by clearing the chart or displaying a message
        const ctx = document.getElementById('salesTrendChart').getContext('2d');
        salesTrendChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: [], // No labels if no data
                datasets: [{
                    label: 'Sales Trend',
                    data: [], // No data
                    backgroundColor: 'rgba(54, 162, 235, 0.2)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 2,
                    fill: true,
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
        return; // Exit the function early
    }

    const labels = data.map(item => item.Month); // Extract month names
    const revenueData = data.map(item => item.Revenue); // Extract revenue data
    const ctx = document.getElementById('salesTrendChart').getContext('2d');

    // Destroy the existing chart if it exists
    if (salesTrendChart) {
        salesTrendChart.destroy();
    }

    // Create a new chart
    salesTrendChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Sales Trend',
                data: revenueData,
                backgroundColor: 'rgba(54, 162, 235, 0.2)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 2,
                fill: true,
            }]
        },
        options: {
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
})
.catch(error => console.error('Error fetching sales trend data:', error));

}
