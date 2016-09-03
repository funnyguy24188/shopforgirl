<?php

class SPGRoleBackEnd
{
    private $menu_hides = array(
        'WP-Optimize',
        'edit.php?post_type=portfolio',
        'edit.php?post_type=staff',
        'edit.php?post_type=testimonial',
        'admin.php?page=amazon-web-services'
    );

    public function init_hook()
    {
        add_action('admin_menu', array($this, 'hide_menu'));
    }

    public function hide_menu()
    {
        $current_user = wp_get_current_user();


        if (!in_array('administrator', $current_user->roles)) {
            foreach ($this->menu_hides as $menu)
                remove_menu_page($menu);
        }

    }

}