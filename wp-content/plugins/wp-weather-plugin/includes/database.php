<?php
if (!defined('ABSPATH')) exit;

function wpweather_create_table()
{
    // Création de la table pour stocker les données météo
    global $wpdb;
    $table_name = $wpdb->prefix . 'weather_cache';
    $charset_collate = $wpdb->get_charset_collate();

    // Création de la table si elle n'existe pas
    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        latitude FLOAT(10,6) NOT NULL,
        longitude FLOAT(10,6) NOT NULL,
        city VARCHAR(100) NOT NULL,
        temp FLOAT(4,1) NOT NULL,
        feelslike FLOAT(4,1) NOT NULL,
        humidity INT(3) NOT NULL,
        wind_kph FLOAT(4,1) NOT NULL,
        visibility_km FLOAT(4,1) NOT NULL,
        pressure_mb FLOAT(6,1) NOT NULL,
        condition_text VARCHAR(100) NOT NULL,
        icon VARCHAR(255) DEFAULT '',
        date DATE NOT NULL,
        PRIMARY KEY  (id),
        KEY location_date (latitude, longitude, date)
    ) $charset_collate;";

    // Inclure la fonction dbDelta pour créer la table
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}
register_activation_hook(__FILE__, 'wpweather_create_table');
