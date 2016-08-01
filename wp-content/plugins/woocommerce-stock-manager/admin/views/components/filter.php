      
      <ul class="stock-manager-navigation">
        <li><span class="navigation-filter-default activ"><?php _e('Filter','stock-manager'); ?></span></li>
        <li><span class="navigation-filter-by-sku"><?php _e('Search by sku','stock-manager'); ?></span></li>
        <!--<li><span class="navigation-filter-display"><?php //_e('Display setting','stock-manager'); ?></span></li>-->
      </ul>
      
      <div class="clear"></div>
      
      <div class="stock-filter filter-block active-filter">
        <form method="get" action="">
          <select name="product-type">
            <option value="simple" <?php if(isset($_GET['product-type']) && $_GET['product-type'] == 'simple'){ echo 'selected="selected"'; } ?> ><?php _e('Simple products','stock-manager'); ?></option>
            <option value="variable" <?php if(isset($_GET['product-type']) && $_GET['product-type'] == 'variable'){ echo 'selected="selected"'; } ?>><?php _e('Products with variation','stock-manager'); ?></option>
          </select>
          <input type="hidden" name="page" value="stock-manager" />
          <input type="submit" name="show-product-type" value="<?php _e('Show','stock-manager'); ?>" class="btn btn-info" />
        </form>
        <form method="get" action="">
          <select name="product-category">
            <option value="all"><?php _e('All categories','stock-manager'); ?></option>
            <?php
              if(isset($_GET['product-category']) && $_GET['product-category'] != 'all' ){
                echo $stock->products_categories($_GET['product-category']);
              }else{
                echo $stock->products_categories();
              }
              
            ?>
          </select>
          <input type="hidden" name="page" value="stock-manager" />
          <input type="submit" name="show-product-category" value="<?php _e('Show','stock-manager'); ?>" class="btn btn-info" />
        </form>
        <form method="get" action="">
          <select name="manage-stock">
            <option value="no"><?php _e('No manage stock','stock-manager'); ?></option>
            <option value="yes"><?php _e('Yes manage stock','stock-manager'); ?></option>
          </select>
          <input type="hidden" name="page" value="stock-manager" />
          <input type="submit" name="show-manage-stock" value="<?php _e('Show','stock-manager'); ?>" class="btn btn-info" />
        </form>
        <form method="get" action="">
          <select name="stock-status">
            <option value="instock"><?php _e('In stock','stock-manager'); ?></option>
            <option value="outofstock"><?php _e('Out of stock','stock-manager'); ?></option>
          </select>
          <input type="hidden" name="page" value="stock-manager" />
          <input type="submit" name="show-stock-status" value="<?php _e('Show','stock-manager'); ?>" class="btn btn-info" />
        </form>
        <a href="<?php echo admin_url().'admin.php?page=stock-manager'; ?>" class="btn btn-danger"><?php _e('Clear filter','stock-manager'); ?></a>
      </div>
      
      <div class="clear"></div>
      
      <div class="filter-by-sku filter-block">
        <form method="get" action="">
          <input type="text" name="sku" class="sku-seach-field" />
          <input type="hidden" name="page" value="stock-manager" />
          <input type="submit" name="show-sku-item" value="<?php _e('Search by sku','stock-manager'); ?>" class="btn btn-info" />
        </form>
      </div>
      
      <div class="clear"></div>
      