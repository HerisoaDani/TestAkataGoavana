<?php

/**
 * Plugin Name: Prévision Météo Locale
 * Description: Affiche la météo selon la localisation de l'utilisateur avec Gutenberg.
 * Version: 1.0
 * Author: Daniella Rakotozandry
 */

if (!defined('ABSPATH')) exit;

// Rendu dynamique du bloc météo
function wp_weather_render_block()
{
    return '<div id="weather-block">Chargement météo...</div>';
}

function wp_weather_register_block()
{
    register_block_type(__DIR__, [
        'render_callback' => 'wp_weather_render_block'
    ]);
}
add_action('init', 'wp_weather_register_block');

// Charger le JS pour l'éditeur ET le front
function wp_weather_enqueue_scripts()
{
    wp_enqueue_script(
        'wpweather-block',
        plugins_url('src/block.js', __FILE__),
        array('wp-blocks', 'wp-element', 'wp-editor'),
        filemtime(plugin_dir_path(__FILE__) . 'src/block.js'),
        true
    );

    // Passer l'URL REST API dynamique à JavaScript
    wp_localize_script('wpweather-block', 'wpweatherData', array(
        'apiUrl' => esc_url_raw(rest_url('wpweather/v1/get-weather'))
    ));

    // Styles optionnels
    wp_enqueue_style(
        'wpweather-style',
        plugins_url('style.css', __FILE__),
        array(),
        filemtime(plugin_dir_path(__FILE__) . 'style.css')
    );
}
add_action('enqueue_block_editor_assets', 'wp_weather_enqueue_scripts');
add_action('wp_enqueue_scripts', 'wp_weather_enqueue_scripts');
