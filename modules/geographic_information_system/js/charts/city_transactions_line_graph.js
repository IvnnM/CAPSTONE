let cityTransactionsChart;

function formatCurrency(amount) {
    return '₱' + parseFloat(amount).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
}

function renderCityTransactionsLineGraph(labels, data, city, groupByYear = false) {
    let groupedData;

    if (groupByYear) {
        // Group the data by year if groupByYear is true
        groupedData = labels.reduce((acc, label, index) => {
            const year = new Date(label).getFullYear(); // Extract year
            
            if (!acc[year]) {
                acc[year] = 0; // Initialize the year if it doesn't exist
            }
            acc[year] += data[index]; // Sum the total amounts for the same year
            return acc;
        }, {});
    } else {
        // Group the data by month and year
        groupedData = labels.reduce((acc, label, index) => {
            const date = new Date(label);
            const yearMonth = `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}`; // Format as YYYY-MM
            
            if (!acc[yearMonth]) {
                acc[yearMonth] = 0; // Initialize the month if it doesn't exist
            }
            acc[yearMonth] += data[index]; // Sum the total amounts for the same month
            return acc;
        }, {});
    }

    // Extract the grouped year-months (or years) and their corresponding total amounts
    const labelsGrouped = Object.keys(groupedData);
    const totalAmountsGrouped = Object.values(groupedData);

    // Sort the labels (years or months) and their corresponding data
    const sortedData = labelsGrouped
        .map((label, index) => ({ label, amount: totalAmountsGrouped[index] })) // Create an array of objects with label and amount
        .sort((a, b) => new Date(a.label) - new Date(b.label)); // Sort by date (works for both years and months)

    // Separate the sorted labels and amounts
    const sortedLabels = sortedData.map(item => item.label);
    const sortedTotalAmounts = sortedData.map(item => item.amount);

    // Create or update the chart
    const ctx = document.getElementById('cityTransactionsChart').getContext('2d');
    if (cityTransactionsChart) {
        cityTransactionsChart.destroy(); // Destroy the existing chart instance if it exists
    }
    cityTransactionsChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: sortedLabels, // Use the sorted labels (either years or months)
            datasets: [{
                label: groupByYear ? 'Total Transactions by Year in ' + city : 'Total Transactions in ' + city,
                data: sortedTotalAmounts, // Use the sorted total amounts
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
                        text: groupByYear ? 'Year' : 'Year & Month'
                    },
                    ticks: {
                        autoSkip: true,
                        maxTicksLimit: groupByYear ? 10 : 12, // Adjust the number of ticks depending on view
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
function filterByYear(labels, data, city) {
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
        renderCityTransactionsLineGraph(filteredLabels, filteredData, city, false); // Month view
    } else {
        // No year selected, group data by year
        renderCityTransactionsLineGraph(labels, data, city, true); // Yearly view
    }
}
