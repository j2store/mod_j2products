<?php
/**
 * @package 		J2Store
 * @copyright 	Copyright (c)2016-19 Sasi varna kumar / J2Store.org
 * @license 		GNU GPL v3 or later
 */
defined('_JEXEC') or die;

//$forced_singleItem = ($params->get('force_single_column') == 1) ? 'true' : 'false';
$total_cols = $params->get('number_of_coloums', 3);
$total_colsDesktop = $params->get('number_of_coloums_Desktop', 3);
$total_colsDesktopSmall = $params->get('number_of_coloums_DesktopSmall', 3);
$total_colsTablet = $params->get('number_of_coloums_Tablet', 3);
$total_colsTabletSmall = $params->get('number_of_coloums_TabletSmall', 2);
$total_colsMobile = $params->get('number_of_coloums_Mobile', 1);
$total_count = count($list); $counter = 0;
$slider_autoplay="true";
//$show_pagination = $params->get('show_pagination', 1) ? "true":"false";
$show_navigation = $params->get('show_navigation', 1) ? "true":"false";
$slider_id = 'jowl-slider-'.$module_id;

$document =JFactory::getDocument();
$document->addStyleSheet(JURI::root(true).'/media/j2store/css/font-awesome.min.css');
?>
<script>
	if(typeof(j2store) == 'undefined') {
		var j2store = {};
	}
	if(typeof(j2store.jQuery) == 'undefined') {
		j2store.jQuery = jQuery.noConflict();
	}

	if(typeof(j2storeURL) == 'undefined') {
		var j2storeURL = '';
	}
	(function($) {
		window.onresize = setcarousel<?php echo $module_id;?>;
		$(document).ready(function() {
			setcarousel<?php echo $module_id;?>();
		});

	})(j2store.jQuery);
    function setcarousel<?php echo $module_id;?>(){
        (function ($) {
            $("#<?php echo $slider_id;?>").owlCarousel('destroy');
            $("#<?php echo $slider_id;?>").owlCarousel({
                loop:false,
                margin:10,
                responsiveRefreshRate : 200,
                slideSpeed : 2000,
                nav: <?php echo $show_navigation;?>,
                autoplay: <?php echo $slider_autoplay?>,
                responsiveClass:true,
                navText: ["<i class='fa fa-caret-left icon-chevron-left icon-white'></i>",
                    "<i class='fa fa-caret-right icon-chevron-right icon-white'></i>"],
                responsive:{
                    0: {
                        items:<?php echo $total_cols;?>
                    },
                    320: {
                        items:<?php echo $total_colsMobile;?>
                    },
                    480: {
                        items: <?php echo $total_colsTabletSmall;?>
                    },
                    768: {
                        items: <?php echo $total_colsTablet;?>
                    },
                    992: {
                        items: <?php echo $total_colsDesktopSmall;?>
                    },
                    1200: {
                        items: <?php echo $total_colsDesktop;?>
                    }
                }
                //navText: ['<svg width="100%" height="100%" viewBox="0 0 11 20"><path style="fill:none;stroke-width: 1px;stroke: #000;" d="M9.554,1.001l-8.607,8.607l8.607,8.606"/></svg>','<svg width="100%" height="100%" viewBox="0 0 11 20" version="1.1"><path style="fill:none;stroke-width: 1px;stroke: #000;" d="M1.054,18.214l8.606,-8.606l-8.606,-8.607"/></svg>'],
            }).on('changed.owl.carousel', syncPosition);
        })(j2store.jQuery);
    }
	function syncPosition(el) {
		(function ($) {
			//if you set loop to false, you have to restore this next line
			//var current = el.item.index;

			//if you disable loop you have to comment this block
			var count = el.item.count-1;
			var current = Math.round(el.item.index - (el.item.count/2) - .5);

			if(current < 0) {
				current = count;
			}
			if(current > count) {
				current = 0;
			}
			//console.log(current);
			//$("#<?php echo $slider_id;?>").data('owl.carousel').to(number, 300, true);
		})(j2store.jQuery);
	}



</script>
<div class="jowl-module-product-slide-<?php echo $module_id;?>"> <!-- owl slider container -->
	<div id="<?php echo $slider_id;?>" class="j2store-product-module j2store-product-module-list owl-carousel">
		<?php if( count($list) > 0 ):?>
			<?php foreach ($list as $product_id => $product) : ?>
				<?php  $rowcount = ((int) $counter % (int) $total_cols) + 1; ?>
                <div>
                    <meta  content="<?php echo $counter+1;?>" />
				<!-- single product -->
				<div class="j2store product-<?php echo $product->j2store_product_id; ?> j2store-module-product ">

					<!-- product image if postion is top -->
					<?php if ($product->image_position == 'top') {
						require( __DIR__.'/default_image.php' );
					} ?>

					<!-- product title -->
                    <h2 class="product-title col-sm-12">
                        <?php if ($product->show_title) : ?>
                            <?php if ($product->link_title) : ?>
                                <a href="<?php echo $product->module_display_link; ?>" title="<?php echo $product->product_name; ?>">
                            <?php endif; ?>

                            <?php echo $product->product_name; ?>

                            <?php if ($product->link_title) : ?>
                                </a>
                            <?php endif; ?>
                        <?php endif; ?>
                    </h2>

                    <?php if (isset($product->event->afterDisplayTitle)) : ?>
                        <?php echo $product->event->afterDisplayTitle; ?>
                    <?php endif; ?>
					<!-- end product title -->

					<div class="product-cart-section ">
						<?php
						if($product->image_position == 'top'){
							$img_class = ' '.$J2gridCol.'12 ';
						}else {
							$img_class = ' '.$J2gridCol.'6 ';
						}
						?>
						<!-- product image if postion is left -->
						<?php if ($product->image_position == 'left') {
							require( __DIR__.'/default_image.php' );
						} ?>
						<div class="product-cart-left-block <?php echo $img_class; ?>" >
							<!-- Product price block-->
							<?php echo J2Store::plugin()->eventWithHtml('BeforeRenderingProductPrice', array($product)); ?>

							<div itemprop="offers" itemscope itemtype="http://schema.org/Offer" class="product-price-container">
								<?php if($product->show_price && $product->show_special_price):?>
									<?php if($product->pricing->base_price != $product->pricing->price):?>
										<?php $class='';?>
										<?php if(isset($product->pricing->is_discount_pricing_available)) $class='strike'; ?>
										<span class="base-price <?php echo $class?>">
						<?php echo J2Store::product()->displayPrice($product->pricing->base_price, $product, $j2params);?>
					</span>
									<?php endif; ?>
									<span class="sale-price">
					<?php echo J2Store::product()->displayPrice($product->pricing->price, $product, $j2params);?>
				</span>
								<?php elseif ($product->show_price && !$product->show_special_price):?>
									<?php if($product->pricing->base_price != $product->pricing->price):?>
										<?php $class='';?>
										<?php if(isset($product->pricing->is_discount_pricing_available)) $class=''; ?>
										<span class="base-price <?php echo $class?>">
						<?php echo J2Store::product()->displayPrice($product->pricing->base_price, $product, $j2params);?>
					</span>
									<?php else:?>
										<span class="sale-price">
						<?php echo J2Store::product()->displayPrice($product->pricing->price, $product, $j2params);?>
					</span>
									<?php endif; ?>
								<?php elseif (!$product->show_price && $product->show_special_price):?>
									<?php if($product->pricing->base_price != $product->pricing->price):?>
										<?php $class='';?>
										<?php if(isset($product->pricing->is_discount_pricing_available)) $class=''; ?>
										<span class="base-price <?php echo $class?>">
						<?php echo J2Store::product()->displayPrice($product->pricing->price, $product, $j2params);?>
					</span>
									<?php endif; ?>
								<?php endif;?>

								<?php if( $product->show_price_taxinfo ): ?>
									<span class="tax-text">
						<?php echo J2Store::product()->get_tax_text(); ?>
					</span>
								<?php endif; ?>

								<meta itemprop="price" content="<?php echo $product->pricing->price; ?>" />
								<meta itemprop="priceCurrency" content="<?php echo $j2currency->getCode(); ?>" />
								<link itemprop="availability" href="<?php echo $product->variant->availability ? 'InStock':'OutOfStock'; ?>" />
							</div>
							<?php //endif; ?>

							<?php echo J2Store::plugin()->eventWithHtml('AfterRenderingProductPrice', array($product)); ?>

							<?php if( $product->show_offers && isset($product->pricing->is_discount_pricing_available) && $product->pricing->base_price > 0): ?>
								<?php $discount =(1 - ($product->pricing->price / $product->pricing->base_price) ) * 100; ?>
								<?php if($discount > 0): ?>
									<span class="discount-percentage">
                                        <?php  echo JText::sprintf('J2STORE_PRODUCT_OFFER',round($discount).'%');?>
				</span>
								<?php endif; ?>
							<?php endif; ?>


							<!-- end Product price block-->

							<!-- SKU -->
							<?php if( $product->show_sku ) : ?>
								<?php if(!empty($product->variant->sku)) : ?>
									<div class="product-sku">
										<span class="sku-text"><?php echo JText::_('J2STORE_SKU')?></span>
										<span itemprop="sku" class="sku"> <?php echo $product->variant->sku; ?> </span>
									</div>
								<?php endif; ?>
							<?php endif; ?>

							<!-- STOCK -->
							<?php if($product->show_stock && J2Store::product()->managing_stock($product->variant)): ?>
								<div class="product-stock-container">
									<?php if($product->variant->availability): ?>
										<span class="<?php echo $product->variant->availability ? 'instock':'outofstock'; ?>">
						<?php echo J2Store::product()->displayStock($product->variant, $params); ?>
					</span>
									<?php else: ?>
										<span class="outofstock">
						<?php echo JText::_('J2STORE_OUT_OF_STOCK'); ?>
					</span>
									<?php endif; ?>
								</div>

								<?php if($product->variant->allow_backorder == 2 && !$product->variant->availability): ?>
									<span class="backorder-notification">
					<?php echo JText::_('J2STORE_BACKORDER_NOTIFICATION'); ?>
				</span>
								<?php endif; ?>
							<?php endif; ?>

							<div class="product_cart_block">

								<?php if($j2params->get('catalog_mode', 0) == 0 ): ?>
									<form action="<?php echo $product->cart_form_action; ?>"
										  method="post" class="j2store-addtocart-form"
										  id="j2store-addtocart-form-<?php echo $product->j2store_product_id; ?>"
										  name="j2store-addtocart-form-<?php echo $product->j2store_product_id; ?>"
										  data-product_id="<?php echo $product->j2store_product_id; ?>"
										  data-product_type="<?php echo $product->product_type; ?>"
										<?php if(isset($product->variant_json)): ?>
											data-product_variants="<?php echo htmlspecialchars($product->variant_json);?>"
										<?php endif; ?>
										  enctype="multipart/form-data">

										<?php $cart_type = $product->list_show_cart;//$this->params->get('list_show_cart', 1);
										$product->display_cart_block = true;
										?>

										<?php

										if ($product->product_type=='configurable') {
											$cart_type = 2	;
										}
                                        if ($product->product_type=='subscriptionproduct' || $product->product_type=='variablesubscriptionproduct') {
                                            $cart_type = 1	;
                                        }

										if($cart_type == 1) : ?>
											<?php if ( $product->product_type=='simple' || $product->product_type=='downloadable' ):
												require( __DIR__.'/default_options.php' );
											elseif ($product->product_type=='variable'):
												require( __DIR__.'/default_variableoptions.php' );
											elseif ($product->product_type=='configurable'):
												$product->display_cart_block = false;
                                            elseif ($product->product_type=='subscriptionproduct'):
                                                require( __DIR__.'/default_subscription.php' );
                                            elseif ($product->product_type=='variablesubscriptionproduct'):
                                                require( __DIR__.'/default_variablesubscriptionproductoptions.php' );
											endif;
											?>
										<?php elseif( $product->product_type=='configurable' || ($cart_type == 2 && isset($product->options) && count($product->options)) || ($cart_type == 3) ):?>
											<!-- we have options so we just redirect -->
											<a href="<?php echo $product->module_display_link; ?>" class="j2store-button-cart <?php echo $params->get('choosebtn_class', 'btn btn-success'); ?>"><?php echo JText::_('J2STORE_VIEW_PRODUCT_DETAILS'); ?></a>
											<?php $product->display_cart_block = false; ?>
										<?php endif; ?>

										<?php echo J2Store::plugin()->eventWithHtml('BeforeAddToCartButton', array($product, J2Store::utilities()->getContext('default_cart'))); ?>

										<?php if ($product->display_cart_block): ?>
											<!-- cart block -->
											<div class="cart-action-complete" style="display:none;">
												<p class="text-success">
													<?php echo JText::_('J2STORE_ITEM_ADDED_TO_CART');?>
													<a href="<?php echo $product->checkout_link; ?>" class="j2store-checkout-link">
														<?php echo JText::_('J2STORE_CHECKOUT'); ?>
													</a>
												</p>
											</div>

											<div id="add-to-cart-<?php echo $product->j2store_product_id; ?>" class="j2store-add-to-cart">

												<?php if($params->get('show_qty_field', 1)): ?>
													<div class="product-qty">
														<input type="number" name="product_qty" value="<?php echo (int) $product->quantity; ?>" class="input-mini form-control" min="<?php echo (int) $product->quantity; ?>" step='1' />
													</div>
												<?php else: ?>
													<input type="hidden" name="product_qty" value="<?php echo (int) $product->quantity; ?>" />
												<?php endif; ?>

												<input type="hidden" name="product_id" value="<?php echo $product->j2store_product_id; ?>" />

												<input
													data-cart-action-always="<?php echo JText::_('J2STORE_ADDING_TO_CART'); ?>"
													data-cart-action-done="<?php echo $product->cart_button_text; ?>"
													data-cart-action-timeout="1000"
													value="<?php echo $product->cart_button_text; ?>"
													type="submit"
													class="j2store-cart-button <?php echo $params->get('addtocart_button_class', 'btn btn-primary');?>"
												/>

											</div>
										<?php elseif( !$product->variant->availability ): ?>
											<input value="<?php echo JText::_('J2STORE_OUT_OF_STOCK'); ?>" type="button" class="j2store_button_no_stock btn btn-warning" />
											<!-- end cart block -->
										<?php endif; ?>

										<?php echo J2Store::plugin()->eventWithHtml('AfterAddToCartButton', array($product, J2Store::utilities()->getContext('default_cart'))); ?>

										<input type="hidden" name="option" value="com_j2store" />
										<input type="hidden" name="view" value="carts" />
										<input type="hidden" name="task" value="addItem" />
										<input type="hidden" name="ajax" value="0" />
										<?php echo JHTML::_( 'form.token' ); ?>
										<input type="hidden" name="return" value="<?php echo base64_encode( JUri::getInstance()->toString() ); ?>" />
										<div class="j2store-notifications"></div>
										<?php if ($product->product_type == 'variable'|| $product->product_type == 'variablesubscriptionproduct'): ?>
											<input type="hidden" name="variant_id" value="<?php echo $product->variant->j2store_variant_id; ?>" />
										<?php endif ?>
									</form>
								<?php endif;?>

								<!-- Quick view -->
								<?php if($product->show_quickview):?>
                                    <a data-fancybox data-type="iframe" class="btn btn-default" data-src="<?php echo $platform->getProductUrl(array('task' => 'view', 'id' => $product->j2store_product_id,'tmpl' => 'component')); ?>" href="javascript:;">
                                        <i class="fa fa-eye"></i> <?php echo JText::_('J2STORE_PRODUCT_QUICKVIEW');?>
                                    </a>
								<?php endif;?>
							</div>
						</div>
						<!-- product image if postion is right -->
						<?php if ($product->image_position == 'right') {
							require( __DIR__.'/default_image.php' );
						} ?>
					</div> <!-- end of product_cart_block -->

					<!-- intro text -->
					<?php if(isset($product->event->beforeDisplayContent) && $product->show_beforedisplaycontent) : ?>
						<?php echo $product->event->beforeDisplayContent; ?>
					<?php endif;?>

					<?php if($product->show_introtext): ?>
						<div class="product-short-description"><?php echo $product->module_introtext; ?></div>
					<?php endif; ?>
					<?php if(isset($product->event->afterDisplayContent) && $product->show_afterdisplaycontent) : ?>
						<?php echo $product->event->afterDisplayContent; ?>
					<?php endif;?>
					<!-- end intro text -->

				</div>
				<!-- end single product -->
                </div>
			<?php endforeach; ?>
		<?php endif; ?>
	</div>
</div>
