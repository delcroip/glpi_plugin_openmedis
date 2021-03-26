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

use Glpi\Event;

include ('../../../inc/includes.php');

Session::checkRight(PluginOpenmedisMedicalConsumable::$rightname, READ);

$cart    = new PluginOpenmedisMedicalConsumable();
$cartype = new PluginOpenmedisMedicalConsumableItem();

if (isset($_POST["add"])) {
   $cartype->check($_POST["plugin_openmedis_medicalconsumableitems_id"], CREATE);

   for ($i=0; $i<$_POST["to_add"]; $i++) {
      unset($cart->fields["id"]);
      $cart->add($_POST);
   }
   Event::log($_POST["plugin_openmedis_medicalconsumableitems_id"], "plugin_openmedis_medicalconsumableitems", 4, "inventory",
              //TRANS: %s is the user login
              sprintf(__('%s adds medical consumables'), $_SESSION["glpiname"]));
   Html::back();

} else if (isset($_POST["purge"])) {
   $cartype->check($_POST["plugin_openmedis_medicalconsumableitems_id"], PURGE);

   if ($cart->delete($_POST, 1)) {
      Event::log($_POST["plugin_openmedis_medicalconsumableitems_id"], "plugin_openmedis_medicalconsumableitems", 4, "inventory",
                 //TRANS: %s is the user login
                 sprintf(__('%s purges a medical consumable'), $_SESSION["glpiname"]));
   }
   Html::back();

} else if (isset($_POST["install"])) {
   if ($_POST["plugin_openmedis_medicalconsumableitems_id"]) {
      $cartype->check($_POST["plugin_openmedis_medicalconsumableitems_id"], UPDATE);
      for ($i=0; $i<$_POST["nbcart"]; $i++) {
         if ($cart->install($_POST["plugin_openmedis_medicaldevices_id"], $_POST["plugin_openmedis_medicalconsumableitems_id"])) {
            Event::log($_POST["plugin_openmedis_medicaldevices_id"], "plugin_openmedis_medicaldevices", 5, "inventory",
                       //TRANS: %s is the user login
                       sprintf(__('%s installs a medical consumable'), $_SESSION["glpiname"]));
         }
      }
   }
   Html::redirect(PluginOpenmedisMedicalDevice::getFormURLWithID($_POST["plugin_openmedis_medicaldevices_id"]));

} else if (isset($_POST["update"])) {
   $cart->check($_POST["id"], UPDATE);

   if ($cart->update($_POST)) {
      Event::log($_POST["plugin_openmedis_medicaldevices_id"], "plugin_openmedis_medicaldevices", 4, "inventory",
                 //TRANS: %s is the user login
                 sprintf(__('%s updates a medical consumable'), $_SESSION["glpiname"]));
   }
   Html::back();

} else {
   Html::back();
}