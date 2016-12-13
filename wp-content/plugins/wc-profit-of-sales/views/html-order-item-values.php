<td class="item_cog" width="1%" data-sort-value="<?php echo esc_attr( $cog ); ?>">
    <div class="view">
        <?php
        if ( isset( $item['line_total'] ) ) {
            echo wc_price( $cog );
        }
        ?>
    </div>
    <?php if ( version_compare( WOOCOMMERCE_VERSION, "2.2.0" ) >= 0 ): ?>
    <div class="edit" style="display: none;">
        <?php $item_cog = esc_attr( wc_format_localized_price($cog) ); ?>
        <input type="text" name="item_cog[<?php echo $item_id; ?>]" placeholder="0" value="<?php echo $item_cog; ?>" data-qty="<?php echo $item_cog; ?>" size="4" class="wc_input_price" />
    </div>
    <?php endif ?>
</td>

