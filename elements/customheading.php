<?php
/**
* @package J2Store
* @copyright Copyright (c)2016-19 Sasi varna kumar / J2Store.org
* @license GNU GPL v3 or later
*/
// no direct access
defined('_JEXEC') or die ;

class JFormFieldCustomHeading extends JFormField 
{
	protected $type = 'customheading';
	
	public function getInput() {
		
		$label = '';
		$label .= $this->getTitle();
		return '<div  class="'.$this->class.'" style="background: #d5e7fa none repeat scroll 0 0; border-bottom: 2px solid #96b0cb; clear: both; color: #369; float: left; font-size: 12px; font-weight: bold; margin: 12px 0 4px; padding: 0;width: 100%;">'
				.'<div style="padding: 6px 8px;" >'.JText::_($label).'</div>'
				.'<div style="background: rgba(0, 0, 0, 0) none repeat scroll 0 0; border: medium none; clear: both; float: none; height: 0; line-height: 0; margin: 0; padding: 0;"></div></div>';

		return  $html;
	}
	
	public function getLabel() {
		return '';
	}
}