<?php global $spg_cart; ?>
<?php $spg_cart->calculate_totals();
?>
<div id="order-page-header" class="titleclass">
    <div class="container">
        <h2 style="float:left;margin-top:0">Hóa đơn</h2>
        <input type="text" readonly id="total-amount" value="<?php echo $spg_cart->get_total(); ?>">
    </div><!--container-->
</div><!--titleclass-->

<div id="content" class="container">
    <div class="row">
        <div class="main col-sm-12 col-md-12" role="main">
            <div class="entry-content" id="order-page" itemprop="mainContentOfPage">
                <div class="order-form-wrap">
                    <form id="order-form" method="post" action="" class="form-horizontal">
                        <section id="product-master-info" class="col-xs-12 col-sm-6">
                            <fieldset id="product-barcode-scanner" title="product-barcode-scanner">
                                <legend>Mã vạch</legend>
                                <div class="form-group">
                                    <div class="col-xs-12 col-sm-7">
                                        <input id="barcode" class="form-control barcode" type="text"
                                               placeholder="Barcode">
                                    </div>

                                    <div class="col-xs-12 col-sm-3">
                                        <input id="quantity" class="form-control" type="text" value="1"
                                               placeholder="Số lượng">
                                    </div>
                                    <div class="col-xs-12 col-sm-2">
                                        <button id="add-more-product" type="button" class="button">Thêm</button>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="col-xs-12 col-sm-12">
                                        <label class="form-label" for="print-order">In hóa đơn</label>
                                        <input id="print-order" name="order[print_order]" checked type="checkbox"
                                               value="1">
                                    </div>

                                </div>
                                <div class="form-group col-sm-12 col-md-12">
                                    <span class="no-product" style="display: none">Sản phẩm không tồn tại</span>
                                </div>
                            </fieldset>
                            <fieldset id="product-list-show">
                                <legend>Danh sách sản phẩm</legend>
                                <table class="table product-list-table">
                                    <thead>
                                    <tr>
                                        <th>STT</th>
                                        <th>Tên sản phẩm</th>
                                        <th>Số lượng</th>
                                        <th>Giá</th>
                                        <th>Thành tiền</th>
                                        <th>Xóa</th>
                                    </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </fieldset>
                        </section>
                        <section id="customer-master-info" class="col-xs-12 col-sm-6">
                            <fieldset id="customer-info" title="customer-info">
                                <legend>Thông tin khách hàng</legend>
                                <div class="form-group">
                                    <div class="col-xs-12 col-sm-4 customer-name-group">
                                        <label class="form-label">Tên khách hàng (*)</label>
                                        <input class="form-control" name="order[customer][name]" required type="text"
                                               placeholder="Nhập tên khách hàng">
                                    </div>
                                    <div class="col-xs-12 col-sm-4 customer-phone-group">
                                        <label class="form-label">Số Điện thoại</label>
                                        <input class="form-control" id="phone" name="order[customer][phone]" type="text"
                                               placeholder="Nhập số Điện thoại">
                                    </div>
                                    <div class="col-xs-12 col-sm-4 customer-email-group">
                                        <label class="form-label">Địa chỉ email</label>
                                        <input class="form-control" name="order[customer][email]" type="email"
                                               placeholder="Nhập địa chỉ">
                                    </div>
                                    <div class="col-xs-12 col-sm-12 customer-address-group">
                                        <label class="form-label">Địa chỉ </label>
                                        <input class="form-control" name="order[customer][address]" type="text"
                                               placeholder="Nhập địa chỉ">
                                    </div>
                                    <div class="col-xs-12 col-sm-12 customer-shipping-address-group">
                                        <label class="form-label">Địa chỉ giao hàng</label>
                                        <input class="form-control" name="order[customer][shipping_address]" type="text"
                                               value="Khách lấy hàng tại shop" placeholder="Nhập địa chỉ giao hàng">
                                    </div>


                                    <div class="col-xs-6 col-sm-6 customer-shipping-fee-group">
                                        <label class="form-label">Phí giao giao hàng</label>
                                        <?php if (WC()->cart->needs_shipping() && WC()->cart->show_shipping()) : ?>
                                            <?php wc_cart_totals_shipping_html(); ?>
                                        <?php endif; ?>
                                    </div>

                                    <div class="col-xs-6 col-sm-6 customer-payment-status-group">
                                        <label class="form-label">Trạng thái order</label>
                                        <label for="payment-comleted">Đã thanh toán
                                            <input checked id="payment-comleted" type="radio" name="order[status]"
                                                   value="completed">
                                        </label>
                                        <label for="payment-pending">Chờ thanh toán
                                            <input id="payment-pending" type="radio" name="order[status]"
                                                   value="pending">
                                        </label>

                                    </div>

                                    <div class="col-xs-12 col-sm-12 customer-save-order-group">
                                        <button class="button primary-button" type="submit">Lưu</button>
                                    </div>
                                </div>
                            </fieldset>
                        </section>
                    </form>
                </div>
            </div>
        </div><!-- /.main -->
        <div id="printerDiv" style="display: none"></div>

        <script type="text/javascript">

            var order_pdf_link = '<?php if (!empty($_SESSION['order_pdf_link'])) {
                $pdf_link = $_SESSION['order_pdf_link'];
                unset($_SESSION['order_pdf_link']);
                echo $pdf_link;
            } else {
                echo '';
            }?>';
            //var order_pdf_link = 'http://shopforgirl.local/wp-content/uploads/order_tmp/8c4a3b.pdf';

            jQuery(document).ready(function () {
                var nonce = '<?php echo wp_create_nonce("update-shipping-method") ?>';
                var cart_data = '<?php echo json_encode(array(
                    'cart_items' => $spg_cart->parse_products_data(),
                    'cart_total' => $spg_cart->get_total()
                )); ?>';
                var shipping_block = true;
                var OrderHandler = function () {

                    this.initCartFontEndData = function (data) {
                        var self = this;
                        self.renderCart(jQuery('#product-list-show table tbody'), data)
                    };


                    this.initDomListener = function () {
                        var self = this;

                        // prevent form default submit
                        jQuery(window).keydown(function (event) {
                            if (event.keyCode == 13) {
                                event.preventDefault();
                                return false;
                            }
                        });

                        // remove the product
                        jQuery('body').on('click', '.fa-remove', function () {
                            var barcode = jQuery(this).closest('tr').data('barcode');

                            self.searchProductAndRemoveFromCart(barcode).done(function (rep) {
                                if (rep.result) {
                                    var data = rep.data;
                                    data['quantity'] = quantity;
                                    self.renderCart(jQuery('#product-list-show table tbody'), data);
                                }
                            });
                            // change shipping method
                        }).on('click', '.shipping_method', function (evt) {
                            var shipping_methods = {};
                            var target = evt.currentTarget;
                            jQuery('select.shipping_method, input[name^=shipping_method][type=radio]:checked, input[name^=shipping_method][type=hidden]').each(function () {
                                shipping_methods[jQuery(target).data('index')] = jQuery(target).val();
                            });

                            self.addShipingMethod(shipping_methods).done(function (rep) {
                                if (rep.result) {
                                    jQuery('#total-amount').val(rep.data);

                                }
                            });
                        });
                        // quantity keydown listener
                        jQuery("#quantity").keydown(function (e) {
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

                        // add more product click listener
                        jQuery('#add-more-product').click(function (e) {
                            e.preventDefault();
                            var barcode = jQuery('#barcode').val();
                            var quantity = jQuery('#quantity').val();
                            var searchProductAndAddToCart = self.searchProductAndAddToCart(barcode, quantity);

                            searchProductAndAddToCart.done(function (rep) {
                                if (rep.result) {
                                    var data = rep.data;
                                    data['quantity'] = quantity;
                                    self.renderCart(jQuery('#product-list-show table tbody'), data);
                                    // clear barcode
                                    jQuery('#barcode').val('');
                                } else {
                                    // not found
                                    jQuery('.no-product').css('display', 'block');
                                    setTimeout(function () {
                                        jQuery('.no-product').fadeOut()
                                    }, 3000);

                                }
                            });

                        });

                        // Phone on enter
                        jQuery('#phone').keyup(function (e) {
                            e.preventDefault();
                            if (e.keyCode == 13) {
                                var phone = jQuery('#phone').val();
                                var customer = self.searchCustomer(phone);
                                customer.done(function (rep) {
                                    if (rep.result) {
                                        var data = rep.data;
                                        jQuery('[name="order[customer][name]"]').val(data.name);
                                        jQuery('[name="order[customer][email]"]').val(data.email);
                                        jQuery('[name="order[customer][address]"]').val(data.address);
                                    }
                                });
                            }
                        });

                        // barcode enter
                        jQuery('#barcode').keyup(function (e) {
                            e.preventDefault();
                            if (e.keyCode == 13) {
                                jQuery('#add-more-product').trigger('click');
                            }
                        });

                        // call print action if pdf link exist
                        if (order_pdf_link) {

                            this.callToPrint(order_pdf_link);
                        }


                    };

                    /**
                     *  Search product via barcode
                     */
                    this.searchProduct = function searchProduct(barcode) {
                        return jQuery.ajax({
                            method: 'POST',
                            dataType: 'json',
                            url: window.location.origin + '/wp-admin/admin-ajax.php',
                            data: {action: 'get_product_info', barcode: barcode}
                        });
                    };

                    this.searchProductAndAddToCart = function searchProduct(barcode, quantity) {
                        return jQuery.ajax({
                            method: 'POST',
                            dataType: 'json',
                            url: window.location.origin + '/wp-admin/admin-ajax.php',
                            data: {action: 'ajax_add_product_to_cart', barcode: barcode, quantity: quantity}
                        });
                    };


                    this.searchProductAndRemoveFromCart = function (barcode) {
                        return jQuery.ajax({
                            method: 'POST',
                            dataType: 'json',
                            url: window.location.origin + '/wp-admin/admin-ajax.php',
                            data: {action: 'ajax_remove_product_to_cart', barcode: barcode}
                        });
                    };

                    this.addShipingMethod = function (shipping_method) {
                        return jQuery.ajax({
                            method: 'POST',
                            dataType: 'json',
                            url: window.location.origin + '/wp-admin/admin-ajax.php',
                            data: {action: 'ajax_add_shipping_method', shipping_method: shipping_method}
                        });
                    };

                    /**
                     * Search customer via phone
                     */
                    this.searchCustomer = function (phone) {
                        return jQuery.ajax({
                            method: 'POST',
                            dataType: 'json',
                            url: window.location.origin + '/wp-admin/admin-ajax.php',
                            data: {action: 'get_customer_info', phone: phone}
                        });
                    };

                    /**
                     * Render the cart  in front end
                     */
                    this.renderCart = function (elem, data) {
                        var self = this;
                        // remove old data
                        elem.find('tr').remove();
                        if (!shipping_block) {
                            jQuery('#shipping_method, .shipping').remove();
                        }
                        jQuery.each(data.cart_items, function (k, arg) {
                            if (typeof arg == 'object') {
                                var name = arg.name;
                                var barcode = arg.barcode.trim();
                                var quantity = parseInt(arg.quantity);
                                var price = parseInt(arg.regular_price);
                                var row_pattern = '<tr class="product-item" data-barcode="{barcode}">' +
                                    '<td><span class="product-item-num">{product_num}</span></td>' +
                                    '<td><input name="order[product][{barcode}]name" readonly class="product-name"  value="{product_name}"></td>' +
                                    '<td><input name="order[product][{barcode}]quantity"  readonly class="product-quantity" value="{product_quantity}"></td>' +
                                    '<td><input class="product-price" type="text" readonly value="{price}"></td>' +
                                    '<td><input class="product-amount" type="text" readonly value="{amount}"></td>' +
                                    '<td><span><i class="fa fa-remove"></i></span></td>' +
                                    '</tr>';

                                // add new
                                var html = row_pattern.replace('{product_num}', k + 1)
                                    .replace(/{barcode}/g, barcode)
                                    .replace('{product_name}', name)
                                    .replace('{product_quantity}', quantity)
                                    .replace('{price}', price)
                                    .replace('{amount}', price * quantity);
                                jQuery(elem).append(html);
                            }
                        });


                        if (!jQuery('select.shipping_method, input[name^=shipping_method][type=radio]:checked, input[name^=shipping_method][type=hidden]').length) {
                            jQuery('.customer-shipping-fee-group').append(data['shipping_block'])
                        }

                        // change total in front end
                        jQuery('#total-amount').val(data.cart_total);

                        // set shipping is false mark for shipping block is rendered
                        shipping_block = false;
                    };

                    this.callToPrint = function (order_pdf_link) {
                        var div = document.getElementById("printerDiv");
                        div.innerHTML = '<iframe style="margin: 0; padding:0; display: block" id="printer-iframe" src=' + order_pdf_link + ' onload="this.contentWindow.print();"></iframe>';
                    }

                };

                var orderHandler = new OrderHandler();
                orderHandler.initDomListener();
                orderHandler.initCartFontEndData(JSON.parse(cart_data));
            });

        </script>