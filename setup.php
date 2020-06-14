<?php
/*
 * @version $Id: HEADER 15930 2011-10-30 15:47:55Z tsmr $
 -------------------------------------------------------------------------
 open_medis plugin for GLPI
 Copyright (C) 2014-2016 by the open_medis Development Team.

 https://github.com/InfotelGLPI/open_medis
 -------------------------------------------------------------------------

 LICENSE

 This file is part of open_medis.

 open_medis is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 open_medis is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with open_medis. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

function plugin_init_open_medis() {
   global $PLUGIN_HOOKS, $CFG_GLPI;
   // to check what it means to be CSRF compatible
   $PLUGIN_HOOKS['csrf_compliant']['open_medis']   = true;
   //load changeprofile function
   $PLUGIN_HOOKS['change_profile']['open_medis']   = ['PluginOpenMEDISProfile',
                                                                'initProfile'];
   // load the javascript
   $PLUGIN_HOOKS['javascript']['open_medis'][]   = '/plugins/open_medis/open_medis.js';

   $plugin = new Plugin();
   if ($plugin->isInstalled('open_medis') && $plugin->isActivated('open_medis')) {

      //Ability to add a rack to a project
      $CFG_GLPI["project_asset_types"][] = 'PluginOpenMedisMedicalDevice';

      $PLUGIN_HOOKS['assign_to_ticket']['open_medis'] = true;
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
         $PLUGIN_HOOKS['treeview']['PluginOpenMedisMedicalDevice'] = '../open_medis/pics/open_medis_icon.png';
      }
      // on user connection ?
      if (Session::getLoginUserID()) {

         include_once (GLPI_ROOT."/plugins/open_medis/inc/medical_device.class.php");
         // add the menu for the user with the right profile
         if (PluginOpenMedisMedicalDevice::canView()) {
            //Display menu entry only if user has right to see it !
            $PLUGIN_HOOKS["menu_toadd"]['open_medis'] = ['assets'  => 'PluginOpenMedisMenu'];
            $PLUGIN_HOOKS['use_massive_action']['open_medis'] = 1;
         }
         // add the config page if the user has the right profile
         /*if (PluginOpenMedisMedicalDevice::canCreate()
            || Config::canUpdate()) {
            $PLUGIN_HOOKS['config_page']['open_medis'] = 'front/config.form.php';
         }
         */

         // load css
         $PLUGIN_HOOKS['add_css']['open_medis']   = "open_medis.css";
         // FIXME: where to find the init 
         $PLUGIN_HOOKS['post_init']['open_medis'] = 'plugin_open_medis_postinit';
         // Add reports 
         /*$PLUGIN_HOOKS['reports']['open_medis']   =
            ['front/report.php' => __("Report - Bays management", "open_medis")];
         */
      }
   }
}

function plugin_version_open_medis() {
   return  ['name'           => _n('Health technologies management',
                                        'Health technologies management',
                                        2, 'open_medis'),
                  'version'        => '0.0.1',
                  'license'        => 'GPLv2+',
                  'author'         => 'Patrick Delcroix',
                  'homepage'       => 'https://github.com/delcroip/glpi_open_medis',
                  'minGlpiVersion' => '9.2'];
}

// Optional : check prerequisites before install : may print errors or add to message after redirect
function plugin_open_medis_check_prerequisites() {
   if (version_compare(GLPI_VERSION, '9.2', 'lt') || version_compare(GLPI_VERSION, '9.3', 'ge')) {
      echo __('This plugin requires GLPI >= 9.2');
      return false;
   }
   return true;
}

// Uninstall process for plugin : need to return true if succeeded : may display messages or add to message after redirect
function plugin_open_medis_check_config() {
   return true;
}
