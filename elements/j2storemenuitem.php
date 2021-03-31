<?php
/*------------------------------------------------------------------------
 # mod_j2store_categories
# ------------------------------------------------------------------------
# author    Gokila priya - Weblogicx India http://www.weblogicxindia.com
# copyright Copyright (C) 2014 - 19 Weblogicxindia.com. All Rights Reserved.
# @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
# Websites: http://j2store.org
# Technical Support:  Forum - http://j2store.org/forum/index.html
-------------------------------------------------------------------------*/
// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );
jimport('joomla.form.formfield');
class JFormFieldJ2StoreMenuItem extends JFormField
{

	protected $type = 'J2storemenuitem';
	/**
	 * Method to get the field input markup.
	 *
	 * @return  string	The field input markup.
	 * @since   1.6
	 */
	protected function getInput()
	{
		$app = JFactory::getApplication();
		$options = array();
		$module_id = $app->input->getInt('id');
		$menus =JMenu::getInstance('site');
		$menu_id = null;
		$menuItems = array();
		$options[''] = JText::_('J2STORE_OPTION_SELECT');
		foreach($menus->getMenu() as $item)
		{
			if($item->type== 'component'){
				if(isset($item->query['option']) && $item->query['option'] == 'com_j2store' ){
					if(isset($item->query['catid'])){
						$options[$item->id] = $item->title;
					}
				}
			}
		}
	 return JHTML::_('select.genericlist', $options, $this->name, array('class'=>"input"), 'value', 'text', $this->value);
	}

}

