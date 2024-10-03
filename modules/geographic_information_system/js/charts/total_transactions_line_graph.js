let transactionsChart;

function formatCurrency(amount) {
    return '₱' + parseFloat(amount).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
}

function renderTotalTransactionsLineGraph() {
    // Fetch all transactions
    const url = '../modules/geographic_information_system/php/get_all_transactions.php';

    fetch(url)
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                console.error(`Error fetching transactions for chart: ${data.error}`);
                return;
            }

            const labels = data.transactions.map(transaction => transaction.date);
            const totalTransactions = data.transactions.map(transaction => parseFloat(transaction.total) || 0);

            // Group data by year
            const yearlyData = labels.reduce((acc, label, index) => {
                const year = new Date(label).getFullYear();
                if (!acc[year]) {
                    acc[year] = 0; // Initialize the year if it doesn't exist
                }
                acc[year] += totalTransactions[index]; // Sum the total amounts for the same year
                return acc;
            }, {});

            // Extract the grouped years and their corresponding total amounts
            const yearLabels = Object.keys(yearlyData);
            const totalAmountsByYear = Object.values(yearlyData);

            if (!transactionsChart) {
                const ctx = document.getElementById('transactionsChart').getContext('2d');
                transactionsChart = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: yearLabels, // Use the grouped year labels
                        datasets: [{
                            label: 'Total Transactions Over Time',
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
                                    callback: function (value) {
                                        return formatCurrency(value);
                                    }
                                }
                            },
                            x: {
                                title: {
                                    display: true,
                                    text: 'Year' // Updated to show "Year"
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
                            }
                        }
                    }
                });
            } else {
                transactionsChart.data.labels = yearLabels;
                transactionsChart.data.datasets[0].data = totalAmountsByYear;
                transactionsChart.update();
            }
        })
        .catch(error => {
            console.error('Error fetching transactions for chart:', error);
        });
}
