<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Graphy Charts</title>
    <?php wp_head(); ?>
</head>

<body class="bg-gray-100 p-6">
    <main class="flex flex-col gap-8 m-16 rounded-lg p-8 bg-white">
        <h1 class="text-4xl font-bold">Graphy</h1>
        <p>Bon retour sur Graphy !</p>
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <div class="flex flex-wrap gap-4">
            <?php foreach ($charts as $chart) : ?>
                <div class="bg-white h-96 w-96 p-4 shadow rounded">
                    <h2 class="text-lg font-bold"><?php echo esc_html($chart['title']); ?></h2>
                    <div class="chart-container">
                        <canvas id="chart-<?php echo esc_attr($chart['id']); ?>"></canvas>
                    </div>
                    <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            var ctx = document.getElementById('chart-<?php echo esc_attr($chart['id']); ?>').getContext('2d');
                            var chartData = <?php echo wp_json_encode($chart['dataset_data']); ?>;
                            var chartType = <?php echo wp_json_encode($chart['dataset_type']); ?>;

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
                                type: 'bar',
                                data: {
                                    labels: chartData[0].labels,
                                    datasets: chartData.map(function(dataset, index) {
                                        var backgroundColors = dataset.data.map((_, i) => colors[i % colors.length]);
                                        var borderColors = dataset.data.map((_, i) => borderColor[i % borderColor.length]);

                                        return {
                                            label: dataset.name,
                                            data: dataset.data,
                                            type: chartType[index],
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
    </main>
    <?php wp_footer(); ?>
</body>

</html>