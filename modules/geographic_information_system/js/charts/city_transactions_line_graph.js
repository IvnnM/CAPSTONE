let cityTransactionsChart;

function formatCurrency(amount) {
    return '₱' + parseFloat(amount).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
}

function renderCityTransactionsLineGraph(labels, data, city) {
    // Group the data by year
    const yearlyData = labels.reduce((acc, label, index) => {
        const year = new Date(label).getFullYear();
        if (!acc[year]) {
            acc[year] = 0; // Initialize the year if it doesn't exist
        }
        acc[year] += data[index]; // Sum the total amounts for the same year
        return acc;
    }, {});

    // Extract the grouped years and their corresponding total amounts
    const yearLabels = Object.keys(yearlyData);
    const totalAmountsByYear = Object.values(yearlyData);

    const ctx = document.getElementById('cityTransactionsChart').getContext('2d');
    if (cityTransactionsChart) {
        cityTransactionsChart.destroy(); // Destroy the existing chart instance if it exists
    }
    cityTransactionsChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: yearLabels, // Use the grouped year labels
            datasets: [{
                label: 'Total Transactions in ' + city,
                data: totalAmountsByYear, // Use the grouped total amounts
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
                        text: 'Year'
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
