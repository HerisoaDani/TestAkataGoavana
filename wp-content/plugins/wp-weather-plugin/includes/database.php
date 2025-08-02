<?php
if (!defined('ABSPATH')) exit; // Sécurité

global $wpdb;
$table_name = $wpdb->prefix . 'weather_cache';

// Créer la table si elle n'existe pas déjà
function wp_weather_create_table()
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'weather_cache';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        latitude FLOAT NOT NULL,
        longitude FLOAT NOT NULL,
        city VARCHAR(100) NOT NULL,
        temp FLOAT NOT NULL,
        condition_text VARCHAR(255) NOT NULL,
        date DATE NOT NULL,
        PRIMARY KEY  (id)
    ) $charset_collate;";

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta($sql);
}
register_activation_hook(__FILE__, 'wp_weather_create_table');
