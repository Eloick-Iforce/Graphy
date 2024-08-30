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

    add_submenu_page(
        null, // Caché du menu
        'Modifier Graphique',
        'Modifier Graphique',
        'manage_options',
        'graphy-modify',
        'graphy_modify_chart'
    );
}

function graphy_page()
{
    $charts = graphy_get_all_charts();

    foreach ($charts as &$chart) {
        $chart['edit_link'] = wp_nonce_url(
            admin_url('admin.php?page=graphy-modify&id=' . $chart['id']),
            'edit_graphy_chart_' . $chart['id']
        );
    }

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


add_action('admin_post_delete_chart', 'graphy_process_delete_chart');

function graphy_process_delete_chart()
{
    if (!isset($_POST['graphy_delete_nonce']) || !wp_verify_nonce($_POST['graphy_delete_nonce'], 'delete_chart')) {
        wp_die('Nonce verification failed');
    }

    if (!current_user_can('manage_options')) {
        wp_die('Unauthorized user');
    }

    $id = intval($_POST['id']);
    if ($id > 0) {
        graphy_delete_chart($id);
        wp_redirect(admin_url('admin.php?page=graphy'));
        exit;
    } else {
        wp_die('Invalid chart ID');
    }
}

function graphy_delete_chart($id)
{

    global $wpdb;
    $table_name = $wpdb->prefix . 'graphy';
    $wpdb->delete($table_name, ['id' => $id]);
}

function graphy_enqueue_scripts()
{
    wp_enqueue_script('chartjs', 'https://cdn.jsdelivr.net/npm/chart.js', array(), null, true);
    wp_enqueue_script('alpinejs', 'https://unpkg.com/alpinejs', array(), null, true);
    wp_enqueue_style('graphy', plugin_dir_url(__FILE__) . 'src/output.css');
}
add_action('admin_enqueue_scripts', 'graphy_enqueue_scripts');
add_action('wp_enqueue_scripts', 'graphy_enqueue_scripts'); // Add this line to enqueue scripts on the frontend

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

    wp_redirect(admin_url('admin.php?page=graphy'));
    echo '<div class="updated notice"><p>Données enregistrées avec succès !</p></div>';
}

function graphy_modify_chart()
{
    if (!current_user_can('manage_options')) {
        wp_die(__('Vous n\'avez pas les permissions nécessaires pour accéder à cette page.'));
    }

    if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
        wp_die(__('Identifiant de graphique invalide.'));
    }

    $chart_id = intval($_GET['id']);

    require 'pages/modify.php';
}

function graphy_process_update_chart()
{
    if (!isset($_POST['graphy_nonce_field']) || !wp_verify_nonce($_POST['graphy_nonce_field'], 'modify_graphy_data_nonce')) {
        wp_die('Nonce verification failed');
    }

    if (!current_user_can('manage_options')) {
        wp_die('Vous n\'avez pas les permissions nécessaires pour accéder à cette page.');
    }

    $chart_id = intval($_POST['chart_id']);
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

    $wpdb->update(
        $table_name,
        array(
            'title' => $chart_title,
            'dataset_type' => $dataset_type,
            'dataset_data' => $dataset_data,
        ),
        array('id' => $chart_id),
        array(
            '%s',
            '%s',
            '%s'
        ),
        array('%d')
    );

    wp_redirect(admin_url('admin.php?page=graphy'));
    exit;
}

add_action('admin_post_graphy_update_chart', 'graphy_process_update_chart');

function register_graphy_widget($widgets_manager)
{
    if (!did_action('elementor/loaded')) {
        return;
    }

    require_once(__DIR__ . '/widget/graphy-widget.php');
    $widgets_manager->register_widget_type(new \Graphy_Widget());
}
add_action('elementor/widgets/widgets_registered', 'register_graphy_widget');
