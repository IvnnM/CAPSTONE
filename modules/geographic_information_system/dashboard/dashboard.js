document.addEventListener('DOMContentLoaded', function() {
    fetchInitialData();
});

async function fetchInitialData() {
    try {
        const response = await fetch('../modules/geographic_information_system/dashboard/fetch_data.php');
        const data = await response.json();
        
        // Populate year dropdown
        const yearSelect = document.getElementById('yearSelect');
        
        // Add "All Years" option first
        const allYearsOption = document.createElement('option');
        allYearsOption.value = 'all';
        allYearsOption.textContent = 'All Years';
        yearSelect.appendChild(allYearsOption);

        // Add specific years
        data.available_years.forEach(year => {
            const option = document.createElement('option');
            option.value = year;
            option.textContent = year;
            if (year === data.selected_year) {
                option.selected = true;
            }
            yearSelect.appendChild(option);
        });

        // Add event listener for year changes
        yearSelect.addEventListener('change', function() {
            fetchDashboardData(this.value);
        });

        // Initial data update
        updateDashboardData(data);
    } catch (error) {
        console.error('Error fetching initial data:', error);
    }
}

async function fetchDashboardData(year) {
    try {
        // If 'all' is selected, don't pass a year parameter
        const url = year === 'all' 
            ? '../modules/geographic_information_system/dashboard/fetch_data.php'
            : `../modules/geographic_information_system/dashboard/fetch_data.php?year=${year}`;
        
        const response = await fetch(url);
        const data = await response.json();
        updateDashboardData(data);
    } catch (error) {
        console.error('Error fetching dashboard data:', error);
    }
}

function updateDashboardData(data) {
    updateStatistics(data.stats);
    createQuarterlySalesTrendChart(data.quarterly_revenue);
    createProvinceCharts(data.province_sales);
    updateLowStockTable(data.low_stock);
    // New chart functions
    createTopSellingProductsChart(data.top_selling_products);
    createLeastSellingProductsChart(data.least_selling_products);
}

function updateStatistics(stats) {
    document.getElementById('total-transactions').textContent = stats.total_transactions;
    document.getElementById('total-revenue').textContent = formatCurrency(stats.total_revenue);
    document.getElementById('pending-orders').textContent = stats.pending_orders;
    document.getElementById('to-ship-orders').textContent = stats.to_ship_orders;
    document.getElementById('delivered-orders').textContent = stats.delivered_orders;
}

function createQuarterlySalesTrendChart(data) {
    const existingChart = Chart.getChart('revenue-chart');
    if (existingChart) {
        existingChart.destroy();
    }
    const ctx = document.getElementById('revenue-chart').getContext('2d');
    
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: data.map(item => `${item.quarter} ${item.year}`),
            datasets: [{
                label: 'Quarterly Revenue',
                data: data.map(item => item.revenue),
                borderColor: 'rgb(75, 192, 192)',
                backgroundColor: 'rgba(75, 192, 192, 0.1)',
                borderWidth: 2,
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            plugins: {
                title: {
                    display: true,
                    text: 'Quarterly Sales Trend',
                    font: {
                        size: 16,
                        weight: 'bold'
                    },
                    padding: 20
                },
                legend: {
                    display: true,
                    position: 'top'
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return 'Revenue: ' + formatCurrency(context.raw);
                        },
                        title: function(tooltipItems) {
                            return tooltipItems[0].label;
                        }
                    }
                }
            },
            scales: {
                x: {
                    title: {
                        display: true,
                        text: 'Quarter',
                        font: {
                            weight: 'bold'
                        }
                    },
                    grid: {
                        display: false
                    }
                },
                y: {
                    title: {
                        display: true,
                        text: 'Revenue (PHP)',
                        font: {
                            weight: 'bold'
                        }
                    },
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return formatCurrency(value);
                        }
                    }
                }
            },
            interaction: {
                intersect: false,
                mode: 'index'
            }
        }
    });
}

function createProvinceCharts(data) {
    // Clear existing chart if any
    const existingChart = Chart.getChart('province-pie-chart');
    if (existingChart) {
        existingChart.destroy();
    }
    // Group data by province
    const provinceData = data.reduce((acc, item) => {
        if (!acc[item.Province]) {
            acc[item.Province] = {
                total_revenue: 0,
                order_count: 0
            };
        }
        acc[item.Province].total_revenue += Number(item.total_revenue);
        acc[item.Province].order_count += Number(item.order_count);
        return acc;
    }, {});

    // Calculate total revenue and percentages
    const totalRevenue = Object.values(provinceData)
        .reduce((sum, item) => sum + item.total_revenue, 0);

    // Prepare data for charts
    const provinces = Object.keys(provinceData);
    const revenues = Object.values(provinceData).map(item => item.total_revenue);
    const percentages = revenues.map(rev => ((rev / totalRevenue) * 100).toFixed(1));

    // Colors for consistent styling
    const colors = [
        'rgb(54, 162, 235)',
        'rgb(75, 192, 192)',
        'rgb(255, 205, 86)',
        'rgb(255, 99, 132)',
        'rgb(153, 102, 255)',
        'rgb(255, 159, 64)'
    ];

    // Create pie chart and progress bars
    createProvincePieChart(provinces, revenues, percentages, colors);
    createProvinceProgressBars(provinces, revenues, percentages, colors, totalRevenue);
}

function createProvincePieChart(provinces, revenues, percentages, colors) {
    const ctx = document.getElementById('province-pie-chart').getContext('2d');

    new Chart(ctx, {
        type: 'pie',
        data: {
            labels: provinces.map((province, index) => `${province} (${percentages[index]}%)`),
            datasets: [{
                data: revenues,
                backgroundColor: colors,
                borderColor: 'white',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    display: false,  // Remove legend
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return `Revenue: ${formatCurrency(context.raw)}`;
                        }
                    }
                },
                datalabels: {
                    color: '#003366',
                    font: {
                        weight: 'bold',
                        size: 16
                    },
                    formatter: (value, context) => {
                        const total = revenues.reduce((sum, val) => sum + val, 0);
                        const percentage = ((value / total) * 100).toFixed(1);

                        // Hide label if percentage is 0
                        return percentage > 0 ? `${percentage}%` : '';
                    }
                }
            }
        },
        plugins: [ChartDataLabels]  // Ensure the plugin is included
    });
}

function createTopSellingProductsChart(data) {
    // Clear existing chart if any
    const existingChart = Chart.getChart('top-selling-products-chart');
    if (existingChart) {
        existingChart.destroy();
    }

    const ctx = document.getElementById('top-selling-products-chart').getContext('2d');
    
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: data.map(item => item.ProductName),
            datasets: [{
                label: 'Quantity Sold',
                data: data.map(item => item.total_quantity_sold),
                backgroundColor: 'rgba(75, 192, 192, 0.6)',
                borderColor: 'rgba(75, 192, 192, 1)',
                borderWidth: 1
            }, {
                label: 'Total Revenue',
                data: data.map(item => item.total_revenue),
                backgroundColor: 'rgba(54, 162, 235, 0.6)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                title: {
                    display: true,
                    text: 'Top Selling Products',
                    font: {
                        size: 16,
                        weight: 'bold'
                    }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const value = context.raw;
                            return context.dataset.label === 'Total Revenue' 
                                ? formatCurrency(value)
                                : value;
                        }
                    }
                }
            },
            scales: {
                y: {
                    title: {
                        display: true,
                        text: 'Quantity / Revenue'
                    },
                    ticks: {
                        callback: function(value, index, values) {
                            return this.chart.data.datasets[0].label === 'Total Revenue' 
                                ? formatCurrency(value)
                                : value;
                        }
                    }
                }
            }
        }
    });
}

function createLeastSellingProductsChart(data) {
    // Clear existing chart if any
    const existingChart = Chart.getChart('least-selling-products-chart');
    if (existingChart) {
        existingChart.destroy();
    }

    const ctx = document.getElementById('least-selling-products-chart').getContext('2d');
    
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: data.map(item => item.ProductName),
            datasets: [{
                label: 'Quantity Sold',
                data: data.map(item => item.total_quantity_sold),
                backgroundColor: 'rgba(255, 99, 132, 0.6)',
                borderColor: 'rgba(255, 99, 132, 1)',
                borderWidth: 1
            }, {
                label: 'Total Revenue',
                data: data.map(item => item.total_revenue),
                backgroundColor: 'rgba(255, 205, 86, 0.6)',
                borderColor: 'rgba(255, 205, 86, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                title: {
                    display: true,
                    text: 'Least Selling Products',
                    font: {
                        size: 16,
                        weight: 'bold'
                    }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const value = context.raw;
                            return context.dataset.label === 'Total Revenue' 
                                ? formatCurrency(value)
                                : value;
                        }
                    }
                }
            },
            scales: {
                y: {
                    title: {
                        display: true,
                        text: 'Quantity / Revenue'
                    },
                    ticks: {
                        callback: function(value, index, values) {
                            return this.chart.data.datasets[0].label === 'Total Revenue' 
                                ? formatCurrency(value)
                                : value;
                        }
                    }
                }
            }
        }
    });
}

function createProvinceProgressBars(provinces, revenues, percentages, colors, totalRevenue) {
    const container = document.getElementById('province-progress-bars');
    container.innerHTML = '';

    provinces.forEach((province, index) => {
        const progressBarContainer = document.createElement('div');
        progressBarContainer.className = 'progress-bar-container';
        progressBarContainer.style.marginBottom = '20px';

        // Create label and percentage display
        const labelDiv = document.createElement('div');
        labelDiv.style.display = 'flex';
        labelDiv.style.justifyContent = 'space-between';
        labelDiv.style.marginBottom = '5px';

        const label = document.createElement('span');
        label.textContent = province;

        const value = document.createElement('span');
        value.textContent = formatCurrency(revenues[index]);

        labelDiv.appendChild(label);
        labelDiv.appendChild(value);

        // Create progress bar
        const progressBarBg = document.createElement('div');
        progressBarBg.style.backgroundColor = '#f0f0f0';
        progressBarBg.style.borderRadius = '4px';
        progressBarBg.style.height = '20px';
        progressBarBg.style.overflow = 'hidden';

        const progressBar = document.createElement('div');
        progressBar.style.width = `${percentages[index]}%`;
        progressBar.style.backgroundColor = colors[index];
        progressBar.style.height = '100%';
        progressBar.style.borderRadius = '4px';
        progressBar.style.transition = 'width 1s ease-in-out';

        // Create percentage label
        const percentageLabel = document.createElement('div');
        percentageLabel.textContent = `${percentages[index]}%`;
        percentageLabel.style.fontSize = '12px';
        percentageLabel.style.color = '#666';
        percentageLabel.style.marginTop = '5px';

        // Assemble all elements
        progressBarBg.appendChild(progressBar);
        progressBarContainer.appendChild(labelDiv);
        progressBarContainer.appendChild(progressBarBg);
        progressBarContainer.appendChild(percentageLabel);
        container.appendChild(progressBarContainer);
    });
}

function updateLowStockTable(data) {
    const tbody = document.getElementById('low-stock-body');
    tbody.innerHTML = '';
    
    data.forEach(item => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td class="border px-4 py-2">${item.InventoryID}</td>
            <td class="border px-4 py-2">${item.OnhandQty}</td>
            <td class="border px-4 py-2">${formatCurrency(item.RetailPrice)}</td>
            <td class="border px-4 py-2">${item.RestockThreshold}</td>
        `;
        tbody.appendChild(row);
    });
}

function formatCurrency(amount) {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'PHP'
    }).format(amount);
}