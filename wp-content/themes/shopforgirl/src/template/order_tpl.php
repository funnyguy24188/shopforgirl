<?php

class OrderTemplate
{
    public static function render($order_data)
    {
        $user = wp_get_current_user();
        $total = $order_data['total'];
        $shipping = $order_data['shipping'];
        $order_id = $order_data['order_id'];
        $username = $user->display_name;
        ?>

        <style type="text/css">

            #print-order-wrap {
                width: 240px;
                overflow: hidden;
            }

            .order-top .main-title {
                text-align: center;
            }

            .order-top .shop-info {
                padding-left: 12px
            }

            .order-top .shop-info p {
                font-style: italic;
                text-align: center;
            }

            .order-top .order-id {
                text-align: center;
            }

            .order-top .sub-title {
                text-align: center;
                font-size: 14px
            }

            .product-item .product-title {
                width: 100%;
                font-style: italic;
                font-weight: bold;
                overflow: hidden
            }

            .product-item .product-meta {
                overflow: hidden;
                display: inline-block;
                width: 100%;
                font-style: italic;
                text-align: right;
            }


        </style>

        <div id="print-order-wrap">
            <!-- Display the order general infomation -->
            <div class="order-top">
                <h2 class="main-title">SHOPFORGIRL</h2>
                <div class="shop-info">
                    <p class="text-center">
                        220 Bà Hạt-P9-Q.10-TP.HCM
                        ĐT : 01279916595
                        FB : shopforgirl.2011
                    </p>
                </div>
                <h3 class="sub-title">Hóa Đơn</h3>
                <h4 class="order-id">Order ID: <?php echo $order_id ?></h4>
                <h5 class="order-id">Ca: <?php echo  $username ?></h5>
            </div>
            <span>==================================</span>
            <!-- End order top -->
            <!-- Display product items in order -->
            <span class="order-content">
                <table>
                    <?php foreach ($order_data['items'] as $item): ?>
                        <tr>
                            <td>
                                <div class="product-item">
                                    <span class="product-title">
                                        <span><?php echo $item['quantity'] ?> x </span>
                                        <span><?php echo $item['name'] ?></span>
                                    </span>
                                    <span class="product-meta">
                                        <span>#<?php echo $item['barcode'] ?> - Giá: <?php echo $item['price'] ?>
                                        </span>
                                    </span>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            </span>
            <!-- End order content -->
            <!-- Display the subtotal of the order -->
            <div class="order-subtotal" style="margin-top: 15px; text-align: right">
                <span><strong>Ship: <?php echo $shipping ?></strong></span><br/>
                <span><strong>Tổng: <?php echo $total ?></strong></span>
            </div>
            <!-- End the order subtotal -->
            <span>==================================</span>
            <!--- Display the Thank you -->
            <div class="order-bottom" style="text-align: center; margin-top: 15px">
                <p style="font-style: italic; width: 100%; text-align: center ;display: inline-block; ">
                    Xin cảm ơn quý khách
                    đã mua hàng tại Shopforgirl
                </p>
                <p style="font-style: italic;width: 100%; text-align: center  ;display: inline-block">
                    Hàng mua rồi miễn trả lại
                </p>
            </div>
            <!-- End the order bottom -->
        </div>

        <?php
    }
}


?>
