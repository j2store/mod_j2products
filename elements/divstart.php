<?php
/**
* @package J2Store
* @copyright Copyright (c)2016-19 Sasi varna kumar / J2Store.org
* @license GNU GPL v3 or later
*/
// no direct access
defined('_JEXEC') or die ;

class JFormFieldDivStart extends JFormField 
{
	protected $type = 'divstart';
	
	public function getInput() {
		$script = '';
		$script .= '<script>';
		$script .= '
		function modj2prod_showSettings(id){
			(function($) {
				$(".source").parent().parent().hide();
				$("."+id).parent().parent().show();
			})(jQuery.noConflict());
		} 
		jQuery("#jform_params_product_source_type").css("display","block");
		
		';
		$script .= '</script>';
		return $script;
	}
	
	public function getLabel() {
		return '';
	}
}