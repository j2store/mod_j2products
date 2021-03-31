<?php
/**
* @package J2Store
* @copyright Copyright (c)2016-19 Sasi varna kumar / J2Store.org
* @license GNU GPL v3 or later
*/
defined('_JEXEC') or die;
$image_root_path = JUri::root();
$image_path = '' ;
if ($product->image_type == 'thumbimage' && isset($product->thumb_image) ) {
	$image_path = $product->thumb_image ; 
} else if ($product->image_type == 'mainimage' && isset($product->main_image) ) {
	$image_path = $product->main_image ; 
}
if($product->image_position == 'top'){
	$img_class = ' span12';
}else {
	$img_class = ' span6';
}
?>
<?php if($product->show_image): ?>
<div class="j2store-product-image  <?php echo $img_class; ?> ">
	<?php if(JFile::exists(JPATH_SITE.'/'.JPath::clean($image_path))):?>
		<?php if($product->module_display_link && $product->link_image ): ?>
            <a href="<?php echo JRoute::_( $product->module_display_link ); ?>" title="<?php echo $product->product_name; ?>">
        <?php endif;?>
		<img itemprop="image" alt="<?php echo $product->product_name ;?>" 
		class="j2store-img-responsive j2store-product-image-<?php echo $product->j2store_product_id; ?>"  
		src="<?php echo $image_root_path.$image_path;?>" 
		width="<?php echo $product->image_size_width ;?>" 
		height="<?php echo $product->image_size_height ;?>"  />
		<?php if($product->module_display_link): ?>
			</a>
		<?php endif;?>
	<?php endif; ?>
</div>
<?php endif; ?>