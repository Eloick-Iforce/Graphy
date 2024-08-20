<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Graphy</title>
    <?php wp_head(); ?>
</head>

<body>
    <main class="flex flex-col gap-8 rounded-lg p-8 bg-white" x-data="chartApp()" x-init="generateChart()">
        <form id="graphyForm" method="post" class="flex flex-col gap-8">
            <?php wp_nonce_field('submit_graphy_data_nonce', 'graphy_nonce_field'); ?>
            <div class="flex justify-between items-center">
                <h1 class="text-2xl font-bold text-black">Nouveau graphique</h1>
                <div class="flex gap-4">
                    <button class="p-0 px-2 border-dashed text-blue-500 border-blue-600 bg-blue-100 hover:bg-blue-600 hover:text-white rounded-full" @click="saveChart()"><span class="text-xl">+</span> Sauvegarder</button>
                    <a href="<?php echo admin_url('admin.php?page=graphy'); ?>" class="p-0 px-2 border text-red-500 border-red-600 bg-red-100 rounded-full flex items-center gap-2 justify-center text-sm hover:bg-red-600 hover:text-white"><span class="text-xl">x</span> Annuler</a>
                </div>
            </div>
            <div class="h-1 mx-8 bg-gray-800 rounded-lg"></div>
            <div class="w-1/3">
                <input type="text" id="chartTitle" class="w-1/3" x-model="chartTitle" name="chartTitle" placeholder="Nom du graphique">
            </div>
            <div id="datasets" class="flex justify-center gap-8 flex-wrap">
                <template x-for="(dataset, datasetIndex) in datasets" :key="datasetIndex">
                    <div class="border border-dashed border-gray-800 rounded-lg p-4 flex flex-col gap-4 w-[45%]">
                        <div class="flex justify-between items-center mb-4">
                            <div class="flex gap-2 items-center">
                                <button class="h-8 w-8 border-dashed text-red-500 border-red-600 bg-red-100 rounded-full flex items-center gap-2 justify-center text-xs hover:bg-red-600 hover:text-white" @click="removeDataset(datasetIndex)"><span class="text-xl">x</span></button>
                                <h3 class="text-xl font-semibold">Jeu de données <span x-text="datasetIndex + 1"></span></h3>
                            </div>
                            <div class="w-1/3">
                                <input type="text" class="w-1/3 p-2 border rounded" x-model="dataset.name" :name="'datasets[' + datasetIndex + '][name]'" placeholder="Nom du jeu de données">
                            </div>
                            <div class="1/3">
                                <select x-model="dataset.type" :name="'datasets[' + datasetIndex + '][type]'" class="p-2 border rounded-lg">
                                    <option value="bar">Graphique en barre</option>
                                    <option value="line">Graphique en Ligne</option>
                                    <option value="pie">Graphique Pie</option>
                                    <option value="doughnut">Graphique Doughnut</option>
                                    <option value="polarArea">Graphique Polar Area</option>
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="block mb-2">Données:</label>
                            <template x-for="(label, index) in dataset.labels" :key="index">
                                <div class="flex items-center mb-2">
                                    <input type="text" class="p-2 border rounded w-1/2 mr-2" x-model="dataset.labels[index]" :name="'datasets[' + datasetIndex + '][labels][' + index + ']'" placeholder="Étiquette">
                                    <input type="number" class="p-2 border rounded w-1/2" x-model="dataset.data[index]" :name="'datasets[' + datasetIndex + '][data][' + index + ']'" placeholder="Valeur">
                                    <button type="button" class="p-0 px-2 border-dashed text-red-500 border-red-600 bg-red-100 rounded-full flex items-center gap-2 justify-center text-xs hover:bg-red-600 hover:text-white" @click="removeLabelAndData(datasetIndex, index)"><span class="text-xl">x</span></button>
                                </div>
                            </template>
                            <button type="button" class="btn-add w-full mt-2 py-2" @click="addLabelAndData(datasetIndex)">+ Ajouter une donnée</button>
                        </div>
                    </div>
                </template>
                <div class="border border-dashed border-orange-400 rounded-lg h-96 bg-orange-50 flex justify-center items-center w-[45%]">
                    <button type="button" @click="addDataset" class="button-add border-none bg-transparent w-full h-full flex justify-center items-center text-2xl"> Ajouter un jeu de données</button>
                    <style>
                        .button-add {
                            background-color: transparent;
                            border: none;
                            width: 100%;
                            color: orange;
                        }

                        .button-add:hover {
                            background-color: orange;
                            color: white;
                        }

                        .button-add:focus {
                            background-color: transparent;
                            border: none;
                            width: 100%;
                            color: orange;
                        }
                    </style>
                </div>
            </div>
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
                datasets: [],
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
                removeLabelAndData(datasetIndex, labelIndex) {
                    this.datasets[datasetIndex].labels.splice(labelIndex, 1);
                    this.datasets[datasetIndex].data.splice(labelIndex, 1);
                },
                generateChart() {
                    const chartData = {
                        labels: this.datasets[0].labels,
                        datasets: this.datasets.map((dataset) => {
                            return {
                                label: dataset.name,
                                data: dataset.data,
                                backgroundColor: 'rgba(54, 162, 235, 0.2)',
                                borderColor: 'rgba(54, 162, 235, 1)',
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