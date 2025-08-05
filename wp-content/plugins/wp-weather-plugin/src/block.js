(function (blocks, element) {
    const { registerBlockType } = blocks;
    const { createElement: el } = element;

    registerBlockType('wpweather/block', {
        title: 'Bloc Météo',
        icon: 'cloud',
        category: 'widgets',
        description: 'Affiche la météo selon la localisation et la date.',
        supports: { html: false },
        edit: () => el('div', { className: 'wpweather-editor-placeholder' }, 'Aperçu météo (front uniquement)'),
        save: () => null
    });

    if (typeof window !== 'undefined' && document.getElementById("weather-block")) {
        const container = document.getElementById("weather-block");
        let selectedDate = new Date().toISOString().slice(0, 10);
        let savedCity = localStorage.getItem("wpweather_city");

        /** Affiche un message d'erreur ou d'info */
        function showMessage(msg) {
            container.innerHTML = `<p style="text-align:center;font-weight:bold;color:#cc0000;">${msg}</p>`;
        }

        /** Affiche les données météo */
        function renderWeather(data) {
            container.innerHTML = `
                <div class="wpweather-card-landscape">
                    <div class="left-side">
                        <h3>🌍 ${data.city}</h3>
                        <small>Dernière mise à jour : ${selectedDate}</small>
                        <img src="${data.icon}" alt="${data.condition_text}" />
                        <div class="temp">${data.temp}°C</div>
                        <div class="condition">${data.condition_text}</div>
                    </div>

                    <div class="right-side">
                        <div class="details" style="font-size:0.85em;">
                            <div>🌡️ Ressenti : ${data.feelslike}°C</div>
                            <div>💧 Humidité : ${data.humidity}%</div>
                            <div>🌬️ Vent : ${data.wind_kph} km/h</div>
                            <div>👁️ Visibilité : ${data.visibility_km} km</div>
                            <div>📊 Pression : ${data.pressure_mb} mb</div>
                        </div>
                        
                        <div class="controls">
                            <input type="date" id="weather-date" value="${selectedDate}" />
                            <button id="refreshWeather">🔄 Actualiser</button>
                        </div>
                    </div>
                </div>
            `;

            // Événements : changement de date
            document.getElementById("weather-date").addEventListener("change", () => {
                selectedDate = document.getElementById("weather-date").value;
                loadWeather();
            });

            // Bouton "Actualiser"
            document.getElementById("refreshWeather").addEventListener("click", () => {
                selectedDate = document.getElementById("weather-date").value;
                loadWeather();
            });
        }

        /** Charge la météo */
        function loadWeather(forceTodayLocation = false) {
            if (forceTodayLocation) {
                selectedDate = new Date().toISOString().slice(0, 10);
                if ("geolocation" in navigator) {
                    navigator.geolocation.getCurrentPosition(pos => {
                        const lat = pos.coords.latitude;
                        const lon = pos.coords.longitude;
                        fetchWeather(`lat=${lat}&lon=${lon}`);
                    }, () => showMessage("Impossible d'obtenir votre localisation."));
                }
                return;
            }

            if (savedCity) {
                fetchWeather(`city=${encodeURIComponent(savedCity)}`);
            } else if ("geolocation" in navigator) {
                navigator.geolocation.getCurrentPosition(pos => {
                    const lat = pos.coords.latitude;
                    const lon = pos.coords.longitude;
                    fetchWeather(`lat=${lat}&lon=${lon}`);
                }, () => showMessage("Impossible d'obtenir votre localisation."));
            } else {
                showMessage("Pas de localisation disponible.");
            }
        }

        /** Récupère la météo via API */
        function fetchWeather(params) {
            const dateParam = `&date=${selectedDate}`;
            fetch(`${wpweatherData.apiUrl}?${params}${dateParam}`)
                .then(r => r.json())
                .then(d => {
                    if (d.error) {
                        showMessage(d.error);
                    } else {
                        renderWeather(d);
                    }
                })
                .catch(() => showMessage("Erreur lors de la récupération météo."));
        }

        /** Bouton extérieur pour forcer la météo d'aujourd'hui */
        function createExternalButton() {
            let btn = document.createElement("button");
            btn.id = "btn-refresh-today";
            btn.innerText = "📍 Météo actuelle (ma position)";
            btn.style.margin = "10px 0";
            btn.style.padding = "8px 12px";
            btn.style.background = "#0073aa";
            btn.style.color = "#fff";
            btn.style.border = "none";
            btn.style.borderRadius = "4px";
            btn.style.cursor = "pointer";
            btn.addEventListener("click", () => {
                loadWeather(true);
            });
            container.parentNode.insertBefore(btn, container);
        }

        // Initialisation
        createExternalButton();
        loadWeather();
    }
})(window.wp.blocks, window.wp.element);
