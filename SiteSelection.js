class SiteSelection {
    constructor(salesData) {
        this.salesData = salesData;
        
        const rankedLocations = this.rankLocationsBySiteSelection();
        this.createDataTable(rankedLocations);
        this.renderTop10ForecastChart();
    }

    calculateStatistics() {
        const locationData = [];
        
        for (const province in this.salesData) {
            for (const city in this.salesData[province]) {
                const cityData = this.salesData[province][city];
                
                // Enhanced profitability calculation
                const forecasts = cityData?.forecasts?.sarima || [];
                const errorMetrics = cityData?.forecasts?.error_metrics || {};
                
                const totalProfitability = forecasts.reduce((sum, value) => sum + value, 0);
                const averageProfitability = totalProfitability / forecasts.length;
                
                locationData.push({
                    province,
                    city,
                    totalProfitability,
                    averageProfitability,
                    mape: errorMetrics.mape || 0,
                    mae: errorMetrics.mae || 0,
                    rmse: errorMetrics.rmse || 0
                });
            }
        }

        return {
            profitability: {
                min: Math.min(...locationData.map(d => d.totalProfitability)),
                max: Math.max(...locationData.map(d => d.totalProfitability))
            },
            locations: locationData
        };
    }

    calculateSiteSelectionScore(locationData) {
        const { totalProfitability, mape, mae, rmse } = locationData;
        const stats = this.calculateStatistics();
        
        // Normalize profitability with log transformation
        const normalizedProfitability = Math.log1p(totalProfitability - stats.profitability.min + 1) / 
                                        Math.log1p(stats.profitability.max - stats.profitability.min + 1);

        // Penalize locations with higher forecast error
        const errorPenalty = 1 - (
            (mape / 100) * 0.4 +  // MAPE has the most weight
            (mae / Math.max(...stats.locations.map(l => l.mae || 1))) * 0.3 +
            (rmse / Math.max(...stats.locations.map(l => l.rmse || 1))) * 0.3
        );

        // Combine profitability and error penalty
        const combinedScore = normalizedProfitability * errorPenalty;

        // Convert to percentage score
        return Math.min(combinedScore * 100, 99.9);
    }

    rankLocationsBySiteSelection() {
        const stats = this.calculateStatistics();
        
        return stats.locations.map(location => ({
            ...location,
            siteSelectionPercentage: this.calculateSiteSelectionScore(location)
        })).sort((a, b) => b.siteSelectionPercentage - a.siteSelectionPercentage);
    }

    createDataTable(rankedLocations) {
        if (!document.getElementById('siteSelectionTable')) {
            const container = document.createElement('div');
            container.className = 'mt-4';
            container.innerHTML = `
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Ranked Locations by Profitability</h3>
                    </div>
                    <div class="card-body">
                        <table id="siteSelectionTable" class="table table-striped table-bordered">
                            <thead>
                                <tr>
                                    <th>Rank</th>
                                    <th>Province</th>
                                    <th>City</th>
                                    <th>Annual Profitability</th>
                                    <th>Avg Monthly Profitability</th>
                                    <th>Site Selection Score</th>
                                    <th>Forecast Error (MAPE)</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            `;
            document.getElementById('top10Metrics').appendChild(container);
        }

        $(document).ready(() => {
            $('#siteSelectionTable').DataTable({
                data: rankedLocations.map((location, index) => [
                    index + 1,
                    location.province,
                    location.city,
                    location.totalProfitability.toLocaleString('en-US', {
                        style: 'currency',
                        currency: 'PHP',
                        minimumFractionDigits: 0,
                        maximumFractionDigits: 0
                    }),
                    location.averageProfitability.toLocaleString('en-US', {
                        style: 'currency',
                        currency: 'PHP',
                        minimumFractionDigits: 0,
                        maximumFractionDigits: 0
                    }),
                    location.siteSelectionPercentage.toFixed(2) + '%',
                    location.mape.toFixed(2) + '%'
                ]),
                pageLength: 10,
                order: [[5, 'desc']],
                responsive: true,
                dom: 'Bfrtip',
                buttons: ['copy', 'csv', 'excel', 'pdf', 'print']
            });
        });
    }

    renderTop10ForecastChart() {
        const rankedLocations = this.rankLocationsBySiteSelection().slice(0, 3);
        const monthNames = [
            'January', 'February', 'March', 'April', 'May', 'June',
            'July', 'August', 'September', 'October', 'November', 'December'
        ];
    
        // Define hardcoded colors for the top 3 cities
        const hardcodedColors = [
            'rgba(255, 99, 132, 1)', // Red for the 1st city
            'rgba(54, 162, 235, 1)', // Blue for the 2nd city
            'rgba(0, 255, 0, 1)'  // Green for the 3rd city
        ];
    
        const datasets = rankedLocations.map((location, index) => ({
            label: `${location.city}, ${location.province}`,
            data: this.salesData[location.province][location.city]?.forecasts?.sarima || [],
            borderColor: hardcodedColors[index] || 'rgba(0,0,0,0.7)', // Use hardcoded colors or default to black
            backgroundColor: 'rgba(0,0,0,0)',
            tension: 0.4,
        }));
    
        const canvas = document.getElementById('top3ForecastChart');
        if (!canvas) {
            console.error('Chart canvas not found!');
            return;
        }
    
        if (this.forecastChart) {
            this.forecastChart.destroy();
        }
    
        this.forecastChart = new Chart(canvas, {
            type: 'line',
            data: {
                labels: monthNames,
                datasets
            },
            options: {
                responsive: true,
                plugins: {
                    title: {
                        display: true,
                        text: 'Top 3 Ranked Locations - Forecasted Revenue (Monthly)',
                        font: {
                            size: 16,
                            weight: 'bold'
                        }
                    },
                    legend: {
                        position: 'top'
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const value = context.parsed.y;
                                return `₱${value.toLocaleString(undefined, {
                                    minimumFractionDigits: 2,
                                    maximumFractionDigits: 2
                                })}`;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Forecasted Revenue (₱)'
                        },
                        ticks: {
                            callback: function(value) {
                                return '₱' + value.toLocaleString();
                            }
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Month'
                        }
                    }
                }
            }
        });
    }

}

export default SiteSelection;