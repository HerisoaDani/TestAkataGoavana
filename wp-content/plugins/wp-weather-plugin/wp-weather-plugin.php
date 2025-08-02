<?php

/**
 * Plugin Name: Prévision Météo Locale
 * Description: Affiche la météo selon la localisation de l'utilisateur avec Gutenberg.
 * Version: 1.0
 * Author: Daniella Rakotozandry
 */

if (!defined('ABSPATH')) exit;

function meteo_local_enqueue_assets()
{
    wp_enqueue_script(
        'meteo-local-block',
        plugin_dir_url(__FILE__) . 'build/index.js',
        array('wp-blocks', 'wp-element', 'wp-editor', 'wp-components'),
        filemtime(plugin_dir_path(__FILE__) . 'build/index.js')
    );

    wp_enqueue_style(
        'meteo-local-style',
        plugin_dir_url(__FILE__) . 'style.css'
    );
}
add_action('enqueue_block_editor_assets', 'meteo_local_enqueue_assets');
