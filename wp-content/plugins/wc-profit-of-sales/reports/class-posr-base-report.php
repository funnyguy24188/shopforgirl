<?php
/**
 * Class POSR_Base_Report
 */

include POSR_PATH . 'includes/PHPColors/Color.php';

class POSR_Base_Report extends WC_Admin_Report
{
    const TEXT_DOMAIN = 'wc-posr';
    protected $report_data;

    public function get_report_data()
    {
        if (empty($this->report_data)) {
            $this->query_report_data();
        }

        return $this->report_data;
    }

    protected function prepare_profit_chart_data( $orders, $costs )
    {
        $profits = $orders;
        foreach(array_keys($profits) as $time) {
            $profits[$time][1] = $orders[$time][1] - $costs[$time][1];
            ($profits[$time][1] < 0)? $profits[$time][1] = 0 : 0;
        }

        return $profits;
    }

    protected function get_another_colour( $colour, $colour_adjust = 20 )
    {
        $clr = new Mexitek\PHPColors\Color( $colour );

        $new_clr = $clr->isDark()? $clr->lighten($colour_adjust) : $clr->darken($colour_adjust);

        return '#' . $new_clr;
    }


    /**
     * Round our totals correctly
     * @param  string $amount
     * @return string
     */
    protected function round_chart_totals( $amount ) {
        if ( is_array( $amount ) ) {
            return array_map( array( $this, 'round_chart_totals' ), $amount );
        } else {
            return wc_format_decimal($amount, wc_get_price_decimals());
        }
    }

    protected function round_totals( $amount ) {
        return round( $amount, wc_get_price_decimals() );
    }

    protected function query_orders()
    {
        $params = array(
            'data' => array(
                'ID' => array(
                    'type'     => 'post_data',
                    'function' => 'COUNT',
                    'name'     => 'count',
                    'distinct' => true,
                ),
                'post_date' => array(
                    'type'     => 'post_data',
                    'function' => '',
                    'name'     => 'post_date'
                )
            ),
            'group_by'            => $this->group_by_query,
            'order_by'            => 'post_date ASC',
            'query_type'          => 'get_results',
            'filter_range'        => true,
            'order_types'         => wc_get_order_types( 'order-count' ),
            'order_status'        => array( 'completed', 'processing', 'on-hold' )
        );

        return (array) $this->get_order_report_data($params);
    }

    protected function query_orders_by_products(array $product_ids)
    {
        $params = array(
            'data' => array(
                'ID' => array(
                    'type'     => 'post_data',
                    'function' => 'COUNT',
                    'name'     => 'count',
                    'distinct' => true,
                ),
                'post_date' => array(
                    'type'     => 'post_data',
                    'function' => '',
                    'name'     => 'post_date'
                ),
                '_product_id' => array(
                    'type'            => 'order_item_meta',
                    'order_item_type' => 'line_item',
                    'function'        => '',
                    'name'            => 'product_id'
                )
            ),
            'where_meta' => array(
                'relation' => 'OR',
                array(
                    'type'       => 'order_item_meta',
                    'meta_key'   => array( '_product_id', '_variation_id' ),
                    'meta_value' => $product_ids,
                    'operator'   => 'IN'
                ),
            ),
            'group_by'            => $this->group_by_query,
            'order_by'            => 'post_date ASC',
            'query_type'          => 'get_results',
            'filter_range'        => true,
            'order_types'         => wc_get_order_types( 'order-count' ),
            'order_status'        => array( 'completed', 'processing', 'on-hold' )
        );

        return (array) $this->get_order_report_data($params);
    }

    protected function query_sales_by_date()
    {
        global $wpdb;

        $query = "
			SELECT posts.post_date,
				SUM(order_itemmeta__line_total.meta_value) AS total_sales,
				SUM(order_itemmeta__cog_total.meta_value) AS total_cog

			FROM {$wpdb->posts} posts

			LEFT JOIN {$wpdb->prefix}woocommerce_order_items order_items
				ON order_items.order_id = posts.ID
					AND order_items.order_item_type = 'line_item'

			LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta order_itemmeta__line_total
				ON order_itemmeta__line_total.order_item_id = order_items.order_item_id
					AND order_itemmeta__line_total.meta_key = '_line_total'

			LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta order_itemmeta__cog_total
				ON order_itemmeta__cog_total.order_item_id = order_items.order_item_id
					AND order_itemmeta__cog_total.meta_key = '_posr_line_cog_total'

			WHERE
				NOT EXISTS(
					SELECT 1
					FROM {$wpdb->posts} refund_order
					WHERE refund_order.post_parent = posts.ID
					  AND refund_order.post_type = 'shop_order_refund'
					  AND refund_order.post_excerpt = 'Order Fully Refunded'
				)
				AND posts.post_type = 'shop_order'
				AND posts.post_date >= '" . date('Y-m-d', $this->start_date ) . "'
				AND posts.post_date < '" . date('Y-m-d', strtotime( '+1 DAY', $this->end_date ) ) . "'

			GROUP BY {$this->group_by_query}
			ORDER BY posts.post_date
		";

        return $wpdb->get_results($query);
    }

    protected function query_net_sales($product_ids = null)
    {
        global $wpdb;

        $where = '';
        if (!is_null($product_ids)) {
            $in_product_ids = "'" . implode("', '", (array) $product_ids) . "'";
            $where .= "AND ( order_itemmeta__product_id.meta_key IN ('_product_id','_variation_id')
                        AND order_itemmeta__product_id.meta_value IN ($in_product_ids) )";
        }

        $query = "
			SELECT posts.ID AS order_id,
			    posts.post_date,
			    posts.post_type AS order_type,
			    order_items.order_item_id,
			    order_itemmeta__product_id.meta_value AS product_id,
			    order_itemmeta__qty.meta_value AS qty,
				order_itemmeta__line_total.meta_value AS total_sales,
				order_itemmeta__cog_total.meta_value AS total_cog

			FROM {$wpdb->posts} posts

			LEFT JOIN {$wpdb->prefix}woocommerce_order_items order_items
				ON order_items.order_id = posts.ID
					AND order_items.order_item_type = 'line_item'

			JOIN {$wpdb->prefix}woocommerce_order_itemmeta order_itemmeta__product_id
				ON order_itemmeta__product_id.order_item_id = order_items.order_item_id
					AND order_itemmeta__product_id.meta_key = '_product_id'

			JOIN {$wpdb->prefix}woocommerce_order_itemmeta order_itemmeta__qty
				ON order_itemmeta__qty.order_item_id = order_items.order_item_id
					AND order_itemmeta__qty.meta_key = '_qty'

			JOIN {$wpdb->prefix}woocommerce_order_itemmeta order_itemmeta__line_total
				ON order_itemmeta__line_total.order_item_id = order_items.order_item_id
					AND order_itemmeta__line_total.meta_key = '_line_total'

			LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta order_itemmeta__cog_total
				ON order_itemmeta__cog_total.order_item_id = order_items.order_item_id
					AND order_itemmeta__cog_total.meta_key = '_posr_line_cog_total'

			WHERE
				NOT EXISTS(
					SELECT 1
					FROM {$wpdb->posts} refund_order
					WHERE refund_order.post_parent = posts.ID
					  AND refund_order.post_type = 'shop_order_refund'
					  AND refund_order.post_excerpt = 'Order Fully Refunded'
				)
				AND posts.post_type = 'shop_order'
				AND posts.post_date >= '" . date('Y-m-d', $this->start_date ) . "'
				AND posts.post_date < '" . date('Y-m-d', strtotime( '+1 DAY', $this->end_date ) ) . "'
				$where
		";

        $sales = (array) $wpdb->get_results($query);

        foreach($sales as &$sale) {
            $query = "
                        SELECT
                            SUM(order_itemmeta__qty.meta_value) as qty,
                            SUM(order_itemmeta__line_total.meta_value) AS total_refund

                        FROM {$wpdb->posts} posts

                        JOIN {$wpdb->prefix}woocommerce_order_items order_items
                            ON order_items.order_id = posts.ID
                            AND order_items.order_item_type = 'line_item'

                        JOIN {$wpdb->prefix}woocommerce_order_itemmeta order_itemmeta__qty
                            ON order_itemmeta__qty.order_item_id = order_items.order_item_id
                            AND order_itemmeta__qty.meta_key = '_qty'

                        JOIN {$wpdb->prefix}woocommerce_order_itemmeta order_itemmeta__line_total
                            ON order_itemmeta__line_total.order_item_id = order_items.order_item_id
                            AND order_itemmeta__line_total.meta_key = '_line_total'

                        JOIN {$wpdb->prefix}woocommerce_order_itemmeta order_itemmeta__refunded_item
                            ON order_itemmeta__refunded_item.order_item_id = order_items.order_item_id
                            AND order_itemmeta__refunded_item.meta_key = '_refunded_item_id'

                        WHERE
                            posts.post_type = 'shop_order_refund'
                            AND posts.post_parent = '{$sale->order_id}'
                            AND order_itemmeta__refunded_item.meta_value = '{$sale->order_item_id}'
                    ";
            $refund = $wpdb->get_row($query);

            if ($refund) {
                // first calculate price and cog per item
                $item_price = $sale->total_sales / $sale->qty;
                $item_cog = $sale->total_cog / $sale->qty;

                // substract sales by refund
                $sale->qty -= $refund->qty;

                // let's update the total
                $sale->total_cog = $sale->qty * $item_cog;
                $sale->total_sales = $sale->qty * $item_price;
            }
        }

        return $sales;
    }

    protected function query_order_items()
    {
        $params = array(
            'data' => array(
                '_qty' => array(
                    'type'            => 'order_item_meta',
                    'order_item_type' => 'line_item',
                    'function'        => 'SUM',
                    'name'            => 'order_item_count'
                ),
                'post_date' => array(
                    'type'     => 'post_data',
                    'function' => '',
                    'name'     => 'post_date'
                ),
            ),
            'where' => array(
                array(
                    'key'      => 'order_items.order_item_type',
                    'value'    => 'line_item',
                    'operator' => '='
                )
            ),
            'group_by'            => $this->group_by_query,
            'order_by'            => 'post_date ASC',
            'query_type'          => 'get_results',
            'filter_range'        => true,
            'order_types'         => wc_get_order_types( 'order-count' ),
            'order_status'        => array( 'completed', 'processing', 'on-hold', 'refunded' ),
        );

        return (array) $this->get_order_report_data($params);
    }

    protected function query_order_items_by_products(array $product_ids)
    {
        $params = array(
            'data' => array(
                '_qty' => array(
                    'type'            => 'order_item_meta',
                    'order_item_type' => 'line_item',
                    'function'        => 'SUM',
                    'name'            => 'order_item_count'
                ),
                'post_date' => array(
                    'type'     => 'post_data',
                    'function' => '',
                    'name'     => 'post_date'
                ),
                '_product_id' => array(
                    'type'            => 'order_item_meta',
                    'order_item_type' => 'line_item',
                    'function'        => '',
                    'name'            => 'product_id'
                )
            ),
            'where_meta' => array(
                'relation' => 'OR',
                array(
                    'type'       => 'order_item_meta',
                    'meta_key'   => array( '_product_id', '_variation_id' ),
                    'meta_value' => $product_ids,
                    'operator'   => 'IN'
                ),
            ),
            'where' => array(
                array(
                    'key'      => 'order_items.order_item_type',
                    'value'    => 'line_item',
                    'operator' => '='
                )
            ),
            'group_by'            => 'product_id,' . $this->group_by_query,
            'order_by'            => 'post_date ASC',
            'query_type'          => 'get_results',
            'filter_range'        => true,
            'order_types'         => wc_get_order_types( 'order-count' ),
            'order_status'        => array( 'completed', 'processing', 'on-hold', 'refunded' ),
        );

        return (array) $this->get_order_report_data($params);
    }

    protected function query_full_refunds()
    {
        $params = array(
            'data' => array(
                '_qty' => array(
                    'type'            => 'order_item_meta',
                    'order_item_type' => 'line_item',
                    'function'        => 'SUM',
                    'name'            => 'refunded_item_count'
                ),
                'post_date' => array(
                    'type'     => 'post_data',
                    'function' => '',
                    'name'     => 'post_date'
                ),
            ),
            'where' => array(
                array(
                    'key'      => 'order_items.order_item_type',
                    'value'    => 'line_item',
                    'operator' => '='
                )
            ),
            'group_by'            => $this->group_by_query,
            'order_by'            => 'post_date ASC',
            'query_type'          => 'get_results',
            'filter_range'        => true,
            'order_types'         => wc_get_order_types( 'order-count' ),
            'order_status'        => array( 'refunded' )
        );

        return (array) $this->get_order_report_data($params);
    }

    protected function query_partial_refunds()
    {
        global $wpdb;

        $query = "
			SELECT posts.post_date,
				SUM(order_itemmeta__qty.meta_value) as refund_qty,
				SUM(order_itemmeta__line_total.meta_value) as refund_value

			FROM {$wpdb->posts} posts

			INNER JOIN {$wpdb->prefix}woocommerce_order_items order_items
				ON posts.ID = order_items.order_id
					AND order_items.order_item_type = 'line_item'

			LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta order_itemmeta__product_id
				ON order_items.order_item_id = order_itemmeta__product_id.order_item_id
					AND order_itemmeta__product_id.meta_key = '_product_id'

			LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta order_itemmeta__qty
				ON order_items.order_item_id = order_itemmeta__qty.order_item_id
					AND order_itemmeta__qty.meta_key = '_qty'

			LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta order_itemmeta__line_total
				ON order_items.order_item_id = order_itemmeta__line_total.order_item_id
					AND order_itemmeta__line_total.meta_key = '_line_total'

			WHERE posts.post_type = 'shop_order_refund'
				AND posts.post_date >= '" . date('Y-m-d', $this->start_date ) . "'
				AND posts.post_date < '" . date('Y-m-d', strtotime( '+1 DAY', $this->end_date ) ) . "'

			GROUP BY posts.post_date
			HAVING SUM(order_itemmeta__qty.meta_value) <> 0
		";

        return $wpdb->get_results($query);
    }
}