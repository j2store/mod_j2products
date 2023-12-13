<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_j2products
 * @author      Gopi
 * @copyright   Copyright (C) 2023 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
class Mod_j2productsInstallerScript {
    function preflight( $type, $parent ) {
        $app = \Joomla\CMS\Factory::getApplication();
        if (version_compare(JVERSION, '3.99.99', 'lt')) {
            $app->enqueueMessage("You are using an old version of Joomla. This module requires Joomla 4.0.0 or later.");
            return false;
        }
        if(!\Joomla\CMS\Component\ComponentHelper::isEnabled('com_j2store')) {
            $app->enqueueMessage( 'J2Store not found. Please install J2Store before installing this module');
            return false;
        }

        $version_file = JPATH_ADMINISTRATOR.'/components/com_j2store/version.php';
        if (\Joomla\CMS\Filesystem\File::exists ( $version_file )) {
            require_once($version_file);
            // abort if the current J2Store release is older
            if (version_compare(J2STORE_VERSION, '4.0.3', 'lt')) {
                $app->enqueueMessage( 'You are using an old version of J2Store. Please upgrade to the latest version 4.0.3');
                return false;
            }
        } else {
            $app->enqueueMessage( 'J2Store not found or the version file is not found. Make sure that you have installed J2Store before installing this module' );
            return false;
        }

        $db = JFactory::getDbo();
        //get the table list
        $tables = $db->getTableList();
        //get prefix
        $prefix = $db->getPrefix();

        //address
        if(in_array($prefix.'extensions', $tables)){
            $fields = $db->getTableColumns('#__extensions');
            if (!array_key_exists('folder', $fields)) {
                $query = "ALTER TABLE #__j2store_addresses ADD `orderfile_upload_id` text NOT NULL DEFAULT AFTER `company`";
                $query= "UPDATE #__extensions SET 'folder'='j2store' WHERE 'extension_id'=  ";
                $this->_executeQuery($query);
            }
        }
    }
    private function _executeQuery($query) {

        $db = JFactory::getDbo();
        $db->setQuery($query);
        try {
            $db->execute();
        }catch (Exception $e) {
            //do nothing. we dont want to fail the install process.
        }


    }
}
