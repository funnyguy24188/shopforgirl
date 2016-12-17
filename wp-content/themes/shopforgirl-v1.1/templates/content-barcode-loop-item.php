<?php require_once(get_stylesheet_directory() . '/src/product/SPGProductDetail.php'); ?>
<?php $product = wc_get_product(); ?>
<div class="product-detail-zone barcode-image-field">
    <div class="barcode-image"><?php echo SPGProductDetail::get_barcode_field($product); ?> </div>
</div>