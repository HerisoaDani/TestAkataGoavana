(function (blocks, element) {
    const { registerBlockType } = blocks;
    const { createElement: el } = element;

    // Déclaration du bloc Gutenberg
    registerBlockType('wpweather/block', {
        title: 'Bloc Météo',
        icon: 'cloud',
        category: 'widgets',
        description: 'Affiche la météo selon la localisation et la date.',
        supports: { html: false },
        edit: () => el('div', { className: 'wpweather-editor-placeholder' }, 'Aperçu météo (front uniquement)'),
        save: () => null
    });

    // Exécution seulement en front
    if (typeof window !== 'undefined' && document.getElementById("weather-block")) {
        const container = document.getElementById("weather-block");
        let selectedDate = new Date().toISOString().slice(0, 10);
        let savedCity = localStorage.getItem("wpweather_city");

        // Charger Font Awesome depuis CDN
        const faLink = document.createElement("link");
        faLink.rel = "stylesheet";
        faLink.href = "https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css";
        document.head.appendChild(faLink);

        /** Affiche un message d'erreur ou d'info */
        function showMessage(msg) {
            container.innerHTML = `<p style="text-align:center;font-weight:bold;color:#cc0000;">${msg}</p>`;
        }

        /** Affiche les données météo */
        function renderWeather(data) {
            container.innerHTML = `
                <div class="wpweather-card-landscape" style="display:flex;flex-wrap:wrap;gap:20px;align-items:center;border:1px solid #ddd;padding:15px;border-radius:8px;box-shadow:0 2px 5px rgba(0,0,0,0.1);">
                    
                    <div class="left-side" style="flex:1;min-width:200px;text-align:center;">
                        <h3><i class="fa-solid fa-earth-africa"></i> ${data.city}</h3>
                        <small>
                            <i class="fa-regular fa-calendar"></i> ${selectedDate} <br>
                            <i class="fa-solid fa-location-dot"></i> ${parseFloat(data.latitude).toFixed(4)}, ${parseFloat(data.longitude).toFixed(4)}
                        </small>
                        <div style="margin-top:10px;">
                            <img src="${data.icon}" alt="${data.condition_text}" style="max-width:80px;"/>
                        </div>
                        <div class="temp" style="font-size:1.8em;font-weight:bold;margin-top:5px;">
                            ${data.temp}°C
                        </div>
                        <div class="condition" style="color:#555;">${data.condition_text}</div>
                    </div>

                    <div class="right-side" style="flex:1;min-width:200px;">
                        <div class="details" style="font-size:0.9em;line-height:1.6;">
                            <div><i class="fa-solid fa-temperature-low"></i> Ressenti : ${data.feelslike}°C</div>
                            <div><i class="fa-solid fa-droplet"></i> Humidité : ${data.humidity}%</div>
                            <div><i class="fa-solid fa-wind"></i> Vent : ${data.wind_kph} km/h</div>
                            <div><i class="fa-regular fa-eye"></i> Visibilité : ${data.visibility_km} km</div>
                            <div><i class="fa-solid fa-gauge"></i> Pression : ${data.pressure_mb} mb</div>
                        </div>
                        
                        <div class="controls" style="margin-top:10px;">
                            <input type="date" id="weather-date" value="${selectedDate}" style="padding:5px;"/>
                            <button id="refreshWeather" style="padding:6px 10px;margin-left:5px;background:#0073aa;color:white;border:none;border-radius:4px;cursor:pointer;">
                                <i class="fa-solid fa-rotate"></i> Actualiser
                            </button>
                        </div>
                    </div>
                </div>
            `;

            // Changement de date
            document.getElementById("weather-date").addEventListener("change", () => {
                selectedDate = document.getElementById("weather-date").value;
                loadWeather();
            });

            // Bouton actualiser
            document.getElementById("refreshWeather").addEventListener("click", () => {
                selectedDate = document.getElementById("weather-date").value;
                loadWeather();
            });
        }

        /** Charge la météo */
        function loadWeather(forceTodayLocation = false, forceRefresh = false) {
            if (forceTodayLocation) {
                selectedDate = new Date().toISOString().slice(0, 10);
                if ("geolocation" in navigator) {
                    navigator.geolocation.getCurrentPosition(pos => {
                        let lat = pos.coords.latitude;
                        let lon = pos.coords.longitude;
                        fetchWeather(`lat=${lat}&lon=${lon}`, forceRefresh);
                    }, () => showMessage("Impossible d'obtenir votre localisation."));
                }
                return;
            }

            if (savedCity) {
                fetchWeather(`city=${encodeURIComponent(savedCity)}`, forceRefresh);
            } else if ("geolocation" in navigator) {
                navigator.geolocation.getCurrentPosition(pos => {
                    fetchWeather(`lat=${pos.coords.latitude}&lon=${pos.coords.longitude}`, forceRefresh);
                }, () => showMessage("Impossible d'obtenir votre localisation."));
            } else {
                showMessage("Pas de localisation disponible.");
            }
        }

        /** Récupère météo via API */
        function fetchWeather(params, forceRefresh = false) {
            const dateParam = `&date=${selectedDate}`;
            const forceParam = forceRefresh ? "&force=1" : "";
            fetch(`${wpweatherData.apiUrl}?${params}${dateParam}${forceParam}`)
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

        /** Bouton extérieur : météo actuelle */
        function createExternalButton() {
            let btn = document.createElement("button");
            btn.id = "btn-refresh-today";
            btn.innerHTML = `<i class="fa-solid fa-location-crosshairs"></i> Météo actuelle (ma position)`;
            btn.style.margin = "10px 0";
            btn.style.padding = "8px 12px";
            btn.style.background = "#0073aa";
            btn.style.color = "#fff";
            btn.style.border = "none";
            btn.style.borderRadius = "4px";
            btn.style.cursor = "pointer";
            btn.addEventListener("click", () => {
                // Ici, on recharge la page complète au lieu de faire un refresh JS
                location.reload();
            });
            container.parentNode.insertBefore(btn, container);
        }

        // Initialisation
        createExternalButton();
        loadWeather();
    }
})(window.wp.blocks, window.wp.element);
