function populateProvinceDropdown(provinces, dropdown) {
    provinces.forEach(province => {
        const option = document.createElement('option');
        option.value = province.Province;
        option.textContent = province.Province;
        dropdown.appendChild(option);
    });
}

function populateCityDropdown(cities, dropdown) {
    dropdown.innerHTML = '<option value="">--Select City--</option>'; // Clear current options
    cities.forEach(city => {
        const option = document.createElement('option');
        option.value = city.City;
        option.textContent = city.City;
        dropdown.appendChild(option);
    });
}

function updateMapWithCities(cities, map, markers) {
    markers.forEach(marker => map.removeLayer(marker));
    markers.length = 0; // Clear existing markers

    if (cities.length === 0) return; // If no cities are found, exit.

    const bounds = new L.LatLngBounds();

    cities.forEach(city => {
        if (!city.LatLng || !city.LatLng.includes(';')) {
            console.error(`Invalid LatLng for city: ${city.City}`);
            return;
        }

        const latLng = city.LatLng.split(';');
        const lat = parseFloat(latLng[0].trim());
        const lng = parseFloat(latLng[1].trim());

        if (isNaN(lat) || isNaN(lng)) {
            console.error(`Invalid latitude or longitude for city: ${city.City} - Lat: ${lat}, Lng: ${lng}`);
            return;
        }

        const marker = L.marker([lat, lng]).addTo(map);
        
        // Use a separate function to handle the marker popup content
        marker.bindPopup(createPopupContent(city.City));
        markers.push(marker);

        fetchCityTransactionData(city.City, marker);
        bounds.extend([lat, lng]);
    });

    if (!bounds.isValid()) {
        console.log('Bounds not valid, skipping fitBounds.');
        return;
    }

    map.fitBounds(bounds);
    console.log('Map bounds updated.');
}

function createPopupContent(city) {
    return `<b>${city}</b><br>Total Transactions: Fetching...<br><a href="../modules/geographic_information_system/gis_city_details.php?city=${encodeURIComponent(city)}">More details</a>`;
}

function fetchCityTransactionData(city, marker) {
    const fetchUrl = `../modules/geographic_information_system/gis_data.php?city=${encodeURIComponent(city)}`;  // Updated path
    console.log(`Fetching data for city: ${city}`);
    console.log(`Fetch URL: ${fetchUrl}`);

    fetch(fetchUrl)
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                console.error(`Error fetching transactions: ${data.error}`);
                marker.setPopupContent(createPopupContent(city) + `<br>Error fetching transactions.`);
            } else {
                marker.setPopupContent(`<b>${city}</b><br>Total Transactions: ${formatCurrency(data.total_transactions)}<br><a href="../modules/geographic_information_system/gis_city_details.php?city=${encodeURIComponent(city)}">More details</a>`);
            }
        })
        .catch(error => {
            console.error('Error fetching transactions:', error);
            marker.setPopupContent(createPopupContent(city) + `<br>Error fetching transactions.`);
        });
}
