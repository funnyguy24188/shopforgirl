<?php

class SPGBarcodePrintTemplate
{
    public static function render($barcode_print)
    {
        ?>
        <style type="text/css">
            #if-inside-wrap {
                width: 240px;
            }
        </style>

        <div id="if-inside-wrap">
        <?php if (!empty($barcode_print)): ?>
        <?php foreach ($barcode_print['items'] as $product_id => $barcodes): ?>
            <?php $num = $barcodes[1];
            $url = $barcodes[0];
            ?>
            <?php for ($i = 0; $i < $num; $i++): ?>
                <div>
                    <img src="<?php echo $url ?>" alt="barcode-<?php echo $product_id ?>">
                </div>
            <?php endfor; ?>
        <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p>No barcode in print queue </p>
    <?php endif; ?>
        <?php

    }
}