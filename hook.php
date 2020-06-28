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
   // load the data
   if ($DB->tableExists("glpi_plugin_openmedis_medicaldevicecategories")) {
      $DB->runFile(GLPI_ROOT ."/plugins/openmedis/sql/data-1.0.0.sql");

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

   $tables =  ["glpi_plugin_openmedis_medicalaccessories",
                    "glpi_plugin_openmedis_medicaldevicecategories",
                    "glpi_plugin_openmedis_medicaldevicemodels",
                    "glpi_plugin_openmedis_medicaldevice",
                    "glpi_plugin_openmedis_medicalaccessories_items",
                    "glpi_plugin_openmedis_medicalaccessorymodels",
                    "glpi_plugin_openmedis_medicalaccessorytypes",
                  "glpi_plugin_openmedis_utilization"];

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
   CommonGLPI::registerStandardTab($type, 'PluginOpenmedisItem_MedicalAppliance');

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
                      => ["glpi_plugin_openmedis_medicalaccessories" => "plugin_openmedis_medicalaccessorytypes_id"],
                      "glpi_plugin_openmedis_utilizations"
                      => ["glpi_plugin_openmedis_medicaldevices" => "plugin_openmedis_utilizations_id"],
                      "glpi_plugin_openmedis_medicalaccessories"
                      => ["glpi_plugin_openmedis_medicalaccessories_items" => "plugin_openmedis_medicalaccessories_id"],
                      "glpi_plugin_openmedis_medicaldevicecategories"
                      => ["s" => "plugin_openmedis_medicaldevicecategories_id",
                     "glpi_plugin_openmedis_medicaldevicecategories" => "plugin_openmedis_medicaldevicecategories_id"],
                      "glpi_plugin_openmedis_medicaldevicemodels"
                      => ["glpi_plugin_openmedis_medicaldevices" => "plugin_openmedis_medicaldevicemodels_id"],
                   "glpi_locations"
                      => ["glpi_plugin_openmedis_medicalaccessories_items" => "locations_id",
                      "glpi_plugin_openmedis_medicaldevices"        => "locations_id"],
                   "glpi_users"
                      => ["glpi_plugin_openmedis_medicaldevices" => "users_id_tech",
                           "glpi_plugin_openmedis_medicaldevices" => "users_id"],
                   "glpi_groups"
                      => ["glpi_plugin_openmedis_medicaldevices" => "groups_id_tech",
                      "glpi_plugin_openmedis_medicaldevices" => "groups_id"],
                   "glpi_manufacturers"
                      => ["glpi_plugin_openmedis_openmedis" => "manufacturers_id",
                           "glpi_plugin_openmedis_medicalaccessories" => "manufacturers_id"],
                    "glpi_entities"
                     => ["glpi_plugin_openmedis_medicalaccessories"         => "entities_id",
                              "glpi_plugin_openmedis_medicalaccessories_items" => "entities_id",
                              "glpi_plugin_openmedis_medicaldevices"        => "entities_id"],
                     "glpi_states"
                     => ["glpi_plugin_openmedis_medicalaccessories_items" => "states_id",
                     "glpi_plugin_openmedis_medicaldevices" => "states_id"]];
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
                   'PluginOpenmedisMedicalAccessoryModel' =>  PluginOpenmedisMedicalAccessoryModel::getTypeName(0),
                   'PluginOpenmedisUtilization' =>  PluginOpenmedisUtilization::getTypeName(0)];
   } else {
      return [];
   }
}

// Define Dropdown tables to be manage in GLPI :
function plugin_openmedis_getComponent() {
   $plugin = new Plugin();
   if ($plugin->isActivated("openmedis")) {
      return [
                   'PluginOpenmedisMedicalAccessories_Item'    =>  PluginOpenmedisMedicalAccessories_item::getTypeName(0)
               ];
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
      case "glpi_plugin_openmedis_medicalaccessories_items" :
         return " LEFT JOIN `glpi_plugin_openmedis_medicalaccessories_items`
         ON (`$ref_table`.`id` = `glpi_plugin_openmedis_medicalaccessories_items`.`items_id`
         AND `glpi_plugin_openmedis_medicalaccessories_items`.`itemtype`= '".$type."Model') ";
         break;

     /* case "glpi_plugin_openmedis_medicaldevice" : // From items
         $out = Search::addLeftJoin($type, $ref_table, $already_link_tables,
                                   "glpi_plugin_openmedis_medicalaccessories_items",
                                   "plugin_openmedis_medicaldevice_id");
         $out .= " LEFT JOIN `glpi_plugin_openmedis_medicaldevice`
                  ON (`glpi_plugin_openmedis_medicaldevice`.`id` = `glpi_plugin_openmedis_medicalaccessories_items`.`items_id`) ";
         */return $out;
         break;
   }
   return "";
}

// Hook done on purge item case
function plugin_item_purge_openmedis($item) {
   $type = get_class($item);
   $temp = new   PluginOpenmedisMedicalAccessories_Item();
   $temp->deleteByCriteria(['itemtype' => $type."Model",
                                 'items_id' => $item->getField('id')]);
   return true;
}

/**
 * @see Search::giveItem()
 *
 * @param $type
 * @param $ID
 * @param $data      array
 * @param $num
 *
 * @return string
*/
function plugin_openmedis_giveItem($type, $ID, array $data, $num) {
   global $DB;

   $searchopt = &Search::getOptions($type);
   $table     = $searchopt[$ID]["table"];
   $field     = $searchopt[$ID]["field"];

   $dbu       = new DbUtils();

   switch ($table.'.'.$field) {
      case "glpi_plugin_openmedis_medicalaccessories_items.items_id" :
         $appliances_id = $data['id'];
         $query_device  = $DB->request(['SELECT DISTINCT' => 'itemtype',
                                        'FROM'            => 'glpi_plugin_openmedis_medicalaccessories_items',
                                        'WHERE'           => ['plugin_openmedis_medicalaccessories_id'
                                                               => $appliances_id],
                                        'ORDER'           => 'itemtype']);
         $number_device  = count($query_device);
         $out            = '';
         if ($number_device > 0) {
            $column = "name";
            if ($type == 'Ticket') {
               $column = "id";
            }
            foreach ($query_device as $id => $row) {
               $type = $row['itemtype'];

               if (!($item = $dbu->getItemForItemtype($type))) {
                  continue;
               }
               $table = $item->getTable();
               if (!empty($table)) {
                  /*
                   *                   $query = "SELECT `".$table."`.`id`
                            FROM `glpi_plugin_appliances_appliances_items`, `".$table."`
                            LEFT JOIN `glpi_entities`
                                  ON (`glpi_entities`.`id` = `".$table."`.`entities_id`)
                            WHERE `".$table."`.`id`
                                       = `glpi_plugin_appliances_appliances_items`.`items_id`
                                   AND `glpi_plugin_appliances_appliances_items`.`itemtype`
                                       = '".$type."'
                                   AND `glpi_plugin_appliances_appliances_items`.`plugin_appliances_appliances_id`
                                       = '".$appliances_id."'".
                                   $dbu->getEntitiesRestrictRequest(" AND ", $table, '', '',
                                                                    $item->maybeRecursive());

                  if ($item->maybeTemplate()) {
                     $query .= " AND `".$table."`.`is_template` = '0'";
                  }
                  $query .= " ORDER BY `glpi_entities`.`completename`,
                                       `$table`.`$column`";

                   */
                  $query = ['SELECT'     => $table.'.id',
                            'FROM'       => 'glpi_plugin_openmedis_medicalaccessories_items',
                            'LEFT JOIN'  => [$table
                                              => ['FKEY' => [$table          => 'id',
                                                             'glpi_plugin_openmedis_medicalaccessories_items' => 'items_id']],
                                             'glpi_entities'
                                              => ['FKEY' => ['glpi_entities' => 'id',
                                                             $table          => 'entities_id']]],
                            'WHERE'      => ['glpi_plugin_openmedis_medicalaccessories_items.itemtype' => $type,
                                             'glpi_plugin_openmedis_medicalaccessories_items.glpi_plugin_openmedis_medicalaccessories_id'
                                                               => $appliances_id]
                                             + getEntitiesRestrictCriteria($table, '', '',
                                                                           $item->maybeRecursive())];

                  if ($item->maybeTemplate()) {
                     $query['WHERE'][$table.'.is_template'] = 0;
                  }
                  $query['ORDER'] = ['glpi_entities.completename', $table.'.'.$column];

                  if ($result_linked = $DB->request($query)) {
                     if (count($result_linked)) {
                        foreach ($result_linked as $id => $row) {
                           if ($item->getFromDB($row['id'])) {
                              $out .= $item->getTypeName(1)." - ".$item->getLink()."<br>";
                           }
                        }
                     }
                  }
               }
            }
         }
         return $out;

      case 'glpi_plugin_openmedis_medicalaccessories_items.name':
         if ($type == 'Ticket') {
            $appliances_id = [];
            if ($data['raw']["ITEM_$num"] != '') {
               $appliances_id = explode('$$$$', $data['raw']["ITEM_$num"]);
            } else {
               $appliances_id = explode('$$$$', $data['raw']["ITEM_".$num."_id"]);
            }
            $ret = [];
            $paAppliance = new PluginAppliancesAppliance();
            foreach ($appliances_id as $ap_id) {
               $paAppliance->getFromDB($ap_id);
               $ret[] = $paAppliance->getLink();
            }
            return implode('<br>', $ret);
         }
         break;

   }
   return "";

}