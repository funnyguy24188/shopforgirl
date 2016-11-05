<?php

class OrderTemplate
{
    public static function render($order_data)
    {
        $user = wp_get_current_user();
        $total = $order_data['total'];

        $shipping = $order_data['shipping'];
        $order_id = $order_data['order_id'];
        $customer_money = $order_data['customer_money'];
        $username = $user->display_name;
        ?>
        <style type="text/css">
            .text-center {
                text-align: center;
            }

            #print-order-wrap {
                width: 240px;
                overflow: hidden;
            }

            .order-top .shop-info p {
                font-style: italic;
                text-align: center;
            }
        </style>
        <table id="print-order-wrap">
            <tbody>
            <tr>
                <td>
                    <span style="font-size: 14px;font-weight: bold; text-decoration: underline; text-align: center"
                          class="main-title">SHOPFORGIRL</span><br>
                    <span class="text-center">220 Bà Hạt-P9-Q.10-TP.HCM
                                    ĐT : 01279916595<br>
                                    FB : shopforgirl.2011<br>
                    </span>
                    <p style="text-align: center">
                        <span style="font-size: 12px; font-weight: bold;text-align: center;"
                              class="sub-title">Hóa Đơn</span><br>
                        <span style="font-size: 9px;" class="order-id">Order ID: <?php echo $order_id ?></span><br>
                        <span style="font-size: 9px;" class="order-id">Ca: <?php echo $username ?></span>
                    </p>
                </td>
            </tr>
            <tr>
                <td>
                    ---------------------------------------
                </td>
            </tr>
            <tr>
                <td>
                    <table>
                        <?php foreach ($order_data['items'] as $item): ?>
                            <tr>
                                <td>
                                    <span class="product-title">
                                        <span><?php echo $item['quantity'] ?> x </span>
                                        <span><?php echo $item['name'] ?></span>
                                    </span>
                                    <span class="product-meta">
                                        <span>#<?php echo $item['barcode'] ?> - Giá: <?php echo $item['price'] ?>
                                        </span>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </table>
                </td>
            </tr>
            <tr>
                <td>
                    <div class="order-subtotal" style="margin-top: 15px; text-align: right">
                        <span><strong>Ship: <?php echo $shipping ?></strong></span><br/>
                        <span><strong>Tổng: <?php echo $total ?></strong></span><br/>
                        <span><strong>Khách đưa: <?php echo $customer_money ?></strong></span><br/>
                        <span><strong>Tiền thừa: <?php echo $customer_money - $total ?></strong></span>
                    </div>
                </td>
            </tr>
            <tr>
                <td>
                    ---------------------------------------
                </td>
            </tr>
            <tr>
                <td>
                    <span style="font-style: italic; width: 100%; text-align: center ;display: inline-block; ">
                        Xin cảm ơn quý khách!!
                    </span><br>
                    <span style="font-style: italic;width: 100%; text-align: center  ;display: inline-block">
                        Hàng mua rồi miễn trả lại
                    </span>
                </td>
            </tr>
            </tbody>
        </table>
        <?php
    }
}


?>
