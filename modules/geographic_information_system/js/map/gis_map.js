document.addEventListener('DOMContentLoaded', function () {
    const provinceDropdown = document.getElementById('province');
    const cityDropdown = document.getElementById('city');
    const map = L.map('map').setView([15.0, 121.0], 7); // Updated to focus on Luzon with a zoom level of 7
    let markers = [];
    let citiesData = [];

    // Initialize the map layer
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);

    // Define bounds for Luzon
    const southWest = L.latLng(12.0, 119.0); // Southernmost part of Luzon
    const northEast = L.latLng(19.0, 126.0); // Northernmost part of Luzon
    const bounds = L.latLngBounds(southWest, northEast);

    // Set max bounds for the map to only show Luzon
    map.setMaxBounds(bounds);
    map.fitBounds(bounds); // Ensure the map is centered and fits within Luzon bounds

    // Fetch location data for dropdowns
    fetch('../includes/get_location_data.php')
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
