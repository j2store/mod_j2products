<?php
/**
* @package J2Store
* @copyright Copyright (c)2016-19 Sasi varna kumar / J2Store.org
* @license GNU GPL v3 or later
*/
// no direct access
defined('_JEXEC') or die ;

class JFormFieldDivEnd extends JFormField 
{
	protected $type = 'divend';
	
	public function getInput() {
		return '</div>';
	}
	
	public function getLabel() {
		return '';
	}
}