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

function plugin_openmedis_install() {
   global $DB;

   
   $migration = new Migration("1.0.0");
   $update    = false;
   if (!$DB->tableExists("glpi_plugin_openmedis_medicaldevice")) {
      $DB->runFile(GLPI_ROOT ."/plugins/openmedis/sql/empty-1.0.0.sql");

   }
   //Migrate profiles to the system introduced in 0.85
  PluginOpenmedisProfile::initProfile();
  PluginOpenmedisProfile::createFirstAccess($_SESSION['glpiactiveprofile']['id']);
   return true;
}

function plugin_openmedis_uninstall() {
   global $DB;

   include_once (GLPI_ROOT."/plugins/openmedis/inc/profile.class.php");
   include_once (GLPI_ROOT."/plugins/openmedis/inc/menu.class.php");

   $migration = new Migration("1.0.0");

   $tables =  ["glpi_plugin_openmedis_medicalaccessory",
                    "glpi_plugin_openmedis_medicaldevicecategory",
                    "glpi_plugin_openmedis_medicaldevicemodels",
                    "glpi_plugin_openmedis_medicaldevice",
                    "glpi_plugin_openmedis_items_medicalaccessory",
                    "glpi_plugin_openmedis_medicalaccessorymodels",
                    "glpi_plugin_openmedis_medicalaccessorytypes"];

   foreach ($tables as $table) {
      // too dangerous to drop table
      $migration->dropTable($table);
   }

      $tables_glpi = ["glpi_displaypreferences",
                        "glpi_documents_items",
                        "glpi_savedsearches",
                        "glpi_logs",
                        "glpi_items_tickets",
                        "glpi_dropdowntranslations"];

   foreach ($tables_glpi as $table_glpi) {
      //fixme to be checked
      $DB->query("DELETE FROM `$table_glpi` WHERE `itemtype` LIKE 'PluginOpenmedis%';");
   }

   //Delete rights associated with the plugin
   $profileRight = new ProfileRight();
   foreach (PluginopenmedisProfile::getAllRights() as $right) {
      $profileRight->deleteByCriteria(['name' => $right['field']]);
   }
  PluginOpenmedisProfile::removeRightsFromSession();
  PluginOpenmedisProfile::uninstallProfile();

  PluginOpenmedisMenu::removeRightsFromSession();
   return true;
}




function plugin_openmedis_postinit() {
   global $PLUGIN_HOOKS, $ORDER_TYPES;

   $PLUGIN_HOOKS['item_purge']['openmedis'] = [];

   foreach (PluginOpenmedisMedicalDevice::getTypes(true) as $type) {
      $PLUGIN_HOOKS['item_purge']['openmedis'][$type]
         = ['PluginOpenmedisItem_MedicalDevice','cleanForItem'];
      CommonGLPI::registerStandardTab($type, 'PluginOpenmedisItem_MedicalDevice');
   }

   $plugin = new Plugin();
   if ($plugin->isInstalled('order') && $plugin->isActivated('order')) {
      array_push($ORDER_TYPES, 'PluginOpenmedisMedicalDevice');
   }

}

function plugin_openmedis_AssignToTicket($types) {

   if (Session::checkRight("plugin_openmedis_openticket", CREATE)) {
     
      $types['PluginOpenmedisMedicalDevice'] = PluginOpenmedisMedicalDevice::getTypeName(2);
   }
   return $types;
}

// Define dropdown relations
function plugin_openmedis_getDatabaseRelations() {
   $plugin = new Plugin();
   if ($plugin->isActivated("openmedis")) {
      return ["glpi_plugin_openmedis_medicalaccessorymodels"
                      => ["glpi_plugin_openmedis_openmedis" => "plugin_openmedis_medicalaccessorymodels_id"],
                      "glpi_plugin_openmedis_medicalaccessorytypes"
                      => ["glpi_plugin_openmedis_medicalaccessory" => "plugin_openmedis_medicalaccessorytypes_id"],
                      "glpi_plugin_openmedis_medicalaccessory"
                      => ["glpi_plugin_openmedis_items_medicalaccessory" => "plugin_openmedis_medicalaccessory_id"],
                      "glpi_plugin_openmedis_medicaldevicecategory"
                      => ["glpi_plugin_openmedis_medicaldevice" => "plugin_openmedis_medicaldevicecategory_id"],
                      "glpi_plugin_openmedis_medicaldevicemodels"
                      => ["glpi_plugin_openmedis_medicaldevice" => "plugin_openmedis_medicaldevicemodels_id"],
                   "glpi_locations"
                      => ["glpi_plugin_openmedis_items_medicalaccessory" => "locations_id",
                      "glpi_plugin_openmedis_medicaldevice"        => "locations_id"],
                   "glpi_users"
                      => ["glpi_plugin_openmedis_medicaldevice" => "users_id_tech",
                           "glpi_plugin_openmedis_medicaldevice" => "users_id"],
                   "glpi_groups"
                      => ["glpi_plugin_openmedis_medicaldevice" => "groups_id_tech",
                      "glpi_plugin_openmedis_medicaldevice" => "groups_id"],
                   "glpi_manufacturers"
                      => ["glpi_plugin_openmedis_openmedis" => "manufacturers_id",
                           "glpi_plugin_openmedis_medicalaccessory" => "manufacturers_id"],
                    "glpi_entities"
                     => ["glpi_plugin_openmedis_medicalaccessory"         => "entities_id",
                              "glpi_plugin_openmedis_items_medicalaccessory" => "entities_id",
                              "glpi_plugin_openmedis_medicaldevice"        => "entities_id"],
                     "glpi_states"
                     => ["glpi_plugin_openmedis_items_medicalaccessory" => "states_id",
                     "glpi_plugin_openmedis_medicaldevice" => "states_id"]];
   } else {
      return [];
   }
}

// Define Dropdown tables to be manage in GLPI :
function plugin_openmedis_getDropdown() {
   $plugin = new Plugin();
   if ($plugin->isActivated("openmedis")) {
      return [
                   'PluginOpenmedisMedicalAccessoryType'    =>  PluginOpenmedisMedicalAccessoryType::getTypeName(0),
                   'PluginOpenmedisMedicalDeviceCategory'   => PluginOpenmedisMedicalDeviceCategory::getTypeName(0),
                   'PluginOpenmedisMedicalDeviceModel'   => PluginOpenmedisMedicalDeviceModel::getTypeName(0),
                   'PluginOpenmedisMedicalAccessoryModel' =>  PluginOpenmedisMedicalAccessoryModel::getTypeName(0)];
   } else {
      return [];
   }
}

function plugin_openmedis_getAddSearchOptions($itemtype) {
   $sopt = [];

   if (in_array($itemtype, PluginOpenmedisMedicalDevice::getTypes(true))) {

      if (PluginOpenmedisMedicalDevice::canView()) {
         $sopt[4460]['table']         = 'glpi_plugin_openmedis_medicaldevice';
         $sopt[4460]['field']         = 'name';
         $sopt[4460]['name']          = _n('Medical Device',
                                           'Medical Devices', 2, 'openmedis')
                                        . " - ". __('Name');
         $sopt[4460]['forcegroupby']  = '1';
         $sopt[4460]['datatype']      = 'itemlink';
         $sopt[4460]['itemlink_type'] = 'MedicalDevice';
         $sopt[4460]['massiveaction'] = false;
      }
   }
   return $sopt;
}

//for search
function plugin_openmedis_addLeftJoin($type, $ref_table, $new_table,
                                  $linkfield, &$already_link_tables) {

   switch ($new_table) {
      case "glpi_plugin_openmedis_items_medicalaccessory" :
         return " LEFT JOIN `glpi_plugin_openmedis_items_medicalaccessory`
         ON (`$ref_table`.`id` = `glpi_plugin_openmedis_items_medicalaccessory`.`items_id`
         AND `glpi_plugin_openmedis_items_medicalaccessory`.`itemtype`= '".$type."Model') ";
         break;

     /* case "glpi_plugin_openmedis_medicaldevice" : // From items
         $out = Search::addLeftJoin($type, $ref_table, $already_link_tables,
                                   "glpi_plugin_openmedis_items_medicalaccessory",
                                   "plugin_openmedis_medicaldevice_id");
         $out .= " LEFT JOIN `glpi_plugin_openmedis_medicaldevice`
                  ON (`glpi_plugin_openmedis_medicaldevice`.`id` = `glpi_plugin_openmedis_items_medicalaccessory`.`items_id`) ";
         */return $out;
         break;
   }
   return "";
}

// Hook done on purge item case
function plugin_item_purge_openmedis($item) {
   $type = get_class($item);
   $temp = new   PluginOpenmedisItem_MedicalAccessory();
   $temp->deleteByCriteria(['itemtype' => $type."Model",
                                 'items_id' => $item->getField('id')]);
   return true;
}
