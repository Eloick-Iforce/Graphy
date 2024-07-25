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
        <div x-data="{ selectedChart: null }">
            <div class="flex w-96 h-96 gap-4">
                <?php foreach ($charts as $chart) : ?>
                    <div class="bg-white h-full w-full p-4 shadow rounded">
                        <h2 class="text-lg font-bold"><?php echo esc_html($chart['title']); ?></h2>
                        <div class="chart-container">
                            <canvas id="chart-<?php echo esc_attr($chart['id']); ?>"></canvas>
                        </div>
                        <script>
                            document.addEventListener('DOMContentLoaded', function() {
                                var ctx = document.getElementById('chart-<?php echo esc_attr($chart['id']); ?>').getContext('2d');
                                var chartData = JSON.parse(<?php echo wp_json_encode($chart['dataset_data']); ?>);
                                var chartType = JSON.parse(<?php echo wp_json_encode($chart['dataset_type']); ?>);

                                new Chart(ctx, {
                                    type: chartType[0],
                                    data: {
                                        labels: chartData[0].labels,
                                        datasets: chartData.map(function(dataset) {
                                            return {
                                                label: dataset.name,
                                                data: dataset.data,
                                                borderColor: 'rgba(75, 192, 192, 1)',
                                                borderWidth: 1,
                                                fill: false
                                            };
                                        })
                                    },
                                    options: {
                                        responsive: true,
                                        maintainAspectRatio: false,
                                    }
                                });
                            });
                        </script>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </main>
    <?php wp_footer(); ?>
</body>

</html>