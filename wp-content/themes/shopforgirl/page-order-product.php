<div id="order-page-header" class="titleclass">
    <div class="container">
        <h2 style="float:left;margin-top:0">Hóa đơn</h2>
        <input type="text" readonly id="total-amount" value="0">
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
                                    <div class="col-xs-12 col-sm-6">
                                        <input id="barcode" class="form-control barcode" type="text"
                                               placeholder="Barcode">
                                    </div>

                                    <div class="col-xs-12 col-sm-2">
                                        <input id="quantity" class="form-control" type="text" value="1"
                                               placeholder="Số lượng">
                                    </div>
                                    <div class="col-xs-12 col-sm-2">
                                        <button id="add-more-product" type="button" class="button">Thêm</button>
                                    </div>
                                    <div class="col-xs-12 col-sm-2">
                                        <button class="button primary-button" type="submit">Lưu</button>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="col-xs-12 col-sm-12">
                                        <label class="form-label" for="print-order">In hóa đơn</label>
                                        <input id="print-order" checked type="checkbox" value="1">
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
                                        <input class="form-control" name="order[customer][shiping_address]" type="text"
                                              value="Khách lấy hàng tại shop"  placeholder="Nhập địa chỉ giao hàng">
                                    </div>
                                    <div class="col-xs-12 col-sm-12 customer-shipping-fee-group">
                                        <label class="form-label">Phí giao giao hàng</label>
                                        
                                    </div>
                                </div>
                            </fieldset>
                        </section>
                    </form>
                </div>
            </div>
        </div><!-- /.main -->

        <script type="text/javascript">
            jQuery(document).ready(function () {

                var OrderHandler = function () {
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
                            self.removeProduct(jQuery(this));
                        });

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


                        jQuery('#add-more-product').click(function (e) {
                            e.preventDefault();
                            var barcode = jQuery('#barcode').val();
                            var searchProduct = self.searchProduct(barcode);

                            searchProduct.done(function (rep) {
                                if (rep.result) {
                                    var data = rep.data;
                                    var quantity = jQuery('#quantity').val();
                                    data['quantity'] = quantity;
                                    self.addProduct(jQuery('#product-list-show table tbody'), data);
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

                        jQuery('#barcode').keyup(function (e) {
                            e.preventDefault();
                            if (e.keyCode == 13) {
                                jQuery('#add-more-product').trigger('click');
                            }
                        });


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
                     * Add product to order list
                     */
                    this.addProduct = function (elem, arg) {
                        var self = this;
                        var name = arg.name;
                        var barcode = arg.barcode.trim();
                        var quantity = parseInt(arg.quantity);
                        var price = parseInt(arg.regular_price);
                        var addNew = true;
                        var row_pattern = '<tr class="product-item" data-barcode="{barcode}">' +
                            '<td><span class="product-item-num">{product_num}</span></td>' +
                            '<td><input name="order[product][{barcode}]name" readonly class="product-name"  value="{product_name}"></td>' +
                            '<td><input name="order[product][{barcode}]quantity"  readonly class="product-quantity" value="{product_quantity}"></td>' +
                            '<td><input class="product-price" type="text" readonly value="{price}"></td>' +
                            '<td><input class="product-amount" type="text" readonly value="{amount}"></td>' +
                            '<td><span><i class="fa fa-remove"></i></span></td>' +
                            '</tr>';


                        // search product exist on the product list
                        var product_list = jQuery('.product-item');
                        jQuery(product_list).each(function (k, item) {
                            var currentQuantity = jQuery(item).find('.product-quantity').val();
                            var barcodeCurrent = jQuery(item).data('barcode');
                            if (barcodeCurrent == barcode) {
                                addNew = false;
                                quantity += parseInt(currentQuantity);
                                var html = row_pattern.replace('{product_num}', k + 1)
                                    .replace('{barcode}', barcode)
                                    .replace('{product_name}', name)
                                    .replace('{product_quantity}', quantity)
                                    .replace('{price}', price)
                                    .replace('{amount}', price * quantity);

                                jQuery(item).replaceWith(html);
                            }


                        });

                        // add new
                        if (addNew) {
                            var num = jQuery('.product-item').length || 1;
                            var html = row_pattern.replace('{product_num}', num)
                                .replace(/{barcode}/g, barcode)
                                .replace('{product_name}', name)
                                .replace('{product_quantity}', quantity)
                                .replace('{price}', price)
                                .replace('{amount}', price * quantity);
                            jQuery(elem).append(html);
                        }

                        // calculate amount
                        self.calculateTotalAmount();
                    };

                    /**
                     * Remove product out of order
                     */
                    this.removeProduct = function (elem) {
                        jQuery(elem).closest('.product-item').remove();
                        this.calculateTotalAmount();
                    };


                    this.calculateTotalAmount = function () {
                        var product_list = jQuery('.product-item');
                        var totalAmount = 0;
                        jQuery(product_list).each(function (k, item) {
                            var amountItem = jQuery(item).find('.product-amount').val();
                            totalAmount += parseInt(amountItem);
                        });
                        jQuery('#total-amount').val(totalAmount);
                    };
                };

                var orderHandler = new OrderHandler();
                orderHandler.initDomListener();
            });

        </script>