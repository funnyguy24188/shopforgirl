<?php

class SPGBarcodePrintTemplate
{
    public static function render($barcode_print)
    {
        ?>
        <div id="if-inside-wrap">
        <?php if (!empty($barcode_print)): ?>
        <?php foreach ($barcode_print['items'] as $product_id => $barcodes): ?>
            <?php $num = $barcodes[1];
            $url = $barcodes[0];
            $product_name = $barcodes[2];
            $product_price = $barcodes[3];
            ?>
            <table>
                <?php for ($i = 0; $i < $num; $i++): ?>
                    <tr>
                        <img src="<?php echo $url ?>" alt="barcode-<?php echo $product_id ?>">
                        <span style="font-size: 6px;"><?php echo $product_name . "($product_price)" ?></span>
                    </tr>
                <?php endfor; ?>
            </table>
        <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p>No barcode in print queue</p>
    <?php endif; ?>
        <?php

    }
}