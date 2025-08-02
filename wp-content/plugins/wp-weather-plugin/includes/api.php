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

    // Vérifier si c'est une recherche par ville
    if (!empty($cityParam)) {
        // On ignore la BDD pour la ville → recherche API directe
        return wp_weather_fetch_from_api_city($cityParam);
    }

    // Vérifier si coordonnées valides
    if (!is_numeric($lat) || !is_numeric($lon)) {
        return array("error" => "Coordonnées invalides.");
    }

    $lat = floatval($lat);
    $lon = floatval($lon);

    // Vérifier si on a déjà les données en BDD
    $row = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM $table_name WHERE latitude = %f AND longitude = %f AND date = %s",
        $lat,
        $lon,
        $today
    ));

    if ($row) {
        return array(
            "city" => $row->city,
            "temp" => $row->temp,
            "condition" => $row->condition_text
        );
    }

    // Sinon → appel API
    return wp_weather_fetch_from_api_coords($lat, $lon);
}

function wp_weather_fetch_from_api_coords($lat, $lon)
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'weather_cache';
    $today = date('Y-m-d');

    $apiKey = 'TA_CLE_API_ICI';
    $url = "https://api.weatherapi.com/v1/current.json?key={$apiKey}&q={$lat},{$lon}&lang=fr";

    $response = wp_remote_get($url);
    if (is_wp_error($response)) {
        return array("error" => "Erreur de connexion à WeatherAPI.");
    }

    $data = json_decode(wp_remote_retrieve_body($response), true);
    if (!isset($data['location']['name'])) {
        return array("error" => "Données météo introuvables.");
    }

    $city = sanitize_text_field($data['location']['name']);
    $temp = floatval($data['current']['temp_c']);
    $condition_text = sanitize_text_field($data['current']['condition']['text']);

    // Sauvegarder en BDD
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
    $apiKey = '5e9cdb4300f949d28ab145108250208';
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
