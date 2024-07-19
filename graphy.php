<?php
/*
Plugin Name: Graphy
Description: Un plugin pour créer des graphiques
Version: 1.0
Author: Eloïck Mickisz
*/

add_action('admin_menu', 'graphy_menu');

function graphy_menu()
{
    add_menu_page(
        'Graphy',
        'Graphy',
        'manage_options',
        'graphy',
        'graphy_page',
        'dashicons-admin-generic',
        6
    );
}

function graphy_page()
{
    require 'pages/index.php';
}

function create_graphy_table()
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'graphy_data';

    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id INT NOT NULL AUTO_INCREMENT,
        dataset_index INT NOT NULL,
        label VARCHAR(255) NOT NULL,
        value INT NOT NULL,
        PRIMARY KEY (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}
register_activation_hook(__FILE__, 'create_graphy_table');

function save_graphy_data($datasets)
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'graphy_data';

    foreach ($datasets as $dataset_index => $dataset) {
        foreach ($dataset['labels'] as $index => $label) {
            $value = $dataset['data'][$index];

            $wpdb->insert(
                $table_name,
                array(
                    'dataset_index' => $dataset_index,
                    'label' => $label,
                    'value' => $value
                ),
                array(
                    '%d',
                    '%s',
                    '%d'
                )
            );
        }
    }
}
