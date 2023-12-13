<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2016-19 Sasi varna kumar / J2Store.org
 * @license GNU GPL v3 or later
 */
defined('_JEXEC') or die;
jimport( 'joomla.application.module.helper' );
jimport('joomla.filesystem.file');
jimport('joomla.filesystem.folder');

$com_path = JPATH_SITE.'/components/com_content/';

if (!class_exists('ContentHelperRoute')) {
    require_once $com_path.'helpers/route.php';
}
if (!class_exists('ContentRouter')) {
    if (version_compare(JVERSION, '3.99.99', 'lt')) {
        include $com_path . 'router.php';
    }
}


/**
 * Helper for mod_j2products
 * @package     J2Store
 * @subpackage  mod_j2products
 */
class ModJ2ProductsHelper
{
    /**
     * Method to include the CSS files
     * */
    public static function includeAssets($params){
        $subTemplate = $params->get('module_subtemplate', 'Default');
        $module_layout_path =  JModuleHelper::getLayoutPath('mod_j2products', $subTemplate.'/default');
        $module_layout_path =  str_replace("default.php", "", $module_layout_path);
        $module_layout_path .= 'assets';

        $path_prefix = str_replace(JPATH_SITE, "", $module_layout_path );
        $files = JFolder::files($module_layout_path);
        $css_files = array();
        $js_files = array();

        if (is_dir($module_layout_path)){
            foreach($files as $file){
                if( self::endsWith($file,'.css') ) {
                    $css_files[] = $path_prefix.'/'.$file;
                }
                if( self::endsWith($file,'.js') ) {
                    $js_files[] = $path_prefix.'/'.$file;
                }
            }
        }

        $document = JFactory::getDocument();
        // include css files
        if ( count($css_files) > 0 ) {
            foreach ($css_files as $css_file) {
                if (\Joomla\CMS\Filesystem\File::exists (JPATH_SITE.$css_file)) {
                    $document->addStyleSheet(rtrim(JURI::root(true),'/').'/'.trim($css_file,'/'));
                }
            }
        }
        // include js files
        if ( count($js_files) > 0 ) {
            foreach ($js_files as $js_file) {
                if (\Joomla\CMS\Filesystem\File::exists (JPATH_SITE.$js_file)) {
                    $document->addScript(rtrim(JURI::root(true),'/').'/'.trim($js_file,'/'));
                }
            }
        }
    }

    public static function getList(&$params)
    {
        // based on product source get the item or item ids
        $obj = new ModJ2ProductsHelper();
        $list  = array();
        $product_ids = $obj->getProductIds($params);
        if (!empty($product_ids)) {
            // pre-process the items or result array
            $list  = $obj->prepareProducts($params, $product_ids);

        }
        return $list;
    }

    /**
     * Method to get the products by source
     * @param JRegistry Object $params module parameters
     * */
    function getProductIds($params){
        $source = $params->get('product_source_type','category');
        $product_ids = array();

        switch ($source) {
            case 'product_tag':
                $limit = $params->get('number_of_items',6);
                $tag_list = $params->get('tag_list',array());
                if(!empty($tag_list)){
                    $selected_tag = implode('\',\'',$tag_list);
                    $db = JFactory::getDbo();
                    $query = $db->getQuery(true);
                    $query->select('product.j2store_product_id')->from('#__j2store_products as product')
                        ->select('price_index.min_price')
                        ->select('price_index.max_price')
                        ->join('LEFT OUTER', '#__j2store_productprice_index price_index ON  product.j2store_product_id=price_index.product_id')
                        ->join('INNER', '#__j2store_variants ON product.j2store_product_id=#__j2store_variants.product_id')
                        ->join('LEFT','#__content as content ON content.id = product.product_source_id')
                        ->join ( 'LEFT', '#__contentitem_tag_map AS tag_map ON content.id = tag_map.content_item_id'  )
                        ->join ( 'LEFT', '#__tags AS tag ON tag_map.tag_id = tag.id'  )
                        ->where($db->qn('#__j2store_variants').'.'.$db->qn('is_master').' = '.$db->q(1))
                        ->where('tag.alias IN (\''.$selected_tag.'\')')
                        ->where('product.product_source ='.$db->q('com_content'))
                        ->where('tag_map.type_alias='.$db->q('com_content.article'))
                        ->where('content.state=1')
                        ->group('product.j2store_product_id');
                    // Define null and now dates
                    $nullDate	= $db->quote($db->getNullDate());
                    //$nowDate	= $db->quote(JFactory::getDate()->toSql());
                    $tz = JFactory::getConfig()->get('offset');
                    $date = JFactory::getDate('now', $tz);

                    //default to the sql formatted date
                    $nowDate = $db->quote( $date->toSql());

                    $query->where('(content.publish_up = '.$nullDate.' OR content.publish_up <= '.$nowDate.' OR content.publish_up IS NULL)')
                        ->where('(content.publish_down = '.$nullDate.' OR content.publish_down >= '.$nowDate.' OR content.publish_down IS NULL)');

                    $user = JFactory::getUser();
                    //access
                    $groups = implode(',', $user->getAuthorisedViewLevels());
                    $query->where('content.access IN (' . $groups . ')');
                    $this->_tagBuildSortQuery($query,$params);
                    $db->setQuery($query,0,$limit);
                    $list = $db->loadObjectList();

                    foreach ($list as $product){
                        $product_ids[] = $product->j2store_product_id;

                    }
                }
                break;
            case 'category': // get the product ids from the categories selected in module params

                $product_ids = array();
                $integration = $params->get('content_integration','joomla');
                // check if file exists and class exists and include the product source file
                $class_path = JPATH_SITE.'/modules/mod_j2products/library/source/'.$integration.'.php' ;
                $product_source_class = 'ProductSource'.ucfirst($integration);

                if (file_exists($class_path) ) {
                    require_once JPATH_SITE.'/modules/mod_j2products/library/source/'.$integration.'.php';
                }elseif(!class_exists($product_source_class)) {
                    return $product_ids ;
                }
                $product_source = new $product_source_class();
                $product_ids = $product_source->getProductIdsByCategory( $params );
                break;
            case 'selected_products': // get the product ids from the selected list
                $limit = $params->get('number_of_items',6);
                $params_product_ids = $params->get('product_ids','');
                $product_ids = $params_product_ids;
                if(!empty($product_ids)){
                    if(!is_array($product_ids)){
                        $product_ids = explode(',',$product_ids);
                    }
                    $db = JFactory::getDbo();
                    $query = $db->getQuery(true);
                    $query->select('product.j2store_product_id')->from('#__j2store_products as product')
                        ->join('LEFT','#__content as content ON content.id = product.product_source_id')
                        ->where('product.product_source ='.$db->q('com_content'))
                        ->where('product.j2store_product_id IN('.implode(',',$product_ids).')');
                    // Define null and now dates
                    $nullDate	= $db->quote($db->getNullDate());
                    //$nowDate	= $db->quote(JFactory::getDate()->toSql());
                    $tz = JFactory::getConfig()->get('offset');
                    $date = JFactory::getDate('now', $tz);

                    //default to the sql formatted date
                    $nowDate = $db->quote( $date->toSql());

                    $query->where('(content.publish_up = '.$nullDate.' OR content.publish_up <= '.$nowDate.' OR content.publish_up IS NULL)')
                        ->where('(content.publish_down = '.$nullDate.' OR content.publish_down >= '.$nowDate.' OR content.publish_down IS NULL)');

                    $user = JFactory::getUser();
                    //access
                    $groups = implode(',', $user->getAuthorisedViewLevels());
                    $query->where('content.access IN (' . $groups . ')');
                    $db->setQuery($query,0,$limit);
                    $product_ids = $db->loadColumn();
                }

                /*if ( !empty($params_product_ids) ) {
                    $product_ids = explode(',', $params_product_ids);
                }*/
                // remove duplicates
                if(!empty($product_ids) && is_array($product_ids)){
                    $product_ids = array_unique($product_ids);
                    $product_ids = array_slice($product_ids, 0, $limit+1);
                }
                break;
            case 'best_selling': // get the product ids of best selling products
                $db = JFactory::getDBO();
                $query = $db->getQuery(true);
                $query->select('a.product_id')
                    ->from('( SELECT product_id, count( product_id ) bestsell_count
											FROM #__j2store_orderitems
											GROUP BY product_id
											ORDER BY bestsell_count DESC ) as a');
                $limit = $params->get('number_of_items',6);
                $query->select('#__j2store_productprice_index.min_price');
                $query->select('#__j2store_productprice_index.max_price');

                $query->join('LEFT','#__j2store_products as product ON product.j2store_product_id = a.product_id');
                $query->join('LEFT','#__content as content ON content.id = product.product_source_id AND product.product_source='.$db->q('com_content'));
                $query->join('LEFT OUTER', '#__j2store_productprice_index ON  a.product_id=#__j2store_productprice_index.product_id');
                $query->join('INNER', '#__j2store_variants ON a.product_id=#__j2store_variants.product_id');
                $query->where(
                    $db->qn('#__j2store_variants').'.'.$db->qn('is_master').' = '.$db->q(1)
                );
                $query->where(
                    $db->qn('#__j2store_variants').'.'.$db->qn('is_master').' = '.$db->q(1)
                );

                $user = JFactory::getUser();
                //access
                $groups = implode(',', $user->getAuthorisedViewLevels());
                $query->where('content.state = 1');
                $query->where('content.access IN (' . $groups . ')');

                $this->_sfBuildSortQuery($query, $params);
                $query->group('a.product_id');
                //$query->groupby('a.product_id');
                $db->setQuery( $query, 0, $limit );
                $product_ids = $db->loadColumn();
                break;
            case 'up_sells': // get the product ids from upsells of the current product

            case 'cross_sells': // get the product ids from cross sells of the current product

            case 'related_products': // get the product ids of both upsells and cross sells of current product or for the product in the cart

            case 'related_buys': // get the product ids from the related purchases of the current product

            default:
                // by default show some products
                $product_ids = array();
                break;
        }
        return $product_ids;
    }

    function _sfBuildSortQuery(&$query,$params) {

        $sort_by = $params->get('sort_by','');
        if ($sort_by) {

            $sortby = '';
            switch ($sort_by) {
                case 'asc':
                    $sortby = 'a.product_id ASC';
                    break;
                case 'desc':
                    $sortby = 'a.product_id DESC';
                    break;
                case 'min_price' :
                    $sortby = '#__j2store_variants.price ASC';
                    break;
                case 'rmin_price' :
                    $sortby = '#__j2store_productprice_index.min_price DESC';
                    break;

                case 'sku' :
                    $sortby = '#__j2store_variants.sku ASC';
                    break;

                case 'rsku' :
                    $sortby = '#__j2store_variants.sku DESC';
                    break;

                case 'random' :
                    $sortby = JFactory::getDbo()->getQuery(true)->Rand().' ASC';
                    break;
            }

            if(!empty($sortby)) {
                $query->order ( $sortby );
            }
        }
    }

    function _tagBuildSortQuery(&$query,$params) {

        $sort_by = $params->get('sort_by','');
        if ($sort_by) {

            $sortby = '';
            switch ($sort_by) {
                case 'asc':
                    $sortby = 'product.j2store_product_id ASC';
                    break;
                case 'desc':
                    $sortby = 'product.j2store_product_id DESC';
                    break;
                case 'min_price' :
                    $sortby = 'price_index.min_price ASC';
                    break;
                case 'rmin_price' :
                    $sortby = 'price_index.min_price DESC';
                    break;

                case 'sku' :
                    $sortby = '#__j2store_variants.sku ASC';
                    break;

                case 'rsku' :
                    $sortby = '#__j2store_variants.sku DESC';
                    break;

                case 'art_asc' :
                    $sortby = 'content.ordering ASC';
                    break;

                case 'art_desc' :
                    $sortby = 'content.ordering DESC';
                    break;

                case 'random' :
                    $sortby = JFactory::getDbo()->getQuery(true)->Rand().' ASC';
                    break;
            }

            if(!empty($sortby)) {
                $query->order ( $sortby );
            }
        }
    }

    /**
     * Method to prepare the products and return in a list
     * @param 	array 	$product_ids 	product ids
     * @return 	list            		list of product objects
     * */
    function prepareProducts($params,$product_ids){
        //static $sets=array();

        $sets=array();
        // prapare a hash of product ids and load in static set
        $product_ids_src = $product_ids ;
        sort($product_ids_src);
        $hash = implode('.',$product_ids_src);
        // if already present in set, return the data
        if ( isset($sets[$hash]) ) {
            return $sets[$hash];
        }

        $product_helper = J2Store::product();

        $integration = $params->get('content_integration','joomla');
        $class_path = JPATH_SITE.'/modules/mod_j2products/library/source/'.$integration.'.php' ;
        $product_source_class = 'ProductSource'.ucfirst($integration);
        $product_source_obj = '';
        if (file_exists($class_path) && !class_exists($product_source_class) ) {
            require_once JPATH_SITE.'/modules/mod_j2products/library/source/'.$integration.'.php';
        }
        if ( class_exists($product_source_class) ) {
            $product_source_obj = new $product_source_class();
        }
        $list = array();
        JPluginHelper::importPlugin('j2store');
        $dispatcher = J2Store::platform()->application();
        JPluginHelper::importPlugin('content');
        foreach ($product_ids as $k => $pid) {
            // prepare the product
            $prod_table = F0FTable::getAninstance('Product', 'J2StoreTable')->getClone();
            $prod_table->load($pid);

            // check if the item exists
            if ( isset($prod_table->j2store_product_id) && $prod_table->j2store_product_id > 0 ) {
                $product = $product_helper->setId( $prod_table->j2store_product_id)->getProduct();
                F0FModel::getTmpInstance('Products', 'J2StoreModel')->runMyBehaviorFlag(true)->getProduct($product);
                if($product->product_source == 'com_content'){
                    /*$product->show_feature_only 		= $params->get('show_feature_only',0);

                    if($product->show_feature_only){
                        if($product->source->featured == 0){
                            continue;
                        }
                    }*/

                    $results = $dispatcher->triggerEvent('onContentBeforeDisplay', array('com_content.category.productlist', &$product->source, &$params));
                    $beforedisplay = '';
                    if(!empty( $results )){
                        $beforedisplay = trim(implode("\n", $results));
                    }
                    $product->event = new stdClass();
                    $product->event->beforeDisplayContent = $beforedisplay;
                    $afterdisplay = '';
                    $result = $dispatcher->triggerEvent('onContentAfterDisplay', array('com_content.category.productlist', &$product->source, &$params));
                    if(!empty( $result )){
                        $afterdisplay = trim(implode("\n", $result));
                    }
                    $product->event->afterDisplayContent = $afterdisplay;

                }


                // prepare the product to have all the show flags, links, titles, data needed to be displayed in the module layout
                $product->show_title 		= $params->get('show_title',1);
                $product->link_title 		= $params->get('link_title',1);
                $product->show_category		= $params->get('show_category',0);
                $product->show_sku			= $params->get('show_sku',0);

                $product->show_price		= $params->get('show_price',1);
                $product->show_price_taxinfo= $params->get('show_price_taxinfo',1);
                $product->show_special_price= $params->get('show_special_price',1);
                $product->show_offers		= $params->get('show_offers',1);
                $product->show_stock		= $params->get('show_stock',0);

                $product->list_show_cart	= $params->get('list_show_cart',1);
                /*$product->show_options		= $params->get('show_options',0);
                $product->show_cart			= $params->get('show_cart',1);*/
                $product->show_introtext	= $params->get('show_introtext',1);
                $product->introtext_limit	= $params->get('introtext_limit',50);
                $product->show_readmore		= $params->get('show_readmore',1);
                $product->show_quickview	= $params->get('show_quickview',0);

                //echo "<pre>";print_r($product->show_quickview);exit;
                if($product->show_quickview){
                    $document = JFactory::getDocument();
                    $document->addStyleSheet(rtrim(JURI::root(true),'/').'/modules/mod_j2products/library/assets/jquery.fancybox.min.css');
                    $document->addScript(rtrim(JURI::root(true),'/').'/modules/mod_j2products/library/assets/jquery.fancybox.min.js');
                }

                $product->show_image		= $params->get('show_image',1);
                $product->link_image		= $params->get('link_image',1);
                $product->image_type		= $params->get('image_type','thumbimage');
                $product->image_size_width	= $params->get('image_size_width',80);
                $product->image_size_height	= $params->get('image_size_height',80);
                $product->image_position	= $params->get('image_position','left');

                $product->show_navigation	= $params->get('show_navigation',0);
                $product->show_pagination	= $params->get('show_pagination',0);
                $product->show_beforedisplaycontent = $params->get('show_beforedisplaycontent',1);
                $product->show_afterdisplaycontent = $params->get('show_afterdisplaycontent',1);

                // define below properties on prepare product
                $product->content_link = '';
                $product->content_image = '';
                $product->category_name = '';
                $product->category_link = '';

                if ( !empty($product_source_obj) ) {
                    $product_source_obj->prepareProduct( $params, $product);
                }
                $menu_id = $params->get('menu_id','');

                $itemid = '';
                if($menu_id){
                    $itemid = '&Itemid='.$menu_id;
                }
                $platform = J2Store::platform();

                $product->module_display_link = $platform->getProductUrl(array('task' => 'view','id' => $product->j2store_product_id.$itemid ),false);
                //$product->module_display_link = JRoute::_('index.php?option=com_j2store&view=products&task=view&id='.$product->j2store_product_id.$itemid);//$product->product_link;
                $product_tag_link = $params->get('product_source_type','category');
                if($product_tag_link == 'product_tag'){
                    $app = J2Store::platform()->application();
                    $menus = $app->getMenu();

                    $db = JFactory::getDbo ();
                    $query = $db->getQuery (true);
                    $query->select ( 'tag.alias' )->from('#__contentitem_tag_map AS c_tag')
                        ->join ( 'LEFT', '#__tags AS tag ON c_tag.tag_id = tag.id'  )
                        ->where ( 'c_tag.content_item_id ='.$db->q($product->product_source_id) );
                    $db->setQuery ( $query );
                    $tags =  $db->loadObjectList ();
                    $tag_list = array();
                    foreach ($tags as $tag){
                        $tag_list[] = $tag->alias;
                    }
                    $ex_menu_id = 0;
                    foreach ($menus->getMenu() as $menu){
                        // find item id related to product id
                        if(isset($menu->type) && isset($menu->query['option']) && isset($menu->query['view']) && isset($menu->query['tag'])
                            && $menu->type == 'component' && $menu->query['option'] == 'com_j2store' && $menu->query['view']=='producttags'){
                            if(in_array($menu->query['tag'],$tag_list)){
                                $ex_menu_id = $menu->id;
                                break;
                            }
                        }
                    }
                    $itemid = '';
                    if($ex_menu_id > 0){
                        $itemid = '&Itemid='.$ex_menu_id;
                    }
                    $product->module_display_link = $platform->getProductUrl(array('task' => 'view','id' => $product->j2store_product_id.$itemid ),false);
                  //  $product->module_display_link = JRoute::_('index.php?option=com_j2store&view=producttags&task=view&id='.$product->j2store_product_id.$itemid);//$product->product_link;
                }
                $product->module_introtext = self::truncate($product->product_short_desc, $product->introtext_limit); // truncated intro text
                if(!empty($product->addtocart_text)) {
                    $product->cart_button_text = JText::_($product->addtocart_text);
                } else {
                    $product->cart_button_text = JText::_('J2STORE_ADD_TO_CART');
                }

                if($product->variant->availability || J2Store::product()->backorders_allowed($product->variant)) {
                    $product->display_cart_block = true;
                } else {
                    $product->display_cart_block = false;
                }

                if($product->product_type == 'variable') {
                    $product->display_cart_block = true;
                }

                if( isset($product) && $product->enabled && $product->visibility ){
                    $list[$pid] = $product ;
                }
            }
            $prod_table->reset();
        }
        //exit;
        $sets[$hash] = $list ;
        return $sets[$hash];
    }

    ///////////////////// 	Utility functions 	////////////////////////

    /**
     * Method to truncate introtext
     *
     * The goal is to get the proper length plain text string with as much of
     * the html intact as possible with all tags properly closed.
     *
     * @param string   $html       The content of the introtext to be truncated
     * @param integer  $maxLength  The maximum number of charactes to render
     *
     * @return  string  The truncated string
     */
    public static function truncate($html, $maxLength = 0)
    {
        $baseLength = strlen($html);
        $diffLength = 0;

        // First get the plain text string. This is the rendered text we want to end up with.
        $ptString = JHtml::_('string.truncate', $html, $maxLength, $noSplit = true, $allowHtml = false);

        for ($maxLength; $maxLength < $baseLength;)
        {
            // Now get the string if we allow html.
            $htmlString = JHtml::_('string.truncate', $html, $maxLength, $noSplit = true, $allowHtml = true);

            // Now get the plain text from the html string.
            $htmlStringToPtString = JHtml::_('string.truncate', $htmlString, $maxLength, $noSplit = true, $allowHtml = false);

            // If the new plain text string matches the original plain text string we are done.
            if ($ptString == $htmlStringToPtString)
            {
                return $htmlString;
            }
            // Get the number of html tag characters in the first $maxlength characters
            $diffLength = strlen($ptString) - strlen($htmlStringToPtString);

            // Set new $maxlength that adjusts for the html tags
            $maxLength += $diffLength;
            if ($baseLength <= $maxLength || $diffLength <= 0)
            {
                return $htmlString;
            }
        }
        return $html;
    }

    public static function endsWith($haystack, $needle) {
        // search forward starting from end minus needle length characters
        return $needle === "" || (($temp = strlen($haystack) - strlen($needle)) >= 0 && strpos($haystack, $needle, $temp) !== FALSE);
    }
}
