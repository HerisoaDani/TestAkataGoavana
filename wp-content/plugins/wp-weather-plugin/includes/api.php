<?php
if (!defined('ABSPATH')) exit;

add_action('rest_api_init', function () {
    register_rest_route('wpweather/v1', '/get-weather', array(
        'methods' => 'GET',
        'callback' => 'wp_weather_get_weather',
        'permission_callback' => '__return_true'
    ));
});

function wp_weather_get_weather(WP_REST_Request $request)
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'weather_cache';
    $today = date('Y-m-d');

    $lat = $request->get_param('lat');
    $lon = $request->get_param('lon');
    $cityParam = sanitize_text_field($request->get_param('city'));

    // 🔍 Log des paramètres
    error_log("WPWeather - Paramètres reçus : lat={$lat}, lon={$lon}, city={$cityParam}");

    // Recherche par ville
    if (!empty($cityParam)) {
        return wp_weather_fetch_from_api_city($cityParam);
    }

    // Coordonnées invalides
    if (!is_numeric($lat) || !is_numeric($lon)) {
        error_log("WPWeather - Coordonnées invalides");
        return array("error" => "Coordonnées invalides.");
    }

    $lat = floatval($lat);
    $lon = floatval($lon);

    // Vérifier en cache
    $row = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM $table_name WHERE latitude = %f AND longitude = %f AND date = %s",
        $lat,
        $lon,
        $today
    ));

    if ($row) {
        error_log("WPWeather - Données trouvées en cache");
        return array(
            "city" => $row->city,
            "temp" => $row->temp,
            "condition" => $row->condition_text
        );
    }

    // Sinon API
    return wp_weather_fetch_from_api_coords($lat, $lon);
}

function wp_weather_fetch_from_api_coords($lat, $lon)
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'weather_cache';
    $today = date('Y-m-d');

    $apiKey = '517eaec408794961bff70427250308';
    $url = "https://api.weatherapi.com/v1/current.json?key={$apiKey}&q={$lat},{$lon}&lang=fr";

    // 🔍 Log URL
    error_log("WPWeather - URL API : " . $url);

    $response = wp_remote_get($url);

    if (is_wp_error($response)) {
        error_log("WPWeather - Erreur connexion API");
        return array("error" => "Erreur de connexion à WeatherAPI.");
    }

    $data = json_decode(wp_remote_retrieve_body($response), true);

    // 🔍 Log réponse brute
    error_log("WPWeather - Réponse API : " . print_r($data, true));

    if (!isset($data['location']['name'])) {
        error_log("WPWeather - Aucune localisation trouvée dans API");
        return array("error" => "Données météo introuvables.");
    }

    $city = sanitize_text_field($data['location']['name']);
    $temp = floatval($data['current']['temp_c']);
    $condition_text = sanitize_text_field($data['current']['condition']['text']);

    // Sauvegarde BDD
    $wpdb->insert($table_name, array(
        'latitude' => $lat,
        'longitude' => $lon,
        'city' => $city,
        'temp' => $temp,
        'condition_text' => $condition_text,
        'date' => $today
    ));

    return array(
        "city" => $city,
        "temp" => $temp,
        "condition" => $condition_text
    );
}


function wp_weather_fetch_from_api_city($cityParam)
{
    $apiKey = '517eaec408794961bff70427250308';
    $url = "https://api.weatherapi.com/v1/current.json?key={$apiKey}&q=" . urlencode($cityParam) . "&lang=fr";

    $response = wp_remote_get($url);
    if (is_wp_error($response)) {
        return array("error" => "Erreur de connexion à WeatherAPI.");
    }

    $data = json_decode(wp_remote_retrieve_body($response), true);
    if (!isset($data['location']['name'])) {
        return array("error" => "Données météo introuvables.");
    }

    return array(
        "city" => sanitize_text_field($data['location']['name']),
        "temp" => floatval($data['current']['temp_c']),
        "condition" => sanitize_text_field($data['current']['condition']['text'])
    );
}
