<?php
/**
 * POSR_Report_Profit_By_Category
 */

require POSR_PATH . 'reports/class-posr-base-report.php';

class POSR_Report_Profit_By_Category extends POSR_Base_Report
{
    const TEXT_DOMAIN = 'wc-posr';
    public $chart_colours = array();
    public $profit_colours = array();
    public $show_categories = array();
    protected $report_data;

    /**
     * Constructor
     */
    public function __construct()
    {
        if (isset($_GET['show_categories']) && is_array($_GET['show_categories'])) {
            $this->show_categories = array_map('absint', $_GET['show_categories']);
        } elseif (isset($_GET['show_categories'])) {
            $this->show_categories = array(absint($_GET['show_categories']));
        }
    }

    /**
     * Get all product ids in a category (and its children)
     * @param  int $category_id
     * @return array
     */
    public function get_products_in_category($category_id)
    {
        $term_ids = get_term_children($category_id, 'product_cat');
        $term_ids[] = $category_id;
        $product_ids = get_objects_in_term($term_ids, 'product_cat');

        return array_unique(apply_filters('woocommerce_report_sales_by_category_get_products_in_category', $product_ids, $category_id));
    }

    protected function query_report_data()
    {
        $this->report_data = new stdClass();

        $this->report_data->net_sales = $this->query_net_sales();
    }

    /**
     * Get the legend for the main chart sidebar
     * @return array
     */
    public function get_chart_legend()
    {
        if (!$this->show_categories) {
            return array();
        }

        $legend = array();
        $index = 0;
        $num_colours = count($this->chart_colours);
        $count_cats = count($this->show_categories);

        foreach ($this->show_categories as $category) {
            $category = get_term($category, 'product_cat');
            $total = 0;
            $profit = 0;
            $product_ids = $this->get_products_in_category($category->term_id);

            foreach ($product_ids as $id) {
                if (isset($this->item_sales[$id])) {
                    $total += $this->item_sales[$id];
                }
                if (isset($this->item_cogs[$id])) {
                    $profit += ($this->item_sales[$id] - $this->item_cogs[$id]);
                }
            }

            $legend[] = array(
                'title' => sprintf(__('%s item sales in %s', self::TEXT_DOMAIN), '<strong>' . wc_price($total) . '</strong>', $category->name),
                'color' => $this->chart_colours[$index % $num_colours],
                'highlight_series' => $index
            );

            $legend[] = array(
                'title' => sprintf(__('%s item profits in %s', self::TEXT_DOMAIN), '<strong>' . wc_price($profit) . '</strong>', $category->name),
                'color' => $this->profit_colours[$index % $num_colours],
                'highlight_series' => $count_cats + $index
            );

            $index++;
        }

        return $legend;
    }

    /**
     * Output the report
     */
    public function output_report()
    {
        $ranges = array(
            'year' => __('Year', 'woocommerce'),
            'last_month' => __('Last Month', 'woocommerce'),
            'month' => __('This Month', 'woocommerce'),
            '7day' => __('Last 7 Days', 'woocommerce')
        );

        $this->chart_colours = array('#3498db', '#34495e', '#1abc9c', '#2ecc71', '#f1c40f', '#e67e22', '#e74c3c', '#2980b9', '#8e44ad', '#2c3e50', '#16a085', '#27ae60', '#f39c12', '#d35400', '#c0392b');

        $this->profit_colours = array();
        foreach ($this->chart_colours as $colour) {
            $this->profit_colours[] = $this->get_another_colour($colour);
        }

        $current_range = !empty($_GET['range']) ? $_GET['range'] : '7day';

        if (!in_array($current_range, array('custom', 'year', 'last_month', 'month', '7day'))) {
            $current_range = '7day';
        }

        $this->calculate_current_range($current_range);

        // Get item sales data
        if ($this->show_categories) {

            $data = $this->get_report_data();

            $this->item_sales = array();
            $this->item_sales_and_times = array();

            $this->item_cogs = array();
            $this->item_cogs_and_times = array();

            if ($data->net_sales) {
                foreach ($data->net_sales as $sale) {
                    switch ($this->chart_groupby) {
                        case 'day' :
                            $time = strtotime(date('Ymd', strtotime($sale->post_date))) * 1000;
                            break;
                        case 'month' :
                            $time = strtotime(date('Ym', strtotime($sale->post_date)) . '01') * 1000;
                            break;
                    }

                    $this->item_sales_and_times[$time][$sale->product_id] = isset($this->item_sales_and_times[$time][$sale->product_id]) ? $this->item_sales_and_times[$time][$sale->product_id] + $sale->total_sales : $sale->total_sales;
                    $this->item_sales[$sale->product_id] = isset($this->item_sales[$sale->product_id]) ? $this->item_sales[$sale->product_id] + $sale->total_sales : $sale->total_sales;

                    $this->item_cogs_and_times[$time][$sale->product_id] = isset($this->item_profits_and_times[$time][$sale->product_id]) ? $this->item_profits_and_times[$time][$sale->product_id] + $sale->total_cog : $sale->total_cog;
                    $this->item_cogs[$sale->product_id] = isset($this->item_cogs[$sale->product_id]) ? $this->item_cogs[$sale->product_id] + $sale->total_cog : $sale->total_cog;
                }
            }
        }

        include(WC()->plugin_path() . '/includes/admin/views/html-report-by-date.php');
    }

    /**
     * [get_chart_widgets description]
     * @return array
     */
    public function get_chart_widgets()
    {
        return array(
            array(
                'title' => __('Categories', 'woocommerce'),
                'callback' => array($this, 'category_widget')
            )
        );
    }

    /**
     * Category selection
     * @return void
     */
    public function category_widget()
    {
        $categories = get_terms('product_cat', array('orderby' => 'name'));
        ?>
        <form method="GET">
            <div>
                <select multiple="multiple" data-placeholder="<?php _e('Select categories&hellip;', 'woocommerce'); ?>"
                        class="<?= (version_compare(WOOCOMMERCE_VERSION, "2.3.0") < 0) ? 'chosen_select' : 'wc-enhanced-select' ?>"
                        id="show_categories" name="show_categories[]" style="width: 205px;">
                    <?php
                    $r = array();
                    $r['pad_counts'] = 1;
                    $r['hierarchical'] = 1;
                    $r['hide_empty'] = 1;
                    $r['value'] = 'id';
                    $r['selected'] = $this->show_categories;

                    include_once(WC()->plugin_path() . '/includes/walkers/class-product-cat-dropdown-walker.php');

                    echo wc_walk_category_dropdown_tree($categories, 0, $r);
                    ?>
                </select>
                <a href="#" class="select_none"><?php _e('None', 'woocommerce'); ?></a>
                <a href="#" class="select_all"><?php _e('All', 'woocommerce'); ?></a>
                <input type="submit" class="submit button" value="<?php _e('Show', 'woocommerce'); ?>"/>
                <input type="hidden" name="range"
                       value="<?php if (!empty($_GET['range'])) echo esc_attr($_GET['range']) ?>"/>
                <input type="hidden" name="start_date"
                       value="<?php if (!empty($_GET['start_date'])) echo esc_attr($_GET['start_date']) ?>"/>
                <input type="hidden" name="end_date"
                       value="<?php if (!empty($_GET['end_date'])) echo esc_attr($_GET['end_date']) ?>"/>
                <input type="hidden" name="page"
                       value="<?php if (!empty($_GET['page'])) echo esc_attr($_GET['page']) ?>"/>
                <input type="hidden" name="tab" value="<?php if (!empty($_GET['tab'])) echo esc_attr($_GET['tab']) ?>"/>
                <input type="hidden" name="report"
                       value="<?php if (!empty($_GET['report'])) echo esc_attr($_GET['report']) ?>"/>
            </div>

            <?php if (version_compare(WOOCOMMERCE_VERSION, "2.3.0") < 0): ?>

                <script type="text/javascript">
                    jQuery(function () {
                        jQuery("select.chosen_select").chosen();

                        // Select all/none
                        jQuery('.chart-widget').on('click', '.select_all', function () {
                            jQuery(this).closest('div').find('select option').attr("selected", "selected");
                            jQuery(this).closest('div').find('select').trigger('chosen:updated');
                            return false;
                        });

                        jQuery('.chart-widget').on('click', '.select_none', function () {
                            jQuery(this).closest('div').find('select option').removeAttr("selected");
                            jQuery(this).closest('div').find('select').trigger('chosen:updated');
                            return false;
                        });
                    });
                </script>

            <?php else: ?>

                <script type="text/javascript">
                    jQuery(function () {
                        // Select all/none
                        jQuery('.chart-widget').on('click', '.select_all', function () {
                            jQuery(this).closest('div').find('select option').attr("selected", "selected");
                            jQuery(this).closest('div').find('select').change();
                            return false;
                        });

                        jQuery('.chart-widget').on('click', '.select_none', function () {
                            jQuery(this).closest('div').find('select option').removeAttr("selected");
                            jQuery(this).closest('div').find('select').change();
                            return false;
                        });
                    });
                </script>

            <?php endif ?>

        </form>
    <?php
    }

    /**
     * Output an export link
     */
    public function get_export_button()
    {
        $current_range = !empty($_GET['range']) ? $_GET['range'] : '7day';
        ?>
        <a
            href="#"
            download="report-<?php echo $current_range; ?>-<?php echo date_i18n('Y-m-d', current_time('timestamp')); ?>.csv"
            class="export_csv"
            data-export="chart"
            data-xaxes="<?php _e('Date', 'woocommerce'); ?>"
            data-groupby="<?php echo $this->chart_groupby; ?>"
            >
            <?php _e('Export CSV', 'woocommerce'); ?>
        </a>
    <?php
    }

    protected function profit_data($sales, $cogs)
    {
        $profits = $sales;

        foreach ($profits as $idx => $profit) {
            if (isset($sales[$idx][0]) && isset($cogs[$idx][0]) && $sales[$idx][0] == $cogs[$idx][0])
                $profits[$idx][1] = $sales[$idx][1] - $cogs[$idx][1];
        }

        return $profits;
    }

    /**
     * Get the main chart
     * @return string
     */
    public function get_main_chart()
    {
        global $wp_locale;

        if (!$this->show_categories) {
            ?>
            <div class="chart-container">
                <p class="chart-prompt"><?php _e('&larr; Choose a category to view stats', 'woocommerce'); ?></p>
            </div>
        <?php
        } else {
            $include_categories = array();
            $chart_data = array();
            $chart_ticks = array();
            $index = 0;

            foreach ($this->show_categories as $category) {
                $category = get_term($category, 'product_cat');
                $product_ids = $this->get_products_in_category($category->term_id);
                $category_total = 0;
                $category_chart_data = array();
                $category_chart_cog_data = array();

                for ($i = 0; $i <= $this->chart_interval; $i++) {
                    $interval_total = 0;
                    $interval_cog_total = 0;

                    switch ($this->chart_groupby) {
                        case 'day' :
                            $time = strtotime(date('Ymd', strtotime("+{$i} DAY", $this->start_date))) * 1000;
                            break;
                        case 'month' :
                            $time = strtotime(date('Ym', strtotime("+{$i} MONTH", $this->start_date)) . '01') * 1000;
                            break;
                    }

                    foreach ($product_ids as $id) {
                        if (isset($this->item_sales_and_times[$time][$id])) {
                            $interval_total += $this->item_sales_and_times[$time][$id];
                            $category_total += $this->item_sales_and_times[$time][$id];
                        }

                        if (isset($this->item_cogs_and_times[$time][$id])) {
                            $interval_cog_total += $this->item_cogs_and_times[$time][$id];
                        }
                    }

                    $category_chart_data[] = array($time, $this->round_totals($interval_total));
                    $category_chart_cog_data[] = array($time, $this->round_totals($interval_cog_total));
                }

                $chart_data[$category->term_id]['category'] = $category->name;
                $chart_data[$category->term_id]['data'] = $category_chart_data;

                $chart_data[$category->term_id]['cog_data'] = $category_chart_cog_data;

                $index++;
            }
            ?>
            <div class="chart-container">
                <div class="chart-placeholder main"></div>
            </div>
            <script type="text/javascript">
                var main_chart;

                jQuery(function () {
                    var drawGraph = function (highlight) {
                        var series = [
                            <?php
                                $index = 0;
                                $num_colours = count( $this->chart_colours );
                                foreach ( $chart_data as $data ) {
                                    $color  = $this->chart_colours[ $index % $num_colours ];
                                    $width  = $this->barwidth / sizeof( $chart_data );
                                    $offset = ( $width * $index );
                                    $series = $data['data'];
                                    foreach ( $series as $key => $series_data )
                                        $series[ $key ][0] = $series_data[0] + $offset;
                                    echo '{
                                        label: "' . esc_js( $data['category'] ) . '",
                                        data: jQuery.parseJSON( "' . json_encode( $series ) . '" ),
                                        color: "' . $color . '",
                                        bars: { fillColor: "' . $color . '", fill: true, show: true, lineWidth: 1, align: "center", barWidth: ' . $width * 0.75 . ', stack: false },
                                        prepend_tooltip: "' . get_woocommerce_currency_symbol() . '",
                                        enable_tooltip: true,
                                        prepend_label: true
                                    },';
                                    $index++;
                                }
                            ?>
                            <?php
								$index = 0;
								$num_colours = count( $this->profit_colours );
								foreach ( $chart_data as $data ) {
									$color  = $this->profit_colours[ $index % $num_colours ];
									$width  = $this->barwidth / sizeof( $chart_data );
									$offset = ( $width * $index );
									$series = $this->profit_data($data['data'], $data['cog_data']);
									foreach ( $series as $key => $series_data )
										$series[ $key ][0] = $series_data[0] + $offset;
									echo '{
										label: "' . esc_js( $data['category'] ) . '",
										data: jQuery.parseJSON( "' . json_encode( $series ) . '" ),
										color: "' . $color . '",
										bars: { fillColor: "' . $color . '", fill: true, show: true, lineWidth: 1, align: "center", barWidth: ' . $width * 0.75 . ', stack: false },
										prepend_tooltip: "' . get_woocommerce_currency_symbol() . '",
										enable_tooltip: true,
										prepend_label: true
									},';
									$index++;
								}
                            ?>
                        ];

                        if (highlight !== 'undefined' && series[highlight]) {
                            highlight_series = series[highlight];

                            highlight_series.color = '#9c5d90';

                            if (highlight_series.bars)
                                highlight_series.bars.fillColor = '#9c5d90';

                            if (highlight_series.lines) {
                                highlight_series.lines.lineWidth = 5;
                            }
                        }

                        main_chart = jQuery.plot(
                            jQuery('.chart-placeholder.main'),
                            series,
                            {
                                legend: {
                                    show: false
                                },
                                grid: {
                                    color: '#aaa',
                                    borderColor: 'transparent',
                                    borderWidth: 0,
                                    hoverable: true
                                },
                                xaxes: [{
                                    color: '#aaa',
                                    reserveSpace: true,
                                    position: "bottom",
                                    tickColor: 'transparent',
                                    mode: "time",
                                    timeformat: "<?php if ( $this->chart_groupby == 'day' ) echo '%d %b'; else echo '%b'; ?>",
                                    monthNames: <?php echo json_encode( array_values( $wp_locale->month_abbrev ) ); ?>,
                                    tickLength: 1,
                                    minTickSize: [1, "<?php echo $this->chart_groupby; ?>"],
                                    tickSize: [1, "<?php echo $this->chart_groupby; ?>"],
                                    font: {
                                        color: "#aaa"
                                    }
                                }],
                                yaxes: [
                                    {
                                        min: 0,
                                        tickDecimals: 2,
                                        color: 'transparent',
                                        font: {color: "#aaa"}
                                    }
                                ],
                            }
                        );

                        jQuery('.chart-placeholder').resize();

                    }

                    drawGraph();

                    jQuery('.highlight_series').hover(
                        function () {
                            drawGraph(jQuery(this).data('series'));
                        },
                        function () {
                            drawGraph();
                        }
                    );
                });
            </script>
        <?php
        }
    }
}
