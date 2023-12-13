<?php
/**
 * @package 		J2Store
 * @copyright 	Copyright (c)2016-19 Sasi varna kumar / J2Store.org
 * @license 		GNU GPL v3 or later
 */
defined('_JEXEC') or die;

class ProductSourceJoomla {

    private $type = 'joomla';
    /**
     * check table is available
     * @param $string - table name
     * @param $force - force check
     * @return boolean
     */
    function checkTable($string='multicats_content_catid',$force=false){
        static $sets;

        if (! is_array ( $sets )) {
            $sets = array ();
        }
        if (! isset ( $sets [$string] ) || $force) {

            $db = JFactory::getDBo();
            $tables = $db->getTableList ();
            $prefix = $db->getPrefix ();
            if (in_array ( $prefix . $string, $tables )) {
                $sets [$string] = true;
            }else{
                $sets [$string] = false;
            }

            if(JComponentHelper::isInstalled('com_multicats')) {

                if(!JComponentHelper::isEnabled('com_multicats'))
                {
                    $sets [ $string ] = false;
                }

            }else {
                $sets [ $string ] = false;
            }

        }
        return $sets [$string];
    }

    function getProductIdsByCategory( $module_params ){

        $params = $module_params ;
        $cat_ids = $params->get('catids','');
        $include_subcats = $params->get('include_subcategories',0); // do not include subcategories by default
        $include_subcat_level = $params->get('include_subcat_level',0);
        $show_feature_only = $params->get('show_feature_only',0);
        $product_ids = array();

        if(empty($cat_ids)){
            return $product_ids;
        }
        if(!is_array($cat_ids)) {
            $cat_ids = (array) $cat_ids;
        }

        if (!empty($cat_ids)){
            $db = JFactory::getDBO();
            $query = $db->getQuery(true);

            $levels = (int) $include_subcat_level;
            $query->select('p.j2store_product_id');
            $query->from('#__j2store_products as p');
            $query->join('LEFT','#__content as a ON a.id=p.product_source_id AND p.product_source='.$db->q('com_content') );
            if ($this->checkTable () ) {
                $query->select('mc.catid as mc_catid')->join('LEFT', '#__multicats_content_catid AS mc ON mc.item_id = a.id');
            }
            $query->where( 'a.state=1' );
            if($show_feature_only){
                $query->where( 'a.featured=1' );
            }

            if(!in_array('*', $cat_ids)) {
                J2Store::platform()->toInteger($cat_ids);
                //$cat_ids = '[[:<:]]'. implode('[[:>:]]|[[:<:]]', $cat_ids) .'[[:>:]]';
                if(version_compare($db->getConnection()->server_info,'7.9.9','>')){
                    $cat_ids = '\\b'. implode('\\b|\\b', $cat_ids) .'\\b';
                }else{
                    $cat_ids = '[[:<:]]'. implode('[[:>:]]|[[:<:]]', $cat_ids) .'[[:>:]]';
                }

                $type = ' ';
                if ($this->checkTable () ) {
                    $categoryEquals = 'mc.catid ' . $type . ' REGEXP BINARY '. $db->q($cat_ids) ;
                }else{
                    $categoryEquals = 'a.catid ' . $type . ' REGEXP BINARY '. $db->q($cat_ids) ;
                }


                /*$categoryEquals  = 'a.catid IN ( ' . implode(',', $cat_ids).' )'  ;
                $cat_ids_regexp = '[[:<:]]'. implode('[[:>:]]|[[:<:]]', $cat_ids) .'[[:>:]]';
                $categoryEquals = 'a.catid REGEXP BINARY '. $db->q($cat_ids_regexp) ;*/

                if ($include_subcats)
                {
                    if ($this->checkTable () ) {
                        $sub_categoryEquals = 'mc.catid ' . $type . ' REGEXP BINARY '. $db->q($cat_ids) ;
                    }else{
                        $sub_categoryEquals = 'this.id ' . $type . ' REGEXP BINARY '. $db->q($cat_ids) ;
                    }
                    // Create a subquery for the subcategory list
                    $subQuery = $db->getQuery(true)
                        ->select('sub.id')
                        ->from( '#__categories as sub');
                    if ($this->checkTable () ) {
                        $subQuery->select('mc.catid as mc_catid')->join('LEFT', '#__multicats_content_catid AS mc ON mc.item_id = sub.id');
                    }
                    $subQuery->join('INNER', '#__categories as this ON sub.lft > this.lft AND sub.rgt < this.rgt')
                        ->where($sub_categoryEquals);

                    if ($levels >= 0)
                    {
                        $subQuery->where('sub.level <= (this.level + ' . $db->q($levels).')');
                    }
                    $db->setQuery($subQuery);
                    $sub_data = $db->loadAssocList();
                    $sub_cats = array();
                    foreach($sub_data as $k=> $sub_cat){
                        $sub_category = explode(',',$sub_cat['id']);
                        foreach ($sub_category as $sub_cat_id){
                            $sub_cats [] = $sub_cat_id;
                        }
                    }
                    if(!empty($sub_cats)){
                        if(version_compare($db->getConnection()->server_info,'7.9.9','>')){
                            $regSubcats = '\\b'. implode('\\b|\\b', $sub_cats) .'\\b';
                        }else{
                            $regSubcats = '[[:<:]]'. implode('[[:>:]]|[[:<:]]', $sub_cats) .'[[:>:]]';
                        }
                        $subCategoryEquals = 'a.catid ' . $type . ' REGEXP BINARY '. $db->q($regSubcats) ;
                        // Add the subquery to the main query
                        $query->where('(' . $categoryEquals . ' OR '.$subCategoryEquals.' )');
                    }else{
                        $query->where($categoryEquals);
                    }
                }
                else
                {
                    $query->where( $categoryEquals );
                }
            }
            $limit = $params->get('number_of_items',6);
            $query->select('#__j2store_productprice_index.min_price');
            $query->select('#__j2store_productprice_index.max_price');
            $query->join('LEFT OUTER', '#__j2store_productprice_index ON  p.j2store_product_id=#__j2store_productprice_index.product_id');
            $query->join('INNER', '#__j2store_variants ON p.j2store_product_id=#__j2store_variants.product_id');
            $query->where(
                $db->qn('#__j2store_variants').'.'.$db->qn('is_master').' = '.$db->q(1)
            );
            $query->select('CASE p.product_type
						WHEN "variable" THEN
							#__j2store_productprice_index.min_price
						ELSE
							#__j2store_variants.price
						END as min_price
			');
            $query->where(
                $db->qn('p').'.'.$db->qn('visibility').' = '.$db->q(1)
            );
            $query->where(
                $db->qn('p').'.'.$db->qn('enabled').' = '.$db->q(1)
            );


            // Define null and now dates
            $nullDate	= $db->quote($db->getNullDate());
            //$nowDate	= $db->quote(JFactory::getDate()->toSql());
            $tz = JFactory::getConfig()->get('offset');
            $date = JFactory::getDate('now', $tz);

            //default to the sql formatted date
            $nowDate = $db->quote( $date->toSql());

            $query	->where('(a.publish_up = '.$nullDate.' OR a.publish_up <= '.$nowDate.' OR a.publish_up IS NULL)')
                ->where('(a.publish_down = '.$nullDate.' OR a.publish_down >= '.$nowDate.' OR a.publish_down IS NULL)');

//            $query->where('(a.publish_up = '.$nullDate.' OR a.publish_up <= '.$nowDate.')')
//                ->where('(a.publish_down = '.$nullDate.' OR a.publish_down >= '.$nowDate.')');
           // echo "<pre>";print_r($query);
            $user = JFactory::getUser();
            //access
            $groups = implode(',', $user->getAuthorisedViewLevels());
            $query->where('a.access IN (' . $groups . ')');
            //echo "<pre>";print_r($params);exit;
            $this->_sfBuildSortQuery($query, $params);
            $db->setQuery( $query, 0, $limit );
            $product_ids = $db->loadColumn();
        }

        return $product_ids;
    }

    function _sfBuildSortQuery(&$query,$params) {

        $sort_by = $params->get('sort_by','');
        if ($sort_by) {

            $sortby = '';
            switch ($sort_by) {
                case 'asc':
                    $sortby = 'p.j2store_product_id ASC';
                    break;
                case 'desc':
                    $sortby = 'p.j2store_product_id DESC';
                    break;
                case 'min_price' :
                    $sortby = 'min_price ASC';
                    break;
                case 'rmin_price' :
                    $sortby = 'min_price DESC';
                    break;

                case 'sku' :
                    $sortby = '#__j2store_variants.sku ASC';
                    break;

                case 'rsku' :
                    $sortby = '#__j2store_variants.sku DESC';
                    break;

                case 'art_asc' :
                    $sortby = 'a.ordering ASC';
                    break;

                case 'art_desc' :
                    $sortby = 'a.ordering DESC';
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


    function prepareProduct( $module_params, &$product ){
        // after title, content events, product links
        if (isset($product->source) && isset($product->source->category_title)) {
            $product->category_name = $product->source->category_title;
        }
    }

    function getCategoryList(){
        // get the list of categories to select in params
    }

    /**
     * Method to get the category table name
     * TODO: add support for other component categories like zoo, dj catlog, easyblog, rs events, sobipro
     * */
    function getTableData(){

        $table = new stdClass();
        $table->category_key_field 	= 'id';
        $table->category_name_field = 'title';
        $table->category_table_name = '#__categories';
        $table->item_key_field 		= 'id';
        $table->item_name_field 	= 'title';
        $table->item_table_name 	= '#__content';
        $table->item_cat_rel_field 	= 'catid';
        $table->item_cat_rel_table 	= '#__content';

        return $table;
    }

}