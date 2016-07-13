<style>
<?php
global $wc_dgallery_admin_interface, $wc_dgallery_fonts_face;

$g_thumb_spacing            = get_option(WOO_DYNAMIC_GALLERY_PREFIX . 'thumb_spacing');

$main_bg_color              = get_option(WOO_DYNAMIC_GALLERY_PREFIX . 'main_bg_color');
$main_border                = get_option(WOO_DYNAMIC_GALLERY_PREFIX . 'main_border');
$main_shadow                = get_option(WOO_DYNAMIC_GALLERY_PREFIX . 'main_shadow');
$main_margin_top            = get_option(WOO_DYNAMIC_GALLERY_PREFIX . 'main_margin_top');
$main_margin_bottom         = get_option(WOO_DYNAMIC_GALLERY_PREFIX . 'main_margin_bottom');
$main_margin_left           = get_option(WOO_DYNAMIC_GALLERY_PREFIX . 'main_margin_left');
$main_margin_right          = get_option(WOO_DYNAMIC_GALLERY_PREFIX . 'main_margin_right');
$main_padding_top           = get_option(WOO_DYNAMIC_GALLERY_PREFIX . 'main_padding_top');
$main_padding_bottom        = get_option(WOO_DYNAMIC_GALLERY_PREFIX . 'main_padding_bottom');
$main_padding_left          = get_option(WOO_DYNAMIC_GALLERY_PREFIX . 'main_padding_left');
$main_padding_right         = get_option(WOO_DYNAMIC_GALLERY_PREFIX . 'main_padding_right');

$navbar_font                = get_option(WOO_DYNAMIC_GALLERY_PREFIX . 'navbar_font');
$navbar_bg_color            = get_option(WOO_DYNAMIC_GALLERY_PREFIX . 'navbar_bg_color');
$navbar_border              = get_option(WOO_DYNAMIC_GALLERY_PREFIX . 'navbar_border');
$navbar_shadow              = get_option(WOO_DYNAMIC_GALLERY_PREFIX . 'navbar_shadow');
$navbar_margin_top          = get_option(WOO_DYNAMIC_GALLERY_PREFIX . 'navbar_margin_top');
$navbar_margin_bottom       = get_option(WOO_DYNAMIC_GALLERY_PREFIX . 'navbar_margin_bottom');
$navbar_margin_left         = get_option(WOO_DYNAMIC_GALLERY_PREFIX . 'navbar_margin_left');
$navbar_margin_right        = get_option(WOO_DYNAMIC_GALLERY_PREFIX . 'navbar_margin_right');
$navbar_padding_top         = get_option(WOO_DYNAMIC_GALLERY_PREFIX . 'navbar_padding_top');
$navbar_padding_bottom      = get_option(WOO_DYNAMIC_GALLERY_PREFIX . 'navbar_padding_bottom');
$navbar_padding_left        = get_option(WOO_DYNAMIC_GALLERY_PREFIX . 'navbar_padding_left');
$navbar_padding_right       = get_option(WOO_DYNAMIC_GALLERY_PREFIX . 'navbar_padding_right');

$navbar_separator           = get_option(WOO_DYNAMIC_GALLERY_PREFIX . 'navbar_separator');

$caption_font               = get_option(WOO_DYNAMIC_GALLERY_PREFIX . 'caption_font');
$caption_bg_color           = get_option(WOO_DYNAMIC_GALLERY_PREFIX . 'caption_bg_color');
$caption_bg_transparent     = get_option(WOO_DYNAMIC_GALLERY_PREFIX . 'caption_bg_transparent');

$transition_scroll_bar      = get_option( WOO_DYNAMIC_GALLERY_PREFIX.'transition_scroll_bar' );

$thumb_show_type            = get_option(WOO_DYNAMIC_GALLERY_PREFIX . 'thumb_show_type', 'slider' );
$thumb_border_color         = get_option(WOO_DYNAMIC_GALLERY_PREFIX . 'thumb_border_color', 'transparent' );
$thumb_current_border_color = get_option(WOO_DYNAMIC_GALLERY_PREFIX . 'thumb_current_border_color', '#96588a' );

?>
#TB_window {
    width: auto !important;
}
.product .onsale {
    z-index: 100;
}
.a3-dgallery .a3dg-image-wrapper {
	<?php echo $wc_dgallery_admin_interface->generate_background_color_css( $main_bg_color ); ?>
    <?php echo $wc_dgallery_admin_interface->generate_border_css( $main_border ); ?>
    <?php echo $wc_dgallery_admin_interface->generate_shadow_css( $main_shadow ); ?>
    margin: <?php echo $main_margin_top; ?>px <?php echo $main_margin_right; ?>px <?php echo $main_margin_bottom; ?>px <?php echo $main_margin_left; ?>px !important;
    padding: <?php echo $main_padding_top; ?>px <?php echo $main_padding_right; ?>px <?php echo $main_padding_bottom; ?>px <?php echo $main_padding_left; ?>px !important;
}
.a3-dgallery .a3dg-image-wrapper .a3dg-image {
    margin-top: <?php echo $main_padding_top; ?>px !important;
}
.a3-dgallery .a3dg-thumbs li {
    margin-right: <?php echo $g_thumb_spacing; ?>px !important;
<?php if ( 'static' == $thumb_show_type ) { ?>
    margin-bottom: <?php echo $g_thumb_spacing; ?>px !important;
<?php } ?>
}

/* Caption Text */
.a3dg-image-wrapper .a3dg-image-description {
    <?php echo $wc_dgallery_fonts_face->generate_font_css( $caption_font ); ?>;
    <?php echo $wc_dgallery_admin_interface->generate_background_color_css( $caption_bg_color, $caption_bg_transparent ); ?>
}

/* Navbar Separator */
.product_gallery .a3dg-navbar-separator {
    <?php echo str_replace( 'border', 'border-left', $wc_dgallery_admin_interface->generate_border_style_css( $navbar_separator ) ); ?>
    margin-left: -<?php echo ( (int)$navbar_separator['width'] / 2 ); ?>px;
}

/* Navbar Control */
.product_gallery .a3dg-navbar-control {
    <?php echo $wc_dgallery_fonts_face->generate_font_css( $navbar_font ); ?>
    <?php echo $wc_dgallery_admin_interface->generate_background_color_css( $navbar_bg_color ); ?>
    <?php echo $wc_dgallery_admin_interface->generate_border_css( $navbar_border ); ?>
    <?php echo $wc_dgallery_admin_interface->generate_shadow_css( $navbar_shadow ); ?>
    margin: <?php echo $navbar_margin_top; ?>px <?php echo $navbar_margin_right; ?>px <?php echo $navbar_margin_bottom; ?>px <?php echo $navbar_margin_left; ?>px !important;
}
.product_gallery .a3dg-navbar-control .slide-ctrl,
.product_gallery .a3dg-navbar-control .icon_zoom {
    padding: <?php echo $navbar_padding_top; ?>px <?php echo $navbar_padding_right; ?>px <?php echo $navbar_padding_bottom; ?>px <?php echo $navbar_padding_left; ?>px !important;
}

/* Lazy Load Scroll */
.a3-dgallery .lazy-load {
    background-color: <?php echo $transition_scroll_bar; ?> !important;
}

.product_gallery .a3-dgallery .a3dg-thumbs li a {
    border: 1px solid <?php echo $thumb_border_color; ?> !important;
}

.a3-dgallery .a3dg-thumbs li a.a3dg-active {
    border: 1px solid <?php echo $thumb_current_border_color; ?> !important;
}

</style>
