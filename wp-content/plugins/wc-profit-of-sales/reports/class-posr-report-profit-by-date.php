<?php
/**
 * POSR_Report_Profit_By_Date CLass
 */

require POSR_PATH . 'reports/class-posr-base-report.php';


class POSR_Report_Profit_By_Date extends POSR_Base_Report
{
	public $chart_colours = array();

	protected function query_report_data()
	{
		$this->report_data = new stdClass;

		$this->report_data->order_counts = $this->query_orders();
		$this->report_data->total_orders = absint( array_sum( wp_list_pluck( $this->report_data->order_counts, 'count' ) ) );

		$this->report_data->net_sales = $this->query_net_sales();
		$this->report_data->total_net_sales = array_sum( wp_list_pluck( $this->report_data->net_sales, 'total_sales' ) ) ;
		$this->report_data->total_cogs = array_sum( wp_list_pluck( $this->report_data->net_sales, 'total_cog' ) ) ;

		//$this->report_data->order_items = $this->query_order_items();
		$this->report_data->total_items = absint( array_sum( wp_list_pluck( $this->report_data->net_sales, 'qty' ) ) );

		$this->report_data->full_refunds = $this->query_full_refunds();

		$this->report_data->refunded_order_items = 0;
		foreach ( $this->report_data->full_refunds as $key => $value ) {
			$this->report_data->refunded_order_items += absint($value->refunded_item_count);
		}

		$this->report_data->partial_refunds = $this->query_partial_refunds();

		$this->report_data->refunded_order_value = 0;
		foreach ( $this->report_data->partial_refunds as $key => $value ) {
			$this->report_data->refunded_order_items += absint( $value->refund_qty );
			$this->report_data->refunded_order_value += (float) $value->refund_value;
		}

		$this->report_data->total_profit = $this->report_data->total_net_sales - $this->report_data->total_cogs;
		$this->report_data->average_profit = $this->report_data->total_profit / ( $this->chart_interval + 1 );
	}

	/**
	 * Get the legend for the main chart sidebar
	 * @return array
	 */
	public function get_chart_legend()
	{
		$legend   = array();

		$data = $this->get_report_data();

		$average_profit_title = '';
		switch ( $this->chart_groupby ) {
			case 'day' :
				$average_profit_title = sprintf( __( '%s average daily profits in this period', self::TEXT_DOMAIN ), '<strong>' . wc_price( $data->average_profit ) . '</strong>' );
			break;
			case 'month' :
				$average_profit_title = sprintf( __( '%s average monthly profits in this period', self::TEXT_DOMAIN ), '<strong>' . wc_price( $data->average_profit ) . '</strong>' );
			break;
		}

		$legend[] = array(
			'title' => sprintf( __( '%s item sales in this period', self::TEXT_DOMAIN ), '<strong>' . wc_price( $data->total_net_sales ) . '</strong>' ),
			'color' => $this->chart_colours['sales_amount'],
			'highlight_series' => 4
		);
        $legend[] = array(
            'title' => sprintf( __( '%s item profits in this period', self::TEXT_DOMAIN ), '<strong>' . wc_price( $data->total_profit ) . '</strong>' ),
            'color' => $this->chart_colours['profit_amount'],
            'highlight_series' => 3
        );
        $legend[] = array(
			'title' => $average_profit_title,
			'color' => $this->chart_colours['average_profit'],
			'highlight_series' => 2
		);
		$legend[] = array(
			'title' => sprintf( __( '%s orders placed', 'woocommerce' ), '<strong>' . $data->total_orders . '</strong>' ),
			'color' => $this->chart_colours['order_count'],
			'highlight_series' => 1
		);
		$legend[] = array(
			'title' => sprintf( __( '%s items purchased', 'woocommerce' ), '<strong>' . $data->total_items . '</strong>' ),
			'color' => $this->chart_colours['item_count'],
			'highlight_series' => 0
		);
		/*$legend[] = array(
			'title' => sprintf( __( '%s items refunded', self::TEXT_DOMAIN ), '<strong>' . $data->refunded_order_items . '</strong>' ),
			'color' => $this->chart_colours['refunded_count'],
			'highlight_series' => 0
		);*/

		return $legend;
	}

	/**
	 * Output the report
	 */
	public function output_report()
	{
		global $woocommerce, $wpdb, $wp_locale;

		$ranges = array(
			'year'         => __( 'Year', 'woocommerce' ),
			'last_month'   => __( 'Last Month', 'woocommerce' ),
			'month'        => __( 'This Month', 'woocommerce' ),
			'7day'         => __( 'Last 7 Days', 'woocommerce' )
		);

		$this->chart_colours = array(
			'sales_amount'   => '#3498db',
			'profit_amount'  => '#1abc9c',
			'average_profit' => '#75b9e7',
			'order_count'    => '#b8c0c5',
			'item_count'     => '#d4d9dc',
			'refunded_count' => '#5c6870',
		);

		$current_range = ! empty( $_GET['range'] ) ? $_GET['range'] : '7day';

		if ( ! in_array( $current_range, array( 'custom', 'year', 'last_month', 'month', '7day' ) ) )
			$current_range = '7day';

		$this->calculate_current_range( $current_range );

		include( WC()->plugin_path() . '/includes/admin/views/html-report-by-date.php');
	}

	/**
	 * Output an export link
	 */
	public function get_export_button() {
		$current_range = ! empty( $_GET['range'] ) ? $_GET['range'] : '7day';
		?>
		<a
			href="#"
			download="report-<?php echo $current_range; ?>-<?php echo date_i18n( 'Y-m-d', current_time('timestamp') ); ?>.csv"
			class="export_csv"
			data-export="chart"
			data-xaxes="<?php _e( 'Date', 'woocommerce' ); ?>"
			data-exclude_series="2"
			data-groupby="<?php echo $this->chart_groupby; ?>"
		>
			<?php _e( 'Export CSV', 'woocommerce' ); ?>
		</a>
		<?php
	}

	/**
	 * Get the main chart
	 * @return string
	 */
	public function get_main_chart()
	{
		global $wp_locale;

		$data = $this->get_report_data();

		// Prepare data for report
		$sales_amounts     = $this->prepare_chart_data( $data->net_sales, 'post_date', 'total_sales', $this->chart_interval, $this->start_date, $this->chart_groupby );
		$cost_amounts      = $this->prepare_chart_data( $data->net_sales, 'post_date', 'total_cog', $this->chart_interval, $this->start_date, $this->chart_groupby );
		$profit_amounts    = $this->prepare_profit_chart_data( $sales_amounts, $cost_amounts );
		$order_counts      = $this->prepare_chart_data( $data->order_counts, 'post_date', 'count', $this->chart_interval, $this->start_date, $this->chart_groupby );
		$order_item_counts = $this->prepare_chart_data( $data->net_sales, 'post_date', 'qty', $this->chart_interval, $this->start_date, $this->chart_groupby );
		//$refunded_item_counts = $this->prepare_chart_data( $data->partial_refunds, 'post_date', 'refund_qty', $this->chart_interval, $this->start_date, $this->chart_groupby );

		// Encode in json format
		$chart_data = json_encode( array(
			'sales_amounts'     => array_map( array($this, 'round_chart_totals'), array_values( $sales_amounts ) ),
			'profit_amounts'    => array_map( array($this, 'round_chart_totals'), array_values( $profit_amounts ) ),
			'order_counts'      => array_values( $order_counts ),
			'order_item_counts' => array_values( $order_item_counts ),
			//'refunded_item_counts' => array_values( $refunded_item_counts ),
		) );
		?>
		<div class="chart-container">
			<div class="chart-placeholder main"></div>
		</div>
		<script type="text/javascript">

			var main_chart;

			jQuery(function(){
				var order_data = jQuery.parseJSON( '<?php echo $chart_data; ?>' );
				var drawGraph = function( highlight ) {
					var series = [
						/*{
							label: "<?php echo esc_js( __( 'Items Refunded', self::TEXT_DOMAIN ) ) ?>",
							data: order_data.refunded_item_counts,
							color: '<?php echo $this->chart_colours['refunded_count']; ?>',
							bars: { fillColor: '<?php echo $this->chart_colours['refunded_count']; ?>', fill: true, show: true, lineWidth: 0, barWidth: <?php echo $this->barwidth; ?> * 0.5, align: 'center' },
							shadowSize: 0,
							hoverable: false
						},*/
						{
							label: "<?php echo esc_js( __( 'Items Purchased', self::TEXT_DOMAIN ) ) ?>",
							data: order_data.order_item_counts,
							color: '<?php echo $this->chart_colours['item_count']; ?>',
							bars: { fillColor: '<?php echo $this->chart_colours['item_count']; ?>', fill: true, show: true, lineWidth: 0, barWidth: <?php echo $this->barwidth; ?> * 0.5, align: 'center' },
							shadowSize: 0,
							hoverable: false
						},
						{
							label: "<?php echo esc_js( __( 'Orders Placed', self::TEXT_DOMAIN ) ) ?>",
							data: order_data.order_counts,
							color: '<?php echo $this->chart_colours['order_count']; ?>',
							bars: { fillColor: '<?php echo $this->chart_colours['order_count']; ?>', fill: true, show: true, lineWidth: 0, barWidth: <?php echo $this->barwidth; ?> * 0.5, align: 'center' },
							shadowSize: 0,
							hoverable: false
						},
						{
							label: "<?php echo esc_js( __( 'Average daily profit', self::TEXT_DOMAIN ) ) ?>",
							data: [ [ <?php echo min( array_keys( $sales_amounts ) ); ?>, <?php echo $data->average_profit; ?> ], [ <?php echo max( array_keys( $sales_amounts ) ); ?>, <?php echo $data->average_profit; ?> ] ],
							yaxis: 2,
							color: '<?php echo $this->chart_colours['average_profit']; ?>',
							points: { show: false },
							lines: { show: true, lineWidth: 2, fill: false },
							shadowSize: 0,
							hoverable: false
						},
						{
							label: "<?php echo esc_js( __( 'Profit amount', self::TEXT_DOMAIN ) ) ?>",
							data: order_data.profit_amounts,
							yaxis: 2,
							color: '<?php echo $this->chart_colours['profit_amount']; ?>',
							points: { show: true, radius: 5, lineWidth: 3, fillColor: '#fff', fill: true },
							lines: { show: true, lineWidth: 4, fill: false },
							shadowSize: 0,
							prepend_tooltip: "<?php echo get_woocommerce_currency_symbol(); ?>"
						},
						{
							label: "<?php echo esc_js( __( 'Sales amount', 'woocommerce' ) ) ?>",
							data: order_data.sales_amounts,
							yaxis: 2,
							color: '<?php echo $this->chart_colours['sales_amount']; ?>',
							points: { show: true, radius: 5, lineWidth: 3, fillColor: '#fff', fill: true },
							lines: { show: true, lineWidth: 4, fill: false },
							shadowSize: 0,
							prepend_tooltip: "<?php echo get_woocommerce_currency_symbol(); ?>"
						}
					];

					if ( highlight !== 'undefined' && series[ highlight ] ) {
						highlight_series = series[ highlight ];

						highlight_series.color = '#9c5d90';

						if ( highlight_series.bars )
							highlight_series.bars.fillColor = '#9c5d90';

						if ( highlight_series.lines ) {
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
						    xaxes: [ {
						    	color: '#aaa',
						    	position: "bottom",
						    	tickColor: 'transparent',
								mode: "time",
								timeformat: "<?php if ( $this->chart_groupby == 'day' ) echo '%d %b'; else echo '%b'; ?>",
								monthNames: <?php echo json_encode( array_values( $wp_locale->month_abbrev ) ) ?>,
								tickLength: 1,
								minTickSize: [1, "<?php echo $this->chart_groupby; ?>"],
								font: {
						    		color: "#aaa"
						    	}
							} ],
						    yaxes: [
						    	{
						    		min: 0,
						    		minTickSize: 1,
						    		tickDecimals: 0,
						    		color: '#d4d9dc',
						    		font: { color: "#aaa" }
						    	},
						    	{
						    		position: "right",
						    		min: 0,
						    		tickDecimals: 2,
						    		alignTicksWithAxis: 1,
						    		color: 'transparent',
						    		font: { color: "#aaa" }
						    	}
						    ]
				 		}
				 	);

					jQuery('.chart-placeholder').resize();
				}

				drawGraph();

				jQuery('.highlight_series').hover(
					function() {
						drawGraph( jQuery(this).data('series') );
					},
					function() {
						drawGraph();
					}
				);
			});
		</script>
		<?php
	}
}