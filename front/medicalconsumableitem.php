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

include ('../../../inc/includes.php');


$plugin = new Plugin();
if (!$plugin->isInstalled('openmedis') || !$plugin->isActivated('openmedis')) {
   Html::displayNotFoundError();
}


if (PluginOpenmedisMedicalConsumable::canView()) {
    Html::header(
        PluginOpenmedisMedicalConsumable::getTypeName(Session::getPluralNumber()),
        $_SERVER['PHP_SELF'], 
        "assets", 
        'PluginOpenmedisMedicalConsumableItem');
    Search::show('PluginOpenmedisMedicalConsumableItem');
    Html::footer();
}else{
    Html::displayRightError();
}

