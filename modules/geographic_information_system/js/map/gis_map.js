document.addEventListener('DOMContentLoaded', function () {
    const provinceDropdown = document.getElementById('province');
    const cityDropdown = document.getElementById('city');
    
    // Focus map on Calabarzon
    const map = L.map('map').setView([14.1, 121.5], 9); // Centered around Calabarzon region
    
    let markers = [];
    let citiesData = [];

    // Initialize the map layer
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);

    // Define bounds for Calabarzon
    const southWest = L.latLng(13.5, 120.0); // Southern part of Calabarzon
    const northEast = L.latLng(14.9, 123.0); // Northern part of Calabarzon
    const bounds = L.latLngBounds(southWest, northEast);

    // Set max bounds for the map to only show Calabarzon
    map.setMaxBounds(bounds);
    map.fitBounds(bounds); // Ensure the map is centered and fits within Calabarzon bounds

    // Fetch location data for dropdowns
    fetch('../includes/get_location_data.php')
        .then(response => response.json())
        .then(data => {
            // Filter provinces to only show Calabarzon (Cavite, Laguna, Batangas, Rizal, Quezon)
            const calabarzonProvinces = data.provinces.filter(province => 
                ['Cavite', 'Laguna', 'Batangas', 'Rizal', 'Quezon'].includes(province.Province)
            );

            // Populate the Province dropdown with Calabarzon provinces
            populateProvinceDropdown(calabarzonProvinces, provinceDropdown);
            citiesData = data.cities;

            // Render total transactions bar graph for Calabarzon provinces by default
            renderTotalTransactionsBarGraph();

            // Initialize transactions chart with default Calabarzon province
            if (calabarzonProvinces.length > 0) {
                const defaultProvince = calabarzonProvinces[0].Province;
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
