<?php
/*
 * @version $Id: HEADER 15930 2011-10-30 15:47:55Z tsmr $
 -------------------------------------------------------------------------
 openmedis plugin for GLPI
 Copyright (C) 2014-2016 by the openmedis Development Team.

 https://github.com/InfotelGLPI/openmedis
 -------------------------------------------------------------------------

 LICENSE

 This file is part of openmedis.

 openmedis is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 openmedis is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with openmedis. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

function plugin_init_openmedis() {
   global $PLUGIN_HOOKS, $CFG_GLPI;
   // to check what it means to be CSRF compatible
   $PLUGIN_HOOKS['csrf_compliant']['openmedis']   = true;
   //load changeprofile function
   $PLUGIN_HOOKS['change_profile']['openmedis']   = ['PluginOpenMEDISProfile',
                                                                'initProfile'];
   // load the javascript
   $PLUGIN_HOOKS['javascript']['openmedis'][]   = '/plugins/openmedis/openmedis.js';

   $plugin = new Plugin();
   if ($plugin->isInstalled('openmedis') && $plugin->isActivated('openmedis')) {

      //Ability to add a rack to a project
      $CFG_GLPI["project_asset_types"][] = 'PluginOpenMedisMedicalDevice';

      $PLUGIN_HOOKS['assign_to_ticket']['openmedis'] = true;
      // add the tab to the medical device form
      Plugin::registerClass('PluginOpenMedisMedicalDevice',
                            ['document_types'       => true,
                                  'location_types'       => true,
                                  'unicity_types'        => true,
                                  'linkgroup_tech_types' => true,
                                  'linkuser_tech_types'  => true,
                                  'infocom_types'        => true,
                                  'ticket_types'         => true]);
      // add the rights in the profile 
      Plugin::registerClass('PluginOpenMedisProfile',
                            ['addtabon' => 'Profile']);
      // FIXME : register the Medical device as tab in the other modules ????
      $types = ['PluginTreeviewConfig',
                     'PluginPositionsPosition'];     
      foreach ($types as $itemtype) {
         if (class_exists($itemtype)) {
            $itemtype::registerType('PluginOpenMedisMedicalDevice');
         }
      }
      //If treeview plugin is installed, add rack as a type of item
      //that can be shown in the tree
      if (class_exists('PluginTreeviewConfig')) {
         $PLUGIN_HOOKS['treeview']['PluginOpenMedisMedicalDevice'] = '../openmedis/pics/openmedis_icon.png';
      }
      // on user connection ?
      if (Session::getLoginUserID()) {

         include_once (GLPI_ROOT."/plugins/openmedis/inc/medical_device.class.php");
         // add the menu for the user with the right profile
         if (PluginOpenMedisMedicalDevice::canView()) {
            //Display menu entry only if user has right to see it !
            $PLUGIN_HOOKS["menu_toadd"]['openmedis'] = ['assets'  => 'PluginOpenMedisMenu'];
            $PLUGIN_HOOKS['use_massive_action']['openmedis'] = 1;
         }
         // add the config page if the user has the right profile
         /*if (PluginOpenMedisMedicalDevice::canCreate()
            || Config::canUpdate()) {
            $PLUGIN_HOOKS['config_page']['openmedis'] = 'front/config.form.php';
         }
         */

         // load css
         $PLUGIN_HOOKS['add_css']['openmedis']   = "openmedis.css";
         // FIXME: where to find the init 
         $PLUGIN_HOOKS['post_init']['openmedis'] = 'plugin_openmedis_postinit';
         // Add reports 
         /*$PLUGIN_HOOKS['reports']['openmedis']   =
            ['front/report.php' => __("Report - Bays management", "openmedis")];
         */
      }
   }
}

function plugin_version_openmedis() {
   return  ['name'           => _n('Health technologies management',
                                        'Health technologies management',
                                        2, 'openmedis'),
                  'version'        => '0.0.1',
                  'license'        => 'GPLv2+',
                  'author'         => 'Patrick Delcroix',
                  'homepage'       => 'https://github.com/delcroip/glpi_openmedis',
                  'minGlpiVersion' => '9.2'];
}

// Optional : check prerequisites before install : may print errors or add to message after redirect
function plugin_openmedis_check_prerequisites() {
   if (version_compare(GLPI_VERSION, '9.2', 'lt') || version_compare(GLPI_VERSION, '9.3', 'ge')) {
      echo __('This plugin requires GLPI >= 9.2');
      return false;
   }
   return true;
}

// Uninstall process for plugin : need to return true if succeeded : may display messages or add to message after redirect
function plugin_openmedis_check_config() {
   return true;
}

