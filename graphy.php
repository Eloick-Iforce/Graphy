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


function graphy_create_table()
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'graphy';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        title varchar(255) NOT NULL,
        datasets longtext NOT NULL,
        PRIMARY KEY (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}

register_activation_hook(__FILE__, 'graphy_create_table');

function graphy_enqueue_scripts()
{
    wp_enqueue_script('chartjs', 'https://cdn.jsdelivr.net/npm/chart.js', array(), null, true);
    wp_enqueue_script('alpinejs', 'https://unpkg.com/alpinejs', array(), null, true);
    wp_localize_script('alpinejs', 'wpApiSettings', array(
        'ajaxurl' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('wp_rest')
    ));
}
add_action('wp_enqueue_scripts', 'graphy_enqueue_scripts');

// Gestion de l'endpoint AJAX pour enregistrer les données
function graphy_save_chart_data()
{
    check_ajax_referer('wp_rest', 'security');

    global $wpdb;
    $table_name = $wpdb->prefix . 'graphy';

    $data = json_decode(file_get_contents('php://input'), true);
    $title = sanitize_text_field($data['data']['title']);
    $datasets = wp_json_encode($data['data']['datasets']);

    $wpdb->insert(
        $table_name,
        array(
            'title' => $title,
            'datasets' => $datasets
        ),
        array(
            '%s',
            '%s'
        )
    );

    if ($wpdb->insert_id) {
        wp_send_json_success();
    } else {
        wp_send_json_error();
    }
}
add_action('wp_ajax_save_chart_data', 'graphy_save_chart_data');
add_action('wp_ajax_nopriv_save_chart_data', 'graphy_save_chart_data');
