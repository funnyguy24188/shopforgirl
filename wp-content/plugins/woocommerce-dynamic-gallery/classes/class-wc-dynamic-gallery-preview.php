<?php
/**
 * WooCommerce Gallery Preview Display Class
 *
 * Class Function into woocommerce plugin
 *
 * Table Of Contents
 *
 * wc_dynamic_gallery_preview()
 */
class WC_Gallery_Preview_Display
{

	public static function wc_dynamic_gallery_preview($request = ''){
		if ( ! is_user_logged_in() || ! current_user_can( 'manage_options' ) ) die();

		global $wc_dgallery_admin_interface, $wc_dgallery_fonts_face;
		$request = $_REQUEST;
		/**
		 * Single Product Image
		 */
		$post = new stdClass();
		$current_db_version = get_option( 'woocommerce_db_version', null );
		$woo_a3_gallery_settings = $request;
		$lightbox_class = 'lightbox';
		$thumbs_list_class	 = '';
		$display_back_and_forward = 'true';

		$post->ID = rand(10,10000);

		if ( isset( $woo_a3_gallery_settings[WOO_DYNAMIC_GALLERY_PREFIX.'width_type'] ) ) {
			$woo_dg_width_type = $woo_a3_gallery_settings[WOO_DYNAMIC_GALLERY_PREFIX.'width_type'];
		} else {
			$woo_dg_width_type = 'px';
		}
		if ( isset( $woo_a3_gallery_settings[WOO_DYNAMIC_GALLERY_PREFIX.'gallery_height_type'] ) ) {
			$gallery_height_type = $woo_a3_gallery_settings[WOO_DYNAMIC_GALLERY_PREFIX.'gallery_height_type'];
		} else {
			$gallery_height_type = 'dynamic';
		}
		if ( $woo_dg_width_type == 'px' ) {
			$g_width = $woo_a3_gallery_settings[WOO_DYNAMIC_GALLERY_PREFIX.'product_gallery_width_fixed'].'px';
			$g_height = $woo_a3_gallery_settings[WOO_DYNAMIC_GALLERY_PREFIX.'product_gallery_height'];
		} else {
			$g_width = $woo_a3_gallery_settings[WOO_DYNAMIC_GALLERY_PREFIX.'product_gallery_width_responsive'].'%';
		}

		$max_height          = 533;
		$width_of_max_height = 400;
		// Set height for when gallery is responsive wide or dynamic height
		if ( 'px' != $woo_dg_width_type || 'dynamic' == $gallery_height_type ) {
			if ( $max_height > 0 ) {
				$g_height = false;
		?>
            <script type="text/javascript">
			(function($){
				$(function(){
					a3revWCDynamicGallery_<?php echo $post->ID; ?> = {

						setHeightProportional: function () {
							var image_wrapper_width = $( '#gallery_<?php echo $post->ID; ?>' ).find('.a3dg-image-wrapper').outerWidth();
							var width_of_max_height = parseInt(<?php echo $width_of_max_height; ?>);
							var image_wrapper_height = parseInt(<?php echo $max_height; ?>);
							if( width_of_max_height > image_wrapper_width ) {
								var ratio = width_of_max_height / image_wrapper_width;
								image_wrapper_height = parseInt(<?php echo $max_height; ?>) / ratio;
							}
							$( '#gallery_<?php echo $post->ID; ?>' ).find('.a3dg-image-wrapper').css({ height: image_wrapper_height });
						}
					}

					a3revWCDynamicGallery_<?php echo $post->ID; ?>.setHeightProportional();

					$( window ).resize(function() {
						a3revWCDynamicGallery_<?php echo $post->ID; ?>.setHeightProportional();
					});
				});
			})(jQuery);
			</script>
		<?php
			} else {
				$g_height = 138;
			}
		}

		$caption_font = $woo_a3_gallery_settings[WOO_DYNAMIC_GALLERY_PREFIX.'caption_font'];
		$navbar_font  = $woo_a3_gallery_settings[WOO_DYNAMIC_GALLERY_PREFIX.'navbar_font'];
		$google_fonts = array( $caption_font['face'], $navbar_font['face'] );
		$wc_dgallery_fonts_face->generate_google_webfonts( $google_fonts );

		?>
        <div class="images" style="100%; margin:30px auto;">
          <div class="product_gallery">
            <?php
			$shop_thumbnail  = wc_get_image_size( 'shop_thumbnail' );
			$g_thumb_width   = $shop_thumbnail['width'];
			$g_thumb_height  = $shop_thumbnail['height'];
			$g_thumb_spacing = $woo_a3_gallery_settings[WOO_DYNAMIC_GALLERY_PREFIX.'thumb_spacing'];
			if ( isset( $woo_a3_gallery_settings[WOO_DYNAMIC_GALLERY_PREFIX.'thumb_show_type'] ) ) {
				$thumb_show_type = 'slider';
			} else {
				$thumb_show_type = 'static';
			}

			$thumb_columns   = $woo_a3_gallery_settings[WOO_DYNAMIC_GALLERY_PREFIX . 'thumb_columns'];
			if ( 'static' == $thumb_show_type ) {
				$thumbs_list_class = 'a3dg-thumbs-static';
				$display_back_and_forward = 'false';
			}

			if ( isset( $woo_a3_gallery_settings[WOO_DYNAMIC_GALLERY_PREFIX.'product_gallery_auto_start'] ) ) {
            	$g_auto = $woo_a3_gallery_settings[WOO_DYNAMIC_GALLERY_PREFIX.'product_gallery_auto_start'];
			} else {
				$g_auto = 'false';
			}

            $g_speed = $woo_a3_gallery_settings[WOO_DYNAMIC_GALLERY_PREFIX.'product_gallery_speed'];
            $g_effect = $woo_a3_gallery_settings[WOO_DYNAMIC_GALLERY_PREFIX.'product_gallery_effect'];
            $g_animation_speed = $woo_a3_gallery_settings[WOO_DYNAMIC_GALLERY_PREFIX.'product_gallery_animation_speed'];

			$main_bg_color = $woo_a3_gallery_settings[WOO_DYNAMIC_GALLERY_PREFIX.'main_bg_color'];
			if ( ! isset( $main_bg_color['enable'] ) ) {
				$main_bg_color['enable'] = 0;
			}
			$main_border = $woo_a3_gallery_settings[WOO_DYNAMIC_GALLERY_PREFIX.'main_border'];
			if ( ! isset( $main_border['corner'] ) ) {
				$main_border['corner'] = 'square';
			}
			$main_shadow = $woo_a3_gallery_settings[WOO_DYNAMIC_GALLERY_PREFIX.'main_shadow'];
			if ( ! isset( $main_shadow['enable'] ) ) {
				$main_shadow['enable'] = 0;
			}
			$main_margin_top     = $woo_a3_gallery_settings[WOO_DYNAMIC_GALLERY_PREFIX.'main_margin_top'];
			$main_margin_bottom  = $woo_a3_gallery_settings[WOO_DYNAMIC_GALLERY_PREFIX.'main_margin_bottom'];
			$main_margin_left    = $woo_a3_gallery_settings[WOO_DYNAMIC_GALLERY_PREFIX.'main_margin_left'];
			$main_margin_right   = $woo_a3_gallery_settings[WOO_DYNAMIC_GALLERY_PREFIX.'main_margin_right'];
			$main_padding_top    = $woo_a3_gallery_settings[WOO_DYNAMIC_GALLERY_PREFIX.'main_padding_top'];
			$main_padding_bottom = $woo_a3_gallery_settings[WOO_DYNAMIC_GALLERY_PREFIX.'main_padding_bottom'];
			$main_padding_left   = $woo_a3_gallery_settings[WOO_DYNAMIC_GALLERY_PREFIX.'main_padding_left'];
			$main_padding_right  = $woo_a3_gallery_settings[WOO_DYNAMIC_GALLERY_PREFIX.'main_padding_right'];

			if ( isset( $woo_a3_gallery_settings[WOO_DYNAMIC_GALLERY_PREFIX.'product_gallery_nav'] ) ) {
				$product_gallery_nav = $woo_a3_gallery_settings[WOO_DYNAMIC_GALLERY_PREFIX.'product_gallery_nav'];
			} else {
				$product_gallery_nav = 'no';
			}
			$navbar_bg_color = $woo_a3_gallery_settings[WOO_DYNAMIC_GALLERY_PREFIX.'navbar_bg_color'];
			if ( ! isset( $navbar_bg_color['enable'] ) ) {
				$navbar_bg_color['enable'] = 0;
			}
			$navbar_border = $woo_a3_gallery_settings[WOO_DYNAMIC_GALLERY_PREFIX.'navbar_border'];
			if ( ! isset( $navbar_border['corner'] ) ) {
				$navbar_border['corner'] = 'square';
			}
			$navbar_shadow = $woo_a3_gallery_settings[WOO_DYNAMIC_GALLERY_PREFIX.'navbar_shadow'];
			if ( ! isset( $navbar_shadow['enable'] ) ) {
				$navbar_shadow['enable'] = 0;
			}
			$navbar_margin_top     = $woo_a3_gallery_settings[WOO_DYNAMIC_GALLERY_PREFIX.'navbar_margin_top'];
			$navbar_margin_bottom  = $woo_a3_gallery_settings[WOO_DYNAMIC_GALLERY_PREFIX.'navbar_margin_bottom'];
			$navbar_margin_left    = $woo_a3_gallery_settings[WOO_DYNAMIC_GALLERY_PREFIX.'navbar_margin_left'];
			$navbar_margin_right   = $woo_a3_gallery_settings[WOO_DYNAMIC_GALLERY_PREFIX.'navbar_margin_right'];
			$navbar_padding_top    = $woo_a3_gallery_settings[WOO_DYNAMIC_GALLERY_PREFIX.'navbar_padding_top'];
			$navbar_padding_bottom = $woo_a3_gallery_settings[WOO_DYNAMIC_GALLERY_PREFIX.'navbar_padding_bottom'];
			$navbar_padding_left   = $woo_a3_gallery_settings[WOO_DYNAMIC_GALLERY_PREFIX.'navbar_padding_left'];
			$navbar_padding_right  = $woo_a3_gallery_settings[WOO_DYNAMIC_GALLERY_PREFIX.'navbar_padding_right'];

			$navbar_separator      = $woo_a3_gallery_settings[WOO_DYNAMIC_GALLERY_PREFIX.'navbar_separator'];

			$caption_bg_color = $woo_a3_gallery_settings[WOO_DYNAMIC_GALLERY_PREFIX.'caption_bg_color'];
			if ( ! isset( $caption_bg_color['enable'] ) ) {
				$caption_bg_color['enable'] = 0;
			}
			$caption_bg_transparent = $woo_a3_gallery_settings[WOO_DYNAMIC_GALLERY_PREFIX.'caption_bg_transparent'];

			$transition_scroll_bar = $woo_a3_gallery_settings[WOO_DYNAMIC_GALLERY_PREFIX.'transition_scroll_bar'];

			if ( isset( $woo_a3_gallery_settings[WOO_DYNAMIC_GALLERY_PREFIX.'lazy_load_scroll'] ) ) {
				$lazy_load_scroll = $woo_a3_gallery_settings[WOO_DYNAMIC_GALLERY_PREFIX.'lazy_load_scroll'];
			} else {
				$lazy_load_scroll = 'no';
			}

			$display_ctrl = '';
			if ( 'no' == $product_gallery_nav ) {
				$display_ctrl = 'display:none !important;';
			}

			$popup_gallery = get_option( WOO_DYNAMIC_GALLERY_PREFIX.'popup_gallery' );
			$zoom_label = __('ZOOM +', 'woo_dgallery');
			if ($popup_gallery == 'deactivate') {
				$lightbox_class = '';
				$zoom_label = '';
			}

			if ( isset( $woo_a3_gallery_settings[WOO_DYNAMIC_GALLERY_PREFIX.'enable_gallery_thumb'] ) ) {
				$enable_gallery_thumb = $woo_a3_gallery_settings[WOO_DYNAMIC_GALLERY_PREFIX.'enable_gallery_thumb'];
			} else {
				$enable_gallery_thumb = 'no';
			}

			$thumb_border_color = $woo_a3_gallery_settings[WOO_DYNAMIC_GALLERY_PREFIX.'thumb_border_color'];
			$thumb_current_border_color = $woo_a3_gallery_settings[WOO_DYNAMIC_GALLERY_PREFIX.'thumb_current_border_color'];

            echo '<style>
				#TB_window{width:auto !important;}
            	.product_gallery .a3-dgallery {
            		margin-left: auto;
            		margin-right: auto;
            	}
				.a3-dgallery .a3dg-image-wrapper {
					'.$wc_dgallery_admin_interface->generate_background_color_css( $main_bg_color ).'
					'.$wc_dgallery_admin_interface->generate_border_css( $main_border ).'
					'.$wc_dgallery_admin_interface->generate_shadow_css( $main_shadow ).'
					margin: '.$main_margin_top.'px '.$main_margin_right.'px '.$main_margin_bottom.'px '.$main_margin_left.'px !important;
					padding: '.$main_padding_top.'px '.$main_padding_right.'px '.$main_padding_bottom.'px '.$main_padding_left.'px !important;
                }
				.a3-dgallery .a3dg-image-wrapper .a3dg-image{
					margin-top:'.$main_padding_top.'px !important;
				}
                .a3-dgallery .a3dg-thumbs li{
                    margin-right: '.$g_thumb_spacing.'px !important;
                }';

            if ( 'static' == $thumb_show_type ) {
            	echo '.a3-dgallery .a3dg-thumbs li{
                    margin-bottom: '.$g_thumb_spacing.'px !important;
                }';
            }

            echo '
				/* Caption Text */
				.a3-dgallery .a3dg-image-wrapper .a3dg-image-description {
					'.$wc_dgallery_fonts_face->generate_font_css( $caption_font ).'
					'.$wc_dgallery_admin_interface->generate_background_color_css( $caption_bg_color, $caption_bg_transparent ).'
				}';

				if ( 'yes' == $product_gallery_nav ) {
					echo '.a3dg-image-wrapper .slide-ctrl {
						display: none !important;
					}';
				}

				if ( 'no' == $lazy_load_scroll ) {
					echo '.a3-dgallery .lazy-load {
						display: none !important;
					}';
				}

				echo '
				/* Navbar Separator */
				.product_gallery .a3dg-navbar-separator {
				    '.str_replace( 'border', 'border-left', $wc_dgallery_admin_interface->generate_border_style_css( $navbar_separator ) ).'
				    margin-left: -'. ( (int)$navbar_separator['width'] / 2 ).'px;
				}

				/* Navbar Control */
				.product_gallery .a3dg-navbar-control {
					'.$display_ctrl.';
				    '.$wc_dgallery_fonts_face->generate_font_css( $navbar_font ).'
				    '.$wc_dgallery_admin_interface->generate_background_color_css( $navbar_bg_color ).'
				    '.$wc_dgallery_admin_interface->generate_border_css( $navbar_border ).'
				    '.$wc_dgallery_admin_interface->generate_shadow_css( $navbar_shadow ).'
				    margin: '.$navbar_margin_top.'px '.$navbar_margin_right.'px '.$navbar_margin_bottom.'px '.$navbar_margin_left.'px !important;
				    width: calc( 100% - '.( $navbar_margin_left + $navbar_margin_right ).'px );
				}
				.product_gallery .a3dg-navbar-control .slide-ctrl,
				.product_gallery .a3dg-navbar-control .icon_zoom {
				    padding: '.$navbar_padding_top.'px '.$navbar_padding_right.'px '.$navbar_padding_bottom.'px '.$navbar_padding_left.'px !important;
				}

				/* Lazy Load Scroll */
				.a3-dgallery .lazy-load {
				    background-color: '.$transition_scroll_bar.' !important;
				}

				.product_gallery .a3-dgallery .a3dg-thumbs li a {
					border:1px solid '.$thumb_border_color.' !important;
				}
				.a3-dgallery .a3dg-thumbs li a.a3dg-active {
					border: 1px solid '.$thumb_current_border_color.' !important;
				}';

			if ( 'deactivate' == $popup_gallery ) {
					echo '.a3-dgallery .a3dg-image-wrapper .a3dg-image img {
						cursor: default;
					}
					.a3-dgallery .a3dg-navbar-control {
						width: calc( 50% - '.( ( $navbar_margin_left + $navbar_margin_right ) / 2 ).'px ) !important;
						float: right;
					}
					.a3-dgallery .a3dg-navbar-control .slide-ctrl {
						width: 100%;
					}
					.a3-dgallery .a3dg-navbar-separator,
					.a3-dgallery .icon_zoom {
						display: none;
					}';
				}

			if ( 'no' == $enable_gallery_thumb ) {
				echo '.a3dg-nav {
					display:none;
					height:1px;
				}
				.woocommerce .images {
					margin-bottom: 15px;
				}';
			}

			echo '
			</style>';

			echo '<script type="text/javascript">
                jQuery(function() {
                    var settings_defaults_'.$post->ID.' = { loader_image: "'.WOO_DYNAMIC_GALLERY_JS_URL.'/mygallery/loader.gif",
                        start_at_index: 0,
                        gallery_ID: "'.$post->ID.'",
						lightbox_class: "'.$lightbox_class.'",
                        description_wrapper: false,
                        thumb_opacity: 0.5,
                        animate_first_image: false,
                        animation_speed: '.$g_animation_speed.'000,
                        width: false,
                        height: false,
                        display_next_and_prev: true,
                        display_back_and_forward: '.$display_back_and_forward.',
                        scroll_jump: 0,
                        slideshow: {
                            enable: true,
                            autostart: '.$g_auto.',
                            speed: '.$g_speed.'000,
                            start_label: "'.__('START SLIDESHOW', 'woo_dgallery').'",
                            stop_label: "'.__('STOP SLIDESHOW', 'woo_dgallery').'",
							zoom_label: "'.$zoom_label.'",
                            stop_on_scroll: true,
                            countdown_prefix: "(",
                            countdown_sufix: ")",
                            onStart: false,
                            onStop: false
                        },
                        effect: "'.$g_effect.'", 
                        enable_keyboard_move: true,
                        cycle: true,
                        callbacks: {
                        init: false,
                        afterImageVisible: false,
                        beforeImageVisible: false
                    }
                };
                jQuery("#gallery_'.$post->ID.'").adGallery(settings_defaults_'.$post->ID.');
            });
            </script>';
            echo '<div id="gallery_'.$post->ID.'"
            class="a3-dgallery"
            data-height_type="'. esc_attr( $gallery_height_type ).'"
			data-show_navbar_control="'. esc_attr( $product_gallery_nav ) .'"
			data-show_thumb="'. esc_attr( $enable_gallery_thumb ) .'"
			data-hide_one_thumb="yes"
			data-thumb_show_type="'. esc_attr( $thumb_show_type ) .'"
			data-thumb_visible="'. esc_attr( $thumb_columns ) .'"
			data-thumb_spacing="'. esc_attr( $g_thumb_spacing ) .'"
            style="width: 100%;
            max-width: '.$g_width.';"
            >
                <div class="a3dg-image-wrapper" style="width: calc(100% - '.( (int) $main_margin_left + (int) $main_margin_right ).'px);' . ( ( $g_height != false ) ? 'height: '.$g_height.'px;' : '' ) . '"></div>
                <div class="lazy-load"></div>
                <div style="clear: both"></div>
                <div class="a3dg-navbar-control"><div class="a3dg-navbar-separator"></div></div>
                <div style="clear: both"></div>
                  <div class="a3dg-nav">
                    <div class="a3dg-thumbs '.$thumbs_list_class.'">
                      <ul class="a3dg-thumb-list">';
						
						$url_demo_img =  '/assets/js/mygallery/images/';
                        $imgs = array($url_demo_img.'image_1.jpg',$url_demo_img.'image_2.jpg',$url_demo_img.'image_3.jpg',$url_demo_img.'image_4.jpg');
                        
                        $script_colorbox = '';
						$script_fancybox = '';
                        if ( !empty( $imgs ) ){	
                            $i = 0;
                            $display = '';
			
                            if(is_array($imgs) && count($imgs)>0){
                                $script_colorbox .= '<script type="text/javascript">';
								$script_fancybox .= '<script type="text/javascript">';
                                $script_colorbox .= '(function($){';		  
								$script_fancybox .= '(function($){';
                                $script_colorbox .= '$(function(){';
								$script_fancybox .= '$(function(){';
                                $script_colorbox .= '$(document).on("click", ".a3-dgallery .lightbox", function(ev) { if( $(this).attr("rel") == "gallery_'.$post->ID.'") {
								var idx = $(".a3dg-image img").attr("idx");';
								$script_fancybox .= '$(document).on("click", ".a3-dgallery .lightbox", function(ev) { if( $(this).attr("rel") == "gallery_'.$post->ID.'") {
								var idx = $(".a3dg-image img").attr("idx");';
								
                                if(count($imgs) <= 1 ){
                                    $script_colorbox .= '$(".gallery_product_'.$post->ID.'").colorbox({open:true, maxWidth:"100%" });';
									$script_fancybox .= '$.fancybox(';
                                }else{
                                    $script_colorbox .= '$(".gallery_product_'.$post->ID.'").colorbox({rel:"gallery_product_'.$post->ID.'", maxWidth:"100%" }); $(".gallery_product_'.$post->ID.'_"+idx).colorbox({open:true, maxWidth:"100%" });';
									$script_fancybox .= '$.fancybox([';
                                }
								
                                $common = '';
                                $idx = 0;
                                foreach($imgs as $item_thumb){
                                    $li_class = '';
                                    if ( 'static' == $thumb_show_type ) {
										if ( $idx % $thumb_columns == 0 ) {
											$li_class    = 'first_item';
										} elseif ( ( $idx % $thumb_columns + 1 ) == $thumb_columns ) {
											$li_class    = 'last_item';
										}
									} else {
										if ( $idx == 0) {
											$li_class = 'first_item';
										} elseif ( $idx == count( $imgs ) - 1 ) {
											$li_class = 'last_item';
										}
									}
                                    $image_attribute = getimagesize( WOO_DYNAMIC_GALLERY_DIR.$item_thumb);
                                    $image_lager_default_url = WOO_DYNAMIC_GALLERY_URL.$item_thumb;
									
									
                                    $thumb_height = $g_thumb_height;
                                    $thumb_width = $g_thumb_width;
                                    $width_old = $image_attribute[0];
                                    $height_old = $image_attribute[1];
                                     if($width_old > $g_thumb_width || $height_old > $g_thumb_height){
                                        if($height_old > $g_thumb_height && $g_thumb_height > 0) {
                                            $factor = ($height_old / $g_thumb_height);
                                            $thumb_height = $g_thumb_height;
                                            $thumb_width = $width_old / $factor;
                                        }
                                        if($thumb_width > $g_thumb_width && $g_thumb_width > 0){
                                            $factor = ($width_old / $g_thumb_width);
                                            $thumb_height = $height_old / $factor;
                                            $thumb_width = $g_thumb_width;
                                        }elseif($thumb_width == $g_thumb_width && $width_old > $g_thumb_width  && $g_thumb_width > 0){
                                            $factor = ($width_old / $g_thumb_width);
                                            $thumb_height = $height_old / $factor;
                                            $thumb_width = $g_thumb_width;
                                        }						
                                    }else{
                                         $thumb_height = $height_old;
                                        $thumb_width = $width_old;
                                    }
                                    
                                    
                                        
                                    $img_description = 'Lorem Ipsum is simply dummy text of the printing and typesetting industry.';
                                            
                                    echo '<li class="'.$li_class.'">';
                                    echo '<a class="gallery_product_'.$post->ID.' gallery_product_'.$post->ID.'_'.$idx.'" title="'. esc_attr( $img_description ) .'" rel="gallery_product_'.$post->ID.'" href="'.$image_lager_default_url.'">';
                                    echo '<img
									org-sizes=""
									org-srcset=""
									sizes=""
									srcset=""
                                    idx="'.$idx.'"
                                    src="'.$image_lager_default_url.'"
                                    alt="'. esc_attr( $img_description ) .'"
                                    class="image'.$i.'"
                                    width="'.$thumb_width.'"
                                    height="'.$thumb_height.'">';
                                    echo '</a>';
									echo '</li>';
                                    $img_description = esc_js( $img_description );
                                    if($img_description != ''){
										$script_fancybox .= $common.'{href:"'.$image_lager_default_url.'",title:"'.$img_description.'"}';
                                    }else{
										$script_fancybox .= $common.'{href:"'.$image_lager_default_url.'",title:""}';
                                    }
                                    $common = ',';
                                    $i++;
									$idx++;
                                 }
								
								 //$.fancybox([ {href : 'img1.jpg', title : 'Title'}, {href : 'img2.jpg', title : 'Title'} ])
                                if(count($imgs) <= 1 ){
									$script_fancybox .= ');';
                                }else{
									$script_fancybox .= '],{
        \'index\': idx
      });';
                                }
                                $script_colorbox .= 'ev.preventDefault();';
                                $script_colorbox .= '} });';
								$script_fancybox .= '} });';
                                $script_colorbox .= '});';
								$script_fancybox .= '});';
                                $script_colorbox .= '})(jQuery);';
								$script_fancybox .= '})(jQuery);';
                                $script_colorbox .= '</script>';
								$script_fancybox .= '</script>';
                            }
                        } else {
                        	$no_image_uri = WC_Dynamic_Gallery_Functions::get_no_image_uri();
                            echo '<li> <a class="lightbox" rel="gallery_product_'.$post->ID.'" href="'.$no_image_uri.'"> <img src="'.$no_image_uri.'" class="image" alt=""> </a> </li>';
                        }

						if ($popup_gallery == 'deactivate') {
							$script_colorbox = '';
							$script_fancybox = '';
						} else if($popup_gallery == 'colorbox'){
                        	echo $script_colorbox;
						} else {
							echo $script_fancybox;
						}
                        echo '</ul>
                        </div>
                      </div>
                    </div>';
                  ?>
          </div>
        </div>
	<?php
	die();
	}
}
?>
