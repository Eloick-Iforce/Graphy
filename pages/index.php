<script src="https://cdn.tailwindcss.com"></script>

<div class="flex flex-col gap-8 m-8">
    <h1 class="text-4xl font-bold">Graphy</h1>
    <p>Bon retour sur Graphy !</p>
    <div class="grid grid-cols-4 gap-8">
        <div>
            <canvas id="chart1"></canvas>
        </div>
        <div>
            <canvas id="chart2"></canvas>
        </div>
        <div>
            <canvas id="chart3"></canvas>
        </div>
        <div>
            <canvas id="chart4"></canvas>
        </div>
        <div>
            <canvas id="chart5"></canvas>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        var chartTypes = ['bar', 'line', 'pie', 'doughnut', 'polarArea']; // Types de graphiques disponibles
        var colors = [
            'rgba(255, 99, 132, 0.5)', 'rgba(54, 162, 235, 0.5)', 'rgba(255, 206, 86, 0.5)', 'rgba(75, 192, 192, 0.5)', 'rgba(153, 102, 255, 0.5)',
            'rgba(255, 159, 64, 0.5)', 'rgba(199, 199, 199, 0.5)', 'rgba(83, 102, 255, 0.5)', 'rgba(120, 200, 132, 0.5)', 'rgba(255, 162, 86, 0.5)',
            'rgba(255, 50, 50, 0.5)', 'rgba(255, 102, 153, 0.5)', 'rgba(102, 153, 255, 0.5)', 'rgba(255, 255, 102, 0.5)', 'rgba(102, 255, 204, 0.5)'
        ]; // Tableau de couleurs

        var borderColor = [
            'rgba(255, 99, 132, 1)', 'rgba(54, 162, 235, 1)', 'rgba(255, 206, 86, 1)', 'rgba(75, 192, 192, 1)', 'rgba(153, 102, 255, 1)',
            'rgba(255, 159, 64, 1)', 'rgba(199, 199, 199, 1)', 'rgba(83, 102, 255, 1)', 'rgba(120, 200, 132, 1)', 'rgba(255, 162, 86, 1)',
            'rgba(255, 50, 50, 1)', 'rgba(255, 102, 153, 1)', 'rgba(102, 153, 255, 1)', 'rgba(255, 255, 102, 1)', 'rgba(102, 255, 204, 1)'
        ]; // Tableau de couleurs des bordures

        function generateChart(canvasId, chartType, data) {
            var backgroundColors = data.map((_, index) => colors[index % colors.length]);
            var borderColors = data.map((_, index) => borderColor[index % borderColor.length]);

            var ctx = document.getElementById(canvasId).getContext('2d');
            var chart = new Chart(ctx, {
                type: chartType,
                data: {
                    labels: ['Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin'],
                    datasets: [{
                        label: 'Données',
                        data: data,
                        backgroundColor: backgroundColors,
                        borderColor: borderColors,
                        borderWidth: 1
                    }]
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

        // Generate charts with each chart type and different data
        var chartData = [
            [12, 19, 3, 5, 2, 3], // Data for chart 1
            [5, 10, 15, 20, 25, 30], // Data for chart 2
            [8, 16, 24, 32, 40, 48], // Data for chart 3
            [1, 2, 3, 4, 5, 6], // Data for chart 4
            [10, 20, 30, 40, 50, 60] // Data for chart 5
        ];

        for (var i = 0; i < chartTypes.length; i++) {
            generateChart('chart' + (i + 1), chartTypes[i], chartData[i]);
        }
    </script>
</div>
<style>
    canvas {
        width: 100%;
        height: 100%;
    }
</style>