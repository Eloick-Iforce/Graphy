<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Graphy Charts</title>
    <!-- TailwindCSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <?php wp_head(); ?>
</head>

<body class="bg-gray-100 p-6">
    <main class="flex flex-col gap-8 m-16 rounded-lg p-8 bg-white">
        <h1 class="text-4xl font-bold">Graphy</h1>
        <p>Bon retour sur Graphy !</p>
        <div x-data="{ selectedChart: null, showModal: false, chart: {}, datasets: [] }">
            <div class="flex flex-wrap gap-4">
                <?php foreach ($charts as $chart) : ?>
                    <div class="bg-white h-96 w-96 p-4 shadow rounded">
                        <h2 class="text-lg font-bold"><?php echo esc_html($chart['title']); ?></h2>
                        <div class="chart-container">
                            <canvas id="chart-<?php echo esc_attr($chart['id']); ?>"></canvas>
                        </div>
                        <button @click="selectedChart = <?php echo esc_attr($chart['id']); ?>; chart = { id: <?php echo esc_attr($chart['id']); ?>, title: '<?php echo esc_html($chart['title']); ?>', datasets: <?php echo wp_json_encode($chart['dataset_data']); ?> }; showModal = true;" class="mt-2 text-blue-500">Modifier</button>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Modal -->
            <div x-show="showModal" x-cloak class="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center">
                <div class="bg-white p-6 rounded-lg shadow-lg w-full max-w-lg">
                    <h2 class="text-2xl font-bold mb-4">Modifier le graphique</h2>
                    <form @submit.prevent="submitForm" class="space-y-4">
                        <div>
                            <label for="title" class="block text-sm font-medium text-gray-700">Titre du graphique</label>
                            <input type="text" id="title" x-model="chart.title" class="border p-2 w-full" required />
                        </div>

                        <template x-for="(dataset, index) in chart.datasets" :key="index">
                            <div>
                                <label :for="'dataset-name-' + index" class="block text-sm font-medium text-gray-700">Nom du dataset</label>
                                <input type="text" :id="'dataset-name-' + index" x-model="dataset.name" class="border p-2 w-full" required />

                                <label :for="'dataset-data-' + index" class="block text-sm font-medium text-gray-700">Données (séparées par des virgules)</label>
                                <input type="text" :id="'dataset-data-' + index" x-model="dataset.data" class="border p-2 w-full" required />
                            </div>
                        </template>

                        <div class="flex justify-end">
                            <button type="button" @click="showModal = false" class="mr-2 bg-gray-300 text-gray-700 px-4 py-2 rounded">Annuler</button>
                            <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded">Enregistrer</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>
    <?php wp_footer(); ?>
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('chartData', () => ({
                selectedChart: null,
                showModal: false,
                chart: {},
                datasets: [],
                submitForm() {
                    let formData = new FormData();
                    formData.append('action', 'update_chart_data');
                    formData.append('chartId', this.chart.id);
                    formData.append('title', this.chart.title);
                    formData.append('datasets', JSON.stringify(this.chart.datasets));
                    formData.append('<?php echo wp_create_nonce('update_chart_data_nonce'); ?>', 'update_chart_nonce_field');

                    fetch(ajaxurl, {
                            method: 'POST',
                            body: formData
                        })
                        .then(response => response.text())
                        .then(data => {
                            console.log(data);
                            this.showModal = false;
                            location.reload();
                        });
                }
            }))
        })
    </script>
</body>

</html>