jQuery(document).ready(function () {
    jQuery('.datepicker').datepicker({
        autoclose: true
    });

    jQuery(".change-order-status").on("change", function () {
        var option = jQuery(this).find('option:selected');
        var message = option.data('message');
        var order_id = option.data('order-id');
        var order_status = option.data('order-status');
        var order_status_text = option.data('order-status-text');
        var order_info = {order_id: order_id, order_status: order_status, order_status_text: order_status_text};

        jQuery('#change-order-modal').find('.modal-body p').remove();
        jQuery('#change-order-modal').find('.modal-body').append('<p>' + message + '</p>');
        jQuery('#change-order-modal').find('.order-status-save').data('order-info', order_info);
        jQuery('#change-order-modal').modal();
    });

    jQuery('.order-status-save').click(function () {
        var order_info = jQuery(this).data('order-info');
        var orderID = order_info.order_id;
        var orderStatus = order_info.order_status;
        var orderStatusText = order_info.order_status_text;
        jQuery.ajax({
            url: ajax_url,
            method: 'post',
            dataType: 'json',
            data: {action: 'ajax_change_order_status', order_id: orderID, order_status: orderStatus},
            success: function (rep) {
                var classes = 'alert-danger';
                var message = '';
                if (rep.result) {
                    classes = 'alert-success';
                    message = rep.message;
                }

                jQuery('#alert-message strong').remove();
                jQuery('#alert-message')
                    .removeClass('alert-danger')
                    .removeClass('alert-success')
                    .addClass(classes)
                    .append('<strong>' + message + '</strong>');
                jQuery('#change-order-modal').modal('hide');

                // change status of on list
                jQuery('#order-id-' + orderID + ' .td-order-status span')[0].className = '';
                jQuery('#order-id-' + orderID + ' .td-order-status span')
                    .addClass('order-status-icon')
                    .addClass('order-' + orderStatus);
                jQuery('#order-id-' + orderID + ' .td-order-status .order-status-icon').text(orderStatusText);
            }
        })
    });

    jQuery('.print-order-detail').click(function () {
        var order_id = jQuery(this).data('order-id');


        jQuery.ajax({
            url: ajax_url,
            method: 'post',
            dataType: 'json',
            data: {action: 'ajax_print_order_detail', order_id: order_id },
            success: function (rep) {
                var classes = 'alert-danger';
                var message = '';
                if (rep.result) {
                    classes = 'alert-success';
                    message = rep.message;

                    var order_pdf_link = rep.data.link;
                    // insert the pdf file
                    var div = document.getElementById("printerDiv");
                    div.innerHTML = '<iframe style="margin: 0; padding:0; display: block" id="printer-iframe" src=' + order_pdf_link + ' onload="this.contentWindow.print();"></iframe>';
                }

            }
        })
    });

    // normallize and reset page when form is submited
    jQuery('#order-list-search-form .submit-btn').click(function () {
        var serialize_data = jQuery('#order-list-search-form').serialize();
        var base_url = window.location.origin + '/' + spg_branch;
        var path = '/order-list/';
        var url = base_url + path + '?' + serialize_data;
        jQuery('#order-list-search-form').attr('action', url).submit();
    });
});