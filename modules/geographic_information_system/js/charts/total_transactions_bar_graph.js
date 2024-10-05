let allTransactionsChart;

function formatCurrency(amount) {
    return '₱' + parseFloat(amount).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
}

function renderTotalTransactionsBarGraph() {
    // Fetch all provinces' transactions
    fetch(`../modules/geographic_information_system/php/get_all_provinces_transactions.php`)
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
                        labels: data.provinces || [],
                        datasets: [{
                            label: 'Total Transactions by Province',
                            data: data.total_transactions.map(amount => parseFloat(amount)) || [],
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
