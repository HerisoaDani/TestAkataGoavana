(function () {
    const container = document.getElementById("weather-block");
    if (!container) return;

    const savedCity = localStorage.getItem("wpweather_city");

    function showMessage(msg) {
        container.innerHTML = `<p>${msg}</p>`;
    }

    function renderWeather(data) {
        container.innerHTML = `
            <strong class="city">${data.city}</strong><br/>
            <span class="temp">${data.temp}°C</span><br/>
            <span class="condition">${data.condition}</span><br/>
            <button id="changeCityBtn">Changer de ville</button>
        `;

        // Bouton pour changer la ville
        const btn = document.getElementById("changeCityBtn");
        if (btn) {
            btn.addEventListener("click", askCity);
        }
    }

    function fetchWeatherByCity(city) {
        const url = `${wpweatherData.apiUrl}?city=${encodeURIComponent(city)}`;
        fetch(url)
            .then(res => res.json())
            .then(data => {
                if (data.error) {
                    showMessage(data.error);
                } else {
                    renderWeather(data);
                }
            })
            .catch(() => showMessage("Erreur lors de la récupération météo."));
    }

    function fetchWeatherByCoords(lat, lon) {
        const url = `${wpweatherData.apiUrl}?lat=${lat}&lon=${lon}`;
        fetch(url)
            .then(res => res.json())
            .then(data => {
                if (data.error) {
                    showMessage(data.error);
                } else {
                    renderWeather(data);
                }
            })
            .catch(() => showMessage("Erreur lors de la récupération météo."));
    }

    // Demande à l'utilisateur une ville manuelle
    function askCity() {
        const userCity = prompt("Entrez votre ville :", savedCity || "");
        if (userCity) {
            localStorage.setItem("wpweather_city", userCity);
            fetchWeatherByCity(userCity);
        }
    }

    // Logique principale
    if (savedCity) {
        // Utilise la ville enregistrée
        fetchWeatherByCity(savedCity);
    } else if ("geolocation" in navigator) {
        navigator.geolocation.getCurrentPosition(
            pos => {
                const lat = pos.coords.latitude;
                const lon = pos.coords.longitude;
                fetchWeatherByCoords(lat, lon);
            },
            () => {
                // Si refus → demande ville
                askCity();
            },
            {
                enableHighAccuracy: true,
                timeout: 10000,
                maximumAge: 0
            }
        );
    } else {
        askCity();
    }
})();
