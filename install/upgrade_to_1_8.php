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



if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access this file directly");
}
include_once(PLUGIN_OPENMEDIS_ROOT.'/install/upgradeStep.class.php');
class PluginOpenmedisUpgradeTo1_8 extends PluginOpenmedisUpgradeStep {
  var $migration;

   /**
    * @param Migration $migration
    */
   public function upgrade(Migration $migration) {
    $this->migration = $migration;
    global $DB;
    $this->migrationStep = '1.7 -> 1.8';
    $err = 0;

    // Migrate state visibility from core table column to relationship table
    if ($DB->fieldExists('glpi_states', 'is_visible_pluginopenmedismedicaldevice')) {
       $this->migration->displayMessage("Migrating state visibility data to relationship table");

       // Get all states that were visible for medical devices
       $states_result = $DB->request([
          'SELECT' => 'id',
          'FROM' => 'glpi_states',
          'WHERE' => ['is_visible_pluginopenmedismedicaldevice' => 1]
       ]);

       // Insert visibility relationships for each state
       foreach ($states_result as $state) {
          $this->migration->addPostQuery(
             "INSERT INTO `glpi_plugin_openmedis_states_items` (`states_id`, `itemtype`) VALUES (" . $state['id'] . ", 'PluginOpenmedisMedicalDevice')"
          );
       }

       // Remove the old column from core table
       $this->migration->dropField('glpi_states', 'is_visible_pluginopenmedismedicaldevice');
    }

    // If no migration was needed but we want to ensure default visibility, create some defaults
    if ($DB->request(['FROM' => 'glpi_plugin_openmedis_states_items', 'WHERE' => ['itemtype' => 'PluginOpenmedisMedicalDevice']])->count() == 0) {

       $this->migration->displayMessage("Setting up default state visibility for medical devices");

       // Add some default states to make visible by default (states with IDs 1-5)
       for ($i = 1; $i <= 5; $i++) {
          $this->migration->addPostQuery(
             "INSERT IGNORE INTO `glpi_plugin_openmedis_states_items` (`states_id`, `itemtype`) VALUES ($i, 'PluginOpenmedisMedicalDevice')"
          );
       }
    }

    if ($err > 0){
      return false;
    }
    else{
      return true;
    }
  }


}
