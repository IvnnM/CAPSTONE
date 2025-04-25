import SiteSelection from './SiteSelection.js';
class SalesDashboard {
    constructor() {
        this.map = null;
        this.geographicData = null;
        this.salesData = null;
        this.selectedProvince = null;
        this.selectedCity = null;
        this.salesChart = null;
        this.patternChart = null;
        this.choroplethLevel = 'city'; // Default level
        this.choroplethMetric = 'revenue'; // Default metric
        this.baseLayers = {};
        this.markers = [];
        this.populationDensityData = {};

        this.init();

        const modalContainer = document.createElement('div');
        modalContainer.id = 'errorModal';
        modalContainer.style.display = 'none';
        modalContainer.innerHTML = `
            <div class="modal-overlay" style="
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background-color: rgba(0, 0, 0, 0.5);
                display: flex;
                justify-content: center;
                align-items: center;
                z-index: 1000;
            ">
                <div class="modal-content" style="
                    background: white;
                    padding: 2rem;
                    border-radius: 8px;
                    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
                    max-width: 500px;
                    width: 90%;
                    position: relative;
                    animation: slideIn 0.3s ease-out;
                ">
                    <div class="modal-header" style="
                        display: flex;
                        align-items: center;
                        margin-bottom: 1rem;
                    ">
                        <div class="warning-icon" style="
                            background: #FEF3C7;
                            padding: 0.75rem;
                            border-radius: 50%;
                            margin-right: 1rem;
                        ">
                            ⚠️
                        </div>
                        <h3 style="
                            font-size: 1.25rem;
                            font-weight: 600;
                            color: #1F2937;
                            margin: 0;
                        ">No Data Available</h3>
                    </div>
                    <div class="modal-body" style="margin-bottom: 1.5rem;">
                        <p style="
                            color: #4B5563;
                            margin-bottom: 1rem;
                        ">We currently don't have any sales data for <span class="city-name" style="font-weight: 600;"></span>. This could be because:</p>
                        <ul style="
                            color: #4B5563;
                            margin-left: 1.5rem;
                            list-style-type: disc;
                        ">
                            <li>No transactions have been recorded in this location yet</li>
                        </ul>
                    </div>
                    <div class="modal-footer" style="
                        display: flex;
                        justify-content: flex-end;
                    ">
                        <button onclick="document.getElementById('errorModal').style.display='none'" style="
                            background: #2563EB;
                            color: white;
                            padding: 0.5rem 1rem;
                            border: none;
                            border-radius: 4px;
                            cursor: pointer;
                            font-weight: 500;
                            transition: background-color 0.2s;
                        ">Close</button>
                    </div>
                </div>
            </div>
            <style>
                @keyframes slideIn {
                    from {
                        transform: translateY(-20px);
                        opacity: 0;
                    }
                    to {
                        transform: translateY(0);
                        opacity: 1;
                    }
                }
                
                .modal-content button:hover {
                    background: #1D4ED8;
                }
            </style>
        `;
        document.body.appendChild(modalContainer);
    }

    // Replace the existing showError method with this enhanced version
    showError(message, cityName = '') {
        const modal = document.getElementById('errorModal');
        if (modal) {
            const cityNameSpan = modal.querySelector('.city-name');
            if (cityNameSpan) {
                cityNameSpan.textContent = cityName;
            }
            modal.style.display = 'block';

            // Hide charts container
            const chartsContainer = document.getElementById('charts');
            if (chartsContainer) {
                chartsContainer.style.display = 'none';
            }

            // Add click event to close modal when clicking outside
            modal.querySelector('.modal-overlay').addEventListener('click', (e) => {
                if (e.target.classList.contains('modal-overlay')) {
                    modal.style.display = 'none';
                }
            });

            // Add escape key listener
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape') {
                    modal.style.display = 'none';
                }
            });
        }
    }

    async init() {
        console.log('Initializing dashboard...');
        await this.loadData();
        this.initializeMap();
        this.setupEventListeners();
        
        const chartsContainer = document.getElementById('charts');
        if (chartsContainer) {
            chartsContainer.style.display = 'none';
        }
        
        await this.calculateAllPopulationDensities();
    
        console.log('SalesJS.Population Density Data:', this.populationDensityData);
    
        // Pass populationDensityData to SiteSelection
        const siteSelection = new SiteSelection(this.salesData, this.geographicData, this.populationDensityData);
        siteSelection.renderTop10ForecastChart();
    
        this.addChoroplethControl();
    }

    async loadData() {
        try {
            const [geographicResponse, salesResponse] = await Promise.all([
                fetch('get_geographic_patterns.php'),
                fetch('get_sales_data.php')
            ]);
            
            this.geographicData = await geographicResponse.json();
            this.salesData = await salesResponse.json();
            
            console.log('Data loaded:', {
                geographic: this.geographicData,
                sales: this.salesData
            });
        } catch (error) {
            console.error('Error loading data:', error);
            // Add visible error message for users
            this.showError('Failed to load dashboard data. Please refresh the page.');
        }
    }

    async loadModel() {
        try {
            if (this.selectedProvince && this.selectedCity) {
                const provinceKey = this.selectedProvince.replace(/\s+/g, '_').toUpperCase();
                const cityKey = this.selectedCity.trim().toUpperCase();

                const salesData = this.salesData[provinceKey] ? this.salesData[provinceKey][cityKey] : null;
                const geographicData = this.geographicData.find(
                    item => item.province.toUpperCase() === this.selectedProvince.toUpperCase() &&
                            item.city.toUpperCase() === this.selectedCity.toUpperCase()
                );

                if (salesData && geographicData) {
                    const modelPath = `/3CAPSTONE/predictive/tfjs_models/${provinceKey}/${cityKey.replace(/\s+/g, '_')}/model.json`;
                    this.model = await tf.loadLayersModel(modelPath);
                    console.log('Model loaded successfully for', provinceKey, cityKey);
                    
                } else {
                    console.warn('Sales or Geographic Data missing for', provinceKey, cityKey);
                }
            }
        } catch (error) {
            console.error('Error loading model:', error);
        }
    }
    
    initializeMap() {
        console.log('Initializing map...');
        this.map = L.map('map', {
            preferCanvas: true
        }).setView([14.6760, 121.0437], 9);

        // Initialize base layers
        this.initializeBaseLayers();
        
        // Add map controls
        this.addMapControls();

        // Add search control
        this.addSearchControl();

        // Load province GeoJSON data
        this.loadProvinceData();
    }

    initializeBaseLayers() {
        // OpenStreetMap layer
        this.baseLayers.osm = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '© OpenStreetMap contributors'
        }).addTo(this.map);

        // Google Streets layer
        this.baseLayers.googleStreets = L.tileLayer('http://{s}.google.com/vt/lyrs=m&x={x}&y={y}&z={z}', {
            maxZoom: 20,
            subdomains: ['mt0', 'mt1', 'mt2', 'mt3'],
            attribution: '&copy; <a href="https://www.google.com/intl/en_us/help/terms_maps.html">Google</a>'
        });

        // Google Satellite layer
        this.baseLayers.googleSat = L.tileLayer('http://{s}.google.com/vt/lyrs=s&x={x}&y={y}&z={z}', {
            maxZoom: 20,
            subdomains: ['mt0', 'mt1', 'mt2', 'mt3']
        });
    }

    addMapControls() {
        // Add layer control
        L.control.layers({
            "OpenStreetMap": this.baseLayers.osm,
            "Google Streets": this.baseLayers.googleStreets,
            "Satellite": this.baseLayers.googleSat
        }).addTo(this.map);

        // Add scale control
        L.control.scale({
            imperial: false,
            position: 'bottomleft'
        }).addTo(this.map);

        // Add reset view control
        const ResetViewControl = L.Control.extend({
            options: {
                position: 'topleft'
            },
            onAdd: (map) => {
                const button = L.DomUtil.create('button', 'reset-view-btn');
                button.innerHTML = 'Reset View';
                L.DomEvent.on(button, 'click', () => {
                    this.resetView();
                });
                return button;
            }
        });
        
        new ResetViewControl().addTo(this.map);
    }

    addSearchControl() {
        const geocoder = L.Control.geocoder({
            defaultMarkGeocode: false,
            placeholder: 'Search address or place...',
            errorMessage: 'Nothing found.',
            suggestMinLength: 3,
            suggestTimeout: 250,
            queryMinLength: 1,
            geocoder: L.Control.Geocoder.nominatim({
                geocodingQueryParams: {
                    viewbox: '120.5,13.5,122.5,14.5',
                    bounded: 1,
                    countrycodes: 'ph'
                }
            })
        }).addTo(this.map);

        geocoder.on('markgeocode', (e) => {
            this.handleSearchResult(e);
        });
    }

    handleSearchResult(e) {
        const latlng = e.geocode.center;
        
        // Check if location is within bounds
        if (this.isLocationWithinBounds(latlng)) {
            this.clearMarkers();
            
            // Add marker for the location
            const marker = L.marker(latlng).addTo(this.map)
                .bindPopup(`<strong>${e.geocode.name}</strong>`)
                .openPopup();
            
            this.markers.push(marker);

            // Zoom to location
            this.map.flyTo(latlng, 14, {
                duration: 1.5,
                easeLinearity: 0.25
            });
        } else {
            alert('Location is outside the specified region.');
        }
    }

    isLocationWithinBounds(latlng) {
        // Define the bounds for CALABARZON region
        const bounds = L.latLngBounds(
            [13.5, 120.5], // Southwest corner
            [14.5, 122.5]  // Northeast corner
        );
        return bounds.contains(latlng);
    }

    clearMarkers() {
        this.markers.forEach(marker => {
            this.map.removeLayer(marker);
        });
        this.markers = [];
    }

    resetView() {
        this.clearMarkers();
        this.map.setView([14.6760, 121.0437], 9);
        
        // Reset any other state as needed
        if (this.selectedProvince || this.selectedCity) {
            this.selectedProvince = null;
            this.selectedCity = null;
            // Update UI elements if necessary
        }
    }
    async loadProvinceData() {
        const provinces = ['BATANGAS', 'CAVITE', 'LAGUNA', 'QUEZON', 'RIZAL'];
        for (const province of provinces) {
            try {
                console.log(`Loading GeoJSON for ${province}...`);
                const response = await fetch(`./geojsonData/${province}.geojson`);
                const geojson = await response.json();
                
                L.geoJSON(geojson, {
                    style: (feature) => this.getStyle(feature),
                    onEachFeature: (feature, layer) => this.onEachFeature(feature, layer)
                }).addTo(this.map);
            } catch (error) {
                console.error(`Error loading ${province} GeoJSON:`, error);
            }
        }
    }

    onEachFeature(feature, layer) {
        const province = feature.properties.PROVINCE;
        const city = feature.properties.CITY || 
                    feature.properties.MUNICIPALITY || 
                    feature.properties.MUNICIPALI;
                    
        const provinceData = this.getProvinceData(province);
        const cityData = this.getCityData(province, city);
        const density = this.calculatePopulationDensity(feature);
        
        let popupContent = `<strong>Province:</strong> ${province}<br>`;
        popupContent += `<strong>City:</strong> ${city}<br>`;
        
        if (provinceData) {
            popupContent += `<strong>Province Revenue:</strong> ₱${provinceData.metrics.province_metrics.total_revenue.toLocaleString()}<br>`;
        }
        
        if (cityData) {
            popupContent += `<strong>City Revenue:</strong> ₱${cityData.metrics.city_metrics.total_revenue.toLocaleString()}<br>`;
        }

        if (density) {
            popupContent += `<strong>Population Density:</strong> ${density.toFixed(2)} people/km²<br>`;
        }
        
        layer.bindPopup(popupContent);

        layer.on({
            mouseover: (e) => {
                const layer = e.target;
                layer.setStyle({
                    weight: 3,
                    fillOpacity: 0.9
                });
                layer.bringToFront();
            },
            mouseout: (e) => {
                const layer = e.target;
                layer.setStyle(this.getStyle(feature));
            },
            click: async (e) => {
                const properties = e.target.feature.properties;
                const clickedCity = properties.CITY || 
                                  properties.MUNICIPALITY || 
                                  properties.MUNICIPALI;

                if (properties.PROVINCE && clickedCity) {
                    const cleanedCity = this.cleanCityName(clickedCity);
                    await this.selectArea(properties.PROVINCE, cleanedCity);
                }
            }
        });
    }

    getProvinceData(province) {
        if (!this.geographicData) return null;
        return this.geographicData.find(d => d.province === province);
    }

    getCityData(province, city) {
        if (!this.geographicData) return null;
        return this.geographicData.find(d => 
            d.province.toUpperCase() === province.toUpperCase() && 
            d.city.toUpperCase() === city.toUpperCase()
        );
    }

    addChoroplethControl() {
        const control = L.control({ position: 'topright' });
        
        control.onAdd = () => {
            const div = L.DomUtil.create('div', 'choropleth-control');
            div.innerHTML = `
                <div style="background: white; padding: 10px; border-radius: 5px; box-shadow: 0 1px 5px rgba(0,0,0,0.4);">
                    <label style="display: block; margin-bottom: 5px;">Choropleth Level:</label>
                    <select id="choroplethLevel" style="width: 100%; padding: 5px; margin-bottom: 10px;">
                        <option value="city">City</option>
                        <option value="province">Province</option>
                    </select>
                    <label style="display: block; margin-bottom: 5px;">Metric:</label>
                    <select id="choroplethMetric" style="width: 100%; padding: 5px;">
                        <option value="revenue">Revenue</option>
                        <option value="density">Population Density</option>
                    </select>
                </div>
            `;
            return div;
        };

        control.addTo(this.map);

        // Add event listeners
        document.getElementById('choroplethLevel').addEventListener('change', (e) => {
            this.choroplethLevel = e.target.value;
            this.refreshMap();
        });

        document.getElementById('choroplethMetric').addEventListener('change', (e) => {
            this.choroplethMetric = e.target.value;
            this.refreshMap();
        });
    }

    calculatePopulationDensity(feature) {
        const population = feature.properties.TOTPOP2010;
        const landArea = feature.properties.LAND_AREA2; // in hectares
        if (population && landArea) {
            // Convert hectares to square kilometers (1 hectare = 0.01 square kilometers)
            const areaInSqKm = landArea * 0.01;
            return population / areaInSqKm;
        }
        return null;
    }

    getColor(data, level, feature) {
        if (this.choroplethMetric === 'revenue') {
            if (!data) return '#cccccc';
            
            let revenue;
            if (level === 'province') {
                revenue = data.metrics.province_metrics.total_revenue;
            } else {
                revenue = data.metrics.city_metrics.total_revenue;
            }

            const thresholds = level === 'province' 
                ? [70000, 100000, 150000, 200000]  // Province thresholds
                : [50000, 70000, 90000, 120000];   // City thresholds

            return revenue > thresholds[3] ? '#084081' :
                   revenue > thresholds[2] ? '#0868ac' :
                   revenue > thresholds[1] ? '#2b8cbe' :
                   revenue > thresholds[0] ? '#4eb3d3' :
                                           '#7bccc4';
        } else if (this.choroplethMetric === 'density') {
            const density = this.calculatePopulationDensity(feature);
            if (!density) return '#cccccc';

            // Thresholds for population density (people per square km)
            const thresholds = [1000, 2000, 5000, 10000];

            return density > thresholds[3] ? '#bd0026' :
                   density > thresholds[2] ? '#f03b20' :
                   density > thresholds[1] ? '#fd8d3c' :
                   density > thresholds[0] ? '#fecc5c' :
                                           '#ffffb2';
        }
        return '#cccccc';
    }

    getStyle(feature) {
        const province = feature.properties.PROVINCE;
        const city = feature.properties.CITY || 
                    feature.properties.MUNICIPALITY || 
                    feature.properties.MUNICIPALI;

        let data;
        if (this.choroplethLevel === 'province') {
            data = this.getProvinceData(province);
        } else {
            data = this.getCityData(province, city);
        }

        return {
            fillColor: this.getColor(data, this.choroplethLevel, feature),
            weight: 2,
            opacity: 1,
            color: 'white',
            dashArray: '3',
            fillOpacity: 0.7
        };
    }

    refreshMap() {
        // Remove existing layers
        this.map.eachLayer((layer) => {
            if (layer instanceof L.GeoJSON) {
                this.map.removeLayer(layer);
            }
        });

        // Reinitialize the map layers
        const provinces = ['BATANGAS', 'CAVITE', 'LAGUNA', 'QUEZON', 'RIZAL'];
        provinces.forEach(async province => {
            try {
                const response = await fetch(`./geojsonData/${province}.geojson`);
                const geojson = await response.json();
                
                L.geoJSON(geojson, {
                    style: (feature) => this.getStyle(feature),
                    onEachFeature: (feature, layer) => this.onEachFeature(feature, layer)
                }).addTo(this.map);
            } catch (error) {
                console.error(`Error loading ${province} GeoJSON:`, error);
            }
        });

        // Update legend
        this.updateLegend();
    }

    updateLegend() {
        if (this.legend) {
            this.map.removeControl(this.legend);
        }

        this.legend = L.control({ position: 'bottomright' });
        this.legend.onAdd = () => {
            const div = L.DomUtil.create('div', 'info legend');
            div.style.backgroundColor = 'white';
            div.style.padding = '10px';
            div.style.borderRadius = '5px';
            div.style.boxShadow = '0 1px 5px rgba(0,0,0,0.4)';

            let legendHtml;

            if (this.choroplethMetric === 'revenue') {
                const thresholds = this.choroplethLevel === 'province' 
                    ? [0, 70000, 100000, 150000, 200000]
                    : [0, 50000, 70000, 90000, 120000];
                const title = `${this.choroplethLevel === 'province' ? 'Province' : 'City'} Revenue`;
                
                legendHtml = `<h4>${title}</h4>`;
                
                for (let i = 0; i < thresholds.length - 1; i++) {
                    const color = this.getColor({ metrics: { 
                        [this.choroplethLevel === 'province' ? 'province_metrics' : 'city_metrics']: { 
                            total_revenue: thresholds[i + 1] + 1 
                        }
                    }}, this.choroplethLevel);

                    legendHtml += `
                        <div style="margin: 5px 0;">
                            <i style="background: ${color}; 
                            width: 18px; height: 18px; float: left; margin-right: 8px; opacity: 0.7;"></i>
                            ₱${thresholds[i].toLocaleString()} - ₱${thresholds[i + 1].toLocaleString()}
                        </div>`;
                }
            } else {
                // Population Density legend with explicit colors
                const densityRanges = [
                    { range: '0 - 1,000', color: '#ffffb2' },
                    { range: '1,000 - 2,000', color: '#fecc5c' },
                    { range: '2,000 - 5,000', color: '#fd8d3c' },
                    { range: '5,000 - 10,000', color: '#f03b20' },
                    { range: '> 10,000', color: '#bd0026' }
                ];

                legendHtml = '<h4>Population Density</h4>';
                
                densityRanges.forEach(item => {
                    legendHtml += `
                        <div style="margin: 5px 0;">
                            <i style="background: ${item.color}; 
                            width: 18px; height: 18px; float: left; margin-right: 8px; opacity: 0.7;"></i>
                            ${item.range} people/km²
                        </div>`;
                });
            }

            div.innerHTML = legendHtml;
            return div;
        };

        this.legend.addTo(this.map);
    }

    cleanCityName(cityName) {
        return cityName.trim().toUpperCase();
    }

    async selectArea(province, city) {
        console.log('selectArea called with:', { province, city });
        
        if (!province || !city) {
            console.error('Invalid selectArea parameters:', { province, city });
            return;
        }

        this.selectedProvince = province;
        this.selectedCity = city;
        
        const provinceKey = this.selectedProvince.replace(/\s+/g, '_').toUpperCase();
        const cityKey = this.selectedCity.trim().toUpperCase();
        
        // Check if data exists before proceeding
        if (!this.salesData?.[provinceKey]?.[cityKey]) {
            this.showError('No data available', this.selectedCity);
            return;
        }
        
        const chartsContainer = document.getElementById('charts');
        if (chartsContainer) {
            chartsContainer.style.display = 'grid';
        }

        // Load model first, then render charts
        await this.loadModel();
        await this.renderCharts();
    }

    async renderCharts() {
        console.log('Rendering charts for:', {
            province: this.selectedProvince,
            city: this.selectedCity
        });

        if (!this.selectedProvince || !this.selectedCity || !this.salesData) {
            console.error('Missing required data for rendering charts');
            return;
        }

        const provinceKey = this.selectedProvince.replace(/\s+/g, '_').toUpperCase();
        const cityKey = this.selectedCity.trim().toUpperCase();
        const cityData = this.salesData[provinceKey]?.[cityKey];

        if (!cityData) {
            console.error(`No data found for ${cityKey} in ${provinceKey}`);
            this.showError(`No data available for ${this.selectedCity}`);
            return;
        }

        // Ensure Chart.js is loaded before rendering
        if (typeof Chart === 'undefined') {
            console.error('Chart.js is not loaded');
            return;
        }

        // Set default Chart.js options
        Chart.defaults.font.family = 'Arial, sans-serif';
        Chart.defaults.responsive = true;
        Chart.defaults.maintainAspectRatio = false;

        await Promise.all([
            this.renderSalesChart(cityData),
            this.renderPatternChart(cityData),
            this.renderRevenueMetrics()
        ]);
    }

    renderSalesChart(cityData) {
        if (!cityData?.forecasts?.sarima) {
            console.error('Sales forecast data is missing');
            return;
        }
    
        const canvas = document.getElementById('salesChart');
        if (!canvas) return;
    
        if (this.salesChart) {
            this.salesChart.destroy();
        }
    
        // Format dates for monthly display
        const formattedDates = cityData.forecasts.dates.map(date => {
            // Convert YYYY-MM format to readable month and year
            const [year, month] = date.split('-');
            return new Date(year, month - 1).toLocaleDateString('en-US', {
                year: 'numeric',
                month: 'short'
            });
        });
    
        // Create monthly data points
        const dataPoints = cityData.forecasts.sarima.map((value, index) => ({
            date: cityData.forecasts.dates[index],
            value: value
        }));
    
        // Sort by date
        dataPoints.sort((a, b) => {
            const [yearA, monthA] = a.date.split('-');
            const [yearB, monthB] = b.date.split('-');
            return new Date(yearA, monthA - 1) - new Date(yearB, monthB - 1);
        });
    
        this.salesChart = new Chart(canvas, {
            type: 'line',
            data: {
                labels: dataPoints.map(point => {
                    const [year, month] = point.date.split('-');
                    return new Date(year, month - 1).toLocaleDateString('en-US', {
                        year: 'numeric',
                        month: 'short'
                    });
                }),
                datasets: [{
                    label: 'Monthly Sales Trend Analysis',
                    data: dataPoints.map(point => point.value),
                    borderColor: 'rgb(75, 192, 192)',
                    backgroundColor: 'rgba(75, 192, 192, 0.1)',
                    tension: 0.1,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    title: {
                        display: true,
                        text: `Monthly Sales Forecast - ${this.selectedCity}`,
                        font: {
                            size: 16,
                            weight: 'bold'
                        }
                    },
                    legend: {
                        position: 'top',
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return `₱${context.parsed.y.toLocaleString(undefined, {
                                    minimumFractionDigits: 2,
                                    maximumFractionDigits: 2
                                })}`;
                            },
                            title: function(context) {
                                return context[0].label; // Month Year format
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Forecasted Monthly Sales (₱)'
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
                        },
                        ticks: {
                            maxRotation: 45,
                            minRotation: 45
                        }
                    }
                }
            }
        });
    }

    renderPatternChart(cityData) {
        if (!cityData?.patterns?.monthly) {
            console.error('Pattern data is missing');
            return;
        }
    
        const canvas = document.getElementById('patternChart');
        if (!canvas) return;
    
        if (this.patternChart) {
            this.patternChart.destroy();
        }
    
        const monthNames = [
            'January', 'February', 'March', 'April', 'May', 'June',
            'July', 'August', 'September', 'October', 'November', 'December'
        ];
    
        // Format the monthly pattern data
        const monthlyData = Object.entries(cityData.patterns.monthly).map(([month, data]) => ({
            month: monthNames[parseInt(month) - 1],
            mean: data.mean
        }));
    
        this.patternChart = new Chart(canvas, {
            type: 'bar',
            data: {
                labels: monthlyData.map(d => d.month),
                datasets: [{
                    label: 'Average Monthly Sales',
                    data: monthlyData.map(d => d.mean),
                    backgroundColor: 'rgba(54, 162, 235, 0.5)',
                    borderColor: 'rgb(54, 162, 235)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    title: {
                        display: true,
                        text: `Monthly Sales Pattern - ${this.selectedCity}`,
                        font: {
                            size: 16,
                            weight: 'bold'
                        }
                    },
                    legend: {
                        position: 'top',
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
                            text: 'Sales Value (₱)'
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
                        },
                        ticks: {
                            maxRotation: 45,
                            minRotation: 45
                        }
                    }
                }
            }
        });
    }

    renderRevenueMetrics() {
        const cityData = this.getCityData(this.selectedProvince, this.selectedCity);
        if (!cityData) return;

        const metrics = document.getElementById('metrics');
        if (!metrics) return;

        const { city_metrics, province_metrics } = cityData.metrics;
        
        metrics.innerHTML = `
          <div class="metrics-container">
            <!-- City Metrics Card -->
            <div class="metric-card">
              <div class="card-header">
                <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M3 21h18M3 7v1a3 3 0 003 3h12a3 3 0 003-3V7M9 21v-6a2 2 0 012-2h2a2 2 0 012 2v6"/>
                </svg>
                <h2 class="card-title">City Metrics</h2>
              </div>
              <div class="metrics-grid">
                <div class="metric-item">
                  <div class="metric-label">Total Revenue</div>
                  <div class="metric-value">₱${city_metrics.total_revenue.toLocaleString()}</div>
                </div>
                <div class="metric-item">
                  <div class="metric-label">Orders</div>
                  <div class="metric-value">${city_metrics.order_count.toLocaleString()}</div>
                </div>
                <div class="metric-item">
                  <div class="metric-label">Average Order</div>
                  <div class="metric-value">₱${city_metrics.avg_order_value.toLocaleString()}</div>
                </div>
                <div class="metric-item contribution-metric">
                  <div class="metric-label">Contribution to Province Revenue</div>
                  <div class="metric-value">${city_metrics.percentage_of_province_revenue.toFixed(1)}%</div>
                </div>
              </div>
            </div>

            <!-- Province Metrics Card -->
            <div class="metric-card">
              <div class="card-header">
                <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M20 7h-3m3 0v14H4V7h16M20 7l-3-4H7L4 7m8 4v7m-4-4v4m8-4v4"/>
                </svg>
                <h2 class="card-title">Province Metrics</h2>
              </div>
              <div class="metrics-grid">
                <div class="metric-item">
                  <div class="metric-label">Total Revenue</div>
                  <div class="metric-value">₱${province_metrics.total_revenue.toLocaleString()}</div>
                </div>
                <div class="metric-item">
                  <div class="metric-label">Orders</div>
                  <div class="metric-value">${province_metrics.order_count.toLocaleString()}</div>
                </div>
                <div class="metric-item">
                  <div class="metric-label">Average Order</div>
                  <div class="metric-value">₱${province_metrics.avg_order_value.toLocaleString()}</div>
                </div>
              </div>
            </div>
          </div>
        `;
    }

    async calculateAllPopulationDensities() {
        const provinces = ['BATANGAS', 'CAVITE', 'LAGUNA', 'QUEZON', 'RIZAL'];
        this.populationDensityData = {};
    
        for (const province of provinces) {
            try {
                const response = await fetch(`./geojsonData/${province}.geojson`);
                const geojson = await response.json();
                
                this.populationDensityData[province] = {};
                
                geojson.features.forEach(feature => {
                    const city = feature.properties.CITY || 
                                feature.properties.MUNICIPALITY || 
                                feature.properties.MUNICIPALI;
                    
                    if (city) {
                        const density = this.calculatePopulationDensity(feature);
                        this.populationDensityData[province][city.toUpperCase()] = {
                            density: density || 0,
                            population: feature.properties.TOTPOP2010 || 0,
                            area: feature.properties.LAND_AREA2 ? feature.properties.LAND_AREA2 * 0.01 : 0 // Convert hectares to km²
                        };
                    }
                });
            } catch (error) {
                console.error(`Error processing ${province} population density:`, error);
            }
        }
    }
    
    calculatePopulationDensity(feature) {
        const population = feature.properties.TOTPOP2010;
        const landArea = feature.properties.LAND_AREA2; // in hectares
        if (population && landArea) {
            // Convert hectares to square kilometers (1 hectare = 0.01 square kilometers)
            const areaInSqKm = landArea * 0.01;
            return population / areaInSqKm;
        }
        return null;
    }
    
    setupEventListeners() {
        console.log('Setting up event listeners...');
    }
}

// Initialize the dashboard when the page loads
document.addEventListener('DOMContentLoaded', () => {
console.log('DOM loaded, initializing dashboard...');
new SalesDashboard();
});