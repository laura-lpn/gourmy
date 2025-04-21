import { useEffect, useState } from 'react';

const MapPreview = ({ address, postalCode, city, country }) => {
  const [latitude, setLatitude] = useState(null);
  const [longitude, setLongitude] = useState(null);
  const [map, setMap] = useState(null);
  const [marker, setMarker] = useState(null);

  useEffect(() => {
    if (!address || !postalCode || !city || !country) {
      return;
    }

    const fullAddress = `${address}, ${postalCode} ${city}, ${country}`;
    const geocoder = new google.maps.Geocoder();

    // Géocodage de l'adresse
    geocoder.geocode({ address: fullAddress }, (results, status) => {
      if (status === 'OK' && results[0]) {
        const { lat, lng } = results[0].geometry.location;
        setLatitude(lat());
        setLongitude(lng());

        if (!map) {
          const mapOptions = {
            center: { lat, lng },
            zoom: 15,
          };
          const newMap = new google.maps.Map(document.getElementById('map'), mapOptions);
          setMap(newMap);

          const newMarker = new google.maps.Marker({
            position: { lat, lng },
            map: newMap,
          });
          setMarker(newMarker);
        } else {
          map.setCenter({ lat, lng });
          marker.setPosition({ lat, lng });
        }
      } else {
        console.error('Géocodage échoué:', status);
      }
    });
  }, [address, postalCode, city, country, map, marker]);

  return (
    <div>
      <h2>Prévisualisation de la carte</h2>
      <div id="map" style={{ height: '400px', width: '100%' }}></div>
      {latitude && longitude && (
        <p>Latitude: {latitude} | Longitude: {longitude}</p>
      )}
    </div>
  );
};

export default MapPreview;