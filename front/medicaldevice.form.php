<?php
/**
 * ---------------------------------------------------------------------
 * GLPI - Gestionnaire Libre de Parc Informatique
 * Copyright (C) 2015-2018 Teclib' and contributors.
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

use Glpi\Event;

include ('../../../inc/includes.php');

// Check if plugin is activated...
$plugin = new Plugin();
if (!$plugin->isInstalled('openmedis') || !$plugin->isActivated('openmedis')) {
   Html::displayNotFoundError();
}

Session::checkRight(PluginOpenmedisMedicalDevice::$rightname, READ);

if (empty($_GET["id"])) {
   $_GET["id"] = "";
}
if (!isset($_GET["withtemplate"])) {
   $_GET["withtemplate"] = "";
}

$medicaldevice = new PluginOpenmedisMedicalDevice();

if (isset($_POST["add"])) {
   $medicaldevice->check(-1, CREATE, $_POST);

   if ($newID = $medicaldevice->add($_POST)) {
      Event::log($newID, "PluginOpenMedisMedicalDevice", 4, "inventory",
                 sprintf(__('%1$s adds the item %2$s'), $_SESSION["glpiname"], $_POST["name"]));
      if ($_SESSION['glpibackcreated']) {
         Html::redirect($medicaldevice->getLinkURL());
      }
   }
   Html::back();

} else if (isset($_POST["delete"])) {
   $medicaldevice->check($_POST["id"], DELETE);
   $medicaldevice->delete($_POST);

   Event::log($_POST["id"], "PluginOpenMedisMedicalDevice", 4, "inventory",
              //TRANS: %s is the user login
              sprintf(__('%s deletes an item'), $_SESSION["glpiname"]));
   $medicaldevice->redirectToList();

} else if (isset($_POST["restore"])) {
   $medicaldevice->check($_POST["id"], DELETE);

   $medicaldevice->restore($_POST);
   Event::log($_POST["id"], "PluginOpenMedisMedicalDevice", 4, "inventory",
              //TRANS: %s is the user login
              sprintf(__('%s restores an item'), $_SESSION["glpiname"]));
   $medicaldevice->redirectToList();

} else if (isset($_POST["purge"])) {
   $medicaldevice->check($_POST["id"], PURGE);

   $medicaldevice->delete($_POST, 1);
   Event::log($_POST["id"], "PluginOpenMedisMedicalDevice", 4, "inventory",
              //TRANS: %s is the user login
              sprintf(__('%s purges an item'), $_SESSION["glpiname"]));
   $medicaldevice->redirectToList();

} else if (isset($_POST["update"])) {
   $medicaldevice->check($_POST["id"], UPDATE);

   $medicaldevice->update($_POST);
   Event::log($_POST["id"], "PluginOpenMedisMedicalDevice", 4, "inventory",
              //TRANS: %s is the user login
              sprintf(__('%s updates an item'), $_SESSION["glpiname"]));
   Html::back();

} else if (isset($_POST["unglobalize"])) {
   $medicaldevice->check($_POST["id"], UPDATE);

   Computer_Item::unglobalizeItem($medicaldevice);
   Event::log($_POST["id"], "PluginOpenMedisMedicalDevice", 4, "inventory",
               //TRANS: %s is the user login
               sprintf(__('%s sets unitary management'), $_SESSION["glpiname"]));

   Html::redirect($medicaldevice->getFormURLWithID($_POST["id"]));

} else {
   Html::header(
      PluginOpenmedisMedicalDevice::getTypeName(Session::getPluralNumber()),
      $_SERVER['PHP_SELF'], 
      "assets", 
      'pluginopenmedismedicaldevice');
   $medicaldevice->display(['id'           => $_GET["id"],
                              'withtemplate' => $_GET["withtemplate"]]);
   Html::footer();
}
