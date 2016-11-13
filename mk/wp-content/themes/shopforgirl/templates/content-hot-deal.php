<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

global $virtue_premium, $woocommerce_loop, $wp_query;

if (empty($woocommerce_loop['columns']))
    $woocommerce_loop['columns'] = apply_filters('loop_shop_columns', 4);

masterslider("hot-deal");

?>

<?php
$args = array(
    'post_type' => 'product',
    'meta_query' => array(
        array('key' => 'hot_deal', 'value' => 1)
    ),

);

$query = new WP_Query($args);


?>

<div id="content" class="container">
    <div class="row">
        <div class="main <?php echo kadence_main_class(); ?>" role="main">

            <?php
            /**
             * woocommerce_before_main_content hook
             *
             * @hooked woocommerce_output_content_wrapper - 10 (outputs opening divs for the content)
             * @hooked woocommerce_breadcrumb - 20
             */
            do_action('woocommerce_before_main_content');
            ?>
            <div class="clearfix">
                <?php do_action('woocommerce_archive_description'); ?>
            </div>

            <?php if ($query->have_posts()) : ?>

                <?php
                /**
                 * woocommerce_before_shop_loop hook
                 * and ($shop_filter == '1')
                 * @hooked woocommerce_result_count - 20
                 * @hooked woocommerce_catalog_ordering - 30
                 */
                do_action('woocommerce_before_shop_loop');
                ?>
                <?php global $virtue_premium;
                $shop_filter = $virtue_premium['shop_filter'];
                $cat_filter = $virtue_premium['cat_filter'];
                if (!empty($virtue_premium['filter_all_text'])) {
                    $alltext = $virtue_premium['filter_all_text'];
                } else {
                    $alltext = __('All', 'virtue');
                }
                if (!empty($virtue_premium['shop_filter_text'])) {
                    $shopfiltertext = $virtue_premium['shop_filter_text'];
                } else {
                    $shopfiltertext = __('Filter Products', 'virtue');
                }
                if (is_shop() && $shop_filter == 1 && !is_search()) { ?>
                    <section id="options" class="clearfix">
                        <?php
                        $categories = get_terms('product_cat');
                        $count = count($categories);
                        echo '<a class="filter-trigger headerfont" data-toggle="collapse" data-target=".filter-collapse"><i class="icon-tags"></i> ' . $shopfiltertext . '</a>';
                        echo '<ul id="filters" class="clearfix option-set filter-collapse">';
                        echo '<li class="postclass"><a href="#" data-filter="*" title="' . $alltext . '" class="selected"><h5>' . $alltext . '</h5><div class="arrow-up"></div></a></li>';
                        if ($count > 0) {
                            foreach ($categories as $category) {
                                $termname = strtolower($category->slug);
                                $termname = preg_replace("/[^a-zA-Z 0-9]+/", " ", $termname);
                                $termname = str_replace(' ', '-', $termname);
                                echo '<li class="postclass"><a href="#" data-filter=".' . $termname . '" title="' . __("Show", "virtue") . ' ' . $category->name . '" rel="' . $termname . '"><h5>' . $category->name . '</h5><div class="arrow-up"></div></a></li>';
                            }
                        }
                        echo "</ul>"; ?>
                    </section>
                <?php } else if (is_product_category() && $cat_filter == 1) { ?>
                    <section id="options" class="clearfix">
                        <?php
                        // get the query object
                        $cat_obj = $query->get_queried_object();
                        $product_cat_ID = $cat_obj->term_id;
                        $termtypes = array('child_of' => $product_cat_ID,);
                        $categories = get_terms('product_cat', $termtypes);
                        $count = count($categories);
                        if ($count > 0) {
                            echo '<a class="filter-trigger headerfont" data-toggle="collapse" data-target=".filter-collapse"><i class="icon-tags"></i> ' . $shopfiltertext . '</a>';
                            echo '<ul id="filters" class="clearfix option-set filter-collapse">';
                            echo '<li class="postclass"><a href="#" data-filter="*" title="' . $alltext . '" class="selected"><h5>' . $alltext . '</h5><div class="arrow-up"></div></a></li>';
                            foreach ($categories as $category) {
                                $termname = strtolower($category->slug);
                                $termname = preg_replace("/[^a-zA-Z 0-9]+/", " ", $termname);
                                $termname = str_replace(' ', '-', $termname);
                                echo '<li class="postclass"><a href="#" data-filter=".' . $termname . '" title="' . __("Show", "virtue") . ' ' . $category->name . '" rel="' . $termname . '"><h5>' . $category->name . '</h5><div class="arrow-up"></div></a></li>';
                            }
                            echo "</ul>";
                        } ?>
                    </section>
                <?php } ?>

                <div class="clearfix <?php echo kadence_category_layout_css(); ?> rowtight product_category_padding">
                    <?php woocommerce_product_subcategories(); ?>
                </div>

                <?php woocommerce_product_loop_start(); ?>

                <?php while ($query->have_posts()) : $query->the_post(); ?>

                    <?php woocommerce_get_template_part('content', 'product'); ?>

                <?php endwhile; // end of the loop. ?>

                <?php
                /**
                 * woocommerce_after_shop_loop hook
                 *
                 * @hooked woocommerce_pagination - 10
                 */
                do_action('woocommerce_after_shop_loop');
                ?>

            <?php else: ?>

                <?php woocommerce_get_template('loop/no-products-found.php'); ?>

            <?php endif; ?>
        </div>
    </div>
</div>