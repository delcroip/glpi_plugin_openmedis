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

class PluginOpenmedisUpgradeTodev {
   /**
    * @param Migration $migration
    */
   function upgrade(Migration $migration) {
      global $DB;

      $migration->setVersion(PLUGIN_OPENMEDIS_VERSION);

      $profileRight = new ProfileRight();

   }
}



