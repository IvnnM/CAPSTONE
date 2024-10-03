let cityTransactionsChart;

function formatCurrency(amount) {
    return '₱' + parseFloat(amount).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
}

function renderCityTransactionsLineGraph(labels, data, city) {
    // Group the data by month and year
    const monthlyData = labels.reduce((acc, label, index) => {
        const date = new Date(label);
        const yearMonth = `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}`; // Format as YYYY-MM
        
        if (!acc[yearMonth]) {
            acc[yearMonth] = 0; // Initialize the month if it doesn't exist
        }
        acc[yearMonth] += data[index]; // Sum the total amounts for the same month
        return acc;
    }, {});

    // Extract the grouped year-months and their corresponding total amounts
    const monthLabels = Object.keys(monthlyData);
    const totalAmountsByMonth = Object.values(monthlyData);

    const ctx = document.getElementById('cityTransactionsChart').getContext('2d');
    if (cityTransactionsChart) {
        cityTransactionsChart.destroy(); // Destroy the existing chart instance if it exists
    }
    cityTransactionsChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: monthLabels, // Use the grouped month labels
            datasets: [{
                label: 'Total Transactions in ' + city,
                data: totalAmountsByMonth, // Use the grouped total amounts
                backgroundColor: 'rgba(255, 99, 132, 0.2)',
                borderColor: 'rgba(255, 99, 132, 1)',
                borderWidth: 2,
                fill: true
            }]
        },
        options: {
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return formatCurrency(value); // Format the y-axis values
                        }
                    }
                },
                x: {
                    title: {
                        display: true,
                        text: 'Month'
                    },
                    ticks: {
                        autoSkip: true,
                        maxTicksLimit: 12, // Show only 12 ticks (months)
                    }
                }
            },
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const label = context.dataset.label || '';
                            const value = formatCurrency(context.raw);
                            return label + ': ' + value;
                        }
                    }
                }
            }
        }
    });
}

// Function to filter transactions by year
function filterByYear() {
    const selectedYear = document.getElementById('year').value;
    const filteredLabels = [];
    const filteredData = [];

    if (selectedYear) {
        // Filter data based on the selected year
        labels.forEach((label, index) => {
            const year = new Date(label).getFullYear();
            if (year === parseInt(selectedYear)) {
                filteredLabels.push(label);
                filteredData.push(data[index]);
            }
        });
    } else {
        // No year selected, use all data
        filteredLabels.push(...labels);
        filteredData.push(...data);
    }

    // Render the chart with filtered data
    renderCityTransactionsLineGraph(filteredLabels, filteredData, '<?php echo htmlspecialchars($city); ?>');
}
