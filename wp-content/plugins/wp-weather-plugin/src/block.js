(function (blocks, element) {
    const { registerBlockType } = blocks;
    const { createElement: el } = element;

    // Enregistrement du bloc Gutenberg
    registerBlockType('wpweather/block', {
        title: 'Bloc Météo',
        icon: 'cloud', // Icône WP
        category: 'widgets',
        edit: function () {
            return el('div', { id: 'weather-block' }, 'Chargement météo...');
        },
        save: function () {
            // Bloc dynamique : le rendu se fait côté PHP
            return el('div', { id: 'weather-block' }, 'Chargement météo...');
        }
    });
})(window.wp.blocks, window.wp.element);

// ==== Code météo en front ====
document.addEventListener("DOMContentLoaded", function () {
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
                        <strong class="city">${data.city}</strong><br/>
                        <span class="temp">${data.temp}°C</span><br/>
                        <span class="condition">${data.condition}</span>
                    `;
                }
            })
            .catch(() => showMessage("Erreur lors de la récupération météo."));
    }

    if ("geolocation" in navigator) {
        navigator.geolocation.getCurrentPosition(
            pos => fetchWeather(pos.coords.latitude, pos.coords.longitude),
            () => showMessage("Localisation refusée. Activez la géolocalisation.")
        );
    } else {
        showMessage("Géolocalisation non supportée.");
    }
});
