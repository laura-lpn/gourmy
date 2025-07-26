import { loadGoogleMapsScript } from "./GoogleMapsLoader.js";

export class MapRoadtrip extends HTMLElement {
  async connectedCallback() {
    const raw = this.getAttribute("points");
    let points;

    try {
      points = JSON.parse(raw || "[]");
    } catch (e) {
      console.error("Erreur JSON :", e);
      return;
    }

    if (!points.length) return;

    this.innerHTML = `<div id="map" class="w-full mx-auto h-full rounded-lg"></div>`;

    if (!window.google?.maps) {
      await loadGoogleMapsScript(this.getAttribute("api-key"));
    }
    this.initMap(points);
  }

  initMap(points) {
    const map = new google.maps.Map(this.querySelector("#map"), {
      center: points[0],
      zoom: points.length === 1 ? 14 : 6,
      mapId: "DEMO_MAP_ID",
    });

    const bounds = new google.maps.LatLngBounds();

    points.forEach((p, i) => {
      const card = document.createElement("div");
      card.className = "bg-white shadow-md rounded-lg overflow-hidden w-24";
      card.style.fontFamily = "sans-serif";

      card.innerHTML = `
        <div class="w-full h-12 bg-gray-200">
          ${
            p.photo
              ? `<img src="${p.photo}" alt="${p.name}" class="w-full h-full object-cover">`
              : `<div class="w-full h-full flex items-center justify-center text-xs text-gray-500">Aucune photo</div>`
          }
        </div>
        <div class="p-1 text-center">
          <p class="text-[11px] font-medium text-black">${i + 1}. ${p.name}</p>
        </div>
      `;

      new google.maps.marker.AdvancedMarkerElement({
        map,
        position: p,
        content: card,
        title: p.name,
      });

      bounds.extend(p);
    });

    if (points.length > 1) {
      map.fitBounds(bounds);

      const directionsService = new google.maps.DirectionsService();
      const directionsRenderer = new google.maps.DirectionsRenderer({
        map,
        suppressMarkers: true,
      });

      directionsService.route(
        {
          origin: points[0],
          destination: points[points.length - 1],
          waypoints: points.slice(1, -1).map((p) => ({
            location: p,
            stopover: true,
          })),
          travelMode: google.maps.TravelMode.DRIVING,
        },
        (res, status) => {
          if (status === "OK") {
            directionsRenderer.setDirections(res);
          } else {
            console.error("Erreur itinéraire :", status);
          }
        }
      );
    }
  }
}

customElements.define("map-roadtrip", MapRoadtrip);