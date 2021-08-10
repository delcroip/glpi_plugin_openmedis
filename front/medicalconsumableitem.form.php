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

use Glpi\Event;

include ('../../../inc/includes.php');

Session::checkRight(PluginOpenmedisMedicalConsumableItem::$rightname, READ);

if (!isset($_GET["id"])) {
   $_GET["id"] = "";
}

$cartype = new PluginOpenmedisMedicalConsumableItem();

if (isset($_POST["add"])) {
   $cartype->check(-1, CREATE, $_POST);

   if ($newID = $cartype->add($_POST)) {
      Event::log($newID, "plugin_openmedis_medicalconsumableitems", 4, "inventory",
                 sprintf(__('%1$s adds the item %2$s', 'openmedis'), $_SESSION["glpiname"], $_POST["name"]));
      if ($_SESSION['glpibackcreated']) {
         Html::redirect($cartype->getLinkURL());
      }
   }
   Html::back();

} else if (isset($_POST["delete"])) {
   $cartype->check($_POST["id"], DELETE);

   if ($cartype->delete($_POST)) {
      Event::log($_POST["id"], "plugin_openmedis_medicalconsumableitems", 4, "inventory",
                 //TRANS: %s is the user login
                 sprintf(__('%s deletes an item', 'openmedis'), $_SESSION["glpiname"]));
   }
   $cartype->redirectToList();

} else if (isset($_POST["restore"])) {
   $cartype->check($_POST["id"], DELETE);

   if ($cartype->restore($_POST)) {
      Event::log($_POST["id"], "plugin_openmedis_medicalconsumableitems", 4, "inventory",
                 //TRANS: %s is the user login
                 sprintf(__('%s restores an item', 'openmedis'), $_SESSION["glpiname"]));
   }
   $cartype->redirectToList();

} else if (isset($_POST["purge"])) {
   $cartype->check($_POST["id"], PURGE);

   if ($cartype->delete($_POST, 1)) {
      Event::log($_POST["id"], "plugin_openmedis_medicalconsumableitems", 4, "inventory",
                 //TRANS: %s is the user login
                 sprintf(__('%s purges an item', 'openmedis'), $_SESSION["glpiname"]));
   }
   $cartype->redirectToList();

} else if (isset($_POST["update"])) {
   $cartype->check($_POST["id"], UPDATE);

   if ($cartype->update($_POST)) {
      Event::log($_POST["id"], "plugin_openmedis_medicalconsumableitems", 4, "inventory",
                 //TRANS: %s is the user login
                 sprintf(__('%s updates an item', 'openmedis'), $_SESSION["glpiname"]));
   }
   Html::back();

} else {
   Html::header(PluginOpenmedisMedicalConsumableItem::getTypeName(Session::getPluralNumber()), 
      $_SERVER['PHP_SELF'], 
      "assets", 
      "PluginOpenmedisMedicalConsumableItem");
   $cartype->display(['id' => $_GET["id"],
      'formoptions'  => "data-track-changes=true"]);
   Html::footer();
}