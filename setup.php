<?php
/**
 -------------------------------------------------------------------------
  LICENSE

 This file is part of openMEDIS plugin for GLPI.

 Reports is free software: you can redistribute it and/or modify
 it under the terms of the GNU Affero General Public License as published by
 the Free Software Foundation, either version 3 of the License, or
 (at your option) any later version.

 openMEDIS is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 GNU Affero General Public License for more details.

 You should have received a copy of the GNU Affero General Public License
 along with Reports. If not, see <http://www.gnu.org/licenses/>.

 @package   openmedis
 @authors   Patrick Delcroix
 @copyright Copyright (c) 2009-2021 Reports plugin team
 @license   AGPL License 3.0 or (at your option) any later version
            http://www.gnu.org/licenses/agpl-3.0-standalone.html
 @link      https://github.com/delcroip/glpi_plugin_openmedis
 @link      http://www.glpi-project.org/
 @since     2021
 --------------------------------------------------------------------------
 */
// Version of the plugin
define('PLUGIN_OPENMEDIS_VERSION', '1.0.1.rc5');
// Schema version of this version
define('PLUGIN_OPENMEDIS_SCHEMA_VERSION', '1.3');
// is or is not an official release of the plugin
define('PLUGIN_OPENMEDIS_IS_OFFICIAL_RELEASE', false);
// Minimal GLPI version, inclusive
define('PLUGIN_OPENMEDIS_GLPI_MIN_VERSION', '9.4');
// Maximum GLPI version, exclusive
define('PLUGIN_OPENMEDIS_GLPI_MAX_VERSION', '9.5');

define('PLUGIN_OPENMEDIS_ROOT', GLPI_ROOT . '/plugins/openmedis');


function plugin_init_openmedis() {
   global $PLUGIN_HOOKS, $CFG_GLPI;
   //AssetType is a default class
   $CFG_GLPI['glpitablesitemtype']['PluginOpenmedisMedicalDeviceType'] = 'glpi_plugin_openmedis_medicaldevicecategories';
   
  $plugin = new Plugin();   
  $CFG_GLPI['devices_in_menu'][]="pluginOpenmedisMedicalDevice"; 
     //$CFG_GLPI["itemdevices"][]='PluginOpenmedisMedicalAccessory_Item';
   // to check what it means to be CSRF compatible
  $PLUGIN_HOOKS['csrf_compliant']['openmedis']   = true;

  $config = Config::getConfigurationValues('openmedis', ['version']);
  if (isset($config['version']) && $config['version'] != PLUGIN_OPENMEDIS_VERSION) {
     $plugin->getFromDBbyDir('openmedis');
     $plugin->update([
           'id'     => $plugin->getID(),
           'state'  => Plugin::NOTUPDATED
     ]);
  }

  if (!$plugin->getFromDBbyDir('openmedis')) {
      // nothing more to do at this moment
      return;
  }

  if ($plugin->isInstalled('openmedis') && $plugin->isActivated('openmedis')) {

      plugin_openmedis_registerClasses();
      plugin_openmedis_addHooks();
      // load the javascript
      // $PLUGIN_HOOKS['javascript']['openmedis'][]   = '/plugins/openmedis/openmedis.js';

  }
}

/**
 * Register classes
 */

function plugin_openmedis_registerClasses(){
   Plugin::registerClass('PluginOpenmedisDeviceMedicalAccessory', [
         'device_types' => true
         ]);
      // add the type in the config so other module could register 
      //$CFG_GLPI['itemPluginOpenmedisMedicalAccessory_types'] = array();
   Plugin::registerClass('PluginOpenmedisMedicalDevice', [
         'reservation_types' => true, // allow reservation
         'document_types'       => true, // allow docs
         'location_types'       => true, // link by location
         'unicity_types'        => true,
         'linkgroup_tech_types' => true,
         'linkuser_tech_types'  => true, 
         'infocom_types'        => true, // suplier, vbuy date ...
         'ticket_types'         => true, // enable to link to ticket (device> ... )
         'contract_types'       => true, // enable^to link contract
         'report_types'          => true,
         'state_types'           => true,
         'linkuser_types'        => true,  // enable device in Mydevice on ticket
         'itemdevices_types' => true,  // enamble the component left menu
         'networkport_types' => true,
         'itemdevicepowersupply_types' => true,
         // (item.$devicetype)._types https://github.com/glpi-project/glpi/blob/ac76869ab88858c047b4a535e08c32a6dd4d1b0f/inc/item_devices.class.php#L234
         //  devicetype is class name https://github.com/glpi-project/glpi/blob/dc9ff8801377a3fb7c3bf3c9a9337b61eb814982/inc/plugin.class.php#L1298
         'pluginopenmedisitemdevicemedicalaccessory_types' => true,
         "asset_types" => true,
         "kb_types" => true
   ]); 

   Plugin::registerClass('PluginOpenmedisMedicalDeviceModel', ['dictionnary_types' => true]);
   Plugin::registerClass('PluginOpenmedisMedicalDeviceCategory', ['dictionnary_types' => true]);
   class_alias('PluginOpenmedisMedicalDeviceCategory','PluginOpenmedisMedicalDeviceType');

   Plugin::registerClass('PluginOpenmedisItem_DeviceMedicalAccessory');
//   Plugin::registerClass('PluginOpenmedisMedicalAccessoryCategory', ['dictionnary_types' => true]);
   Plugin::registerClass('PluginOpenmedisMedicalAccessoryType', ['dictionnary_types' => true]);

   Plugin::registerClass('PluginOpenmedisProfile', [
         'addtabon' => 'Profile',
   ]); 
   Plugin::registerClass('PluginOpenmedisMedicalConsumable',[
      "infocom_types" => true,
      "consumables_types" => true,]);

   Plugin::registerClass('PluginOpenmedisMedicalConsumableItem_MedicalDeviceModel'); 
   Plugin::registerClass('PluginOpenmedisMedicalConsumableItem', [
      "location_types" => true,
      'contract_types'  => true,
      'link_types'  => true,
      "document_types" => true
   ]); 
   Plugin::registerClass('PluginOpenmedisMedicalConsumableItemType', ['dictionnary_types' => true]); 
}

/**
 * Adds all hooks the plugin needs
 */
function plugin_openmedis_addHooks() {
   global $PLUGIN_HOOKS;
   //load changeprofile function
   $PLUGIN_HOOKS['change_profile']['openmedis']   = [
      'PluginOpenmedisProfile',
      'initProfile'
   ];
   //if glpi is loaded
   //if (Session::getLoginUserID()) {
   //   $PLUGIN_HOOKS['menu']['flyvemdm'] = true;
   //}
   $PLUGIN_HOOKS['post_init']['openmedis'] = 'plugin_openmedis_postinit';
      
   if (Session::getLoginUserID()) {
      if (Session::haveRight(PluginOpenmedisMedicalDevice::$rightname, READ)) {
         $PLUGIN_HOOKS["menu_toadd"]['openmedis'] = ['assets'  => ['PluginOpenmedisMedicalDevice', 'PluginOpenmedisMedicalConsumableItem']];
         $PLUGIN_HOOKS['assign_to_ticket']['openmedis'] = true;
         
      }
      // Notifications
      $PLUGIN_HOOKS['item_get_events']['openmedis'] = []; // TODO event about consumable
      $PLUGIN_HOOKS['item_get_datas']['openmedis'] = [];

      $PLUGIN_HOOKS['use_massive_action']['openmedis'] = 1;
      //If treeview plugin is installed, add rack as a type of item
      //that can be shown in the tree
      if (class_exists('PluginTreeviewConfig')) {
         $PLUGIN_HOOKS['treeview']['PluginOpenmedisMedicalDevice'] = '../openmedis/pics/openmedis_icon.png';
      }
      
   }
  


}





function plugin_version_openmedis() {
   $author = '<a href="https://github.com/delcroip">Patrick Delcroix</a>';
   
   $requirements =  ['name'           => _n('Health technology management', 'Health technologies management' , 1 
                                        , 'openmedis'),
                  'version'        => PLUGIN_OPENMEDIS_VERSION,
                  'license'        => 'GPLv2+',
                  'author'         => $author ,
                  'homepage'       => 'https://github.com/delcroip/glpi_openmedis',
                  'minGlpiVersion' => PLUGIN_OPENMEDIS_GLPI_MIN_VERSION];
   if (PLUGIN_OPENMEDIS_IS_OFFICIAL_RELEASE) {
      // This is not a development version
      $requirements['requirements']['glpi']['max'] = PLUGIN_OPENMEDIS_GLPI_MAX_VERSION;
   }
   return $requirements;
}

// Optional : check prerequisites before install : may print errors or add to message after redirect
function plugin_openmedis_check_prerequisites() {
   $prerequisitesSuccess = true;

   /*if (!is_readable(__DIR__ . '/vendor/autoload.php') || !is_file(__DIR__ . '/vendor/autoload.php')) {
      echo "Run composer install --no-dev in the plugin directory<br>";
      $prerequisitesSuccess = false;
   }*/

   if (version_compare(GLPI_VERSION, PLUGIN_OPENMEDIS_GLPI_MIN_VERSION, 'lt')
       || PLUGIN_OPENMEDIS_IS_OFFICIAL_RELEASE && version_compare(GLPI_VERSION, PLUGIN_OPENMEDIS_GLPI_MAX_VERSION, 'ge')) {
      echo "This plugin requires GLPi >= " . PLUGIN_OPENMEDIS_GLPI_MIN_VERSION . " and GLPI < " . PLUGIN_OPENMEDIS_GLPI_MAX_VERSION . "<br>";
      $prerequisitesSuccess = false;
   }

   return $prerequisitesSuccess;
}

// Uninstall process for plugin : need to return true if succeeded : may display messages or add to message after redirect
function plugin_openmedis_check_config() {
   return true;
}

function plugin_datainjection_migratetypes_openmedis($types) {

   $types[8210] = 'PluginOpenmedisMedicalDevice';
   return $types;
}
