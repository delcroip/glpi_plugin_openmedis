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

   include_once (GLPI_ROOT."/plugins/openmedis/inc/profile.class.php");
   $migration = new Migration("1.7.1");
   $update    = false;

   if (!$DB->tableExists("glpi_plugin_openmedis_openmedis")
        && !$DB->tableExists("glpi_plugin_openmedis_configs")) {
      $DB->runFile(GLPI_ROOT ."/plugins/openmedis/sql/empty-1.0.1.sql");

   } else if ($DB->tableExists("glpi_plugin_rack_content")
      && !$DB->fieldExists("glpi_plugin_rack_content", "first_powersupply")) {
      $update = true;
      $DB->runFile(GLPI_ROOT ."/plugins/openmedis/sql/update-1.0.2.sql");
      $DB->runFile(GLPI_ROOT ."/plugins/openmedis/sql/update-1.1.0.sql");

   } else if ($DB->tableExists("glpi_plugin_rack")
               && $DB->tableExists("glpi_plugin_openmedis_profiles")) {
      $update = true;
      $DB->runFile(GLPI_ROOT ."/plugins/openmedis/sql/update-1.1.0.sql");
   }
   //from 1.1 version
   if ($DB->tableExists("glpi_plugin_openmedis_openmedis")
      && !$DB->fieldExists("glpi_plugin_openmedis_openmedis", "otherserial")) {
      $DB->runFile(GLPI_ROOT ."/plugins/openmedis/sql/update-1.2.1.sql");
   }

   if ($DB->tableExists("glpi_plugin_openmedis_openmedis")
      && !$DB->fieldExists("glpi_plugin_openmedis_openmedis", "users_id_tech")) {
      $DB->runFile(GLPI_ROOT ."/plugins/openmedis/sql/update-1.3.0.sql");
   }

   if (!$DB->tableExists("glpi_plugin_openmedis_racktypes")) {
      $DB->runFile(GLPI_ROOT ."/plugins/openmedis/sql/update-1.3.2.sql");
   }

   if ($DB->tableExists("glpi_plugin_openmedis_racktypes")
                  && !$DB->fieldExists("glpi_plugin_openmedis_racktypes", "is_recursive")) {
      $DB->runFile(GLPI_ROOT ."/plugins/openmedis/sql/update-1.4.1.sql");
   }

   if ($DB->tableExists("glpi_plugin_openmedis_profiles")
                  && !$DB->fieldExists("glpi_plugin_openmedis_profiles", "open_ticket")) {
      $DB->runFile(GLPI_ROOT ."/plugins/openmedis/sql/update-1.4.2.sql");
   }
   if ($DB->tableExists("glpi_plugin_openmedis_roomlocations")
                  && !$DB->fieldExists("glpi_plugin_openmedis_roomlocations", "ancestors_cache")) {
      $DB->runFile(GLPI_ROOT ."/plugins/openmedis/sql/update-1.7.1.sql");
   }

   $migration->addField('glpi_plugin_openmedis_configs', 'add_location_on_new_item', 'bool');
   $migration->addField('glpi_plugin_openmedis_configs', 'forward_location_on_change', 'bool');
   $migration->migrationOneTable('glpi_plugin_openmedis_configs');

   if ($update) {
      foreach ($DB->request('glpi_plugin_openmedis_profiles') as $data) {
         $query  = "UPDATE `glpi_plugin_openmedis_profiles`
                    SET `profiles_id` = '".$data["id"]."'
                    WHERE `id` = '".$data["id"]."';";
         $result = $DB->query($query);
      }

      $migration->dropField('glpi_plugin_openmedis_profiles', 'name');

      Plugin::migrateItemType([4450 => 'PluginopenmedisRack',
                                    4451 => 'PluginopenmedisOther'],
                              ["glpi_savedsearches",
                                    "glpi_savedsearches_users",
                                    "glpi_displaypreferences",
                                    "glpi_documents_items",
                                    "glpi_infocoms",
                                    "glpi_logs",
                                    "glpi_items_tickets"],
                              ["glpi_plugin_openmedis_openmedis_items",
                              "glpi_plugin_openmedis_itemspecifications"]);
   }

   $notepad_tables = ['glpi_plugin_openmedis_openmedis'];

   foreach ($notepad_tables as $t) {
      // Migrate data
      if ($DB->fieldExists($t, 'notepad')) {
         $query = "SELECT id, notepad
                      FROM `$t`
                      WHERE notepad IS NOT NULL
                            AND notepad <>'';";
         foreach ($DB->request($query) as $data) {
            $iq = "INSERT INTO `glpi_notepads`
                             (`itemtype`, `items_id`, `content`, `date`, `date_mod`)
                      VALUES ('".getItemTypeForTable($t)."', '".$data['id']."',
                              '".addslashes($data['notepad'])."', NOW(), NOW())";
            $DB->queryOrDie($iq, "0.85 migrate notepad data");
         }
         $query = "ALTER TABLE `glpi_plugin_openmedis_openmedis` DROP COLUMN `notepad`;";
         $DB->query($query);
      }
   }

   $migration->addField('glpi_plugin_openmedis_rackmodels', 'entities_id', 'integer');
   $migration->addField('glpi_plugin_openmedis_rackmodels', 'is_recursive', 'bool');
   $migration->addKey('glpi_plugin_openmedis_rackmodels', 'entities_id');
   $migration->addKey('glpi_plugin_openmedis_rackmodels', 'is_recursive');
   $migration->migrationOneTable('glpi_plugin_openmedis_rackmodels');

   //Migrate profiles to the system introduced in 0.85
   PluginopenmedisProfile::initProfile();
   PluginopenmedisProfile::createFirstAccess($_SESSION['glpiactiveprofile']['id']);

   //Drop old profile table : not used anymore
   $migration->dropTable('glpi_plugin_openmedis_profiles');

   return true;
}

function plugin_openmedis_uninstall() {
   global $DB;

   include_once (GLPI_ROOT."/plugins/openmedis/inc/profile.class.php");
   include_once (GLPI_ROOT."/plugins/openmedis/inc/menu.class.php");

   $migration = new Migration("1.6.1");

   $tables =  ["glpi_plugin_openmedis_openmedis",
                    "glpi_plugin_openmedis_openmedis_items",
                    "glpi_plugin_openmedis_itemspecifications",
                    "glpi_plugin_openmedis_rackmodels",
                    "glpi_plugin_openmedis_roomlocations",
                    "glpi_plugin_openmedis_connections",
                    "glpi_plugin_openmedis_configs",
                    "glpi_plugin_openmedis_others",
                    "glpi_plugin_openmedis_othermodels",
                    "glpi_plugin_openmedis_racktypes",
                    "glpi_plugin_openmedis_openmedistates"];

   foreach ($tables as $table) {
      $migration->dropTable($table);
   }

   //old versions
   $tables = ["glpi_plugin_rack",
                   "glpi_plugin_rack_content",
                   "glpi_plugin_rack_device_spec",
                   "glpi_plugin_rack_profiles",
                    "glpi_plugin_openmedis_profiles",
                   "glpi_plugin_rack_config",
                   "glpi_dropdown_plugin_rack_room_locations",
                   "glpi_dropdown_plugin_rack_ways",
                   "glpi_plugin_rack_others",
                   "glpi_dropdown_plugin_rack_others_type"];

   foreach ($tables as $table) {
      $migration->dropTable($table);
   }

   $tables_glpi = ["glpi_displaypreferences",
                        "glpi_documents_items",
                        "glpi_savedsearches",
                        "glpi_logs",
                        "glpi_items_tickets",
                        "glpi_dropdowntranslations"];

   foreach ($tables_glpi as $table_glpi) {
      $DB->query("DELETE FROM `$table_glpi` WHERE `itemtype` LIKE 'Pluginopenmedis%';");
   }

   //Delete rights associated with the plugin
   $profileRight = new ProfileRight();
   foreach (PluginopenmedisProfile::getAllRights() as $right) {
      $profileRight->deleteByCriteria(['name' => $right['field']]);
   }
   PluginopenmedisProfile::removeRightsFromSession();
   PluginopenmedisProfile::removeRightsFromDB();

   PluginopenmedisMenu::removeRightsFromSession();
   return true;
}

function plugin_openmedis_postinit() {
   global $PLUGIN_HOOKS, $ORDER_TYPES;

   $PLUGIN_HOOKS['item_purge']['openmedis'] = [];

   foreach (PluginopenmedisRack::getTypes(true) as $type) {
      $PLUGIN_HOOKS['item_purge']['openmedis'][$type]
         = ['PluginopenmedisRack_Item','cleanForItem'];
      CommonGLPI::registerStandardTab($type, 'PluginopenmedisRack_Item');
   }
   foreach (PluginopenmedisItemSpecification::getModelClasses() as $model) {
      CommonGLPI::registerStandardTab($model, 'PluginopenmedisItemSpecification');
   }
   $plugin = new Plugin();
   if ($plugin->isInstalled('order') && $plugin->isActivated('order')) {
      array_push($ORDER_TYPES, 'PluginopenmedisRack');
   }

}

function plugin_openmedis_AssignToTicket($types) {

   if (Session::haveRight("plugin_openmedis_open_ticket", "1")) {
      $types['PluginopenmedisRack'] = PluginopenmedisRack::getTypeName(2);
   }
   return $types;
}

// Define dropdown relations
function plugin_openmedis_getDatabaseRelations() {
   $plugin = new Plugin();
   if ($plugin->isActivated("openmedis")) {
      return ["glpi_plugin_openmedis_roomlocations"
                      => ["glpi_plugin_openmedis_openmedis" => "plugin_openmedis_roomlocations_id"],
                   "glpi_plugin_openmedis_rackmodels"
                      => ["glpi_plugin_openmedis_openmedis" => "plugin_openmedis_rackmodels_id"],
                   "glpi_locations"
                      => ["glpi_plugin_openmedis_openmedis" => "locations_id"],
                   "glpi_users"
                      => ["glpi_plugin_openmedis_openmedis" => "users_id_tech"],
                   "glpi_groups"
                      => ["glpi_plugin_openmedis_openmedis" => "groups_id_tech"],
                   "glpi_manufacturers"
                      => ["glpi_plugin_openmedis_openmedis" => "manufacturers_id"],
                   "glpi_plugin_openmedis_openmedis"
                      => ["glpi_plugin_openmedis_openmedis_items" => "plugin_openmedis_openmedis_id"],
                   "glpi_plugin_openmedis_itemspecifications"
                      => ["glpi_plugin_openmedis_openmedis_items" => "plugin_openmedis_itemspecifications_id"],
                   "glpi_plugin_openmedis_connections"
                     => ["glpi_plugin_openmedis_openmedis_items" => "first_powersupply"],
                   "glpi_plugin_openmedis_connections"
                     => ["glpi_plugin_openmedis_openmedis_items" => "second_powersupply"],
                   "glpi_plugin_openmedis_othermodels"
                     => ["glpi_plugin_openmedis_others" => "plugin_openmedis_othermodels_id"],
                   "glpi_plugin_openmedis_racktypes"
                     => ["glpi_plugin_openmedis_openmedis" => "plugin_openmedis_racktypes_id"],
                   "glpi_plugin_openmedis_openmedistates"
                     => ["glpi_plugin_openmedis_openmedis" => "plugin_openmedis_openmedistates_id"],
                   "glpi_entities"
                     => ["glpi_plugin_openmedis_openmedis"         => "entities_id",
                              "glpi_plugin_openmedis_roomlocations" => "entities_id",
                              "glpi_plugin_openmedis_others"        => "entities_id"]];
   } else {
      return [];
   }
}

// Define Dropdown tables to be manage in GLPI :
function plugin_openmedis_getDropdown() {
   $plugin = new Plugin();
   if ($plugin->isActivated("openmedis")) {
      return ['PluginopenmedisRoomLocation' => _n('Place', 'Places', 2, 'openmedis'),
                   'PluginopenmedisRackModel'    => __('Model'),
                   'PluginopenmedisConnection'   => __('Power supply connection', 'openmedis'),
                   'PluginopenmedisOtherModel'   => __('Others equipments', 'openmedis'),
                   'PluginopenmedisRackType'     => _n('Type', 'Types', 2),
                   'Pluginopenmedisopenmedistate'    => _n('Status', 'Statuses', 2)];
   } else {
      return [];
   }
}

function plugin_openmedis_getAddSearchOptions($itemtype) {
   $sopt = [];
   if (in_array($itemtype, PluginopenmedisRack::getTypes(true))) {

      if (PluginopenmedisRack::canView()) {
         $sopt[4460]['table']         = 'glpi_plugin_openmedis_openmedis';
         $sopt[4460]['field']         = 'name';
         $sopt[4460]['name']          = _n('Rack enclosure',
                                           'Rack enclosures', 2, 'openmedis')
                                        . " - ". __('Name');
         $sopt[4460]['forcegroupby']  = '1';
         $sopt[4460]['datatype']      = 'itemlink';
         $sopt[4460]['itemlink_type'] = 'PluginopenmedisRack';
         $sopt[4460]['massiveaction'] = false;
      }
   }
   return $sopt;
}

//for search
function plugin_openmedis_addLeftJoin($type, $ref_table, $new_table,
                                  $linkfield, &$already_link_tables) {

   switch ($new_table) {
      case "glpi_plugin_openmedis_openmedis_items" :
         return " LEFT JOIN `glpi_plugin_openmedis_openmedis_items`
         ON (`$ref_table`.`id` = `glpi_plugin_openmedis_openmedis_items`.`items_id`
         AND `glpi_plugin_openmedis_openmedis_items`.`itemtype`= '".$type."Model') ";
         break;

      case "glpi_plugin_openmedis_openmedis" : // From items
         $out = Search::addLeftJoin($type, $ref_table, $already_link_tables,
                                   "glpi_plugin_openmedis_openmedis_items",
                                   "plugin_openmedis_openmedis_id");
         $out .= " LEFT JOIN `glpi_plugin_openmedis_openmedis`
                  ON (`glpi_plugin_openmedis_openmedis`.`id` = `glpi_plugin_openmedis_openmedis_items`.`plugin_openmedis_openmedis_id`) ";
         return $out;
         break;
   }
   return "";
}

// Hook done on purge item case
function plugin_item_purge_openmedis($item) {
   $type = get_class($item);
   $temp = new PluginopenmedisRack_Item();
   $temp->deleteByCriteria(['itemtype' => $type."Model",
                                 'items_id' => $item->getField('id')]);
   return true;
}
