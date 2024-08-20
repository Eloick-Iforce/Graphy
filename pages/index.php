<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Graphy Charts</title>
    <?php wp_head(); ?>
</head>

<body class="bg-gray-100 p-6">
    <main class="flex flex-col items-center gap-8 rounded-lg p-8 bg-white" x-data="{ open: false }">
        <div class="flex flex-col items-center justify-center gap-16">
            <h1 class="text-4xl font-bold text-center">Graphy</h1>

            <div class="flex justify-between items-center gap-8 w-full">
                <h2 class="text-2xl font-light italic">Vos graphiques :</h2>
                <a href="<?php echo admin_url('admin.php?page=graphy-new'); ?>" class="py-1 px-4 border text-purple-500 border-purple-600 bg-purple-100 hover:bg-purple-600 hover:text-white rounded-full flex items-center gap-2 justify-center text-sm"><span class="text-2xl">+</span> Nouveau graphique</a>
            </div>

            <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
            <div class="flex flex-wrap gap-4">
                <?php foreach ($charts as $chart) : ?>
                    <div class="bg-white h-96 w-[48rem] p-4 shadow rounded flex flex-col gap-4">
                        <div class="flex justify-between items-center gap-4">
                            <h3 class="text-xl font-bold"><?php echo esc_html($chart['title']); ?></h3>
                            <div class="flex gap-2">
                                <button class="p-0 px-2 border-dashed text-blue-500 border-blue-600 bg-blue-100 hover:bg-blue-600 hover:text-white rounded-full flex items-center gap-2 justify-center text-xs"><span class="text-xl">+</span> Modifier</button>
                                <form action="<?php echo admin_url('admin-post.php'); ?>" method="post">
                                    <?php wp_nonce_field('delete_chart', 'graphy_delete_nonce'); ?>
                                    <input type="hidden" name="action" value="delete_chart">
                                    <input type="hidden" name="id" value="<?php echo esc_attr($chart['id']); ?>">
                                    <button class="p-0 px-2 border-dashed text-red-500 border-red-600 bg-red-100 rounded-full flex items-center gap-2 justify-center text-xs hover:bg-red-600 hover:text-white"><span class="text-xl">x</span> Supprimer</button>
                                </form>
                            </div>
                        </div>
                        <div class="chart-container">
                            <canvas id="chart-<?php echo esc_attr($chart['id']); ?>"></canvas>
                        </div>
                        <script>
                            document.addEventListener('DOMContentLoaded', function() {
                                var ctx = document.getElementById('chart-<?php echo esc_attr($chart['id']); ?>').getContext('2d');
                                var chartData = <?php echo wp_json_encode(json_decode($chart['dataset_data'], true)); ?>;
                                var chartTypes = <?php echo wp_json_encode(json_decode($chart['dataset_type'], true)); ?>;

                                var colors = [
                                    'rgba(255, 99, 132, 0.2)',
                                    'rgba(54, 162, 235, 0.2)',
                                    'rgba(255, 206, 86, 0.2)',
                                    'rgba(75, 192, 192, 0.2)',
                                    'rgba(153, 102, 255, 0.2)',
                                    'rgba(255, 159, 64, 0.2)'
                                ];
                                var borderColor = [
                                    'rgba(255, 99, 132, 1)',
                                    'rgba(54, 162, 235, 1)',
                                    'rgba(255, 206, 86, 1)',
                                    'rgba(75, 192, 192, 1)',
                                    'rgba(153, 102, 255, 1)',
                                    'rgba(255, 159, 64, 1)'
                                ];

                                new Chart(ctx, {
                                    type: chartTypes[0],
                                    data: {
                                        labels: chartData[0].labels,
                                        datasets: chartData.map(function(dataset, index) {
                                            var backgroundColors = dataset.data.map((_, i) => colors[i % colors.length]);
                                            var borderColors = dataset.data.map((_, i) => borderColor[i % borderColor.length]);

                                            return {
                                                label: dataset.name,
                                                data: dataset.data,
                                                type: chartTypes[index],
                                                backgroundColor: backgroundColors,
                                                borderColor: borderColors,
                                                borderWidth: 1,
                                                fill: false
                                            };
                                        })
                                    },
                                    options: {
                                        responsive: true,
                                        maintainAspectRatio: false,
                                        scales: {
                                            y: {
                                                beginAtZero: true
                                            }
                                        }
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