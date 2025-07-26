import { loadGoogleMapsScript } from "./GoogleMapsLoader.js";

export class MapPreview extends HTMLElement {
  constructor() {
    super();
    this.state = { latitude: null, longitude: null };
    this.map = null;
    this.marker = null;
  }

  connectedCallback() {
    this.innerHTML = this.render();
    if (window.google?.maps) {
      this.initMap();
    } else {
      loadGoogleMapsScript(this.getAttribute("api-key")).then(() =>
        this.initMap()
      );
    }
  }

  initMap() {
    const mapContainer = this.querySelector("#map");
    if (!mapContainer) return;

    const address = `${this.getAttribute("address")}, ${this.getAttribute(
      "postal-code"
    )} ${this.getAttribute("city")}, ${this.getAttribute("country")}`;
    const restaurantName = this.getAttribute("name") || "Restaurant";
    const photoUrl = this.getAttribute("photo") || null;

    const geocoder = new google.maps.Geocoder();

    geocoder.geocode({ address: address }, (results, status) => {
      if (status === "OK" && results[0]) {
        const lat = results[0].geometry.location.lat();
        const lng = results[0].geometry.location.lng();
        this.state.latitude = lat;
        this.state.longitude = lng;

        const map = new google.maps.Map(mapContainer, {
          center: { lat, lng },
          zoom: 15,
          mapId: "DEMO_MAP_ID",
        });

        const card = document.createElement("div");
        card.className = "map-card";
        card.innerHTML = `
          <div class="w-full h-12 bg-gray-200">
            ${
              photoUrl
                ? `<img src="${photoUrl}" alt="${restaurantName}" class="map-card-img">`
                : `<div class="map-card-placeholder">Aucune photo</div>`
            }
          </div>
          <div class="map-card-title">
            ${restaurantName}
          </div>
        `;

        new google.maps.marker.AdvancedMarkerElement({
          map,
          position: { lat, lng },
          content: card,
          title: restaurantName,
        });
      } else {
        console.error("Géocodage échoué :", status);
      }
    });
  }

  render() {
    return `<div>
        <div id="map" class="w-full h-[400px] rounded-xl">
      </div>
    </div>`;
  }
}

customElements.define("map-preview", MapPreview);
