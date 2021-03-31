<?php
/**
* @package    J2Store
* @copyright  Copyright (c)2016-19 Sasi varna kumar / J2Store.org
* @license    GNU GPL v3 or later
*/
// No direct access
defined('_JEXEC') or die;
$options = $product->options;
?>

<?php if ($options) { ?>

      <div class="options" id="variable-options-<?php echo $product->j2store_product_id?>" >
        <?php foreach ($options as $option) { ?>

        <?php echo J2Store::plugin()->eventWithHtml('BeforeDisplaySingleProductOption', array($product, &$option)); ?>

        <?php //var_dump($option); ?>
        <?php if ($option['type'] == 'select') { ?>
        <!-- select -->
        <div id="j2option-<?php echo $option['productoption_id']; ?>" class="option">
          <?php if ($option['required']) { ?>
          <span class="required">*</span>
          <?php } ?>
          <b><?php echo JText::_($option['option_name']); ?>:</b><br />
          <select name="product_option[<?php echo $option['productoption_id']; ?>]"
         	 onChange="doAjaxPrice(
         	 			<?php echo $product->j2store_product_id?>,
         	 			'#j2option-<?php echo $option["productoption_id"]; ?>'
         	 			);"
          >
            <?php foreach ($option['optionvalue'] as $option_value) { ?>
            	<?php $checked = ''; if($option_value['product_optionvalue_default']) $checked = 'selected="selected"'; ?>
            <option <?php echo $checked; ?> value="<?php echo $option_value['product_optionvalue_id']; ?>"
            		<?php echo  $option_value['product_optionvalue_attribs'] ;?>
					data-product_id="<?php echo $product->j2store_product_id?>"
           		  >
            	<?php echo stripslashes(htmlspecialchars(JText::_($option_value['optionvalue_name']))); ?>
            </option>
            <?php } ?>
          </select>
        </div>
        <br />
        <?php } ?>

        <?php if ($option['type'] == 'radio') { ?>
          <!-- radio -->
        <div id="j2option-<?php echo $option['productoption_id']; ?>" class="option">
          <?php if ($option['required']) { ?>
          <span class="required">*</span>
          <?php } ?>
          <b ><?php echo JText::_($option['option_name']); ?>:</b><br />
          <?php foreach ($option['optionvalue'] as $option_value) { ?>
          	<?php $checked = ''; if($option_value['product_optionvalue_default']) $checked = 'checked="checked"'; ?>

          <input <?php echo $checked; ?> type="radio" name="product_option[<?php echo $option['productoption_id']; ?>]"
          	 onClick="doAjaxPrice(
          	 		<?php echo $product->j2store_product_id?>,
          	 		'#j2option-<?php echo $option["productoption_id"]; ?>'
          	 );"
            value="<?php echo $option_value['product_optionvalue_id']; ?>" id="j2option-value-<?php echo $option_value['product_optionvalue_id']; ?>"
		       <?php echo $option_value['product_optionvalue_attribs'];?>
            />

          <?php if(
          			$j2params->get('image_for_product_options', 0) &&
          			  isset($option_value['optionvalue_image']) &&
          			!empty($option_value['optionvalue_image'])
				):
          ?>
			<img class="optionvalue-image-<?php echo $option_value['product_optionvalue_id']; ?>" src="<?php echo JUri::root(true).'/'.$option_value['optionvalue_image']; ?>"
			 	<?php echo $option_value['product_optionvalue_attribs'];?>
			 />
          <?php endif; ?>
          <label for="j2option-value-<?php echo $option_value['product_optionvalue_id']; ?>"
          	<?php echo $option_value['product_optionvalue_attribs'];?> >
          <?php echo stripslashes(htmlspecialchars(JText::_($option_value['optionvalue_name']))); ?>
          </label>
          <br />
          <?php } ?>
        </div>
        <br />
        <?php } ?>
    		<?php echo J2Store::plugin()->eventWithHtml('AfterDisplaySingleProductOption', array($product, $option)); ?>
        <?php } ?>
      </div>
      <?php } ?>
