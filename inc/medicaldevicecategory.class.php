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

/// Class PluginOpenmedisMedicalDeviceCategory
class PluginOpenmedisMedicalDeviceCategory extends CommonTreeDropdown {

   public $can_be_translated = true;
  // public $must_be_replace              = true;
   public $dohistory                    = true;

   static $rightname                    = 'plugin_openmedis_medicaldevicecategory';


   static function getTypeName($nb = 0) {
      return _n('Medical device category', 'Medical device categories', $nb, 'openmedis');
   }

   static function getFieldLabel($nb = 0, $v = 0) {
      switch ($v == 0)
      {
         case 2:
            return _n('Code', 'Codes', $nb, 'openmedis');
            break;
         case 1:
            return _n('Generic name', 'Generic names', $nb, 'openmedis');
            break;
         default:
         case 0:
            return _n('Category', 'Categories', $nb, 'openmedis');
            break;
   }


         
   }



   function getAdditionalFields() {

      $tab = [['name'      => 'code',
      'label'     => $this->getFieldLabel(0,2),
      'type'      => 'text',
      'list'      => true],
      ['name'      => 'label',
      'label'     => $this->getFieldLabel(0,1),
      'type'      => 'text',
      'list'      => true],
      
            ['name'      => 'plugin_openmedis_medicaldevicecategories_id',
                         'label'     => __('Parent', 'openmedis'),
                         'type'      => 'dropdownValue',
                         'permit_select_parent' => true,
                         'displaywith' => ['code','label']],
                  ];

      if (!Session::haveRightsOr(PluginOpenmedisMedicalDeviceCategory::$rightname, [CREATE, UPDATE, DELETE])) {

         unset($tab[7]);
      }
      return $tab;

   }
   function rawSearchOptions() {
      $tab                       =  parent::rawSearchOptions();
      $tab[] =[
         'id'                 => '60',
         'table'              => $this->getTable(),
         'field'              => 'code',
         'name'               => __('Code'),
         'datatype'           => 'itemlink',
         'right'              => PluginOpenmedisMedicalDeviceCategory::$rightname
      ];
      $tab[] = [
         'id'                 => '80',
         'table'              => $this->getTable(),
         'field'              => 'label',
         'name'               => __('Label'),
         'datatype'           => 'text',
         'right'              => PluginOpenmedisMedicalDeviceCategory::$rightname
      ];
      $tab[] = [
         'id'                 => '100',
         'table'              => $this->getTable(),
         'field'              => 'comment',
         'name'               => __('Comment'),
         'datatype'           => 'text',
         'right'              => PluginOpenmedisMedicalDeviceCategory::$rightname
      ];

      return $tab;
   }


   // taken fron drop down without name
   function showForm($ID, $options = []) {
      global $CFG_GLPI;

      if (!$this->isNewID($ID)) {
         $this->check($ID, READ);
      } else {
         // Create item
         $this->check(-1, CREATE);
      }
      $this->showFormHeader($options);

      $fields = $this->getAdditionalFields();
      $nb     = count($fields);

      echo "<tr class='tab_bg_1'><td></td>";
      echo "<td>";
      
      echo "</td>";

      echo "<td rowspan='".($nb+1)."'>". __('Comments')."</td>";
      echo "<td rowspan='".($nb+1)."'>
            <textarea cols='45' rows='".($nb+2)."' name='comment' >".$this->fields["comment"];
      echo "</textarea></td>";



      echo "<tr class='tab_bg_1'>";
      echo "<td>".__('Code')."</td>\n";
      echo "<td>";
      echo Html::input("code");
      echo "</td>\n";
      echo "</tr>\n";

      
      echo "<tr class='tab_bg_1'>";
      echo "<td>".__('Label')."</td>\n";
      echo "<td>";
      echo Html::input("label");
      echo "</td>\n";
      echo "</tr>\n";

      echo "</tr>\n";
      echo "<tr>\n";
      echo "<td>".__('Parent', 'openmedis')."</td>\n";
      echo "<td>";

      PluginOpenmedisMedicalDeviceCategory::dropdown(['value' => $this->fields["plugin_openmedis_medicaldevicecategories_id"],
      'permit_select_parent' => true,
      'displaywith' => ['code','label'],
      'entity' => $this->getEntityID(),
      'used'   => ($ID>0 ? getSonsOf($this->getTable(), $ID)
      : [])]);
      echo "</td></tr>\n";


      if (isset($this->fields['is_protected']) && $this->fields['is_protected']) {
         $options['candel'] = false;
      }

      if (isset($_REQUEST['_in_modal'])) {
         echo "<input type='hidden' name='_in_modal' value='1'>";
      }
      $this->showFormButtons($options);

      return true;
   }
   function post_updateItem($history = 1) {
      // define the "name" to generate the "completename"
      $this->updates['name'] = $this->updates['code'].' - '.$this->updates['label'];
      parent::post_updateItem($history);
   }

   static function getIcon() {
      return "fas fa-laptop-medical";
   }
}
