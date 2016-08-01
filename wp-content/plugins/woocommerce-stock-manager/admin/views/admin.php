f<?php
/**
 * @package   WooCommerce Stock Manager
 * @author    Vladislav Musílek
 * @license   GPL-2.0+
 * @link      http:/toret.cz
 * @copyright 2015 Toret.cz
 */
$stock = $this->stock();

/**
 * Save all data
 *
 */   
if(isset($_POST['save-all'])){
  $stock->save_all($_POST);
  //add redirect
  
}


?>


<div class="wrap">

	<h2><?php echo esc_html( get_admin_page_title() ); ?></h2>
  
  

  
<div class="t-col-12">
  <div class="toret-box box-info">
    <div class="box-header">
      <h3 class="box-title"><?php _e('Stock manager','stock-manager'); ?></h3>
    </div>
  <div class="box-body">
      
      <?php include('components/filter.php'); ?>
      
      <div class="clear"></div>
    <form method="post" action="" style="position:relative;">
    <div class="lineloader"></div>  
      <table class="table-bordered">
        <tr>
          <th><?php _e('SKU','stock-manager'); ?></th>
          <th><?php _e('ID','stock-manager'); ?></th>
          <th><?php _e('Name','stock-manager'); ?></th>
          <th><?php _e('Product type','stock-manager'); ?></th>
          <th><?php _e('Parent ID','stock-manager'); ?></th>
          <th><?php _e('Price','stock-manager'); ?></th>
          <th><?php _e('Weight','stock-manager'); ?></th>
          <th><?php _e('Manage stock','stock-manager'); ?></th>
          <th><?php _e('Stock status','stock-manager'); ?></th>
          <th><?php _e('Backorders','stock-manager'); ?></th>
          <th style="width:50px;"><?php _e('Stock','stock-manager'); ?></th>
          <?php do_action( 'stock_manager_table_th' ); ?>
          <th style="width:100px;"><?php _e('Save','stock-manager'); ?></th>
        </tr>
      <?php $products = $stock->get_products($_GET); ?>
      <?php foreach( $products as $item ){ 
        $product_meta = get_post_meta($item->ID);
        $item_product = get_product($item->ID);
        $product_type = $item_product->product_type;
      ?>
        <tr>
          <input type="hidden" name="product_id[<?php echo $item->ID; ?>]" value="<?php echo $item->ID; ?>" />
          <td><?php if(!empty($product_meta['_sku'][0])){ echo $product_meta['_sku'][0]; } ?></td>
          <td class="td_center"><?php echo $item->ID; ?></td>
          <td><a href="<?php echo admin_url().'post.php?post='.$item->ID.'&action=edit'; ?>" target="_blank"><?php echo $item->post_title; ?></a></td>
          <td class="td_center">
            <?php if($product_type == 'variable'){
              echo '<span class="btn btn-info btn-sm show-variable" data-variable="'.$item->ID.'">'.__('Show variables','stock-manager').'</span>';
            }else{ 
              echo $product_type; 
            } ?>
          </td>
          <td></td>
          <?php WCM_Table::price_box($product_meta, $item->ID); ?>
          <?php WCM_Table::weight_box($product_meta, $item->ID); ?>
          <td>
            <select name="manage_stock[<?php echo $item->ID; ?>]" class="manage_stock_<?php echo $item->ID; ?>">
              <option value="yes" <?php if(!empty($product_meta['_manage_stock'][0]) && $product_meta['_manage_stock'][0] == 'yes'){ echo 'selected="selected"'; } ?>><?php _e('Yes','stock-manager'); ?></option>
              <option value="no" <?php if(!empty($product_meta['_manage_stock'][0]) && $product_meta['_manage_stock'][0] == 'no'){ echo 'selected="selected"'; } ?>><?php _e('No','stock-manager'); ?></option>
            </select>
          </td>
          <td>
            <select name="stock_status[<?php echo $item->ID; ?>]" class="stock_status_<?php echo $item->ID; ?>">
              <option value="instock" <?php if(!empty($product_meta['_stock_status'][0]) && $product_meta['_stock_status'][0] == 'instock'){ echo 'selected="selected"'; } ?>><?php _e('In stock','stock-manager'); ?></option>
              <option value="outofstock" <?php if(!empty($product_meta['_stock_status'][0]) && $product_meta['_stock_status'][0] == 'outofstock'){ echo 'selected="selected"'; } ?>><?php _e('Out of stock','stock-manager'); ?></option>
            </select>
          </td>
          <td>
            <select name="backorders[<?php echo $item->ID; ?>]" class="backorders_<?php echo $item->ID; ?>">
              <option value="no" <?php if(!empty($product_meta['_backorders'][0]) && $product_meta['_backorders'][0] == 'no'){ echo 'selected="selected"'; } ?>><?php _e('No','stock-manager'); ?></option>
              <option value="notify" <?php if(!empty($product_meta['_backorders'][0]) && $product_meta['_backorders'][0] == 'notify'){ echo 'selected="selected"'; } ?>><?php _e('Notify','stock-manager'); ?></option>
              <option value="yes" <?php if(!empty($product_meta['_backorders'][0]) && $product_meta['_backorders'][0] == 'yes'){ echo 'selected="selected"'; } ?>><?php _e('Yes','stock-manager'); ?></option>
            </select>
          </td>
          <?php 
            $class = '';
            if(!empty($product_meta['_stock'])){
            if($product_meta['_stock'][0] < 1){ 
              $stock_number = $product_meta['_stock'][0];
              $class = 'outofstock';
            }else{ 
              $stock_number = $product_meta['_stock'][0];
              if($product_meta['_stock'][0] < 5){ $class = 'lowstock'; }else{
                 $class = 'instock';
              } 
            } 
            }else{
               $class = '';
            }
            ?>
          <td class="td_center <?php echo $class; ?>" style="width:90px;">
            <input type="number" name="stock[<?php echo $item->ID; ?>]" step="1" value="<?php echo round($stock_number); ?>" class="stock_<?php echo $item->ID; ?>" style="width:90px;" />
          </td>
          <?php do_action( 'stock_manager_table_simple_td' ); ?>
          <td class="td_center"><span class="btn btn-primary btn-sm save-product" data-product="<?php echo $item->ID; ?>"><?php _e('Save','stock-manager'); ?></span></td>
        </tr>
        
        <?php 
            if($product_type == 'variable'){
                $args = array(
	               'post_parent' => $item->ID,
	               'post_type'   => 'product_variation', 
	               'numberposts' => -1,
	               'post_status' => 'publish' 
                ); 
                $variations_array = get_children( $args );
                foreach($variations_array as $vars){
             
        $product_meta = get_post_meta($vars->ID);
        $item_product = get_product($vars->ID);
        $product_type = 'product variation' ;
        
      ?>
        <tr class="variation-line variation-item-<?php echo $item->ID; ?>">
          <input type="hidden" name="product_id[<?php echo $vars->ID; ?>]" value="<?php echo $vars->ID; ?>" />
          <td><?php if(!empty($product_meta['_sku'][0])){ echo $product_meta['_sku'][0]; } ?></td>
          <td class="td_center"><?php echo $vars->ID; ?></td>
          <td><?php 
          foreach($item_product->variation_data as $k => $v){ 
             $tag = get_term_by('slug', $v, str_replace('attribute_','',$k));
             if($tag == false ){
               echo $v.' ';
             }else{
             if(is_array($tag)){
              echo $tag['name'].' ';
             }else{
              echo $tag->name.' ';
             }
             }
          } 
          ?></td>
          <td><?php echo $product_type; ?></td>
          <td><?php echo $item->ID; ?></td>
          <?php WCM_Table::price_box($product_meta, $vars->ID); ?>
          <?php WCM_Table::weight_box($product_meta, $vars->ID); ?>
          <td>
            <select name="manage_stock[<?php echo $vars->ID; ?>]" class="manage_stock_<?php echo $vars->ID; ?>">
              <option value="yes" <?php if(!empty($product_meta['_manage_stock'][0]) && $product_meta['_manage_stock'][0] == 'yes'){ echo 'selected="selected"'; } ?>><?php _e('Yes','stock-manager'); ?></option>
              <option value="no" <?php if(!empty($product_meta['_manage_stock'][0]) && $product_meta['_manage_stock'][0] == 'no'){ echo 'selected="selected"'; } ?>><?php _e('No','stock-manager'); ?></option>
            </select>
          </td>
          <td>
            <select name="stock_status[<?php echo $vars->ID; ?>]" class="stock_status_<?php echo $vars->ID; ?>">
              <option value="instock" <?php if(!empty($product_meta['_stock_status'][0]) && $product_meta['_stock_status'][0] == 'instock'){ echo 'selected="selected"'; } ?>><?php _e('In stock','stock-manager'); ?></option>
              <option value="outofstock" <?php if(!empty($product_meta['_stock_status'][0]) && $product_meta['_stock_status'][0] == 'outofstock'){ echo 'selected="selected"'; } ?>><?php _e('Out of stock','stock-manager'); ?></option>
            </select>
          </td>
          <td>
            <select name="backorders[<?php echo $vars->ID; ?>]" class="backorders_<?php echo $vars->ID; ?>">
              <option value="no" <?php if(!empty($product_meta['_backorders'][0]) && $product_meta['_backorders'][0] == 'no'){ echo 'selected="selected"'; } ?>><?php _e('No','stock-manager'); ?></option>
              <option value="notify" <?php if(!empty($product_meta['_backorders'][0]) && $product_meta['_backorders'][0] == 'notify'){ echo 'selected="selected"'; } ?>><?php _e('Notify','stock-manager'); ?></option>
              <option value="yes" <?php if(!empty($product_meta['_backorders'][0]) && $product_meta['_backorders'][0] == 'yes'){ echo 'selected="selected"'; } ?>><?php _e('Yes','stock-manager'); ?></option>
            </select>
          </td>
          <?php
          $class = '';
            if(!empty($product_meta['_stock'])){
            if($product_meta['_stock'][0] < 1){ 
              $stock_number = $product_meta['_stock'][0];
              $class = 'outofstock';
            }else{ 
              $stock_number = $product_meta['_stock'][0];
              if($product_meta['_stock'][0] < 5){ $class = 'lowstock'; }else{
                 $class = 'instock';
              } 
            } 
            }else{
               $class = '';
            }
            ?>
          <td class="td_center <?php echo $class; ?>" style="width:90px;">
            <?php if(empty($product_meta['_stock'][0])){ $stock_number = 0; }else{ $stock_number = $product_meta['_stock'][0]; } ?>
            <input type="number" name="stock[<?php echo $vars->ID; ?>]" step="1" value="<?php echo $stock_number; ?>" class="stock_<?php echo $vars->ID; ?>" style="width:90px;" />
          </td>
          <?php do_action( 'stock_manager_table_variation_td' ); ?>
          <input type="hidden" name="wsm-ajax-nonce-<?php echo $vars->ID; ?>" class="wsm-ajax-nonce_<?php echo $vars->ID; ?>" value="<?php echo wp_create_nonce( 'wsm-ajax-nonce-'.$vars->ID ); ?>" />
          <td class="td_center"><span class="btn btn-primary btn-sm save-product" data-product="<?php echo $vars->ID; ?>"><?php _e('Save','stock-manager'); ?></span></td>
        </tr>      
        <?php        
                }
            }
        ?>
        
      <?php } ?>
      
      </table>
      <input type="submit" name="save-all" class="btn btn-danger" value="<?php _e('Save all','stock-manager') ?>" />
      </form>
      <div class="clear"></div>
      <?php echo $stock->pagination(); ?>
  </div>
</div>  
  

</div>
