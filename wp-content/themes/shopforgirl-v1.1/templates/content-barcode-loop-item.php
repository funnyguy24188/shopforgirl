<?php require_once(get_stylesheet_directory() . '/src/product/SPGProductDetail.php'); ?>
<?php $product = wc_get_product(); ?>
<div class="product-detail-zone barcode-image-field">
    <strong>Barcode: <?php echo get_post_meta(SPGProductDetail::get_product_id($product), '_barcode_field', true) ?></strong>
</div>