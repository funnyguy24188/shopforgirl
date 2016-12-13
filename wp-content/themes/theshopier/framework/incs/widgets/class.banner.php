<?php 
// **********************************************************************// 
// ! Register Nexthemes Widgets
// **********************************************************************// 

class Theshopier_Banner_Widget extends Theshopier_Widget {

    public function __construct() {
        $this->widget_cssclass    = 'nth-banner';
		$this->widget_description = esc_html__( "Build a banner shortcode", 'theshopier' );
		$this->widget_id          = 'theshopier_banner';
		$this->widget_name        = esc_html__('Banner', 'theshopier' );
		
		$this->settings           = array(
			'title'  => array(
				'type'  => 'text',
				'std'	=> '',
				'label' => esc_html__( 'Title', 'theshopier' ),
			),
			'bg_image'  => array(
				'type'  => 'attach_image',
				'std'	=> '',
				'label' => esc_html__( 'Background image', 'theshopier' ),
			),
			'bn_content'  => array(
				'type'  => 'textarea',
				'std'	=> '',
				'label' => esc_html__( 'Banner content', 'theshopier' ),
			),
		);
		parent::__construct();
    }

    function widget($args, $instance) {
        extract($args);
		
		ob_start();
		$this->widget_start( $args, $instance );
		
		$attrs = '';
		if(isset( $instance['bg_image'] ) && strlen( trim( $instance['bg_image'] ) ) > 0 ) {
			$attrs .= ' bg_image="'.esc_attr( $instance['bg_image'] ).'"';
		}
		
		echo do_shortcode( "[theshopier_banner{$attrs}]{$instance['bn_content']}[/theshopier_banner]" );
		
		$this->widget_end( $args );
		echo ob_get_clean();
    }
}
