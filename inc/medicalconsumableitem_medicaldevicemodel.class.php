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

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access this file directly");
}

// Relation between medicalconsumable and MedicalDeviceModel
// since version 0.84
class PluginOpenmedisMedicalConsumableItem_MedicalDeviceModel extends CommonDBRelation {

   // From CommonDBRelation
   static public $itemtype_1          = 'PluginOpenmedisMedicalConsumableItem';
   static public $items_id_1          = 'plugin_openmedis_medicalconsumableitems_id';

   static public $itemtype_2          = 'PluginOpenmedisMedicalDeviceModel';
   static public $items_id_2          = 'plugin_openmedis_medicaldevicemodels_id';
   static public $checkItem_2_Rights  = self::DONT_CHECK_ITEM_RIGHTS;



   function getForbiddenStandardMassiveAction() {

      $forbidden   = parent::getForbiddenStandardMassiveAction();
      $forbidden[] = 'update';
      return $forbidden;
   }


   static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0) {

      switch ($item->getType()) {
         case 'PluginOpenmedisMedicalConsumableItem' :
            self::showForMedicalConsumable($item);
            break;

      }
      return true;
   }


   function getTabNameForItem(CommonGLPI $item, $withtemplate = 0) {

      if (!$withtemplate && PluginOpenmedisMedicalDevice::canView()) {
         $nb = 0;
         switch ($item->getType()) {
            case 'PluginOpenmedisMedicalConsumableItem' :
               if ($_SESSION['glpishow_count_on_tabs']) {
                  $nb = self::countForItem($item);
               }
               return self::createTabEntry(PluginOpenmedisMedicalDeviceModel::getTypeName(Session::getPluralNumber()), $nb);
         }
      }
      return '';
   }


   /**
    * Show the device model that are compatible with a medical consumable item type
    *
    * @param $item   MedicalConsumable object
    *
    * @return boolean|void
   **/
   static function showForMedicalConsumable(PluginOpenmedisMedicalConsumableItem $item) {

      $instID = $item->getField('id');
      if (!$item->can($instID, READ)) {
         return false;
      }
      $canedit = $item->canEdit($instID);
      $rand    = mt_rand();

      $iterator = self::getListForItem($item);
      $number = count($iterator);

      $used  = [];
      $datas = [];
      while ($data = $iterator->next()) {
         $used[$data["id"]] = $data["id"];
         $datas[$data["linkid"]]  = $data;
      }

      if ($canedit) {
         echo "<div class='firstbloc'>";
         echo "<form name='plugin_openmedis_medicaldevicemodel_form$rand' id='plugin_openmedis_medicaldevicemodel_form$rand' method='post'";
         echo " action='".static::getFormURL()."'>";

         echo "<table class='tab_cadre_fixe'>";
         echo "<tr class='tab_bg_1'>";
         echo "<th colspan='6'>".__('Add a compatible medical device model')."</th></tr>";

         echo "<tr><td class='tab_bg_2 center'>";
         echo "<input type='hidden' name='plugin_openmedis_medicalconsumableitems_id' value='$instID'>";
         PluginOpenmedisMedicalDeviceModel::dropdown(['used' => $used]);
         echo "</td><td class='tab_bg_2 center'>";
         echo "<input type='submit' name='add' value=\""._sx('button', 'Add')."\" class='submit'>";
         echo "</td></tr>";
         echo "</table>";
         Html::closeForm();
         echo "</div>";
      }

      if ($number) {
         echo "<div class='spaced'>";
         if ($canedit) {
            $rand     = mt_rand();
            Html::openMassiveActionsForm('mass'.__CLASS__.$rand);
            $massiveactionparams = ['num_displayed' => min($_SESSION['glpilist_limit'], count($used)),
                              'container'     => 'mass'.__CLASS__.$rand];
            Html::showMassiveActions($massiveactionparams);
         }

         echo "<table class='tab_cadre_fixehov'>";
         $header_begin  = "<tr>";
         $header_top    = '';
         $header_bottom = '';
         $header_end    = '';
         if ($canedit) {
            $header_begin  .= "<th width='10'>";
            $header_top    .= Html::getCheckAllAsCheckbox('mass'.__CLASS__.$rand);
            $header_bottom .= Html::getCheckAllAsCheckbox('mass'.__CLASS__.$rand);
            $header_end    .= "</th>";
         }
         $header_end .= "<th>"._n('Model', 'Models', 1)."</th></tr>";
         echo $header_begin.$header_top.$header_end;

         foreach ($datas as $data) {
            echo "<tr class='tab_bg_1'>";
            if ($canedit) {
               echo "<td width='10'>";
               Html::showMassiveActionCheckBox(__CLASS__, $data["linkid"]);
               echo "</td>";
            }
            $opt = [
               'is_deleted' => 0,
               'criteria'   => [
                  [
                     'field'      => 40, // medicaldevice model
                     'searchtype' => 'equals',
                     'value'      => $data["id"],
                  ]
               ]
            ];
            $url = PluginOpenmedisMedicalDevice::getSearchURL()."?".Toolbox::append_params($opt, '&amp;');
            echo "<td class='center'><a href='".$url."'>".$data["name"]."</a></td>";
            echo "</tr>";
         }
         echo $header_begin.$header_bottom.$header_end;
         echo "</table>";
         if ($canedit) {
            $massiveactionparams['ontop'] = false;
            Html::showMassiveActions($massiveactionparams);
            Html::closeForm();
         }
         echo "</div>";
      } else {
         echo "<p class='center b'>".__('No item found')."</p>";
      }
   }

}