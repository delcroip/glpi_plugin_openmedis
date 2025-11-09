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

class PluginOpenmedisUpgradeTo1_1 extends PluginOpenmedisUpgradeStep{
   var $migration;
   /**
    * @param Migration $migration
    */
   public function upgrade(Migration $migration) {
	   global $DB;
    $this->migration = $migration;
    $this->migrationStep = '1.0 -> 1.1';
    $err = 0;

    if (!$DB->tableExists("glpi_plugin_openmedis_devicemedicalaccessories")) {
        if (!$DB->runFile(__DIR__ ."/mysql/upgrade_to_1_1.sql")){
            $this->migration->displayWarning("Error in migration 1.0 to 1.1 : " . $DB->error(), true);
            $err++;
        }
      $err += $this->addfieldIfNotExists('glpi_states',
        'is_visible_pluginopenmedismedicaldevice',"tinyint(1) NOT NULL DEFAULT '1'", true);
      $err += $this->renameTableifExists('glpi_plugin_openmedis_items_devicemedicalaccessories', 
        'glpi_plugin_openmedis_items_medicalaccessories');
      $err += $this->renameTableIfExists('glpi_plugin_openmedis_devicemedicalaccessories', 
        'glpi_plugin_openmedis_devicemedicalaccessories');
      $err += $this->addfieldIfNotExists('glpi_plugin_openmedis_medicaldevices',
        'init_usages_counter','int(11) NOT NULL DEFAULT 0');
      $err += $this->addfieldIfNotExists('glpi_plugin_openmedis_medicaldevices',
        'last_usages_counter','int(11) NOT NULL DEFAULT 0');
      $err += $this->renamefieldIfExists('glpi_plugin_openmedis_items_devicemedicalaccessories',
      'plugin_openmedis_devicemedicalaccessories_id','plugin_openmedis_devicemedicalaccessories_id',
      'int(11) NOT NULL DEFAULT 0');
      $err += $this->replaceIndexIfExists('glpi_plugin_openmedis_items_devicemedicalaccessories',
        'plugin_openmedis_medicaldevice_id', 'plugin_openmedis_medicaldevices_id', 'items_id');
    }
    if ($err > 0){
      return false;
    }
    else{
      return true;
    }
  }


}
