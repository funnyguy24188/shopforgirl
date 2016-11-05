jQuery(document).ready(function () {
    // sub-directory option
    spg_branch = 'mk';
    ajax_url = window.location.origin + '/' + spg_branch + '/' + '/wp-admin/admin-ajax.php';

    // move commend facebook to after sidebar
    if (jQuery('.single-post').length) {
        if (jQuery('.fb-comments').length) {

            var fb_comment = jQuery('.fb-comments');
            jQuery('.fb-comments').remove();
            jQuery('#ktsidebar').closest('#content').append(fb_comment);
        }
    }
});
