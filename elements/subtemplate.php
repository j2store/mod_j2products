<?php
/**
* @package J2Store
* @copyright Copyright (c)2016-19 Sasi varna kumar / J2Store.org
* @license GNU GPL v3 or later
*/
// no direct access
defined('_JEXEC') or die ;

jimport('joomla.html.html');
jimport('joomla.form.formfield');
jimport('joomla.form.helper');
jimport('joomla.filesystem.folder');
jimport('joomla.filesystem.file');
JFormHelper::loadFieldClass('list');

class JFormFieldSubTemplate  extends JFormFieldList  {
    protected $type = 'subtemplate';

    public function getInput(){
        $moduleName = $this->element->attributes()->modulename;
        $moduleTemplatesPath = JPATH_SITE.DS.'modules'.DS.$moduleName.DS.'tmpl';
        $moduleTemplatesFolders = JFolder::folders($moduleTemplatesPath);


        $db = JFactory::getDBO();
        $query = "SELECT template FROM #__template_styles WHERE client_id = 0 AND home = 1";
        $db->setQuery($query);
        $defaultemplate = $db->loadResult();
        $templatePath = JPATH_SITE.DS.'templates'.DS.$defaultemplate.DS.'html'.DS.$moduleName;

        if (JFolder::exists($templatePath))
        {
            $templateFolders = JFolder::folders($templatePath);
            $folders = @array_merge($templateFolders, $moduleTemplatesFolders);
            $folders = @array_unique($folders);
        }
        else
        {
            $folders = $moduleTemplatesFolders;
        }

        $exclude = 'Default';
        $options = array();

        foreach ($folders as $folder)
        {
            if (preg_match(chr(1).$exclude.chr(1), $folder))
            {
                continue;
            }
            $options[] = JHTML::_('select.option', $folder, $folder);
        }

        array_unshift($options, JHTML::_('select.option', 'Default', '-- '.JText::_('MOD_J2PRODUCTS_USE_DEFAULT').' --'));

        $fieldName = $this->name;
        return JHTML::_('select.genericlist', $options, $fieldName, 'class="inputbox"', 'value', 'text', $this->value, $this->name);

    }
}