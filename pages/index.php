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

        <!-- Formulaire de création du graphique -->
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
            const exampleChartData = {
                type: 'bar', // Exemple : type de graphique
                labels: ['Label 1', 'Label 2'], // Exemple : étiquettes
                datasets: [{
                    name: 'Dataset 1',
                    data: [10, 20] // Exemple : données
                }]
            };

            // Générer le graphique avec les données d'exemple
            generateChart(exampleChartData);
        </script>
    </main>
    <?php wp_footer(); ?>
</body>

</html>