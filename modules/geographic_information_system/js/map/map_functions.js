
// Function to fetch province data and update the map
function fetchProvincesAndUpdateMap() {
    fetch('../modules/geographic_information_system/php/get_all_provinces.php') // Adjusted to the correct path
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                console.error(`Error fetching provinces: ${data.error}`);
                return;
            }

            // Assuming data.provinces is an array of province objects
            const provinces = data.provinces || [];
            provinces.forEach(province => {
                // For each province, you can create markers or shapes here
                // Example: Creating a marker for each province
                const marker = L.marker([province.Lat, province.Lng]).addTo(map)
                    .bindPopup(`<b>${province.Province}</b><br>Total Transactions: Fetching...`);
                markers.push(marker);
                fetchProvinceTransactionData(province.Province, marker);
            });
        })
        .catch(error => {
            console.error('Error fetching provinces:', error);
        });
}

// Function to fetch transaction data for a province and update the marker popup
function fetchProvinceTransactionData(province, marker) {
    const fetchUrl = `../modules/geographic_information_system/php/get_province_transactions.php?province=${encodeURIComponent(province)}`; // Updated path
    console.log(`Fetching data for province: ${province}`);
    console.log(`Fetch URL: ${fetchUrl}`);

    fetch(fetchUrl)
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                console.error(`Error fetching transactions for ${province}: ${data.error}`);
                marker.setPopupContent(`<b>${province}</b><br>Error fetching transactions.`);
            } else {
                marker.setPopupContent(`<b>${province}</b><br>Total Transactions: ${formatCurrency(data.total_transactions)}`);
            }
        })
        .catch(error => {
            console.error('Error fetching transactions:', error);
            marker.setPopupContent(`<b>${province}</b><br>Error fetching transactions.`);
        });
}

