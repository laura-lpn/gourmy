export class MapPreview extends HTMLElement {
  constructor() {
    super();
    this.state = {
      latitude: null,
      longitude: null,
    };
    this.map = null;
    this.marker = null;
  }

  connectedCallback() {
    if (typeof google === 'undefined' || !google.maps) {
      window.initMap = this.initMap.bind(this);
      this.loadGoogleMapsScript();
    } else {
      this.initMap();
    }
  }

  loadGoogleMapsScript() {
    const script = document.createElement('script');
    script.src = 'https://maps.googleapis.com/maps/api/js?key=AIzaSyCDWeDyCsZGLwzEDg6JpQ7tuOAoJQcv2L4&libraries=places&callback=initMap';
    script.async = true;
    script.defer = true;
    document.head.appendChild(script);
  }

  initMap() {
    const mapContainer = this.querySelector('#map');
    if (!mapContainer) {
      console.error('Élément de la carte introuvable');
      return;
    }

    const address = `${this.getAttribute('address')}, ${this.getAttribute('postal-code')} ${this.getAttribute('city')}, ${this.getAttribute('country')}`;
    const geocoder = new google.maps.Geocoder();

    geocoder.geocode({ address: address }, (results, status) => {
      if (status === 'OK' && results[0]) {
        const lat = results[0].geometry.location.lat();
        const lng = results[0].geometry.location.lng();
        this.state.latitude = lat;
        this.state.longitude = lng;

        const mapOptions = {
          center: { lat, lng },
          zoom: 15,
        };

        this.map = new google.maps.Map(mapContainer, mapOptions);
        this.marker = new google.maps.Marker({
          position: { lat, lng },
          map: this.map,
        });
      } else {
        console.error('Géocodage échoué:', status);
      }
    });
  }

  render() {
    return `
      <div>
        <h2>Prévisualisation de la carte</h2>
        <div id="map" style="height: 400px; width: 100%;"></div>
        <p>Latitude: ${this.state.latitude} | Longitude: ${this.state.longitude}</p>
      </div>
    `;
  }
}

customElements.define('map-preview', MapPreview);