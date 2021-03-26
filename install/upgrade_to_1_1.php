<?php
/**
 * LICENSE
 *
 * Copyright © 2016-2018 Teclib'
 * Copyright © 2010-2018 by the FusionInventory Development Team.
 * Copyright © 2010-2018 by patrick Delcroix <patrick@pmpd.eu>
 *
 * This file is part ofopenMedis Plugin for GLPI.
 *
 *openMedis Plugin for GLPI is a subproject ofopenMedis.openMedis is a mobile
 * device management software.
 *
 *openMedis Plugin for GLPI is free software: you can redistribute it and/or
 * modify it under the terms of the GNU Affero General Public License as published
 * by the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *openMedis Plugin for GLPI is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 * You should have received a copy of the GNU Affero General Public License
 * along with openMedis Plugin for GLPI. If not, see http://www.gnu.org/licenses/.
 * ------------------------------------------------------------------------------
 * @copyright Copyright © 2018 Teclib
 * @license   https://www.gnu.org/licenses/agpl.txt AGPLv3+
 * @link      https://github.com/delcroip/glpi_open_medis

 * ------------------------------------------------------------------------------
 */



if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access this file directly");
}

class PluginOpenmedisUpgradeTo1_1 {

   /**
    * @param Migration $migration
    */
   public function upgrade(Migration $migration) {
    global $DB;
    if (!$DB->tableExists("glpi_plugin_openmedis_medicalconsomables")) {
        if (!$DB->runFile(__DIR__ ."/mysql/upgrade_to_1_1.sql")){
            $this->migration->displayWarning("Error in migration 1.0 to 1.1 : " . $DB->error(), true);
            return false;
        }   
        return true;
    }
    $this->migration->displayWarning("table to be created by the migration already existing : " . $DB->error(), true);
    return false;
  }
}