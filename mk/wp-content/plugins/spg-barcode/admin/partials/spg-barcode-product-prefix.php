<?php
$product_terms = get_terms(array('taxonomy' => 'product_cat', 'hide_empty' => false, 'parent' => 0));
?>
<h3>SPG Barcode settings</h3>

<form action="admin.php?page=spg_barcode_prefix" method="POST">
    <table class="form-table cmb_metabox">
        <tbody>
        <tr>
            <td>
                <div>
                    <label>Barcode number</label>
                    <input name="barcode_default_length" type="text"
                           value="<?php echo (!empty($spg_option['barcode_default_length'])) ? $spg_option['barcode_default_length'] : 0 ?>">
                    <?php if (!empty($spg_option['barcode_default_length'])): ?>
                        <span>Barcode format: XX</span>
                        <?php for ($i = 0; $i < $spg_option['barcode_default_length']-2; $i++): ?>
                            <span style="text-decoration: underline"><?php echo ($i + 1) ?></span>
                        <?php endfor; ?>
                    <?php endif; ?>

                </div>
            </td>
        </tr>
        <?php foreach ($product_terms AS $term): ?>
            <tr>
                <td>
                    <div>
                        <?php

                        $product_prefix_term_name = '';
                        if (!empty($spg_option['product_prefix_term'][$term->term_taxonomy_id])) {
                            $product_prefix_term_name = $spg_option['product_prefix_term'][$term->term_taxonomy_id];
                        }

                        ?>
                        <input type="text" disabled value="<?php echo $term->name ?>">
                        <input name="product_prefix_term[<?php echo $term->term_taxonomy_id ?>]" type="text"
                               value="<?php echo $product_prefix_term_name ?>">
                    </div>
                </td>
            </tr>
        <?php endforeach; ?>
        <tr>
            <td>
                <div>
                    <input type="text" disabled value="No Category">
                    <input name="product_prefix_term[nocat]" type="text"
                           value="<?php echo (!empty($spg_option['product_prefix_term']['nocat'])) ? $spg_option['product_prefix_term']['nocat'] : '' ?>">
                </div>
            </td>
        </tr>
        </tbody>
        <tfoot>
        <tr>
            <td>
                <input name="save" type="submit" class="button button-primary button-large" id="publish" value="Update">
            </td>
        </tr>
        </tfoot>
    </table>

</form>
