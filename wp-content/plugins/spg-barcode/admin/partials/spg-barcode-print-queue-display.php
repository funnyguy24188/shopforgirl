<div class="main" id="print-queue-grid">
    <div class="container">
        <h3>Barcode Queue Grid</h3>
        <div class="main-content">
            <?php if (!empty($barcode_print)): ?>
            <div class="statistic">
                <ul>
                    <?php foreach ($barcode_print['items'] as $product_id => $barcodes): ?>
                        <?php $num = $barcodes[1];
                        $product_name = $barcodes[2]; ?>
                        <?php ?>
                        <li><?php echo $product_name ?>: <?php echo $num ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <div class="controls">
                <input value="Print Barcode Grid" class="button button-primary button-large"
                       id="print-barcode-queue-btn" type="button">
                <form method="post">
                    <input name="clear-all-print-queue" type="hidden" value="1">
                    <input value="Clear All" class="button button-primary button-large"
                           id="clear-all-barcode-queue-btn" type="submit">
                </form>
            </div>
            <div class="clearfix"></div>
            <div class="print-queue-grid-wrapper">

                <?php foreach ($barcode_print['items'] as $product_id => $barcodes): ?>
                    <?php $num = $barcodes[1];
                    $url = $barcodes[0];
                    ?>
                    <?php for ($i = 0; $i < $num; $i++): ?>
                        <div class="print-queue-grid-item">
                            <img src="<?php echo $url ?>" alt="barcode-<?php echo $product_id ?>">
                        </div>
                    <?php endfor; ?>
                <?php endforeach; ?>
                <?php else: ?>
                    <p>No barcode in print queue </p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<iframe style="margin: 0; padding:0; display: none" src="<?php echo $url_full_pdf_print ?>"
        id="iframe-print-queue"></iframe>