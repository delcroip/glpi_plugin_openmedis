<?php
/**
 * ---------------------------------------------------------------------
 * GLPI - Gestionnaire Libre de Parc Informatique
 * Copyright (C) 2015-2021 Teclib' and contributors.
 *
 * http://glpi-project.org
 *
 * based on GLPI - Gestionnaire Libre de Parc Informatique
 * Copyright © 2021 by Patrick delcroix <patrick@pmpd.eu>
 * This file is part of openmedis Plugin for GLPI.
 * ---------------------------------------------------------------------
 *
 * LICENSE
 *
 *
 * GLPI is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * GLPI is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with GLPI. If not, see <http://www.gnu.org/licenses/>.
 * ---------------------------------------------------------------------
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

