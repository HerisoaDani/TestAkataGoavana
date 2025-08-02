// Récupération des fonctions Gutenberg depuis les variables globales
const { registerBlockType } = wp.blocks;
const { useEffect } = wp.element;

registerBlockType('wpweather/block', {
    edit() {
        // Ce code s'exécute uniquement dans l'éditeur
        useEffect(() => {
            const container = document.getElementById("weather-block");
            if (!container) return;

            if ("geolocation" in navigator) {
                navigator.geolocation.getCurrentPosition(
                    (pos) => {
                        const lat = pos.coords.latitude;
                        const lon = pos.coords.longitude;

                        fetch(`/wp-json/wpweather/v1/get-weather?lat=${lat}&lon=${lon}`)
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
                        container.innerText = "Localisation refusée.";
                    }
                );
            } else {
                container.innerText = "Géolocalisation non supportée.";
            }
        }, []);

        return "Bloc météo (prévisualisation)";
    },
    save() {
        return null; // Rendu dynamique via PHP
    }
});