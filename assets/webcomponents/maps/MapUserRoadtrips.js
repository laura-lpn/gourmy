export class MapUserRoadtrips extends HTMLElement {
    async connectedCallback() {
        const raw = this.getAttribute("roadtrips");
        let roadtrips;

        try {
            roadtrips = JSON.parse(raw || "[]");
        } catch (e) {
            console.error("Erreur JSON:", e);
            return;
        }

        if (!roadtrips.length) return;

        this.innerHTML = `<div id="map" class="w-full mx-auto h-full rounded-lg"></div>`;

        await this.loadGoogleMapsScript();
        await this.waitForGoogleMapsReady();
        this.initMap(roadtrips);
    }

    loadGoogleMapsScript() {
      if (window.google?.maps) return Promise.resolve();

      const apiKey = this.getAttribute("api-key");
      return new Promise((resolve, reject) => {
        if (document.querySelector('script[src*="maps.googleapis.com/maps/api/js"]')) {
          return resolve();
        }

        const script = document.createElement("script");
        script.src = `https://maps.googleapis.com/maps/api/js?key=${apiKey}&v=beta&libraries=maps,marker&loading=async`;
        script.async = true;
        script.defer = true;
        script.onload = resolve;
        script.onerror = reject;
        document.head.appendChild(script);
      });
    }

    waitForGoogleMapsReady() {
        return new Promise((resolve) => {
            const check = () => {
                if (window.google?.maps?.Map) {
                    resolve();
                } else {
                    setTimeout(check, 100);
                }
            };
            check();
        });
    }

    initMap(roadtrips) {
        // Regroupe tous les points pour centrer correctement
        const allPoints = roadtrips.flatMap(rt => rt.points);
        const map = new google.maps.Map(this.querySelector("#map"), {
            center: allPoints[0],
            zoom: allPoints.length === 1 ? 14 : 6,
            mapId: "DEMO_MAP_ID"
        });

        const bounds = new google.maps.LatLngBounds();
        const colors = ["#4285F4", "#EA4335", "#FBBC05", "#34A853", "#AA00FF"];

        roadtrips.forEach((roadtrip, idx) => {
            const color = colors[idx % colors.length];
            const path = [];

            roadtrip.points.forEach((p, i) => {
                const label = document.createElement("div");
                label.innerHTML = `
                    <div class="bg-[${color}] text-white py-1 px-2 rounded-md text-xs">
                        RT${idx + 1}-${i + 1}: ${p.name}
                    </div>
                `;

                if (google.maps.marker?.AdvancedMarkerElement) {
                    new google.maps.marker.AdvancedMarkerElement({
                        map,
                        position: p,
                        content: label,
                        title: p.name
                    });
                } else {
                    new google.maps.Marker({
                        map,
                        position: p,
                        title: p.name
                    });
                }

                path.push(p);
                bounds.extend(p);
            });

            // Trace un itinéraire simple entre les points de chaque roadtrip
            if (path.length > 1) {
                new google.maps.Polyline({
                    map,
                    path,
                    strokeColor: color,
                    strokeOpacity: 0.7,
                    strokeWeight: 4
                });
            }
        });

        if (allPoints.length > 1) {
            map.fitBounds(bounds);
        }
    }
}

customElements.define("map-user-roadtrips", MapUserRoadtrips);
