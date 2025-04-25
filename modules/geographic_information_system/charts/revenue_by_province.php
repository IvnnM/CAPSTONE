<script>
    // Fetch data from the server-side script
    fetch('./charts/get_revenue_data.php')
        .then(response => response.json())
        .then(data => {
            const labels = data.map(item => item.Province);
            const revenueData = data.map(item => item.Revenue);

            // Create the chart
            const ctx = document.getElementById('revenueChart').getContext('2d');
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Revenue',
                        data: revenueData,
                        backgroundColor: 'rgba(75, 192, 192, 0.2)',
                        borderColor: 'rgba(75, 192, 192, 1)',
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
