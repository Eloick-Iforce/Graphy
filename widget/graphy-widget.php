<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

class Graphy_Widget extends \Elementor\Widget_Base
{

    public function get_name()
    {
        return 'graphy_widget';
    }

    public function get_title()
    {
        return __('Graphy Widget', 'graphy');
    }

    public function get_icon()
    {
        return 'eicon-editor-paste-word';
    }

    public function get_categories()
    {
        return ['general'];
    }

    protected function _register_controls()
    {
        $this->start_controls_section(
            'content_section',
            [
                'label' => __('Content', 'graphy'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        global $wpdb;
        $table_name = $wpdb->prefix . 'graphy';
        $charts = $wpdb->get_results("SELECT * FROM $table_name", ARRAY_A);
        $options = [];

        if ($charts) {
            foreach ($charts as $chart) {
                $options[$chart['id']] = $chart['title'];
            }
        }

        $this->add_control(
            'selected_chart',
            [
                'label' => __('Select Chart', 'graphy'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'options' => $options,
            ]
        );

        $this->end_controls_section();
    }

    protected function render()
    {
        $settings = $this->get_settings_for_display();
        if (!isset($settings['selected_chart'])) {
            return;
        }

        $chart_id = $settings['selected_chart'];

        global $wpdb;
        $table_name = $wpdb->prefix . 'graphy';
        $chart = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $chart_id), ARRAY_A);

        if ($chart) {
?>
            <div class="graphy-chart-widget">
                <canvas id="chart-<?php echo esc_attr($chart['id']); ?>"></canvas>
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
<?php
        } else {
            echo __('No chart found', 'graphy');
        }
    }
}
