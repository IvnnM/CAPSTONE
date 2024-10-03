let transactionsChart;
let allTransactionsChart;
let cityTransactionsChart; // Keep this as a global variable

function formatCurrency(amount) {
    return '₱' + parseFloat(amount).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
}

function renderTotalTransactionsChart() {
    fetch(`../modules/geographic_information_system/get_all_provinces_transactions.php`)
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                console.error(`Error fetching transactions for total chart: ${data.error}`);
                return;
            }

            if (!allTransactionsChart) {
                const ctx = document.getElementById('allTransactionsChart').getContext('2d');
                allTransactionsChart = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: data.provinces || [], // Ensure it's an array
                        datasets: [{
                            label: 'Total Transactions by Province',
                            data: data.total_transactions.map(amount => parseFloat(amount)) || [], // Ensure it's an array
                            backgroundColor: 'rgba(75, 192, 192, 0.2)',
                            borderColor: 'rgba(75, 192, 192, 1)',
                            borderWidth: 1
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
                allTransactionsChart.data.labels = data.provinces || [];
                allTransactionsChart.data.datasets[0].data = data.total_transactions.map(amount => parseFloat(amount)) || [];
                allTransactionsChart.update();
            }
        })
        .catch(error => {
            console.error('Error fetching transactions for total chart:', error);
        });
}

function renderTransactionsChart(province) {
    fetch(`../modules/geographic_information_system/get_province_transactions.php?province=${province}`)
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                console.error(`Error fetching transactions for chart: ${data.error}`);
                return;
            }

            // Prepare data for the line chart
            const labels = data.transactions.map(transaction => transaction.date); // Assuming 'date' is a property in your data
            const totalTransactions = data.transactions.map(transaction => parseFloat(transaction.total) || 0); // Assuming 'total' is a property in your data

            if (!transactionsChart) {
                const ctx = document.getElementById('transactionsChart').getContext('2d');
                transactionsChart = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: 'Total Transactions',
                            data: totalTransactions,
                            backgroundColor: 'rgba(255, 99, 132, 0.2)',
                            borderColor: 'rgba(255, 99, 132, 1)',
                            borderWidth: 2,
                            fill: true // Fill under the line
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
                                    text: 'Date'
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
                transactionsChart.data.labels = labels;
                transactionsChart.data.datasets[0].data = totalTransactions;
                transactionsChart.update();
            }
        })
        .catch(error => {
            console.error('Error fetching transactions for chart:', error);
        });
}

// New function to render the city transactions chart
function renderCityTransactionsChart(labels, data, city) {
    const ctx = document.getElementById('cityTransactionsChart').getContext('2d');

    // If the chart already exists, update it; otherwise, create a new one
    if (cityTransactionsChart) {
        cityTransactionsChart.data.labels = labels;
        cityTransactionsChart.data.datasets[0].data = data;
        cityTransactionsChart.update();
    } else {
        cityTransactionsChart = new Chart(ctx, {
            type: 'line', // Change this to 'bar' if you prefer a bar chart
            data: {
                labels: labels,
                datasets: [{
                    label: 'Total Transactions in ' + city,
                    data: data,
                    backgroundColor: 'rgba(255, 99, 132, 0.2)',
                    borderColor: 'rgba(255, 99, 132, 1)',
                    borderWidth: 2,
                    fill: true // Fill under the line
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
                            text: 'Date'
                        }
                    }
                },
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const label = context.dataset.label || '';
                                const value = '₱' + context.raw.toFixed(2);
                                return label + ': ' + value;
                            }
                        }
                    }
                }
            }
        });
    }
}
