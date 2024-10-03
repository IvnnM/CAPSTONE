document.addEventListener('DOMContentLoaded', function () {
    const provinceDropdown = document.getElementById('province');
    const cityDropdown = document.getElementById('city');
    const map = L.map('map').setView([13.7500, 121.0500], 8);
    let markers = [];
    let citiesData = [];

    // Initialize the map layer
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);

    // Define bounds for the Philippines
    const southWest = L.latLng(4.1235, 116.8469);
    const northEast = L.latLng(21.1910, 126.6042); 
    const bounds = L.latLngBounds(southWest, northEast);

    // Set max bounds for the map
    map.setMaxBounds(bounds);

    // Fetch location data for dropdowns
    fetch('../includes/get_location_data.php')  // Corrected path
        .then(response => response.json())
        .then(data => {
            // Populate the Province dropdown
            populateProvinceDropdown(data.provinces, provinceDropdown);
            citiesData = data.cities;

            // Render total transactions bar graph for all provinces by default
            renderTotalTransactionsBarGraph();

            // Initialize transactions chart with default province
            if (data.provinces.length > 0) {
                const defaultProvince = data.provinces[0].Province;
                renderTotalTransactionsLineGraph(defaultProvince);
            }

            // Handle Province selection
            provinceDropdown.addEventListener('change', function () {
                const selectedProvince = provinceDropdown.value;

                // Filter cities based on selected Province
                const filteredCities = citiesData.filter(city => city.Province === selectedProvince);
                populateCityDropdown(filteredCities, cityDropdown);

                // Update the map to mark/pin the cities for the selected Province
                updateMapWithCities(filteredCities, map, markers);

                // Fetch and render the transaction data for the selected province
                renderTotalTransactionsLineGraph(selectedProvince);
            });
        });

    // Add event listener for city selection
    cityDropdown.addEventListener('change', function () {
        const selectedCity = cityDropdown.value;

        if (!selectedCity) return; // If no city is selected, exit

        // Find the city data from citiesData
        const cityData = citiesData.find(city => city.City === selectedCity);
        if (!cityData || !cityData.LatLng || !cityData.LatLng.includes(';')) return;

        // Split the LatLng string to get coordinates
        const latLng = cityData.LatLng.split(';');
        const lat = parseFloat(latLng[0].trim());
        const lng = parseFloat(latLng[1].trim());

        // Set the map view to the selected city
        map.setView([lat, lng], 13); // Zoom in to level 13

        // Find the corresponding marker and open its popup
        const marker = markers.find(marker => 
            marker.getLatLng().lat === lat && marker.getLatLng().lng === lng
        );
        if (marker) {
            marker.openPopup();
        } else {
            console.error(`Marker for city ${selectedCity} not found.`);
        }
    });
});
