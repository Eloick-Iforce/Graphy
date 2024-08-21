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
            <script src="https://cdn.jsdelivr.net/npm/alpinejs@2.8.2"></script>
            <div class="flex flex-wrap gap-4">
                <?php foreach ($charts as $chart) : ?>
                    <div class="bg-white h-96 w-[48rem] p-4 shadow rounded flex flex-col gap-4">
                        <div class="flex justify-between items-center gap-4">
                            <h3 class="text-xl font-bold"><?php echo esc_html($chart['title']); ?></h3>
                            <div class="flex gap-2">
                                <form action="<?php echo admin_url('admin.php?page=graphy-modify&id=' . esc_attr($chart['id'])); ?>" method="post">
                                    <input type=hidden name="action" value="modify_chart">
                                    <input type=hidden name="id" value="<?php echo esc_attr($chart['id']); ?>">
                                    <button class="p-0 px-2 border-dashed text-blue-500 border-blue-600 bg-blue-100 hover:bg-blue-600 hover:text-white rounded-full flex items-center gap-2 justify-center text-xs"><span class="text-xl">+</span> Modifier</button>
                                </form>
                                <div class="relative" x-data="{ open: false }">
                                    <button @click="open = !open" class="py-1 px-2 border text-blue-500 border-blue-600 bg-blue-100 hover:bg-blue-600 hover:text-white rounded-full flex items-center gap-2 justify-center text-xs">
                                        Plus d'action
                                    </button>
                                    <div x-show="open" @click.away="open = false" class="absolute right-0 mt-2 bg-white border border-gray-200 rounded shadow-md w-48">
                                        <a href="#" class="block px-4 py-2 text-sm text-red-500 hover:bg-gray-100"
                                            @click.prevent="document.getElementById('delete-form-<?php echo esc_attr($chart['id']); ?>').submit();">
                                            Supprimer
                                        </a>
                                        <a href="#" class="block px-4 py-2 text-sm hover:bg-gray-100" onClick="exportChartToCSV('<?php echo esc_attr($chart['id']); ?>')">Exporter en CSV</a>
                                        <a href="#" class="block px-4 py-2 text-sm hover:bg-gray-100" onClick="exportChartToPNG('<?php echo esc_attr($chart['id']); ?>')">Exporter en PNG</a>
                                        <a href="#" class="block px-4 py-2 text-sm hover:bg-gray-100" onClick="exportChartToJSON('<?php echo esc_attr($chart['id']); ?>')">Exporter en JSON</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="chart-container">
                            <canvas id="chart-<?php echo esc_attr($chart['id']); ?>"></canvas>
                        </div>
                        <div id="chart-data-<?php echo esc_attr($chart['id']); ?>" class="hidden"><?php echo htmlspecialchars(json_encode($chart['dataset_data'])); ?></div>
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

                            function convertToCSV(objArray) {
                                var array = typeof objArray != 'object' ? JSON.parse(objArray) : objArray;
                                var str = '';
                                for (var i = 0; i < array.length; i++) {
                                    var line = '';
                                    for (var index in array[i]) {
                                        if (line != '') line += ','

                                        line += array[i][index];
                                    }
                                    str += line + '\n';
                                }
                                return str;
                            }

                            function exportChartToCSV(chartId) {
                                var chartData = JSON.parse(document.getElementById('chart-data-' + chartId).innerText);
                                var csvData = chartData.map(dataset => ({
                                    "Dataset Name": dataset.name,
                                    "Data": dataset.data.join(",")
                                }));

                                var csvContent = "data:text/csv;charset=utf-8," + convertToCSV(csvData);
                                var encodedUri = encodeURI(csvContent);
                                var link = document.createElement("a");
                                link.setAttribute("href", encodedUri);
                                link.setAttribute("download", `chart_${chartId}.csv`);
                                document.body.appendChild(link);
                                link.click();
                                document.body.removeChild(link);
                            }

                            function exportChartToPNG(chartId) {
                                var canvas = document.getElementById('chart-' + chartId);
                                var link = document.createElement("a");
                                link.href = canvas.toDataURL("image/png");
                                link.download = `chart_${chartId}.png`;
                                link.click();
                            }

                            function exportChartToJSON(chartId) {
                                var chartData = JSON.parse(document.getElementById('chart-data-' + chartId).innerText);
                                var jsonContent = JSON.stringify(chartData);

                                var encodedUri = "data:application/json;charset=utf-8," + encodeURIComponent(jsonContent);
                                var link = document.createElement("a");
                                link.setAttribute("href", encodedUri);
                                link.setAttribute("download", `chart_${chartId}.json`);
                                document.body.appendChild(link);
                                link.click();
                                document.body.removeChild(link);
                            }
                        </script>
                    </div>
                    <form id="delete-form-<?php echo esc_attr($chart['id']); ?>" action="<?php echo admin_url('admin-post.php'); ?>" method="post" class="hidden">
                        <?php wp_nonce_field('delete_chart', 'graphy_delete_nonce'); ?>
                        <input type="hidden" name="action" value="delete_chart">
                        <input type="hidden" name="id" value="<?php echo esc_attr($chart['id']); ?>">
                    </form>
                <?php endforeach; ?>
            </div>
        </div>
    </main>
    <?php wp_footer(); ?>
</body>

</html>