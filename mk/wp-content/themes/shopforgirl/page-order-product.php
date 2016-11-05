<?php global $spg_cart; ?>
<?php $spg_cart->calculate_totals();
?>
<div id="order-page-header" class="titleclass">
    <div class="container">
        <?php if (have_message()): ?>
            <div class="alert alert-dismissible <?php echo (( get_message_type() == 'error' ) ? 'alert-danger' : '') ?>" id="alert-message" role="alert">
                <?php echo get_message_content(); ?>
            </div>
        <?php endif; ?>
        <h2 style="float:left;margin-top:0"><?php the_title() ?></h2>
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
                                        <input id="quantity" class="numberic form-control" type="number" value="1"
                                               placeholder="Số lượng">
                                    </div>
                                    <div class="col-xs-12 col-sm-2">
                                        <button id="add-more-product" type="button" class="btn btn-success">Thêm
                                        </button>
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
                                    <span class="no-product" style="display: none">Sản phẩm không tồn tại hoặc số lượng bán vượt số lượng tồn</span>
                                </div>
                            </fieldset>
                            <fieldset id="product-list-show">
                                <legend>Danh sách sản phẩm</legend>
                                <table class="table product-list-table">
                                    <thead>
                                    <tr>
                                        <th style="width: 7%">STT</th>
                                        <th style="width: 38%">Tên sản phẩm</th>
                                        <th style="width: 10%">S.lượng</th>
                                        <th style="width: 15%">Giá</th>
                                        <th style="width: 15%">Thành tiền</th>
                                        <th style="width: 15%">Xóa</th>
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

                                    <div class="clearfix"></div>

                                    <div class="col-xs-8 col-sm-8 customer-money-payment-group">
                                        <label class="form-label">Khách đưa</label>
                                        <input class="numberic form-control" id="customer-money" type="number"
                                               name="order[customer_money]" value="0">
                                    </div>


                                    <div class="col-xs-4 col-sm-4 customer-save-order-group">
                                        <button class="btn btn-success primary-button" type="submit">Lưu</button>
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

            var nonce = '<?php echo wp_create_nonce("update-shipping-method") ?>';
            var cart_data = '<?php echo json_encode(array(
                'cart_items' => $spg_cart->parse_products_data(),
                'cart_total' => $spg_cart->get_total()
            )); ?>';

        </script>

        <script src="<?php echo get_stylesheet_directory_uri() . '/assets/js/order-product.js' ?>"></script>