<?php
/**
* @package 		J2Store
* @copyright 	Copyright (c)2016-19 Sasi varna kumar / J2Store.org
* @license 		GNU GPL v3 or later
*/
defined('_JEXEC') or die;
jimport( 'joomla.application.module.helper' );
require_once (JPATH_ADMINISTRATOR.'/components/com_j2store/version.php');
require_once (JPATH_ADMINISTRATOR.'/components/com_j2store/helpers/j2store.php');
require_once (JPATH_SITE.'/modules/mod_j2products/helper.php');
require_once (JPATH_ADMINISTRATOR.'/components/com_j2store/helpers/strapper.php');
J2StoreStrapper::addJS();
JFactory::getLanguage ()->load ('com_j2store', JPATH_ADMINISTRATOR);
$document =JFactory::getDocument();
//$document->addScript(JURI::root(true).'/media/j2store/js/j2store.js');
$document->addStyleSheet(JURI::root(true).'/media/j2store/css/j2store.css');
$subTemplate = $params->get('module_subtemplate', 'Default');

// add additional CSS and JS files associated with the layout
ModJ2ProductsHelper::includeAssets($params);
//$document->addScript(JURI::root(true).'/modules/mod_j2products/tmpl/Carousel/owl.carousel.js');

if (!defined('F0F_INCLUDED'))
{
	include_once JPATH_LIBRARIES . '/f0f/include.php';
}

$j2params 	= J2Store::config();
$j2currency	= J2Store::currency();
$J2gridRow = ($j2params->get('bootstrap_version', 2) == 2) ? 'row-fluid' : 'row';
$J2gridCol = ($j2params->get('bootstrap_version', 2) == 2) ? 'span' : 'col-md-';
$list = ModJ2ProductsHelper::getList($params);
$moduleclass_sfx = htmlspecialchars($params->get('moduleclass_sfx'));
$module_id = $module->id;  // module id
require JModuleHelper::getLayoutPath('mod_j2products', $subTemplate.'/default');