let transactionsChart;

function formatCurrency(amount) {
    return '₱' + parseFloat(amount).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
}

function renderTotalTransactionsLineGraph(year) {
    // Get the selected province
    const selectedProvince = document.getElementById('province').value;

    // Determine the URL based on whether a year is provided
    const url = year 
        ? `../modules/geographic_information_system/php/get_transactions_by_year.php?year=${year}&province=${selectedProvince}` 
        : `../modules/geographic_information_system/php/get_all_transactions.php?province=${selectedProvince}`; // Fetch all transactions when no year is selected

    // Fetch transactions
    fetch(url)
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                console.error(`Error fetching transactions for chart: ${data.error}`);
                return;
            }

            // Clear chart if no transactions found
            if (!data.transactions || data.transactions.length === 0) {
                transactionsChart.data.labels = Array(12).fill('');
                transactionsChart.data.datasets[0].data = Array(12).fill(0);
                transactionsChart.update();
                return; // Exit if no transactions are found
            }

            // Initialize labels and totalTransactions array for 12 months
            let labels = Array(12).fill('');
            let totalTransactions = Array(12).fill(0); // Initialize for 12 months

            // Process the transactions to fill labels and totalTransactions
            data.transactions.forEach(transaction => {
                const monthIndex = transaction.month - 1; // Adjust to 0-based index
                labels[monthIndex] = transaction.month_name; // Use month name
                totalTransactions[monthIndex] += parseFloat(transaction.total) || 0; // Accumulate totals
            });

            if (!transactionsChart) {
                const ctx = document.getElementById('transactionsChart').getContext('2d');
                transactionsChart = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: labels, // Use the processed labels
                        datasets: [{
                            label: 'Sales',
                            data: totalTransactions, // Use the processed total amounts
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
                                    callback: function (value) {
                                        return formatCurrency(value);
                                    }
                                }
                            },
                            x: {
                                title: {
                                    display: true,
                                    text: 'Month' // Always show "Month" for this graph
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
                                text: 'Overall Revenue' // Default title before selections are made
                            }
                        }
                    }
                });
            } else {
                // Update chart data
                transactionsChart.data.labels = labels;
                transactionsChart.data.datasets[0].data = totalTransactions;

                // Update chart title dynamically only if a year is selected
                if (year) {
                    transactionsChart.options.plugins.title.text = `Annual income for ${selectedProvince} in ${year}`;
                } else {
                    transactionsChart.options.plugins.title.text = ` Total Revenue Over Time`; // Without year
                }

                transactionsChart.update();
            }
        })
        .catch(error => {
            console.error('Error fetching transactions for chart:', error);
        });
}

// Add event listeners after DOM is loaded
document.addEventListener('DOMContentLoaded', function () {
    // Event listener for year dropdown
    document.getElementById('year').addEventListener('change', function () {
        const selectedYear = this.value; // Get the selected year
        console.log('Selected Year:', selectedYear); // Log the selected year
        renderTotalTransactionsLineGraph(selectedYear); // Call your graph rendering function
    });

    // Event listener for province changes
    document.getElementById('province').addEventListener('change', function () {
        const selectedYear = document.getElementById('year').value; // Get the selected year
        renderTotalTransactionsLineGraph(selectedYear); // Update chart based on new province selection
    });
});
