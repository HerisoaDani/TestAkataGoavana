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

    // Vérif format date
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateParam)) {
        return array("error" => "Date invalide.");
    }

    // Recherche en cache
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
        return (array) $row; // Retourne cache
    }

    // Sinon appel API
    if ($cityParam) {
        return wpweather_fetch_and_store($cityParam, null, null, $dateParam);
    } elseif (is_numeric($lat) && is_numeric($lon)) {
        return wpweather_fetch_and_store(null, $lat, $lon, $dateParam);
    }

    return array("error" => "Aucune localisation fournie.");
}

/**
 * Récupère depuis WeatherAPI et stocke en BDD
 */
function wpweather_fetch_and_store($city = null, $lat = null, $lon = null, $date)
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'weather_cache';
    $apiKey     = '517eaec408794961bff70427250308';

    // Utiliser lat/lon si dispo
    $location = ($lat && $lon) ? "{$lat},{$lon}" : $city;

    // Utiliser history si date passée
    $endpoint = ($date < date('Y-m-d')) ? 'history' : 'forecast';
    $url = "https://api.weatherapi.com/v1/{$endpoint}.json?key={$apiKey}&q={$location}&lang=fr&dt={$date}";

    $response = wp_remote_get($url);
    if (is_wp_error($response)) {
        return array("error" => "Erreur connexion API.");
    }

    $data = json_decode(wp_remote_retrieve_body($response), true);

    if (empty($data['location']['name']) || empty($data['forecast']['forecastday'][0]['day'])) {
        return array("error" => "Pas de données météo trouvées.");
    }

    $forecastDay = $data['forecast']['forecastday'][0]['day'];

    $insertData = array(
        'latitude'       => floatval($data['location']['lat']),
        'longitude'      => floatval($data['location']['lon']),
        'city'           => sanitize_text_field($data['location']['name']),
        'temp'           => floatval($forecastDay['avgtemp_c']),
        'feelslike'      => floatval($forecastDay['avgtemp_c']),
        'humidity'       => intval($forecastDay['avghumidity']),
        'wind_kph'       => floatval($forecastDay['maxwind_kph']),
        'visibility_km'  => floatval($forecastDay['avgvis_km']),
        'pressure_mb'    => 0,
        'condition_text' => sanitize_text_field($forecastDay['condition']['text']),
        'icon'           => esc_url_raw("https:" . $forecastDay['condition']['icon']),
        'date'           => $date
    );

    // Éviter les doublons
    $exists = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM $table_name WHERE city = %s AND date = %s",
        $insertData['city'],
        $insertData['date']
    ));

    if (!$exists) {
        $wpdb->insert($table_name, $insertData);
    }

    return $insertData;
}
