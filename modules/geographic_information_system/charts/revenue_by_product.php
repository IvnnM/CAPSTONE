<script>
    // Fetch data from the server-side script for Revenue by Product
    fetch('./charts/get_revenue_by_product.php')
        .then(response => response.json())
        .then(data => {
            const labels = data.map(item => item.ProductName);
            const revenueData = data.map(item => item.Revenue);

            // Create the chart for Revenue by Product
            const ctx = document.getElementById('revenueProductChart').getContext('2d');
            new Chart(ctx, {
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
        .catch(error => console.error('Error fetching data:', error));
</script>
