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
class PluginOpenmedisUpgradeTo1_4 extends PluginOpenmedisUpgradeStep {
  var $migration;

   /**
    * @param Migration $migration
    */
   public function upgrade(Migration $migration) {
    $this->migration = $migration;
    global $DB;
    $err = 0;
    /*if (!$DB->fieldExists("glpi_states","")) {
        if (!$DB->runFile(__DIR__ ."/mysql/upgrade_to_1_2.sql")){
        $this->migration->displayWarning("Error in migration 1.1 to 1.2 : " . $DB->error(), true);
            $err++;
        }
    }*/
    
    $this->migrationStep = '1.3 -> 1.4';
    // repalce accessorycat by device cat
    $err += $this->removeTableIfExists('glpi_plugin_openmedis_medicalaccessorycategories');
    $err += $this->renamefieldIfExists('glpi_plugin_openmedis_devicemedicalaccessories', '`plugin_openmedis_medicalaccessorycategories_id`','`plugin_openmedis_medicaldevicecategories_id`', "int(11) DEFAULT NULL", true );
    if ($err > 0){
      return false;
    }
    else{
      return true;
    }
  }


}