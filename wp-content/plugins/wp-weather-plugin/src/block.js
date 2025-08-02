(function () {
    function getWeather() {
        const container = document.getElementById("weather-block");
        if (!container) return;

        if ("geolocation" in navigator) {
            navigator.geolocation.getCurrentPosition(
                (pos) => {
                    const lat = pos.coords.latitude;
                    const lon = pos.coords.longitude;

                    // URL dynamique basée sur ton site WordPress
                    const apiUrl = `${window.location.origin}${window.location.pathname.includes('TestAkataGoavana') ? '/TestAkataGoavana' : ''}/wp-json/wpweather/v1/get-weather?lat=${lat}&lon=${lon}`;

                    fetch(apiUrl)
                        .then(res => res.json())
                        .then(data => {
                            if (data.error) {
                                container.innerText = data.error;
                            } else {
                                container.innerHTML = `
                                    <strong>${data.city}</strong><br/>
                                    Température : ${data.temp}°C<br/>
                                    ${data.condition}
                                `;
                            }
                        })
                        .catch(() => {
                            container.innerText = "Erreur lors de la récupération météo.";
                        });
                },
                () => {
                    container.innerText = "Localisation refusée. Activez la géolocalisation.";
                }
            );
        } else {
            container.innerText = "Géolocalisation non supportée.";
        }
    }

    document.addEventListener("DOMContentLoaded", getWeather);
})();
