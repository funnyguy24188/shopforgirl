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
            ?>
            <?php for ($i = 0; $i < $num; $i++): ?>
                <table style="padding-top: 50px">
                    <tr>
                        <td>
                            <img src="<?php echo $url ?>" alt="barcode-<?php echo $product_id ?>">
                        </td>
                    </tr>
                </table>
            <?php endfor; ?>
        <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p>No barcode in print queue </p>
    <?php endif; ?>
        <?php

    }
}