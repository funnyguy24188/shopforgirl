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
        if (!is_admin()) {
            add_filter('wp_get_nav_menu_items', array($this, 'wp_get_nav_menu_items'), 10, 3);
        }
    }

    public function hide_menu()
    {
        $current_user = wp_get_current_user();


        if (!in_array('administrator', $current_user->roles)) {
            foreach ($this->menu_hides as $menu)
                remove_menu_page($menu);
        }

    }

    /**
     * Modify top menu for role staff and administrator only
     * @param $items
     * @param $menu
     * @param $args
     */
    public function wp_get_nav_menu_items($items, $menu, $args)
    {
        $ret = array();
        $current_user = wp_get_current_user();
        if ($menu->slug == 'staff-menu') {
            $roles = $current_user->roles;
            if (in_array('administrator', $roles) || in_array('staff', $roles) || in_array('shop_manager', $roles)) {
                $ret = $items;
            }
        } else {
            $ret = $items;
        }
        return $ret;

    }

}