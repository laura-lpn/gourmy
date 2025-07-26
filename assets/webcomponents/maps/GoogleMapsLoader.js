export function loadGoogleMapsScript(apiKey) {
  if (window.google?.maps) return Promise.resolve();

  return new Promise((resolve, reject) => {
    if (document.querySelector('script[src*="maps.googleapis.com/maps/api/js"]')) {
      return resolve();
    }

    const script = document.createElement("script");
    script.src = `https://maps.googleapis.com/maps/api/js?key=${apiKey}&v=weekly&libraries=maps,marker,places`;
    script.async = true;
    script.defer = true;
    script.onload = resolve;
    script.onerror = reject;

    document.head.appendChild(script);
  });
}