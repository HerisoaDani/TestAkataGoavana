<?php

register_activation_hook(__FILE__, 'wp_weather_create_table');

function wp_weather_create_table()
{
    global $wpdb;
    $table = $wpdb->prefix . 'weather_cache';
    $charset = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table (
        id INT NOT NULL AUTO_INCREMENT,
        city VARCHAR(100),
        lat VARCHAR(50),
        lon VARCHAR(50),
        date DATE,
        temp FLOAT,
        condition VARCHAR(100),
        PRIMARY KEY (id)
    ) $charset;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}
