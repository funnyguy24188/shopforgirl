<?php get_header('shop'); ?>

<?php require_once('src/product/SPGProductDetail.php') ?>
    <div id="single-product-page-header" class="titleclass">
        <div class="container">
            <h2 style="float:left;margin-top:0"><?php the_title() ?></h2>
        </div><!--container-->
    </div><!--titleclass-->

<?php

$product_single_barcode = null;
global $post;
global $product;
if (isset($_GET['barcode'])) {
    $product_finding = new SPGProductDetail();
    $result = $product_finding->get_product_info($_GET['barcode'], false);
    if ($result['result']) {
        $post = $result['raw_post'];
        $product = wc_get_product($result['data']['id']);
    }
}

?>

    <div id="content" class="container product-barcode-finding">
        <div class="row">
            <div class="barcode-group">
                <div class="col-sm-24 col-sm-24 col-md-24 col-lg-24">
                    <form method="get">
                        <input type="text" class="form-control" name="barcode"
                               value="<?php echo isset($_GET['barcode']) ? $_GET['barcode'] : '' ?>"
                               placeholder="Barcode sản phẩm">
                    </form>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="product-info-group">
                <div class="col-sm-24 col-sm-24 col-md-24 col-lg-24">
                    <?php if (!empty($post) && $post->post_type != 'page'): ?>
                        <?php setup_postdata($post) ?>
                        <?php woocommerce_get_template_part('woocommerce/single-product/content', 'single-barcode-product'); ?>
                    <?php else: ?>
                        <span>No product</span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

<?php get_footer('shop'); ?>