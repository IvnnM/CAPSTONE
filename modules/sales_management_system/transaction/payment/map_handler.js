class MapHandler {
    constructor(options) {
        this.locationData = options.locationData;
        this.map = null;
        this.marker = null;
        this.geoJsonLayer = null;
        this.locationWatcher = null;
        this.stores = [];
        this.routingControl = null;
        this.selectedStore = null;
        this.geocoder = null;
    }
  
    async initialize() {
        this.initializeMap();
        await this.loadStores(); // Add this line to load stores during initialization
        this.setupEventListeners();
        this.initializeGeocoder();
    }
  
    initializeMap() {
        this.map = L.map('map', {
            preferCanvas: true
        }).setView([14.0600, 121.4900], 8);
  
        L.tileLayer('http://{s}.google.com/vt/lyrs=m&x={x}&y={y}&z={z}', {
            maxZoom: 20,
            subdomains: ['mt0', 'mt1', 'mt2', 'mt3'],
            attribution: '&copy; <a href="https://www.google.com/intl/en-US/help/terms_maps.html">Google</a>'
        }).addTo(this.map);
  
        this.loadGeoJsonLayers();
    }
  
    loadGeoJsonLayers() {
        try {
            const provinces = ['BATANGAS', 'CAVITE', 'LAGUNA', 'QUEZON', 'RIZAL'];
            provinces.forEach(province => {
                const geojsonVar = window[`${province}geojson`];
                if (typeof geojsonVar !== 'undefined') {
                    this.addGeoJsonLayer(geojsonVar);
                }
            });
        } catch (error) {
            console.error("Error loading GeoJSON layers:", error);
        }
    }
  
    addGeoJsonLayer(geojsonData) {
        L.geoJSON(geojsonData, {
            style: {
                fillColor: '#3388ff',
                weight: 2,
                opacity: 1,
                color: 'white',
                dashArray: '3',
                fillOpacity: 0.2
            },
            onEachFeature: (feature, layer) => {
                layer.on({
                    mouseover: this.highlightFeature.bind(this),
                    mouseout: this.resetHighlight.bind(this),
                    click: this.zoomToFeature.bind(this)
                });
            }
        }).addTo(this.map);
    }
  
    highlightFeature(e) {
        const layer = e.target;
        layer.setStyle({
            weight: 3,
            color: '#666',
            fillOpacity: 0.4
        });
    }
  
    resetHighlight(e) {
        if (this.geoJsonLayer) {
            this.geoJsonLayer.resetStyle(e.target);
        }
    }
  
    zoomToFeature(e) {
        this.map.fitBounds(e.target.getBounds());
    }

    async updateLocationInfo(latlng) {
      const point = turf.point([latlng.lng, latlng.lat]);
      let selectedProvince = null;
      let selectedCity = null;
      let isValidLocation = false;
  
      const provinces = ['BATANGAS', 'CAVITE', 'LAGUNA', 'QUEZON', 'RIZAL'];
      
      // Check if the coordinates are within any of the province polygons
      provinces.forEach(province => {
          const geojsonVar = window[`${province}geojson`];
          if (geojsonVar) {
              geojsonVar.features.forEach(feature => {
                  if (turf.booleanPointInPolygon(point, feature)) {
                      selectedProvince = feature.properties.PROVINCE;
                      selectedCity = feature.properties.MUNICIPALI;
                      isValidLocation = true;
                  }
              });
          }
      });
  
      // Log the selected province, city, and coordinates
      console.log(`Selected Province: ${selectedProvince}`);
      console.log(`Selected City: ${selectedCity}`);
      console.log(`Coordinates: ${latlng.lat}, ${latlng.lng}`);
  
      // Update UI with selected location info
      document.getElementById('selected-province').textContent = selectedProvince || 'Not selected';
      document.getElementById('selected-city').textContent = selectedCity || 'Not selected';
  
      if (isValidLocation) {
          // Find the matching location ID from the location data
          const matchingLocation = this.locationData.find(loc =>
              loc.Province === selectedProvince && loc.City === selectedCity
          );
          
          if (matchingLocation) {
              document.getElementById('location_id').value = matchingLocation.LocationID;
              document.querySelector('.invalid-location').style.display = 'none';
          } else {
              console.error('No matching location found for the selected province and city.');
              document.querySelector('.invalid-location').style.display = 'block';
          }
      } else {
          document.querySelector('.invalid-location').style.display = 'block';
      }
  
      return isValidLocation;
    }

    async loadStores() {
        try {
            const response = await fetch('get_stores.php');
            this.stores = await response.json();
            
            // Add store markers to map
            this.stores.forEach(store => {
                L.marker([store.lat, store.lng], {
                    icon: L.divIcon({
                        className: 'store-marker',
                        html: '🏪',
                        iconSize: [25, 25]
                    })
                }).addTo(this.map)
                .bindPopup(`Store: ${store.city}, ${store.province}<br>Delivery Fee: ₱${store.delivery_fee}`);
            });
        } catch (error) {
            console.error("Error loading stores:", error);
        }
    }

    findNearestStore(customerLat, customerLng) {
        let nearestStore = null;
        let shortestDistance = Infinity;

        this.stores.forEach(store => {
            const distance = this.calculateDistance(
                customerLat, 
                customerLng, 
                store.lat, 
                store.lng
            );

            if (distance < shortestDistance) {
                shortestDistance = distance;
                nearestStore = store;
            }
        });

        console.log(`Nearest store distance: ${shortestDistance.toFixed(2)} km`);
        return nearestStore;
    }

    calculateDistance(lat1, lon1, lat2, lon2) {
        const R = 6371; // Earth's radius in km
        const dLat = this.deg2rad(lat2 - lat1);
        const dLon = this.deg2rad(lon2 - lon1);
        const a = 
            Math.sin(dLat/2) * Math.sin(dLat/2) +
            Math.cos(this.deg2rad(lat1)) * Math.cos(this.deg2rad(lat2)) * 
            Math.sin(dLon/2) * Math.sin(dLon/2);
        const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
        return R * c;
    }

    deg2rad(deg) {
        return deg * (Math.PI/180);
    }

    async updateRoute(customerLatLng, store) {
        // Remove existing route if any
        if (this.routingControl) {
            this.map.removeControl(this.routingControl);
        }
    
        // Create new route
        this.routingControl = L.Routing.control({
            waypoints: [
                L.latLng(customerLatLng.lat, customerLatLng.lng),
                L.latLng(store.lat, store.lng)
            ],
            routeWhileDragging: false,
            showAlternatives: false,
            createMarker: () => null,
            lineOptions: {
                styles: [{ color: '#0d6efd', weight: 4 }]
            }
        }).addTo(this.map);
        this.routingControl.hide();
        
        // Calculate distance
        const distance = this.calculateDistance(
            customerLatLng.lat,
            customerLatLng.lng,
            store.lat,
            store.lng
        );
    
        try {
            console.log('Calculating delivery fee for:', {
                store_id: store.id,
                distance: distance
            });
    
            // Update the path to your PHP file
            const response = await fetch('calc_delv_fee.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    store_id: store.id,
                    distance: distance
                })
            });
    
            // Log the raw response for debugging
            const rawResponse = await response.text();
            console.log('Raw response:', rawResponse);
    
            // Try to parse the response as JSON
            let data;
            try {
                data = JSON.parse(rawResponse);
            } catch (e) {
                console.error('Failed to parse JSON response:', e);
                throw new Error('Invalid response format from server');
            }
    
            if (!data.success) {
                throw new Error(data.error || 'Unknown server error');
            }
    
            // Update delivery fee display
            const deliveryFee = parseFloat(data.delivery_fee);
            document.getElementById('delivery_fee').textContent = `₱${deliveryFee.toFixed(2)}`;
            
            // Update delivery fee input field
            document.getElementById('delivery_fee_input').value = deliveryFee.toFixed(2); // Add this line
            // Update grand total
            const subtotalElement = document.getElementById('subtotal');
            const subtotal = parseFloat(subtotalElement.textContent.replace('₱', '').replace(',', ''));
            const grandTotal = subtotal + deliveryFee;
            document.getElementById('grand_total').textContent = `₱${grandTotal.toFixed(2)}`;
    
        } catch (error) {
            console.error('Error calculating delivery fee:', error);
            // Show a more user-friendly error message
            const errorMessage = document.createElement('div');
            errorMessage.className = 'alert alert-danger mt-2';
            errorMessage.textContent = 'Unable to calculate delivery fee. Please try again or contact support.';
            
            const priceSummary = document.querySelector('.price-summary');
            if (priceSummary) {
                priceSummary.appendChild(errorMessage);
                // Remove the error message after 5 seconds
                setTimeout(() => errorMessage.remove(), 5000);
            }
        }
    
        // Store the selected store info
        this.selectedStore = store;
        
        // Add hidden input for store ID and distance
        let storeInput = document.getElementById('selected_store');
        if (!storeInput) {
            storeInput = document.createElement('input');
            storeInput.type = 'hidden';
            storeInput.id = 'selected_store';
            storeInput.name = 'selected_store';
            document.getElementById('checkoutForm').appendChild(storeInput);
        }
        storeInput.value = store.id;
    
        // Add distance input
        let distanceInput = document.getElementById('delivery_distance');
        if (!distanceInput) {
            distanceInput = document.createElement('input');
            distanceInput.type = 'hidden';
            distanceInput.id = 'delivery_distance';
            distanceInput.name = 'delivery_distance';
            document.getElementById('checkoutForm').appendChild(distanceInput);
        }
        distanceInput.value = distance.toFixed(2);
    }

    // Modify your existing click handler in setupEventListeners
    async handleLocationSelection(latlng) {
        const isValid = await this.updateLocationInfo(latlng);
        if (isValid) {
            document.getElementById("exact_coordinates").value = `${latlng.lat},${latlng.lng}`;
            
            // Find and set nearest store
            const nearestStore = this.findNearestStore(latlng.lat, latlng.lng);
            if (nearestStore) {
                this.updateRoute(latlng, nearestStore);
            }
        }
    }
    
        initializeGeocoder() {
    // Create custom geocoder control
    this.geocoder = L.Control.geocoder({
        defaultMarkGeocode: false,
        placeholder: 'Search address or place...',
        errorMessage: 'Nothing found.',
        suggestMinLength: 3,
        suggestTimeout: 250,
        queryMinLength: 1,
        geocoder: L.Control.Geocoder.nominatim({
            geocodingQueryParams: {
                viewbox: '120.5,13.5,122.5,14.5', // Adjust these coordinates to match your region
                bounded: 1,
                countrycodes: 'ph' // Restrict to Philippines
            }
        })
    }).addTo(this.map);

    // Handle search result selection
    this.geocoder.on('markgeocode', async (e) => {
        // Uncheck the "Use my current location" checkbox
        const useCurrentLocationCheckbox = document.getElementById('useCurrentLocation');
        if (useCurrentLocationCheckbox) {
            useCurrentLocationCheckbox.checked = false;
        }

        // Stop location tracking if it's active
        if (this.locationWatcher) {
            navigator.geolocation.clearWatch(this.locationWatcher);
            this.locationWatcher = null;
        }

        const latlng = e.geocode.center; // Get the latlng of the selected geocode

        // Validate the location using the existing updateLocationInfo method
        const isValid = await this.updateLocationInfo(latlng);
        
        if (isValid) {
            // Remove existing marker if any
            if (this.marker) {
                this.map.removeLayer(this.marker);
            }

            // Create a new marker
            this.marker = L.marker(latlng, { draggable: true }).addTo(this.map);

            // Center and zoom the map
            this.map.flyTo(latlng, 14, {
                duration: 1.5,
                easeLinearity: 0.25
            });

            // Set exact coordinates
            document.getElementById("exact_coordinates").value = `${latlng.lat},${latlng.lng}`;
            
            // Find and set nearest store
            const nearestStore = this.findNearestStore(latlng.lat, latlng.lng);
            if (nearestStore) {
                this.updateRoute(latlng, nearestStore);
            }

            // Add dragend event to marker
            this.marker.on('dragend', async (event) => {
                const newLatLng = event.target.getLatLng();
                await this.handleLocationSelection(newLatLng);
            });
        } else {
            alert('Location is outside the specified region.');
        }
    });
}
    
    //Customer Location
    // Update setupEventListeners to conditionally handle map clicks
    setupEventListeners() {
        this.map.on('click', async (e) => {
            if (!document.getElementById('useCurrentLocation').checked) {
                if (this.marker) {
                    this.marker.setLatLng(e.latlng);
                } else {
                    this.marker = L.marker(e.latlng, { draggable: true }).addTo(this.map);
                }

                await this.handleLocationSelection(e.latlng);

                this.marker.on('dragend', async (event) => {
                    const newLatLng = event.target.getLatLng();
                    await this.handleLocationSelection(newLatLng);
                });
            }
        });
  
        document.getElementById('useCurrentLocation').addEventListener('change', (e) => {
            if (e.target.checked) {
                if (this.marker) {
                    this.map.removeLayer(this.marker);
                    this.marker = null;
                }
                this.startLocationTracking();
            } else {
                if (this.locationWatcher) {
                    navigator.geolocation.clearWatch(this.locationWatcher);
                    this.locationWatcher = null;
                }
                if (this.marker) {
                    this.map.removeLayer(this.marker);
                    this.marker = null;
                }
            }
        });
    }
  
    startLocationTracking() {
        if ("geolocation" in navigator) {
            this.locationWatcher = navigator.geolocation.watchPosition(
                async (position) => {
                    const latlng = L.latLng(position.coords.latitude, position.coords.longitude);
                    
                    if (!this.marker) {
                        this.marker = L.marker(latlng).addTo(this.map);
                        this.map.setView(latlng, 15);
                    } else {
                        this.marker.setLatLng(latlng);
                    }

                    await this.handleLocationSelection(latlng);
                },
                (error) => {
                    console.error("Error getting location:", error);
                    alert("Unable to get your location. Please check your device settings or select location manually.");
                    document.getElementById('useCurrentLocation').checked = false;
                },
                {
                    enableHighAccuracy: true,
                    timeout: 5000,
                    maximumAge: 0
                }
            );
        } else {
            alert("Geolocation is not supported by your browser");
            document.getElementById('useCurrentLocation').checked = false;
        }
    }
  }