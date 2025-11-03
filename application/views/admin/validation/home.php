<script>
    // Poll Analytics Chart Data
    var total_polls_data = <?php echo $total_polls_chart; ?>;
    var total_votes_data = <?php echo $total_votes_chart; ?>;
    var total_comments_data = <?php echo $total_comments_chart; ?>;
    var label_months = <?php echo $months; ?>;

    // Main Analytics Chart
    var pollAnalyticsOptions = {
        chart: {
            height: 339,
            type: "line",
            stacked: false,
            toolbar: {
                show: false
            }
        },
        stroke: {
            width: [0, 2, 4],
            curve: "smooth"
        },
        plotOptions: {
            bar: {
                columnWidth: "30%"
            }
        },
        colors: ["#5b73e8", "#34c38f", "#f46a6a"],
        series: [{
                name: "Total Polls",
                type: "column",
                data: total_polls_data
            }, {
                name: "Total Votes",
                type: "area",
                data: total_votes_data
            },
            {
                name: "Total Comments",
                type: "line",
                data: total_comments_data
            }
        ],
        fill: {
            opacity: [0.85, 0.25, 1],
            gradient: {
                inverseColors: false,
                shade: "light",
                type: "vertical",
                opacityFrom: 0.85,
                opacityTo: 0.55,
                stops: [0, 100, 100, 100]
            }
        },
        labels: label_months,
        markers: {
            size: 0
        },
        yaxis: {
            title: {
                text: "Count"
            }
        },
        tooltip: {
            shared: true,
            intersect: false,
            y: {
                formatter: function(e) {
                    if (e !== undefined) {
                        return e.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
                    }
                    return e;
                }
            }
        },
        grid: {
            borderColor: "#f1f1f1"
        }
    };

    var pollChart = new ApexCharts(document.querySelector("#poll-analytics-chart"), pollAnalyticsOptions);
    pollChart.render();

    // User Activity Donut Chart
    var active_users = <?php echo $active_users_count; ?>;
    var inactive_users = <?php echo $inactive_users_count; ?>;
    var new_users = <?php echo $new_users_count; ?>;

    var userActivityOptions = {
        chart: {
            height: 320,
            type: "donut"
        },
        series: [active_users, inactive_users, new_users],
        labels: ["Active Users", "Inactive Users", "New Users"],
        colors: ["#34c38f", "#f46a6a", "#5b73e8"],
        legend: {
            show: true,
            position: "bottom",
            horizontalAlign: "center",
            verticalAlign: "middle",
            floating: false,
            fontSize: "14px",
            offsetX: 0
        },
        responsive: [{
            breakpoint: 600,
            options: {
                chart: {
                    height: 240
                },
                legend: {
                    show: false
                }
            }
        }]
    };

    var userActivityChart = new ApexCharts(document.querySelector("#user-activity-chart"), userActivityOptions);
    userActivityChart.render();

    // Small sparkline charts for cards
    function createSparklineChart(selector, color, data) {
        var options = {
            series: [{
                data: data || [89, 36, 63, 54, 25, 44, 40, 25, 66, 41, 20]
            }],
            fill: {
                colors: [color]
            },
            chart: {
                type: "bar",
                width: 70,
                height: 40,
                sparkline: {
                    enabled: true
                }
            },
            plotOptions: {
                bar: {
                    columnWidth: "50%"
                }
            },
            labels: [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11],
            xaxis: {
                crosshairs: {
                    width: 1
                }
            },
            tooltip: {
                fixed: {
                    enabled: false
                },
                x: {
                    show: false
                },
                y: {
                    title: {
                        formatter: function(e) {
                            return ""
                        }
                    }
                },
                marker: {
                    show: false
                }
            }
        };

        var chart = new ApexCharts(document.querySelector(selector), options);
        chart.render();
    }

    // Shuffle function for random data
    function shuffle(arra1) {
        var ctr = arra1.length,
            temp, index;
        while (ctr > 0) {
            index = Math.floor(Math.random() * ctr);
            ctr--;
            temp = arra1[ctr];
            arra1[ctr] = arra1[index];
            arra1[index] = temp;
        }
        return arra1;
    }

    // Initialize sparkline charts
    document.addEventListener('DOMContentLoaded', function() {

        // Blue sparkline for total users
        createSparklineChart('.just_for_dummy', "#5b73e8", shuffle([89, 36, 63, 54, 25, 44, 40, 25, 66, 41, 20]));

        // Green sparkline for total polls
        createSparklineChart('.just_for_dummy_green', "#34c38f", shuffle([45, 67, 23, 78, 56, 34, 89, 45, 67, 23, 45]));

        // Info sparkline for total votes
        createSparklineChart('.just_for_dummy_info', "#50a5f1", shuffle([78, 45, 89, 34, 67, 23, 56, 78, 45, 89, 34]));

        // Warning sparkline for active polls
        createSparklineChart('.just_for_dummy_warning', "#f1b44c", shuffle([34, 78, 45, 89, 23, 67, 56, 34, 78, 45, 89]));
    });

    // Real-time updates (optional)
    function updateDashboardData() {
        // Ajax call to fetch updated statistics
        $.ajax({
            url: '<?php echo base_url("zbt_admin/dashboard/get_stats") ?>',
            method: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.status === 200) {
                    // Update counter elements
                    $('[data-plugin="counterup1"]').each(function(index) {
                        var newValue = response.data[$(this).data('stat')];
                        if (newValue !== undefined) {
                            $(this).text(newValue);
                        }
                    });
                }
            }
        });
    }

    // Update dashboard every 5 minutes
    setInterval(updateDashboardData, 300000);
</script>