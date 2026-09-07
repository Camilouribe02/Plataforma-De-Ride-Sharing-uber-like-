function togglePassword() {
    const input = document.getElementById("password");

    if (!input) return;

    input.type = input.type === "password" ? "text" : "password";
}

const themeBtn = document.getElementById("themeBtn");

if (themeBtn) {
    themeBtn.addEventListener("click", () => {
        document.body.classList.toggle("dark-mode");
        themeBtn.textContent =
            document.body.classList.contains("dark-mode") ? "☾" : "☼";
    });
}

// ===================== SOLICITUD DE VIAJE =====================
if (document.getElementById('map')) {
    let map, currentMarker, destinationMarker, routeLine;
    let currentCoords = null, destinationCoords = null, selectedVehicle = 'Moto';
    let routeKm = null;
    let searchTimer;
    // Santander, Colombia: límite geográfico aproximado para la búsqueda.
    const SANTANDER_VIEWBOX = '-74.65,8.45,-72.45,5.45';

    const currentText = document.getElementById('currentLocationText');
    const currentStatus = document.getElementById('currentLocationStatus');
    const destinationInput = document.getElementById('destinationInput');
    const resultsBox = document.getElementById('destinationResults');
    const requestBtn = document.getElementById('requestRide');
    const fareText = document.getElementById('fareText');
    const fareDetail = document.getElementById('fareDetail');

    function formatCOP(value) { return '$' + Math.round(value).toLocaleString('es-CO'); }
    function calculateFare() {
        if (!routeKm) { fareText.textContent = 'Selecciona una ruta'; fareDetail.textContent = 'El valor cambia según la distancia y el vehículo.'; return; }
        // Tarifa demostrativa: base + valor por kilómetro.
        const rates = { Moto: { base: 3000, perKm: 1000 }, Carro: { base: 5000, perKm: 2000 } };
        const r = rates[selectedVehicle];
        const total = r.base + (routeKm * r.perKm);
        fareText.textContent = formatCOP(total);
        fareDetail.textContent = `${selectedVehicle}: tarifa base ${formatCOP(r.base)} + ${formatCOP(r.perKm)} por km`;
    }

    map = L.map('map', { zoomControl: true }).setView([7.1193, -73.1227], 13);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; OpenStreetMap'
    }).addTo(map);

    function updateButton() {
        const ready = currentCoords && destinationCoords;
        requestBtn.disabled = !ready;
        requestBtn.textContent = ready ? `Solicitar viaje en ${selectedVehicle}` : 'Completa la ubicación para solicitar';
    }

    async function reverseGeocode(lat, lng) {
        try {
            const res = await fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&zoom=18&addressdetails=1`);
            const data = await res.json();
            return data.display_name || 'Ubicación actual';
        } catch (e) { return 'Ubicación actual detectada'; }
    }

    async function setCurrentLocation(position) {
        const { latitude: lat, longitude: lng } = position.coords;
        currentCoords = [lat, lng];
        if (currentMarker) map.removeLayer(currentMarker);
        currentMarker = L.marker(currentCoords).addTo(map).bindPopup('Tu ubicación actual');
        map.setView(currentCoords, 16);
        currentText.textContent = 'Ubicación actual confirmada';
        currentStatus.textContent = 'Buscando dirección exacta...';
        const address = await reverseGeocode(lat, lng);
        currentText.textContent = address;
        currentStatus.textContent = 'Tu punto de recogida está listo.';
        destinationInput.disabled = false;
        document.getElementById('locationWarning').textContent = '✓ Ubicación actual confirmada. Ahora selecciona tu destino.';
        updateButton();
    }

    function locationError(error) {
        currentText.textContent = 'No pudimos obtener tu ubicación';
        currentStatus.textContent = 'Activa la ubicación del dispositivo y permite el acceso en el navegador.';
        document.getElementById('locationWarning').textContent = '📍 La ubicación actual es obligatoria para solicitar un viaje.';
        destinationInput.disabled = true;
        updateButton();
    }

    function getLocation() {
        if (!navigator.geolocation) return locationError();
        currentText.textContent = 'Obteniendo tu ubicación...';
        currentStatus.textContent = 'Acepta el permiso de ubicación en tu navegador.';
        navigator.geolocation.getCurrentPosition(setCurrentLocation, locationError, {
            enableHighAccuracy: true, timeout: 15000, maximumAge: 30000
        });
    }

    document.getElementById('refreshLocation').addEventListener('click', getLocation);
    getLocation();

    destinationInput.addEventListener('input', () => {
        clearTimeout(searchTimer);
        const query = destinationInput.value.trim();
        if (query.length < 3) { resultsBox.style.display = 'none'; return; }
        searchTimer = setTimeout(async () => {
            try {
                const res = await fetch(`https://nominatim.openstreetmap.org/search?format=json&addressdetails=1&countrycodes=co&bounded=1&viewbox=${SANTANDER_VIEWBOX}&limit=8&q=${encodeURIComponent(query + ', Santander, Colombia')}`);
                const rawPlaces = await res.json();
                // Solo permitimos resultados cuya división administrativa pertenezca a Santander.
                const places = rawPlaces.filter(p => {
                    const a = p.address || {};
                    return Object.values(a).some(v => String(v).toLowerCase().includes('santander')) &&
                           !String(p.display_name).toLowerCase().includes('norte de santander');
                });
                resultsBox.innerHTML = places.length ? places.map((p, i) =>
                    `<div class="destination-result"><span class="result-pin">📍</span><span data-index="${i}">${p.display_name}</span></div>`
                ).join('') : '<div class="destination-result empty-result">Solo puedes seleccionar destinos dentro de Santander, Colombia.</div>';
                resultsBox.style.display = 'block';
                resultsBox.querySelectorAll('[data-index]').forEach(el => el.addEventListener('click', () => selectDestination(places[el.dataset.index])));
            } catch (e) { resultsBox.style.display = 'none'; }
        }, 500);
    });

    async function selectDestination(place) {
        const addressText = (place.display_name || '').toLowerCase();
        const isSantander = Object.values(place.address || {}).some(v => String(v).toLowerCase().includes('santander')) && !addressText.includes('norte de santander');
        if (!isSantander) { alert('El destino debe estar dentro del departamento de Santander, Colombia.'); return; }
        destinationCoords = [parseFloat(place.lat), parseFloat(place.lon)];
        destinationInput.value = place.display_name;
        resultsBox.style.display = 'none';
        document.getElementById('selectedDestination').textContent = '✓ Destino seleccionado: ' + place.display_name;
        document.getElementById('selectedDestination').classList.remove('hidden');
        if (destinationMarker) map.removeLayer(destinationMarker);
        destinationMarker = L.marker(destinationCoords).addTo(map).bindPopup('Destino').openPopup();
        await drawRoute();
        updateButton();
    }

    async function drawRoute() {
        if (!currentCoords || !destinationCoords) return;
        if (routeLine) map.removeLayer(routeLine);
        try {
            const url = `https://router.project-osrm.org/route/v1/driving/${currentCoords[1]},${currentCoords[0]};${destinationCoords[1]},${destinationCoords[0]}?overview=full&geometries=geojson`;
            const res = await fetch(url); const data = await res.json();
            const route = data.routes && data.routes[0];
            if (!route) return;
            routeLine = L.geoJSON(route.geometry, { style: { color: '#e50914', weight: 5 } }).addTo(map);
            map.fitBounds(routeLine.getBounds(), { padding: [45, 45] });
            routeKm = route.distance / 1000;
            document.getElementById('distanceText').textContent = routeKm.toFixed(1) + ' km';
            document.getElementById('durationText').textContent = Math.ceil(route.duration / 60) + ' min';
            calculateFare();
        } catch (e) { console.error('No fue posible calcular la ruta', e); }
    }

    const vehicleOptions = document.getElementById('vehicleOptions');
    const selectedVehicleBox = document.getElementById('selectedVehicleBox');
    document.querySelectorAll('.vehicle-card').forEach(card => {
        card.addEventListener('click', () => {
            selectedVehicle = card.dataset.vehicle;
            document.querySelectorAll('.vehicle-card').forEach(c => {
                c.classList.toggle('active', c === card);
                c.classList.toggle('hidden-vehicle', c !== card);
            });
            document.getElementById('selectedVehicleIcon').textContent = selectedVehicle === 'Moto' ? '🏍️' : '🚗';
            document.getElementById('selectedVehicleName').textContent = selectedVehicle;
            selectedVehicleBox.classList.remove('hidden');
            calculateFare();
            updateButton();
        });
    });

    document.getElementById('changeVehicle').addEventListener('click', () => {
        document.querySelectorAll('.vehicle-card').forEach(c => c.classList.remove('hidden-vehicle'));
        selectedVehicleBox.classList.add('hidden');
    });

    requestBtn.addEventListener('click', () => {
        if (!currentCoords || !destinationCoords || !routeKm) return;
        alert(`Solicitud creada: ${selectedVehicle}. Tarifa estimada: ${fareText.textContent}. Ahora el sistema puede buscar un conductor disponible.`);
    });
}
