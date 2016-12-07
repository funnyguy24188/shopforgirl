<?php
function spg_register_sidebar()
{
    register_sidebar(array('name' => 'Fashion New',
        'id' => 'fashion-new',
        'before_widget' => '<section id="%1$s" class="widget %2$s"><div class="widget-inner">',
        'after_widget' => '</div></section>',
        'before_title' => '<h3>',
        'after_title' => '</h3>',
    ));
}

add_action('widgets_init', 'spg_register_sidebar');