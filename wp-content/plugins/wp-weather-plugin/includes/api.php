<?php

add_action('rest_api_init', function () {
    register_rest_route('wpweather/v1', '/get-weather', [
        'methods' => 'GET',
        'callback' => 'wp_weather_get_weather'
    ]);
});

function wp_weather_get_weather($request)
{
    global $wpdb;

    $lat = sanitize_text_field($request->get_param('lat'));
    $lon = sanitize_text_field($request->get_param('lon'));
    $today = date('Y-m-d');

    // Vérifie le cache
    $table = $wpdb->prefix . 'weather_cache';
    $cache = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM $table WHERE lat=%s AND lon=%s AND date=%s",
        $lat,
        $lon,
        $today
    ));

    if ($cache) {
        return [
            'city' => $cache->city,
            'temp' => $cache->temp,
            'condition' => $cache->condition
        ];
    }

    // Appel WeatherAPI
    $apiKey = 'eedb425208b54663b2184256250208';
    $url = "https://api.weatherapi.com/v1/current.json?key={$apiKey}&q={$lat},{$lon}&lang=fr";

    $response = wp_remote_get($url);
    if (is_wp_error($response)) {
        return ['error' => 'Erreur API météo.'];
    }

    $data = json_decode(wp_remote_retrieve_body($response), true);
    if (!isset($data['location'])) {
        return ['error' => 'Pas de données météo disponibles.'];
    }

    // Sauvegarde en BDD
    $wpdb->insert($table, [
        'city' => $data['location']['name'],
        'lat' => $lat,
        'lon' => $lon,
        'date' => $today,
        'temp' => $data['current']['temp_c'],
        'condition' => $data['current']['condition']['text']
    ]);

    return [
        'city' => $data['location']['name'],
        'temp' => $data['current']['temp_c'],
        'condition' => $data['current']['condition']['text']
    ];
}
