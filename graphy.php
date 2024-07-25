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

    add_submenu_page(
        'graphy',
        'New Chart',
        'New Chart',
        'manage_options',
        'graphy-new',
        'graphy_new_chart'
    );
}

function graphy_page()
{
    $charts = graphy_get_all_charts();

    require 'pages/index.php';
}

function graphy_new_chart()
{
    if (isset($_POST['chartTitle']) && check_admin_referer('submit_graphy_data_nonce', 'graphy_nonce_field')) {
        graphy_save_data();
    }
    require 'pages/new.php';
}

function graphy_create_table()
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'graphy';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        title varchar(255) NOT NULL,
        dataset_type longtext NOT NULL,
        dataset_data longtext NOT NULL,
        PRIMARY KEY (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}

register_activation_hook(__FILE__, 'graphy_create_table');


function graphy_get_all_charts()
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'graphy';
    $results = $wpdb->get_results("SELECT * FROM $table_name", ARRAY_A);
    return $results;
}

function graphy_get_chart($id)
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'graphy';
    $result = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $id), ARRAY_A);
    return $result;
}


function graphy_enqueue_scripts()
{
    wp_enqueue_script('chartjs', 'https://cdn.jsdelivr.net/npm/chart.js', array(), null, true);
    wp_enqueue_script('alpinejs', 'https://unpkg.com/alpinejs', array(), null, true);
    wp_enqueue_script('tailwindcss', 'https://cdn.tailwindcss.com', array(), null, true);
}
add_action('admin_enqueue_scripts', 'graphy_enqueue_scripts');

function graphy_save_data()
{
    if (!current_user_can('manage_options')) {
        wp_die('Vous n\'avez pas les permissions nécessaires pour accéder à cette page.');
    }

    $chart_title = sanitize_text_field($_POST['chartTitle']);
    $datasets = array_map(function ($dataset) {
        return [
            'name' => sanitize_text_field($dataset['name']),
            'type' => sanitize_text_field($dataset['type']),
            'labels' => array_map('sanitize_text_field', $dataset['labels']),
            'data' => array_map('floatval', $dataset['data']),
        ];
    }, $_POST['datasets']);

    $dataset_type = wp_json_encode(array_column($datasets, 'type'));
    $dataset_data = wp_json_encode($datasets);

    global $wpdb;
    $table_name = $wpdb->prefix . 'graphy';

    $wpdb->insert(
        $table_name,
        array(
            'title' => $chart_title,
            'dataset_type' => $dataset_type,
            'dataset_data' => $dataset_data,
        ),
        array(
            '%s',
            '%s',
            '%s'
        )
    );

    echo '<div class="updated notice"><p>Données enregistrées avec succès !</p></div>';
}
