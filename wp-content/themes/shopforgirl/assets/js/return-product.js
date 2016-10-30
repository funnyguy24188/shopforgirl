jQuery(document).ready(function () {

    function autoCloseMessage(selector, delay) {
        jQuery(selector).removeAttr('style');
        window.setTimeout(function () {
            jQuery(selector).stop().fadeTo(500, 0).slideUp(500).find('strong').remove();
        }, delay);
    }

    var ReturnOrder = function () {
        var tableDetail = jQuery('#order-detail-table');
        var dangerClass = 'alert-danger';
        var successClass = 'alert-success';

        this.initDomListener = function () {
            var self = this;

            jQuery('body').on('click', '#load-order', function () {
                var orderID = jQuery('#order-id').val().trim();
                // load the order when click to load order button
                var ret = self.loadOrder(orderID);
                ret.done(function (rep) {
                    if (rep.result) {
                        self.renderOrderDetail(rep.data);
                    } else {
                        var classes = dangerClass;
                        var message = '<strong>' + rep.message + '</strong>';
                        jQuery('#alert-message').find('strong').remove();
                        jQuery('#alert-message')
                            .addClass(classes)
                            .append(message);
                        autoCloseMessage('#alert-message', 5000);
                    }
                });
            }).on('click', '.return_chk', function () {
                jQuery(this).closest('tr').find('.return-quantity').prop("disabled", !jQuery(this).prop('checked'));
            }).on('click', '#save-return-order', function () {
                var orderID = jQuery('#order-id').val().trim();
                if (self.validateForm()) {
                    var ret = self.saveReturnOrder(orderID);
                    var classes = '';
                    ret.done(function (rep) {
                        jQuery('#alert-message').removeClass(successClass);
                        jQuery('#alert-message').removeClass(dangerClass);
                        classes = successClass;
                        if (rep.result) {
                            // remove check box and disable content change number for avoid error
                            jQuery.each(rep.data, function (k, item) {
                                var chk = jQuery('[data-item-id="' + item.item_id + '"]');

                                // reset the number
                                var quantity = jQuery('[name="return_quantity[' + item.item_id + ']"]');
                                var tdQuantity = jQuery(quantity).closest('tr').find('.td-quantity');
                                if (typeof quantity != 'undefined') {
                                    jQuery(quantity).prop('disabled', true);
                                    var tdCurrentQuantity = parseInt(jQuery(tdQuantity).text());
                                    tdCurrentQuantity -= jQuery(quantity).val();
                                    jQuery(quantity).val(0);
                                    // set current quantity
                                    jQuery(tdQuantity).text(tdCurrentQuantity);

                                    // remove the check
                                    if (typeof chk != 'undefined' && tdCurrentQuantity == 0) {
                                        jQuery(chk).remove();

                                    } else {
                                        // uncheck
                                        jQuery(chk).prop('checked', false);
                                    }

                                }

                            });

                        } else {
                            classes = dangerClass;
                        }
                        var message = '<strong>' + rep.message + '</strong>';
                        jQuery('#alert-message').find('strong').remove();
                        jQuery('#alert-message')
                            .addClass(classes)
                            .append(message);
                        autoCloseMessage('#alert-message', 5000);


                    })
                } else {
                    classes = dangerClass;
                    var message = '<strong>Có lỗi nhập liệu vui lòng kiểm tra lại</strong>';
                    jQuery('#alert-message').find('strong').remove();
                    jQuery('#alert-message')
                        .addClass(classes)
                        .append(message);
                    autoCloseMessage('#alert-message', 5000);
                }
            }).on('submit', '#return-product', function (e) {
                e.preventDefault();
            });

        };
        /**
         * Load order from the backend to front end
         */
        this.loadOrder = function (orderID) {
            return jQuery.ajax({
                url: window.location.origin + '/wp-admin/admin-ajax.php',
                method: 'post',
                dataType: 'json',
                data: {action: 'ajax_get_order_info', order_id: orderID}
            });
        };

        /**
         * Render the order detail for return
         * @param data
         */

        this.renderOrderDetail = function (data) {
            // clear all first
            tableDetail.find('.order-detail-body tr').remove();

            // show the order general data
            for (var attr in data.order_general_info) {
                var class_attr = '.' + attr.replace(/_/g, '-');
                if (attr == 'order_status') {
                    jQuery(class_attr).removeClass(/order-wc-/g);
                    jQuery(class_attr).addClass('order-' + data.order_general_info[attr]);
                    jQuery(class_attr).text(data.order_general_info[attr + '_text']);
                } else {
                    jQuery(class_attr).text(data.order_general_info[attr]);
                }

            }
            // show product row to the table
            jQuery.each(data.product_data, function (k, item) {
                var row = '<tr>' +
                    ((item.quantity > 0) ? '<td><input type="checkbox" data-item-id="{item_id}" class="return_chk"></td>' : '<td></td>') +
                    '<td>{stt}</td>' +
                    '<td>{barcode}</td>' +
                    '<td>{product_name}</td>' +
                    '<td>{price}</td>' +
                    '<td class="td-quantity">{quantity}</td>' +
                    '<td><input type="number" class="form-control return-quantity" disabled name="return_quantity[{item_id}]" value="0"></td>' +
                    '</tr>';
                row = row.replace('{stt}', k + 1);
                row = row.replace(/{barcode}/g, item.barcode);
                row = row.replace(/{item_id}/g, item.item_id);
                row = row.replace('{product_name}', item.product_name);
                row = row.replace('{price}', item.price);
                row = row.replace('{quantity}', item.quantity);
                tableDetail.find('.order-detail-body').append(row);
            });

        };

        this.saveReturnOrder = function (orderID) {
            var order_data = [];
            jQuery('.return_chk:checked').each(function (k, item) {
                var item_id = jQuery(item).data('item-id');
                var quantity_input = jQuery('[name="return_quantity[' + item_id + ']"]').val();
                if (typeof quantity_input != 'undefined') {
                    order_data.push({item_id: item_id, quantity: quantity_input || 0})
                }

            });
            return jQuery.ajax({
                url: window.location.origin + '/wp-admin/admin-ajax.php',
                method: 'post',
                dataType: 'json',
                data: {action: 'ajax_save_return_order', order_id: orderID, return_data: order_data}
            })
        };

        /**
         * Validate the number product return
         */
        this.validateForm = function () {
            // reset the number
            var ret = true;
            jQuery('.return-quantity').each(function (k, item) {
                var tdQuantity = parseInt(jQuery(item).closest('tr').find('.td-quantity').text());
                var returnQuantity = parseInt(jQuery(item).val());
                if (jQuery(item).prop('disabled')) {
                    if ((tdQuantity < returnQuantity) || returnQuantity < 1) {
                        ret = false;
                    }
                }

            });
            return ret;
        };

    };

    var returnOrder = new ReturnOrder();
    returnOrder.initDomListener();
});