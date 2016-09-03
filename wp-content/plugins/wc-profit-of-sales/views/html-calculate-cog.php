<?php
if (!defined('POSR_PATH'))
  exit();
?>

<div class="wrap proman">
    <h2><?= __('Calculate Cost of Goods', self::TEXT_DOMAIN) ?></h2>

    <?php
    if (!$has_run):
    ?>
    <p>
        <?php
        echo __("Click the 'Calculate COG' button below to calculate cost of products for ALL ORDERS based on current cost that you entered in product details form.", self::TEXT_DOMAIN);
        echo '<br /><br />';
        echo __("'Clear Cache' can be used for clearing report cache whenever you want the reports to retrieve newest data.", self::TEXT_DOMAIN);
        echo '<br /><br />';
        echo __("<strong>Please be aware the Calculate COG process will overwrite previous cost calculation if you had run this process before and it cannot be undone.</strong>", self::TEXT_DOMAIN);
        ?>
    </p>
    <div class="submit">
        <form action="" method="POST">
            <input type="hidden" name="posr_calculate_nonce" value="<?=wp_create_nonce('posr_calculate')?>" />
            <input class="button-primary" type="submit" name="Calculate" value="<?= __('Calculate COG', self::TEXT_DOMAIN) ?>" onclick="return confirm('Are you sure want to continue?')" />
            <input class="button" type="submit" name="ClearCache" value="<?= __('Clear Cache', self::TEXT_DOMAIN) ?>" />
        </form>
    </div>

    <?php
    else:
    ?>
    <p>
        <?= __('Process completed.', self::TEXT_DOMAIN) ?>
    </p>

    <?php
    endif;
    ?>
</div>