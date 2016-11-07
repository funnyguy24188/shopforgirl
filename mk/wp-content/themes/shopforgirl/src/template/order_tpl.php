<?php

class OrderTemplate
{
    public static function render($order_data)
    {
        $user_post = get_post($order_data['order_id']);
        $user_id = $user_post->post_author;

        $user_query = new WP_User_Query(array('id' => $user_id));
        $user = $user_query->get_results()[0];
        $total = $order_data['total'];

        $shipping = $order_data['shipping'];
        $order_id = $order_data['order_id'];
        $order_date = $order_data['order_date'];
        $username = $user->display_name;
        $customer_money = $order_data['customer_money'];

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
                          class="main-title">SHOPFORGIRL</span><br/>
                    <div class="text-center">
                        <span style="text-align: center">220 Bà Hạt-Q10</span><br/>
                        <span style="text-align: center">ĐT: 01279916595</span><br/>
                        <span style="text-align: center">153/3 NT Minh Khai-Q1</span><br/>
                        <span style="text-align: center">ĐT: 08.66872459</span><br/>
                        <span style="text-align: center">FB: shopforgirl.2011</span><br/>
                    </div>
                    <span style="font-size: 12px; font-weight: bold;text-align: center;"
                          class="sub-title">Hóa Đơn</span><br/>
                    <div style="text-align: left">
                        <span style="font-size: 9px;" class="order-id">Order ID: <?php echo $order_id ?></span><br>
                        <span style="font-size: 9px;" class="order-id">Ca: <?php echo $username ?></span><br>
                        <span style="font-size: 9px;" class="order-id">Ngày: <?php echo $order_date ?></span>
                    </div>
                </td>
            </tr>
            <tr>
                <td>
                    <hr>
                </td>
            </tr>
            <tr>
                <td>
                    <table>
                        <tr>
                            <th width="82px;" style="font-weight: bold">Tên hàng</th>
                            <th style="font-weight: bold">T.tiền</th>
                        </tr>
                        <?php foreach ($order_data['items'] as $item): ?>
                            <tr>
                                <td style="text-align: left">
                                    <?php echo $item['quantity'] . 'x ' . $item['name'] . '(' . $item['price'] / $item['quantity'] . ')' ?>
                                </td>
                                <td>
                                    <?php echo $item['price'] ?>
                                </td>
                            </tr>
                            <tr>
                                <td style="text-align: left">
                                    <?php echo '#' . $item['barcode'] ?>
                                </td>
                                <td></td>
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
                        <?php if (!empty($customer_money)): ?>
                            <span><strong>Khách đưa: <?php echo $customer_money ?></strong></span><br/>
                            <span><strong>Tiền thừa: <?php echo $customer_money - $total ?></strong></span>
                        <?php endif; ?>
                    </div>
                </td>
            </tr>
            <tr>
                <td>
                    <hr>
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
