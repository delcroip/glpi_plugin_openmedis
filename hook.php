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
/**
 * Entry point for installation process
 */
function plugin_openmedis_install() {
   $version   = plugin_version_openmedis();
   $migration = new Migration($version['version']);
   $update    = false;
   require_once(PLUGIN_OPENMEDIS_ROOT . "/install/install.class.php");
   spl_autoload_register([PluginOpenmedisInstall::class, 'autoload']);
   $install = new PluginOpenmedisInstall();
   if (!$install->isPluginInstalled()
      || isset($_SESSION['plugin_openmedis']['cli'])
      && $_SESSION['plugin_openmedis']['cli'] == 'force-install') {
      return $install->install($migration);
   }
   return $install->upgrade($migration);
}
/**
 * Uninstalls the plugin
 * @return boolean True if success
 */
function plugin_openmedis_uninstall() {
   require_once(PLUGIN_OPENMEDIS_ROOT . "/install/install.class.php");
   $install = new PluginOpenmedisInstall();
   return $install->uninstall();
}

/**
 * Second pass of initialization after all other initiaization of other plugins
 * Also force inclusion of this file
 */
function plugin_openmedis_postinit() {
   global $PLUGIN_HOOKS, $ORDER_TYPES;

   $PLUGIN_HOOKS['item_purge']['openmedis'] = [];

   foreach (PluginOpenmedisMedicalDevice::getTypes(true) as $type) {
      $PLUGIN_HOOKS['item_purge']['openmedis'][$type]
         = ['PluginOpenmedisItem_DeviceMedicalDevice','cleanForItem'];
      CommonGLPI::registerStandardTab($type, 'PluginOpenmedisItem_DeviceMedicalDevice');
   }

   $plugin = new Plugin();
   if ($plugin->isInstalled('order') && $plugin->isActivated('order')) {
      array_push($ORDER_TYPES, 'PluginOpenmedisMedicalDevice');
   }
  
}
/**
 * @param string $type
 * @return array
 */
function plugin_openmedis_MassiveActions($type) {
   // TODO:  add massive action
   switch ($type) {
     // case 'User':
     //    return [PluginFlyvemdmInvitation::class . MassiveAction::CLASS_ACTION_SEPARATOR . 'InviteUser' =>
     //          __("Invite to enroll a device", 'flyvemdm')];
   }
   return [];
}

/**
 * Actions done when a profile_user is being purged
 * @param CommonDBTM $item
 */
function plugin_openmedis_hook_pre_profileuser_purge(CommonDBTM $item) {
 // NA
}

function plugin_openmedis_AssignToTicket($types) {

   if (Session::haveRight("plugin_openmedis_openticket", CREATE)) {
      $types['PluginOpenmedisMedicalDevice'] = PluginOpenmedisMedicalDevice::getTypeName(2);
   }
   return $types;
}

// Define dropdown relations
function plugin_openmedis_getDatabaseRelations() {
   $plugin = new Plugin();
   if ($plugin->isActivated("openmedis")) {
      return [ "glpi_plugin_openmedis_medicalaccessorycategories"
                      => ["glpi_plugin_openmedis_devicemedicalaccessories" => "plugin_openmedis_medicalaccessorycategories_id"],
               "glpi_plugin_openmedis_medicalaccessorytypes"
                      => ["glpi_plugin_openmedis_devicemedicalaccessories" => "plugin_openmedis_medicalaccessorytypes_id"],
               "glpi_plugin_openmedis_utilizations"
                      => ["glpi_plugin_openmedis_medicaldevices" => "plugin_openmedis_utilizations_id"],
               "glpi_plugin_openmedis_devicemedicalaccessories"
                      => ["glpi_plugin_openmedis_items_devicemedicalaccessories" => "plugin_openmedis_devicemedicalaccessories_id"],
               "glpi_plugin_openmedis_medicaldevicecategories"
                      => ["glpi_plugin_openmedis_medicaldevices" => "plugin_openmedis_medicaldevicecategories_id"],
               "glpi_plugin_openmedis_medicaldevicemodels"
                      => ["glpi_plugin_openmedis_medicaldevices" => "plugin_openmedis_medicaldevicemodels_id"],
               "glpi_locations"
                      => ["glpi_plugin_openmedis_items_devicemedicalaccessories" => "locations_id",
                      "glpi_plugin_openmedis_medicaldevices"        => "locations_id"],
               "glpi_users"
                      => ["glpi_plugin_openmedis_medicaldevices" => "users_id_tech",
                           "glpi_plugin_openmedis_medicaldevices" => "users_id"],
               "glpi_groups"
                      => ["glpi_plugin_openmedis_medicaldevices" => "groups_id_tech",
                      "glpi_plugin_openmedis_medicaldevices" => "groups_id"],
               "glpi_manufacturers"
                      => ["glpi_plugin_openmedis_openmedis" => "manufacturers_id",
                           "glpi_plugin_openmedis_devicemedicalaccessories" => "manufacturers_id"],
               "glpi_entities"
                     => ["glpi_plugin_openmedis_devicemedicalaccessories"         => "entities_id",
                              "glpi_plugin_openmedis_items_devicemedicalaccessories" => "entities_id",
                              "glpi_plugin_openmedis_medicaldevices"        => "entities_id"],
               "glpi_states"
                     => ["glpi_plugin_openmedis_items_devicemedicalaccessories" => "states_id",
                     "glpi_plugin_openmedis_medicaldevices" => "states_id"],
                     "glpi_plugin_openmedis_medicalconsumableitems_medicaldevicemodels" => [
                    "glpi_plugin_openmedis_medicalconsumableitems" => "plugin_openmedis_medicalconsumableitems_id",
                    "glpi_plugin_openmedis_medicaldevicemodels" => "plugin_openmedis_medicaldevicemodels_id"
                                      ],
                 "glpi_plugin_openmedis_medicalconsumables" =>
                 ["glpi_plugin_openmedis_medicaldevice" => "plugin_openmedis_medicaldevices_id",
                 "glpi_plugin_openmedis_medicalconsumableitems" => "plugin_openmedis_medicalconsumableitems_id"],
                 "glpi_plugin_openmedis_medicalconsumableitems" => ["glpi_plugin_openmedis_medicalconsumableitemtypes" => "plugin_openmedis_medicalconsumableitemtypes_id"]];

   } else {
      return [];
   }
}

/**
 * Define Dropdown tables to be managed in GLPI
 * @return array
 */
function plugin_openmedis_getDropdown() {
   $plugin = new Plugin();
   if ($plugin->isActivated("openmedis")) {
      return [
            PluginOpenmedisMedicalAccessoryType::class    =>  PluginOpenmedisMedicalAccessoryType::getTypeName(0),
            PluginOpenmedisMedicalDeviceCategory::class   => PluginOpenmedisMedicalDeviceCategory::getTypeName(0),
            PluginOpenmedisMedicalDeviceModel::class   => PluginOpenmedisMedicalDeviceModel::getTypeName(0),
            PluginOpenmedisMedicalAccessoryCategory::class =>  PluginOpenmedisMedicalAccessoryCategory::getTypeName(0),
            PluginOpenmedisUtilization::class =>  PluginOpenmedisUtilization::getTypeName(0),
            PluginOpenmedisMedicalConsumableItemType::class => PluginOpenmedisMedicalConsumableItemType::getTypeName(0)];
   } else {
      return [];
   }
}




function plugin_openmedis_getAddSearchOptions($itemtype) {
   $sopt = [];

   if (in_array($itemtype, PluginOpenmedisMedicalDevice::getTypes(true))) {

      if (PluginOpenmedisMedicalDevice::canView()) {
         $sopt[8610]['table']         = 'glpi_plugin_openmedis_medicaldevices';
         $sopt[8610]['field']         = 'name';
         $sopt[8610]['name']          = PluginOpenmedisMedicalDevice::getTypeName(2). " - ". __('Name');
         $sopt[8610]['forcegroupby']  = '1';
         $sopt[8610]['datatype']      = 'itemlink';
         $sopt[8610]['itemlink_type'] = 'MedicalDevice';
         $sopt[8610]['massiveaction'] = false;
      }
   }
   return $sopt;
}

/**
 * @param string $itemtype
 * @return string
 */
function plugin_openmedis_addDefaultSelect($itemtype) {
   $selected = '';

   return $selected;
}


//for search
function plugin_openmedis_addLeftJoin($type, $ref_table, $new_table,
                                  $linkfield, &$already_link_tables) {

   switch ($new_table) {
      case "glpi_plugin_openmedis_items_devicemedicalaccessories" :
         return " LEFT JOIN `glpi_plugin_openmedis_items_devicemedicalaccessories`
         ON (`$ref_table`.`id` = `glpi_plugin_openmedis_items_devicemedicalaccessories`.`items_id`
         AND `glpi_plugin_openmedis_items_devicemedicalaccessories`.`itemtype`= '".$type."Model') ";
         break;

     case "glpi_plugin_openmedis_medicaldevices" : // From items
         $out = Search::addLeftJoin($type, $ref_table, $already_link_tables,
                                   "glpi_plugin_openmedis_items_devicemedicalaccessories",
                                   "plugin_openmedis_medicaldevices_id");
         $out .= " LEFT JOIN `glpi_plugin_openmedis_medicaldevices`
                  ON (`glpi_plugin_openmedis_medicaldevices`.`id` = `glpi_plugin_openmedis_items_devicemedicalaccessories`.`items_id`) ";
         return $out;
         break;
   }
   return "";
}



// Hook done on purge item case
function plugin_openmedis_item_purge($item) {
   $type = get_class($item);
   $temp = new   PluginOpenmedisItem_DeviceMedicalAccessory();
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
      case "glpi_plugin_openmedis_items_devicemedicalaccessories.items_id" :
         $appliances_id = $data['id'];
         $query_device  = $DB->request(['SELECT DISTINCT' => 'itemtype',
                                        'FROM'            => 'glpi_plugin_openmedis_items_devicemedicalaccessories',
                                        'WHERE'           => ['plugin_openmedis_devicemedicalaccessories_id'
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
                            'FROM'       => 'glpi_plugin_openmedis_items_devicemedicalaccessories',
                            'LEFT JOIN'  => [$table
                                              => ['FKEY' => [$table          => 'id',
                                                             'glpi_plugin_openmedis_items_devicemedicalaccessories' => 'items_id']],
                                             'glpi_entities'
                                              => ['FKEY' => ['glpi_entities' => 'id',
                                                             $table          => 'entities_id']]],
                            'WHERE'      => ['glpi_plugin_openmedis_items_devicemedicalaccessories.itemtype' => $type,
                                             'glpi_plugin_openmedis_items_devicemedicalaccessories.glpi_plugin_openmedis_devicemedicalaccessories_id'
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

      case 'glpi_plugin_openmedis_items_devicemedicalaccessories.name':
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
