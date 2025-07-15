export class MapRoadtrip extends HTMLElement {
	async connectedCallback() {
		const raw = this.getAttribute("points");
		let points;

		try {
			points = JSON.parse(raw || "[]");
		} catch (e) {
			console.error("Erreur JSON:", e);
			return;
		}

		if (!points.length) return;

		this.innerHTML = `<div id="map" class="w-full mx-auto h-full rounded-lg"></div>`;

		await this.loadGoogleMapsScript();
		await this.waitForGoogleMapsReady();
		this.initMap(points);
	}

	loadGoogleMapsScript() {
		if (window.google?.maps) return Promise.resolve();

		return new Promise((resolve, reject) => {
			if (document.querySelector('script[src*="maps.googleapis.com/maps/api/js"]')) {
				// déjà en cours de chargement
				return resolve();
			}

			const script = document.createElement("script");
			script.src = "https://maps.googleapis.com/maps/api/js?key=AIzaSyCDWeDyCsZGLwzEDg6JpQ7tuOAoJQcv2L4&v=beta&libraries=maps,marker&loading=async";
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
				if (window.google?.maps?.Map && window.google.maps.DirectionsService) {
					resolve();
				} else {
					setTimeout(check, 100);
				}
			};
			check();
		});
	}

	initMap(points) {
		const map = new google.maps.Map(this.querySelector("#map"), {
			center: points[0],
			zoom: points.length === 1 ? 14 : 6,
			mapId: "DEMO_MAP_ID"
		});

		const bounds = new google.maps.LatLngBounds();

		points.forEach((p, i) => {
			const label = document.createElement("div");
			label.innerHTML = `
				<div class="bg-blue text-white py-1 px-2 rounded-md text-xs">
					${i + 1}. ${p.name}
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
			bounds.extend(p);
		});

		if (points.length > 1) {
			map.fitBounds(bounds);

			const directionsService = new google.maps.DirectionsService();
			const directionsRenderer = new google.maps.DirectionsRenderer({ map });

			directionsService.route({
				origin: points[0],
				destination: points[points.length - 1],
				waypoints: points.slice(1, -1).map(p => ({
					location: p,
					stopover: true
				})),
				travelMode: google.maps.TravelMode.DRIVING
			}, (res, status) => {
				if (status === "OK") {
					directionsRenderer.setDirections(res);
				} else {
					console.error("Erreur itinéraire :", status);
				}
			});
		}
	}
}

customElements.define("map-roadtrip", MapRoadtrip);