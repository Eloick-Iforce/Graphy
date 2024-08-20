<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Graphy</title>
    <?php wp_head(); ?>
</head>

<body>
    <main class="flex flex-col gap-8 rounded-lg p-8 bg-white" x-data="chartApp()">
        <h1 class="text-4xl font-bold">Graphy</h1>
        <p>Nouveau graphique</p>
        <form id="graphyForm" method="post">
            <?php wp_nonce_field('submit_graphy_data_nonce', 'graphy_nonce_field'); ?>
            <div class="mb-4">
                <label for="chartTitle" class="block">Titre du graphique:</label>
                <input type="text" id="chartTitle" class="p-2 border rounded w-full" x-model="chartTitle" name="chartTitle" placeholder="Titre du graphique">
            </div>
            <div id="datasets">
                <template x-for="(dataset, datasetIndex) in datasets" :key="datasetIndex">
                    <div class="mb-4 p-4 border rounded bg-blue-200/30 border-blue-300">
                        <div class="flex justify-between items-center mb-2">
                            <div>
                                <h3 class="text-lg font-semibold">Dataset <span x-text="datasetIndex + 1"></span></h3>
                                <input type="text" class="p-2 border rounded mt-2" x-model="dataset.name" :name="'datasets[' + datasetIndex + '][name]'" placeholder="Nom du dataset">
                            </div>
                            <button type="button" class="p-2 bg-red-500 text-white rounded" @click="removeDataset(datasetIndex)">Supprimer</button>
                        </div>
                        <div>
                            <label for="chartType" class="block">Type de graphique:</label>
                            <select x-model="dataset.type" :name="'datasets[' + datasetIndex + '][type]'" class="p-2 border rounded">
                                <option value="bar">Bar</option>
                                <option value="line">Line</option>
                                <option value="pie">Pie</option>
                                <option value="doughnut">Doughnut</option>
                                <option value="polarArea">Polar Area</option>
                            </select>
                        </div>
                        <div class="mt-2">
                            <label class="block">Étiquettes et Données:</label>
                            <template x-for="(label, index) in dataset.labels" :key="index">
                                <div class="flex items-center mb-2">
                                    <input type="text" class="p-2 border rounded w-1/2 mr-2" x-model="dataset.labels[index]" :name="'datasets[' + datasetIndex + '][labels][' + index + ']'" placeholder="Étiquette">
                                    <input type="number" class="p-2 border rounded w-1/2" x-model="dataset.data[index]" :name="'datasets[' + datasetIndex + '][data][' + index + ']'" placeholder="Valeur">
                                </div>
                            </template>
                            <button type="button" class="p-2 bg-violet-600 text-white rounded font-bold" @click="addLabelAndData(datasetIndex)">Ajouter une étiquette et une donnée</button>
                        </div>
                    </div>
                </template>
                <button type="button" class="bg-green-500 text-white rounded w-fit px-4 py-2" @click="addDataset">Ajouter un set de données</button>
            </div>
            <button type="submit" class="bg-blue-500 text-white rounded w-fit px-4 py-2">Enregistrer les données</button>
        </form>
        <div class="grid grid-cols-1 gap-8 mt-8">
            <div>
                <canvas id="userChart"></canvas>
            </div>
        </div>
    </main>
    <script>
        function chartApp() {
            return {
                chartTitle: '',
                datasets: [{
                    name: 'Dataset 1',
                    type: 'bar',
                    labels: [''],
                    data: ['']
                }],
                chartInstance: null,
                addDataset() {
                    this.datasets.push({
                        name: `Dataset ${this.datasets.length + 1}`,
                        type: 'bar',
                        labels: [],
                        data: []
                    });
                },
                removeDataset(index) {
                    this.datasets.splice(index, 1);
                },
                addLabelAndData(datasetIndex) {
                    this.datasets[datasetIndex].labels.push('');
                    this.datasets[datasetIndex].data.push(0);
                },
                generateChart() {
                    const chartData = {
                        labels: this.datasets[0].labels,
                        datasets: this.datasets.map((dataset, datasetIndex) => {
                            const colors = [
                                'rgba(255, 99, 132, 0.2)',
                                'rgba(54, 162, 235, 0.2)',
                                'rgba(255, 206, 86, 0.2)',
                                'rgba(75, 192, 192, 0.2)',
                                'rgba(153, 102, 255, 0.2)',
                                'rgba(255, 159, 64, 0.2)'
                            ];
                            const borderColor = [
                                'rgba(255, 99, 132, 1)',
                                'rgba(54, 162, 235, 1)',
                                'rgba(255, 206, 86, 1)',
                                'rgba(75, 192, 192, 1)',
                                'rgba(153, 102, 255, 1)',
                                'rgba(255, 159, 64, 1)'
                            ];
                            const backgroundColors = dataset.data.map((_, index) => colors[index % colors.length]);
                            const borderColors = dataset.data.map((_, index) => borderColor[index % borderColor.length]);

                            return {
                                label: dataset.name,
                                data: dataset.data,
                                backgroundColor: backgroundColors,
                                borderColor: borderColors,
                                borderWidth: 1,
                                type: dataset.type
                            };
                        })
                    };

                    const ctx = document.getElementById('userChart').getContext('2d');

                    if (this.chartInstance) {
                        this.chartInstance.destroy();
                    }

                    this.chartInstance = new Chart(ctx, {
                        type: this.datasets[0].type,
                        data: chartData,
                        options: {
                            scales: {
                                y: {
                                    beginAtZero: true
                                }
                            }
                        }
                    });
                },
            };
        }
    </script>
    <?php wp_footer(); ?>
</body>

</html>