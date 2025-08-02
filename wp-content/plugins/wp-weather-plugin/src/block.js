(function () {
    const container = document.getElementById("weather-block");
    if (!container) return;

    function showMessage(msg) {
        container.innerHTML = `<p>${msg}</p>`;
    }

    function fetchWeather(lat, lon) {
        const url = `${wpweatherData.apiUrl}?lat=${lat}&lon=${lon}`;
        fetch(url)
            .then(res => res.json())
            .then(data => {
                if (data.error) {
                    showMessage(data.error);
                } else {
                    container.innerHTML = `
                        <strong>${data.city}</strong><br/>
                        Température : ${data.temp}°C<br/>
                        ${data.condition}
                    `;
                }
            })
            .catch(() => showMessage("Erreur lors de la récupération météo."));
    }

    if ("geolocation" in navigator) {
        navigator.geolocation.getCurrentPosition(
            pos => {
                fetchWeather(pos.coords.latitude, pos.coords.longitude);
            },
            () => showMessage("Localisation refusée. Activez la géolocalisation.")
        );
    } else {
        showMessage("Géolocalisation non supportée.");
    }
})();
