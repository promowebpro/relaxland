import L from 'leaflet';
import 'leaflet/dist/leaflet.css';

export function mountAboutMap(element) {
    const latitude = Number(element.dataset.latitude);
    const longitude = Number(element.dataset.longitude);
    const zoom = Number(element.dataset.zoom);
    const status = element.parentElement.querySelector('[data-about-map-status]');
    const map = L.map(element, { scrollWheelZoom: false, zoomControl: true })
        .setView([latitude, longitude], zoom);
    map.attributionControl.setPosition('bottomleft');
    const tiles = L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright" target="_blank" rel="noopener">OpenStreetMap</a>',
    }).addTo(map);
    let loaded = false;
    tiles.on('tileload', () => { loaded = true; if (status) status.hidden = true; });
    tiles.on('tileerror', () => { if (!loaded && status) status.hidden = false; });
    const popup = document.createElement('span');
    popup.textContent = element.dataset.label;
    L.marker([latitude, longitude], {
        icon: L.divIcon({ className: '', html: '<span class="about-map-marker">РЛ</span>', iconSize: [40, 40], iconAnchor: [20, 20] }),
        title: element.dataset.label,
        alt: element.dataset.label,
    }).addTo(map).bindPopup(popup);
    element.querySelector('.leaflet-control-zoom-in').setAttribute('aria-label', 'Увеличить масштаб');
    element.querySelector('.leaflet-control-zoom-out').setAttribute('aria-label', 'Уменьшить масштаб');
    const reportState = () => {
        element.dataset.mapZoom = String(map.getZoom());
        element.dataset.mapCenter = `${map.getCenter().lat},${map.getCenter().lng}`;
    };
    map.on('moveend zoomend', reportState);
    element.addEventListener('focusin', () => map.scrollWheelZoom.enable());
    element.addEventListener('mouseleave', () => map.scrollWheelZoom.disable());
    new ResizeObserver(() => map.invalidateSize({ pan: false })).observe(element);
    element.dataset.mapReady = 'true';
    reportState();
}
