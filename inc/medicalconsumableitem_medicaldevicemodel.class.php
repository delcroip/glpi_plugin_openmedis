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
         default:
            return false;
      }
      return true;
   }

   function getTabNameForItem(CommonGLPI $item, $withtemplate = 0) {

      if (!$withtemplate) {
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
      $instID = $item->getID();

      if (!$item->can($instID, READ)) {
         return false;
      }
      $canedit = $item->canEdit($instID);
      $rand    = mt_rand();

      $iterator = self::getListForItem($item);
      $number = count($iterator);

      $used  = [];
      $datas = [];
      foreach ($iterator as $data) {
         $used[$data["id"]] = $data["id"];
         $datas[$data["linkid"]]  = $data;
      }

      if ($canedit) {
         echo "<div class='firstbloc'>";
         echo "<form name='plugin_openmedis_medicaldevicemodel_form$rand' id='plugin_openmedis_medicaldevicemodel_form$rand' method='post'";
         echo " action='".static::getFormURL()."'>";

         echo "<table class='tab_cadre_fixe'>";
         echo "<tr class='tab_bg_1'>";
         echo "<th colspan='6'>".__('Add a compatible medical device model', 'openmedis')."</th></tr>";

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
         $header_end .= "<th>".__('Name')."</th>";
         $header_end .= "<th>".__('Product Number')."</th>";
         $header_end .= "<th>".__('Comments')."</th></tr>";
         echo $header_begin.$header_top.$header_end;

         foreach ($datas as $linkid => $data) {
            echo "<tr class='tab_bg_1'>";
            if ($canedit) {
               echo "<td width='10'>";
               Html::showMassiveActionCheckBox(__CLASS__, $linkid);
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
            $url = PluginOpenmedisMedicalDevice::getSearchURL()."?".Toolbox::append_params($opt, '&');
            echo "<td class='center'><a href='".$url."'>".($data["name"] ?? '')."</a></td>";
            echo "<td class='center'>".($data["product_number"] ?? '')."</td>";
            echo "<td class='center'>".($data["comment"] ?? '')."</td>";
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
         echo "<div style='background: #ffebee; padding: 10px; margin: 10px 0; border: 1px solid #f44336;'>";
         echo "<p><strong>DEBUG: No items found.</strong> Number is: $number, Datas count: " . count($datas) . "</p>";
         echo "<p>This means either:</p>";
         echo "<ul>";
         echo "<li>No compatible medical device models exist in the database</li>";
         echo "<li>The SQL query is not returning results despite working in phpMyAdmin</li>";
         echo "<li>There's a permission or entity restriction issue</li>";
         echo "<li>The iterator is not working as expected</li>";
         echo "</ul>";
         echo "</div>";

         echo "<p class='center b'>".__('No item found')."</p>";
      }
   }
}