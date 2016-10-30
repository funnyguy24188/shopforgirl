<div id="return-page-header" class="titleclass">
    <div class="container">
        <h2 style="float:left;margin-top:0"><?php the_title() ?></h2>
    </div><!--container-->
</div><!--titleclass-->


<div id="content" class="container">
    <div class="tutorial">
        <strong>Nhập số hóa đơn, load hóa đơn từ hệ thống để trả hàng.
            <br/>Lưu ý: Số lượng trả lại phải nhỏ hơn hoặc bằng số lượng trên hóa đơn.
        </strong>
    </div>
    <div class="alert alert-dismissible" id="alert-message" role="alert"></div>
    <form class="horizontal-form" id="return-product">
        <div class="row">
            <div class="form-group">
                <label class="control-label">Số Hóa đơn:</label>
            </div>
            <div class="form-group">
                <div class="col-md-4">
                    <input placeholder="Số hóa đơn" id="order-id" type="text" class="form-control">
                </div>
                <div class="col-md-2">
                    <button class="btn btn-info" id="load-order" type="button">Load Hóa đơn</button>
                </div>
                <div class="col-md-2">
                    <button class="btn btn-success" id="save-return-order" type="button">Lưu hóa đơn</button>
                </div>
            </div>
        </div>

        <div class="order-general-info">
            <div class="col-xs-12 col-sm-12 col-md-4">
                <div class="order-customer-info-block-1">
                    <p><strong>Số hóa đơn: <span class="order-general-item order-id"></span></strong></p>
                    <p><strong>Ngày đặt: <span class="order-general-item order-date"></span></strong></p>
                    <p><strong>Tên khách hàng: <span class="order-general-item order-customer-name"></span></strong></p>
                </div>
            </div>
            <div class="col-xs-12 col-sm-12 col-md-4">
                <div class="order-customer-info-block-2">
                    <p><strong>Địa chỉ: <span class="order-general-item order-customer-address"></span></strong></p>
                    <p><strong>Điện thoại: <span class="order-general-item order-customer-phone"></span></strong></p>
                    <p><strong>Email: <span class="order-general-item order-customer-email"></span></strong></p>
                </div>
            </div>

            <div class="col-xs-12 col-sm-12 col-md-4">
                <div class="order-customer-info-block-3">
                    <p><strong>Tổng hóa đơn: <span class="order-general-item order-total"></span></strong></p>
                    <p><strong>Trạng thái: <span class="order-general-item order-status-icon order-status"></span></strong></p>
                </div>
            </div>

            <table id="order-detail-table" class="table">
                <thead>
                <tr>
                    <th></th>
                    <th>STT</th>
                    <th>Barcode</th>
                    <th>Tên sản phẩm</th>
                    <th>Giá</th>
                    <th>Số lượng</th>
                    <th>Số lượng trả lại</th>
                </tr>
                </thead>
                <tbody class="order-detail-body"></tbody>
            </table>
        </div>

    </form>
</div>

<script src="<?php echo get_stylesheet_directory_uri() . '/assets/js/return-product.js' ?>"></script>