<?php require_once 'src/order/SPGOrder.php' ?>
<?php require_once 'src/order/SPGOrderList.php' ?>
<?php
/**
 * Get order list
 */

$order_object = new SPGOrderList();
$order_status = !empty($_GET['order_status']) ? $_GET['order_status'] : '';
$start_date = !empty($_GET['start_date']) ? $_GET['start_date'] : '';
$end_date = !empty($_GET['end_date']) ? $_GET['end_date'] : '';
$term = !empty($_GET['term']) ? $_GET['term'] : '';
$page = get_query_var('paged', 1);

$order_list = $order_object->get_order_list($term, $order_status, $start_date, $end_date, $page);
$order_statuses = SPGOrder::get_order_status();
$current_user = wp_get_current_user();

?>

<div id="return-page-header" class="titleclass">
    <div class="container">
        <h2 style="float:left;margin-top:0"><?php the_title() ?></h2>
    </div><!--container-->
</div><!--titleclass-->

<div id="content" class="container">
    <div class="alert alert-dismissible" id="alert-message" role="alert"></div>
    <div class="row">
        <form class="form-horizontal" id="order-list-search-form">
            <div class="col-md-12">
                <select name="order_status">
                    <option value="">Tất cả</option>
                    <?php foreach ($order_statuses as $order_key => $order_status_name): ?>
                        <option <?php echo(($order_key == $order_status) ? 'selected' : '') ?>
                            value="<?php echo $order_key ?>"><?php echo $order_status_name ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-12 filter-wrapper">
                <div class="col-md-6 date-filter-box-wrapper">
                    <strong>Ngày bắt đầu:</strong>
                    <input value="<?php echo $start_date ?>" class="datepicker" id="start-date" name="start_date"
                           data-date-format="dd/mm/yyyy">

                    <strong>Ngày kết thúc:</strong>
                    <input value="<?php echo $end_date ?>" class="datepicker" id="end-date" name="end_date"
                           data-date-format="dd/mm/yyyy">
                </div>
                <div class="col-md-4 search-box-wrapper">
                    <input class="form-control search-box" value="<?php echo $term ?>" name="term"
                           placeholder="Nhập từ khóa để tìm">
                    <button class="btn btn-success submit-btn">Tìm kiếm</button>
                </div>
            </div>
        </form>
    </div>
    <div class="row">
        <div class="main col-sm-12 col-md-12" role="main">
            <div class="entry-content" id="order-page" itemprop="mainContentOfPage">
                <table class="table table-responsive table-hover table-order-list">
                    <thead>
                    <tr>
                        <th></th>
                        <th>ID</th>
                        <th>Ngày lập</th>
                        <th>Số lượng</th>
                        <th>Tổng tiền</th>
                        <th>Trạng thái</th>
                        <th></th>
                    </tr>
                    </thead>
                    <?php if (!empty($order_list)): ?>
                        <tbody>
                        <?php foreach ($order_list as $order_id => $order): ?>
                            <?php if (is_array($order)): ?>
                                <tr class="clickable" data-toggle="collapse" id="order-id-<?php echo $order_id ?>"
                                data-target=".order-id-<?php echo $order_id ?>">
                                <td><i class="glyphicon glyphicon-plus"></i></td>
                                <td>#<?php echo $order['order_short_info']['order_id'] ?></td>
                                <td><?php echo $order['order_short_info']['order_date'] ?></td>
                                <td><?php echo $order['order_short_info']['order_quantity'] ?></td>
                                <td><?php echo $order['order_short_info']['order_total'] ?></td>
                                <td class="td-order-status">
                                <span
                                    class="order-status-icon order-<?php echo $order['order_short_info']['order_status'] ?>">
                                    <?php echo $order['order_short_info']['order_status_text'] ?>
                                </span>
                                </td>
                                <td>
                                <a href="#" data-order-id="<?php echo $order_id ?>" class="print-order-detail"><i
                                        class="fa fa-print" aria-hidden="true"></i></a>
                                <?php if (in_array('administrator', $current_user->roles) || in_array('shop_manager', $current_user->roles)): ?>
                                    <a target="_blank"
                                       href="<?php echo site_url("wp-admin/post.php?post=$order_id&action=edit") ?>"><i
                                            class="fa fa-pencil-square-o" aria-hidden="true"></i></a>
                                <?php endif; ?>
                            <select class="change-order-status">
                                <?php foreach ($order_statuses as $order_status_key => $order_status_name): ?>
                                    <option class="change-status-opt" data-toggle="modal"
                                            data-order-id="<?php echo $order['order_short_info']['order_id'] ?>"
                                            data-order-status-text="<?php echo $order_status_name ?>"
                                            data-order-status="<?php echo $order_status_key ?>"
                                            data-message="Thay đổi trạng thái hóa đơn thành <?php echo $order_status_name ?>"
                                            data-target="#change-order-modal" <?php echo(($order_status_key == $order['order_short_info']['order_status']) ? 'selected' : '') ?>
                                            value="<?php echo $order_status_key ?>"><?php echo $order_status_name ?></option>
                                <?php endforeach; ?>
                            </select>
                            </td>
                            </tr>
                            <tr class="collapse order-id-<?php echo $order_id ?>">
                                <td colspan="7">
                                    <div class="col-md-3 customer-info">
                                        <p>Tên khách
                                            hàng: <?php echo $order['customer_short_info']['customer_name'] ?></p>
                                        <p>Địa chỉ: <?php echo $order['customer_short_info']['address'] ?></p>
                                        <p>Điện thoại: <?php echo $order['customer_short_info']['phone'] ?></p>
                                        <!-- <p>Email: <?php /*echo $order['customer_short_info']['email'] */ ?></p>-->
                                    </div>
                                    <div class="col-md-9 product-list">
                                        <label>Chi tiết hóa đơn</label>
                                        <table class="table">
                                            <thead>
                                            <tr>
                                                <th>Item ID</th>
                                                <th>ID Sản phẩm</th>
                                                <th>Barcode</th>
                                                <th>Tên sản phẩm</th>
                                                <th>Số lượng</th>
                                                <th>Đơn giá</th>
                                                <th>Thành tiền</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            <?php foreach ($order['order_detail'] as $line_item): ?>
                                                <tr>
                                                    <td><?php echo $line_item['item_id'] ?></td>
                                                    <td><?php echo $line_item['product_id'] ?></td>
                                                    <td><?php echo $line_item['barcode'] ?></td>
                                                    <td><?php echo $line_item['product_name'] ?></td>
                                                    <td><?php echo $line_item['quantity'] ?></td>
                                                    <td><?php echo $line_item['price'] ?></td>
                                                    <td><?php echo $line_item['quantity'] * $line_item['price'] ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </td>
                            </tr>
                            <?php endif; ?>
                        <?php endforeach; ?>
                        </tbody>
                    <?php endif; ?>
                </table>
            </div>
        </div>
    </div>
    <div class="spg-pagination">
        <?php spg_wp_pagenavi($order_list['query_object']); ?>
    </div>
</div>

<div id="printerDiv" style="display: none"></div>

<div class="modal fade" tabindex="-1" id="change-order-modal" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">Thay đổi trạng thái hóa đơn</h4>
            </div>
            <div class="modal-body">

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Đóng</button>
                <button type="button" class="btn btn-primary order-status-save">Lưu</button>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->