jQuery(document).ready(function () {
    // move commend facebook to after sidebar
    if (jQuery('.single-post').length) {
        if (jQuery('.fb-comments').length) {

            var fb_comment = jQuery('.fb-comments');
            jQuery('.fb-comments').remove();
            jQuery('#ktsidebar').closest('#content').append(fb_comment);
        }
    }
});
