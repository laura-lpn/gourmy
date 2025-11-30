import { loadGoogleMapsScript } from "./GoogleMapsLoader.js";

export class MapUserRoadtrips extends HTMLElement {
  async connectedCallback() {
    const raw = this.getAttribute("roadtrips");
    let roadtrips;

    try {
      roadtrips = JSON.parse(raw || "[]");
    } catch (e) {
      console.error("Erreur JSON :", e);
      return;
    }

    if (!roadtrips.length) return;

    this.innerHTML = `<div id="map" class="w-full mx-auto h-full rounded-xl"></div>`;

    if (!window.google?.maps) {
      await loadGoogleMapsScript(this.getAttribute("api-key"));
    }
    this.initMap(roadtrips);
  }

  initMap(roadtrips) {
    const allPoints = roadtrips.flatMap((rt) => rt.points);
    const map = new google.maps.Map(this.querySelector("#map"), {
      center: allPoints[0],
      zoom: allPoints.length === 1 ? 14 : 6,
      mapId: "DEMO_MAP_ID",
    });

    const bounds = new google.maps.LatLngBounds();
    const colors = ["#4285F4", "#EA4335", "#FBBC05", "#34A853", "#AA00FF"];

    const offsetPosition = (p, idx) => {
      const offset = 0.0005 * idx;
      return { lat: p.lat + offset, lng: p.lng + offset };
    };

    roadtrips.forEach((roadtrip, idx) => {
      const color = colors[idx % colors.length];
      const path = [];

      roadtrip.points.forEach((p, i) => {
        const adjustedPos = offsetPosition(p, idx);

        const card = document.createElement("div");
        card.className = "map-card";
        card.style.border = `1px solid ${color}`;

        card.innerHTML = `
          <div class="w-full bg-white">
            <div class="map-card-title">
              ${i + 1}. ${p.name}
            </div>
          </div>
        `;

        new google.maps.marker.AdvancedMarkerElement({
          map,
          position: adjustedPos,
          content: card,
          title: p.name,
        });

        path.push(adjustedPos);
        bounds.extend(adjustedPos);
      });

      if (path.length > 1) {
        new google.maps.Polyline({
          map,
          path,
          strokeColor: color,
          strokeOpacity: 0.7,
          strokeWeight: 4,
        });
      }
    });

    if (allPoints.length > 1) {
      map.fitBounds(bounds);
    }
  }
}

customElements.define("map-user-roadtrips", MapUserRoadtrips);