(function ($) {

    $(document).ready(function () {
        $('.barcode-sm-print').click(function () {
            var product_id = jQuery(this).closest('div.product-barcode-metabox').find('input[name="product_id"]').val();
            var number = jQuery(this).closest('div.product-barcode-metabox').find('input[name="number_barcode"]').val();
            jQuery.ajax({
                type: 'POST',
                url: window.location.origin + '/wp-admin/admin-ajax.php',
                data: {action: 'ajax_add_queue_print_barcode', product_id: product_id, product_number: number},
                success: function (rep) {
                    rep = JSON.parse(rep);
                    if(rep) {
                        var message = rep.data;
                        $('.spg-barcode-message p').remove();
                        $('.spg-barcode-message').append('<p>' + message + '</p>');
                    }
                }
            });
        });

        $('#print-barcode-queue-btn').click(function(){
            $('#iframe-print-queue')[0].contentWindow.print();
        });

        jQuery(".barcode-sm-number").keydown(function (e) {
            // Allow: backspace, delete, tab, escape, enter and .
            if (jQuery.inArray(e.keyCode, [46, 8, 9, 27, 13, 110]) !== -1 ||
                // Allow: Ctrl+A
                (e.keyCode == 65 && e.ctrlKey === true) ||
                // Allow: Ctrl+C
                (e.keyCode == 67 && e.ctrlKey === true) ||
                // Allow: Ctrl+X
                (e.keyCode == 88 && e.ctrlKey === true) ||
                // Allow: home, end, left, right
                (e.keyCode >= 35 && e.keyCode <= 39)) {
                // let it happen, don't do anything
                return;
            }
            // Ensure that it is a number and stop the keypress
            if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105)) {
                e.preventDefault();
            }
        });


    });

})(jQuery);
