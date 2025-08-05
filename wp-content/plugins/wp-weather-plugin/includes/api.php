<?php
if (!defined('ABSPATH')) exit;

/**
 * Enregistrement de la route API REST
 */
add_action('rest_api_init', function () {
    register_rest_route('wpweather/v1', '/get-weather', array(
        'methods' => 'GET',
        'callback' => 'wp_weather_get_weather',
        'permission_callback' => '__return_true'
    ));
});

/**
 * Fonction principale : récupère la météo depuis la BDD ou l'API
 */
function wp_weather_get_weather(WP_REST_Request $request)
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'weather_cache';

    // Paramètres reçus
    $lat       = $request->get_param('lat');
    $lon       = $request->get_param('lon');
    $cityParam = sanitize_text_field($request->get_param('city'));
    $dateParam = sanitize_text_field($request->get_param('date')) ?: date('Y-m-d');

    //  Vérif format de la date (YYYY-MM-DD)
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateParam)) {
        return array("error" => "Date invalide.");
    }

    //Recherche en cache BDD
    $row = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM $table_name 
         WHERE ((latitude = %f AND longitude = %f) OR city = %s) 
         AND date = %s",
        floatval($lat),
        floatval($lon),
        $cityParam,
        $dateParam
    ));

    if ($row) {
        return (array) $row; // Retourne données en cache
    }

    // **récupération via API
    if ($cityParam) {
        return wpweather_fetch_and_store($cityParam, null, null, $dateParam);
    } elseif (is_numeric($lat) && is_numeric($lon)) {
        return wpweather_fetch_and_store(null, $lat, $lon, $dateParam);
    }

    return array("error" => "Aucune localisation fournie.");
}

/**
 * Récupère les données météo depuis WeatherAPI et les stocke en BDD
 */
function wpweather_fetch_and_store($city = null, $lat = null, $lon = null, $date)
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'weather_cache';
    $apiKey     = '517eaec408794961bff70427250308';

    // Construction de la requête API
    $location = $city ?: "$lat,$lon";
    $url = "https://api.weatherapi.com/v1/forecast.json?key={$apiKey}&q={$location}&lang=fr&dt={$date}";

    // Appel API
    $response = wp_remote_get($url);
    if (is_wp_error($response)) {
        return array("error" => "Erreur connexion API.");
    }

    $data = json_decode(wp_remote_retrieve_body($response), true);

    // Si aucune localisation trouvée
    if (empty($data['location']['name']) || empty($data['forecast']['forecastday'][0]['day'])) {
        return array("error" => "Pas de données météo trouvées.");
    }

    // Extraction des données
    $forecastDay = $data['forecast']['forecastday'][0]['day'];

    $insertData = array(
        'latitude'       => isset($lat) ? floatval($lat) : 0,
        'longitude'      => isset($lon) ? floatval($lon) : 0,
        'city'           => sanitize_text_field($data['location']['name']),
        'temp'           => floatval($forecastDay['avgtemp_c']),
        'feelslike'      => floatval($forecastDay['avgtemp_c']), // Pas de feelslike daily dans API
        'humidity'       => intval($forecastDay['avghumidity']),
        'wind_kph'       => floatval($forecastDay['maxwind_kph']),
        'visibility_km'  => floatval($forecastDay['avgvis_km']),
        'pressure_mb'    => 0, // Optionnel, non fourni par forecast daily
        'condition_text' => sanitize_text_field($forecastDay['condition']['text']),
        'icon'           => esc_url_raw("https:" . $forecastDay['condition']['icon']),
        'date'           => $date
    );

    // Insertion en BDD avec vérification
    $inserted = $wpdb->insert($table_name, $insertData);
    if ($inserted === false) {
        error_log("WPWeather - Erreur INSERT : " . $wpdb->last_error);
    } else {
        error_log("WPWeather - Données insérées pour {$insertData['city']} le {$date}");
    }

    return $insertData;
}
