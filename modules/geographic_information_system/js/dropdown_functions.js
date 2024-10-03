// Function to populate the Province dropdown
function populateProvinceDropdown(provinces, dropdown) {
    dropdown.innerHTML = '<option value="">--Select Province--</option>'; // Clear current options
    provinces.forEach(province => {
        const option = document.createElement('option');
        option.value = province.Province; // Set the value to the province name
        option.textContent = province.Province; // Set the display text
        dropdown.appendChild(option);
    });
}

// Function to populate the City dropdown
function populateCityDropdown(cities, dropdown) {
    dropdown.innerHTML = '<option value="">--Select City--</option>'; // Clear current options
    cities.forEach(city => {
        const option = document.createElement('option');
        option.value = city.City; // Set the value to the city name
        option.textContent = city.City; // Set the display text
        dropdown.appendChild(option);
    });
}

// Function to fetch years from get_years.php and populate the year dropdown
function fetchAndPopulateYears(dropdown) {
    fetch('../modules/geographic_information_system/php/get_years.php') // Ensure the correct path
        .then(response => response.json())
        .then(years => {
            if (Array.isArray(years)) {
                populateYearDropdown(years, dropdown); // Populate the dropdown
            } else {
                console.error(years.error ? `Error fetching years: ${years.error}` : 'Unexpected response format:', years);
            }
        })
        .catch(error => console.error('Error fetching years:', error));
}

// Function to populate the Year dropdown
function populateYearDropdown(years, dropdown) {
    dropdown.innerHTML = '<option value="">--Select Year--</option>'; // Clear current options
    years.forEach(year => {
        const option = document.createElement('option');
        option.value = year; // Set the value to the year
        option.textContent = year; // Set the display text
        dropdown.appendChild(option);
    });
}

// Function to update the map with city markers
function updateMapWithCities(cities, map, markers) {
    markers.forEach(marker => map.removeLayer(marker)); // Remove existing markers
    markers.length = 0; // Clear markers array

    if (!cities.length) return; // Exit if no cities found

    const bounds = new L.LatLngBounds();

    cities.forEach(city => {
        if (!isValidCityData(city)) return; // Validate city data

        const [lat, lng] = city.LatLng.split(';').map(coord => parseFloat(coord.trim()));
        const marker = L.marker([lat, lng]).addTo(map); // Add marker to map
        marker.bindPopup(createPopupContent(city.City)); // Bind popup to marker
        markers.push(marker); // Add marker to markers array

        fetchCityTransactionData(city.City, marker); // Fetch transaction data for the city
        bounds.extend([lat, lng]); // Extend map bounds
    });

    if (bounds.isValid()) {
        map.fitBounds(bounds); // Fit map to bounds
        console.log('Map bounds updated.');
    } else {
        console.log('Bounds not valid, skipping fitBounds.');
    }
}

// Helper function to validate city data
function isValidCityData(city) {
    if (!city.LatLng || !city.LatLng.includes(';')) {
        console.error(`Invalid LatLng for city: ${city.City}`);
        return false;
    }
    return true;
}

// Function to create popup content for the city marker
function createPopupContent(city) {
    return `<b>${city}</b><br>Total Transactions: Fetching...<br><a href="../modules/geographic_information_system/gis_city_details.php?city=${encodeURIComponent(city)}">More details</a>`;
}

// Function to fetch transaction data for a city and update the marker popup
function fetchCityTransactionData(city, marker) {
    const fetchUrl = `../modules/geographic_information_system/php/gis_data.php?city=${encodeURIComponent(city)}`; // Updated path
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

// Wrap event listeners in a DOMContentLoaded event to ensure the DOM is fully loaded
document.addEventListener('DOMContentLoaded', function () {
    // Add event listener for the year dropdown to render the chart
    document.getElementById('year').addEventListener('change', function () {  // Changed to 'year' to match your HTML
        const selectedYear = this.value; // Get the selected year
        console.log('Selected Year:', selectedYear); // Log the selected year
        renderTotalTransactionsLineGraph(selectedYear); // Call your graph rendering function
    });

    // Add event listener for the province dropdown
    document.getElementById('province').addEventListener('change', function () {  // Changed to 'province' to match your HTML
        const selectedProvince = this.value;
        console.log('Selected Province:', selectedProvince);
        // Logic for province selection, but do NOT call renderTotalTransactionsLineGraph
    });

    // Add event listener for the city dropdown
    document.getElementById('city').addEventListener('change', function () {  // Changed to 'city' to match your HTML
        const selectedCity = this.value;
        console.log('Selected City:', selectedCity);
        // Logic for city selection, but do NOT call renderTotalTransactionsLineGraph
    });
});
