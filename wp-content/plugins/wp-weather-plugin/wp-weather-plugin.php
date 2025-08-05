<?php

/**
 * Plugin Name: Prévision Météo Locale
 * Description: Affiche la météo selon la localisation de l'utilisateur avec Gutenberg (cache BDD).
 * Version: 2.1.2
 * Author: Daniella Rakotozandry
 */

if (!defined('ABSPATH')) exit;

// Charger API & BDD
require_once plugin_dir_path(__FILE__) . 'includes/api.php';
require_once plugin_dir_path(__FILE__) . 'includes/database.php';

/**
 * Création de la table à l'activation du plugin
 */
register_activation_hook(__FILE__, 'wpweather_create_table');

/**
 * Vérifie à chaque chargement si la table existe, sinon la crée
 * (permet de corriger si l'activation n'a pas fonctionné)
 */
function wpweather_check_table_exists()
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'weather_cache';

    if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") !== $table_name) {
        wpweather_create_table();
    }
}
add_action('plugins_loaded', 'wpweather_check_table_exists');

/**
 * Rendu dynamique du bloc météo
 */
function wp_weather_render_block()
{
    return '<div id="weather-block">Chargement météo...</div>';
}

/**
 * Enregistrement du bloc Gutenberg
 */
function wp_weather_register_block()
{
    register_block_type(
        __DIR__, // Utilise block.json
        array(
            'render_callback' => 'wp_weather_render_block'
        )
    );
}
add_action('init', 'wp_weather_register_block');

/**
 * Enregistrement des scripts & styles
 */
function wp_weather_register_assets()
{
    // Script bloc Gutenberg + logique front météo
    wp_register_script(
        'wpweather-block',
        plugins_url('src/block.js', __FILE__),
        array('wp-blocks', 'wp-element'),
        filemtime(plugin_dir_path(__FILE__) . 'src/block.js'),
        true
    );

    // Localisation des données pour JS
    wp_localize_script('wpweather-block', 'wpweatherData', array(
        'apiUrl' => esc_url_raw(rest_url('wpweather/v1/get-weather'))
    ));

    // Styles CSS
    wp_register_style(
        'wpweather-style',
        plugins_url('style.css', __FILE__),
        array(),
        filemtime(plugin_dir_path(__FILE__) . 'style.css')
    );
}
add_action('init', 'wp_weather_register_assets');

/**
 * Chargement dans l’éditeur Gutenberg
 */
function wp_weather_enqueue_editor_assets()
{
    wp_enqueue_script('wpweather-block');
    wp_enqueue_style('wpweather-style');
}
add_action('enqueue_block_editor_assets', 'wp_weather_enqueue_editor_assets');

/**
 * Chargement en front
 */
function wp_weather_enqueue_front_assets()
{
    wp_enqueue_script('wpweather-block');
    wp_enqueue_style('wpweather-style');
}
add_action('wp_enqueue_scripts', 'wp_weather_enqueue_front_assets');
