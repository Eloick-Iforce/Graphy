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
    if (isset($_POST['chartTitle']) && check_admin_referer('submit_graphy_data_nonce', 'graphy_nonce_field')) {
        graphy_save_data();
    }

    $chart_data = graphy_get_chart_data();

?>
    <!DOCTYPE html>
    <html lang="fr">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Graphy</title>
        <?php wp_head(); ?>
    </head>

    <body>
        <main class="flex flex-col gap-8 m-16 rounded-lg p-8 bg-white">
            <h1 class="text-4xl font-bold">Graphy</h1>
            <p>Bon retour sur Graphy !</p>

            <form id="graphyForm" method="post">
                <?php wp_nonce_field('submit_graphy_data_nonce', 'graphy_nonce_field'); ?>
                <div class="mb-4">
                    <label for="chartTitle" class="block">Titre du graphique:</label>
                    <input type="text" id="chartTitle" name="chartTitle" class="p-2 border rounded w-full" placeholder="Titre du graphique">
                </div>
                <div>
                    <!-- Template pour les ensembles de données. Vous pouvez générer ces éléments dynamiquement si nécessaire -->
                    <div class="mb-4 p-4 border rounded bg-blue-200/30 border-blue-300">
                        <div class="flex justify-between items-center mb-2">
                            <div>
                                <h3 class="text-lg font-semibold">Dataset 1</h3>
                                <input type="text" name="datasets[0][name]" class="p-2 border rounded mt-2" placeholder="Nom du dataset">
                            </div>
                            <button type="button" class="p-2 bg-red-500 text-white rounded">Supprimer</button>
                        </div>
                        <div>
                            <label for="chartType" class="block">Type de graphique:</label>
                            <select name="datasets[0][type]" class="p-2 border rounded">
                                <option value="bar">Bar</option>
                                <option value="line">Line</option>
                                <option value="pie">Pie</option>
                                <option value="doughnut">Doughnut</option>
                                <option value="polarArea">Polar Area</option>
                            </select>
                        </div>
                        <div class="mt-2">
                            <label class="block">Étiquettes et Données:</label>
                            <div class="flex items-center mb-2">
                                <input type="text" name="datasets[0][labels][]" class="p-2 border rounded w-1/2 mr-2" placeholder="Étiquette">
                                <input type="number" name="datasets[0][data][]" class="p-2 border rounded w-1/2" placeholder="Valeur">
                            </div>
                            <!-- Ajouter d'autres champs si nécessaire -->
                        </div>
                    </div>
                    <!-- Ajouter d'autres ensembles de données dynamiquement si nécessaire -->
                </div>
                <button type="submit" class="bg-blue-500 text-white rounded w-fit px-4 py-2">Enregistrer les données</button>
            </form>

            <!-- Affichage du graphique -->
            <div class="grid grid-cols-1 gap-8 mt-8">
                <div>
                    <canvas id="userChart"></canvas>
                </div>
            </div>

            <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
            <script>
                // Fonction pour générer le graphique
                function generateChart(chartData) {
                    const ctx = document.getElementById('userChart').getContext('2d');

                    new Chart(ctx, {
                        type: chartData.type,
                        data: {
                            labels: chartData.labels,
                            datasets: chartData.datasets.map(dataset => ({
                                label: dataset.name,
                                data: dataset.data,
                                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                                borderColor: 'rgba(75, 192, 192, 1)',
                                borderWidth: 1
                            }))
                        },
                        options: {
                            scales: {
                                y: {
                                    beginAtZero: true
                                }
                            }
                        }
                    });
                }

                // Exemple de données pour le graphique
                const exampleChartData = <?php echo json_encode($chart_data); ?>;

                // Générer le graphique avec les données d'exemple
                generateChart(exampleChartData);
            </script>
        </main>
        <?php wp_footer(); ?>
    </body>

    </html>
<?php
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

    // Redirection ou message de succès
    echo '<div class="updated notice"><p>Données enregistrées avec succès !</p></div>';
}

function graphy_get_chart_data()
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'graphy';

    $results = $wpdb->get_results("SELECT * FROM $table_name ORDER BY id DESC LIMIT 1", ARRAY_A);

    if (!empty($results)) {
        $latest_entry = $results[0];
        $datasets = json_decode($latest_entry['dataset_data'], true);

        return [
            'type' => json_decode($latest_entry['dataset_type'], true)[0] ?? 'bar',
            'labels' => $datasets[0]['labels'] ?? [],
            'datasets' => array_map(function ($dataset) {
                return [
                    'name' => $dataset['name'],
                    'data' => $dataset['data']
                ];
            }, $datasets)
        ];
    }

    // Retourner des données par défaut si aucune entrée trouvée
    return [
        'type' => 'bar',
        'labels' => ['Example'],
        'datasets' => [
            [
                'name' => 'Dataset 1',
                'data' => [10]
            ]
        ]
    ];
}
?>