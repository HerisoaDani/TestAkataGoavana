<?php
if (!defined('ABSPATH')) exit;

global $wpdb;
$table_name = $wpdb->prefix . 'weather_cache';

/**
 * Création de la table au moment de l'activation du plugin
 */
function wpweather_create_table()
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'weather_cache';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        latitude FLOAT(10,6) NOT NULL,
        longitude FLOAT(10,6) NOT NULL,
        city VARCHAR(100) NOT NULL,
        temp FLOAT(4,1) NOT NULL,
        condition_text VARCHAR(100) NOT NULL,
        date DATE NOT NULL,
        PRIMARY KEY  (id),
        KEY location_date (latitude, longitude, date)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}
register_activation_hook(__FILE__, 'wpweather_create_table');
