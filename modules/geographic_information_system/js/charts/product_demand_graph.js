let productDemandChart;

function renderProductDemandGraph(year) {
    // Get the selected province
    const selectedProvince = document.getElementById('province').value;

    // Build the API URL based on whether a year is provided
    let url = `../modules/geographic_information_system/php/get_product_demand.php?`;
    if (year) {
        url += `year=${year}`;
    }
    if (selectedProvince) {
        url += year ? `&province=${selectedProvince}` : `province=${selectedProvince}`;
    }

    // Fetch product demand data
    fetch(url)
        .then(response => response.json())
        .then(data => {
            if (!data.products || data.products.length === 0) {
                productDemandChart.data.labels = [];
                productDemandChart.data.datasets[0].data = [];
                productDemandChart.update();
                
                // Update title to indicate no data
                productDemandChart.options.plugins.title.text = 'No Product Demand Data Available';
                return; // Exit if no data found
            }

            // Extract product names and quantities
            const productNames = data.products.map(p => p.ProductName);
            const productQuantities = data.products.map(p => parseFloat(p.total_quantity));

            // Initialize the chart if it's not created yet
            if (!productDemandChart) {
                const ctx = document.getElementById('productDemandChart').getContext('2d');
                productDemandChart = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: productNames,
                        datasets: [{
                            label: 'Total Quantity Sold',
                            data: productQuantities,
                            backgroundColor: 'rgba(54, 162, 235, 0.6)',
                            borderColor: 'rgba(54, 162, 235, 1)',
                            borderWidth: 1
                        }]
                    },
                    options: {
                        scales: {
                            y: {
                                beginAtZero: true
                            },
                            x: {
                                title: {
                                    display: true,
                                    text: 'Products'
                                }
                            }
                        },
                        plugins: {
                            tooltip: {
                                callbacks: {
                                    label: function (context) {
                                        return `Total Sold: ${context.raw}`;
                                    }
                                }
                            },
                            title: {
                                display: true,
                                text: `Top Selling Products${(year || selectedProvince) ? ` for ${selectedProvince} in ${year}` : ''}`
                            }
                        }
                    }
                });
            } else {
                // Update chart data
                productDemandChart.data.labels = productNames;
                productDemandChart.data.datasets[0].data = productQuantities;

                // Update the chart title based on selected province and year
                productDemandChart.options.plugins.title.text = `Top Selling Products${(year || selectedProvince) ? ` for ${selectedProvince} in ${year}` : ' in All Products'}`;

                productDemandChart.update();
            }
        })
        .catch(error => {
            console.error('Error fetching product demand data:', error);
        });
}

// Add event listeners after DOM is loaded
document.addEventListener('DOMContentLoaded', function () {
    // Event listener for year dropdown
    document.getElementById('year').addEventListener('change', function () {
        const selectedYear = this.value; // Get the selected year
        renderProductDemandGraph(selectedYear); // Call your graph rendering function
    });

    // Event listener for province changes
    document.getElementById('province').addEventListener('change', function () {
        const selectedYear = document.getElementById('year').value; // Get the selected year
        renderProductDemandGraph(selectedYear); // Update chart based on new province selection
    });

    // Initial rendering on page load
    const initialYear = document.getElementById('year').value; // Get the initial selected year
    renderProductDemandGraph(initialYear); // Call your graph rendering function
});
