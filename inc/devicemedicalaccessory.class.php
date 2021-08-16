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

class  PluginOpenmedisDeviceMedicalAccessory extends CommonDevice {

   static protected $forward_entity_to = ['PluginOpenmedisItem_DeviceMedicalAccessory', 'Infocom'];
   static $rightname                   = 'plugin_openmedis_devicemedicalaccessory';
   static function getTypeName($nb = 0) {
      return _n('Medical accessory', 'Medical accessories', $nb, 'openmedis');
   }


   function getAdditionalFields() {
      return array_merge(
         parent::getAdditionalFields(),
         [
            [
               'name'  => 'plugin_openmedis_medicalaccessorytypes_id',
               'label' => __('Type'),
               'type'  => 'dropdownValue'
            ],
            [
               'name'  => 'plugin_openmedis_medicaldevicecategories_id',
               'label' => PluginOpenmedisMedicalDeviceCategory::getFieldLabel(0),
               'type'  => 'dropdownValue'
            ],
            [
               'name'   => 'part_number',
               'label'  => __('Part Number'),
               'type'   => 'text'
            ],
            [
               'name'   => 'picture',
               'label'  => __('Picture'),
               'type'   => 'picture'
            ]
         ]
      );
   }


   function rawSearchOptions() {
      $tab = parent::rawSearchOptions();

      $tab[] = [
         'id'                 => '11',
         'table'              => $this->getTable(),
         'field'              => 'part_number',
         'name'               => __('Part Number'),
         'datatype'           => 'string',
         'autocomplete'       => true,
      ];

      $tab[] = [
         'id'                 => '12',
         'table'              => 'glpi_plugin_openmedis_medicalaccessorycategories',
         'field'              => 'name',
         'name'               => PluginOpenmedisMedicalDeviceCategory::getFieldLabel(0),
         'datatype'           => 'dropdown'
      ];

      $tab[] = [
         'id'                 => '13',
         'table'              => 'glpi_plugin_openmedis_medicalaccessorytypes',
         'field'              => 'name',
         'name'               => __('Type'),
         'datatype'           => 'dropdown'
      ];

      return $tab;
   }

   static function getHTMLTableHeader($itemtype, HTMLTableBase $base,
                                      HTMLTableSuperHeader $super = null,
                                      HTMLTableHeader $father = null, array $options = []) {

      $column = parent::getHTMLTableHeader($itemtype, $base, $super, $father, $options);

      if ($column == $father) {
         return $father;
      }

      Manufacturer::getHTMLTableHeader(__CLASS__, $base, $super, $father, $options);
      $base->addHeader('medicalaccessory_type', __('Type'), $super, $father);
      $base->addHeader('medicaldevice_category', PluginOpenmedisMedicalDeviceCategory::getFieldLabel(0), $super, $father);
      $base->addHeader('part_number', sprintf('%1$s', __('Part Number')), $super, $father);
   }

   function getHTMLTableCellForItem(HTMLTableRow $row = null, CommonDBTM $item = null,
                                    HTMLTableCell $father = null, array $options = []) {

      $column = parent::getHTMLTableCellForItem($row, $item, $father, $options);

      if ($column == $father) {
         return $father;
      }

      Manufacturer::getHTMLTableCellsForItem($row, $this, null, $options);

      if ($this->fields["plugin_openmedis_medicalaccessorytypes_id"]) {
         $row->addCell(
            $row->getHeaderByName('medicalaccessory_type'),
            Dropdown::getDropdownName("glpi_plugin_openmedis_medicalaccessorytypes",
            $this->fields["plugin_openmedis_medicalaccessorytypes_id"]),
            $father
         );
      }

      if ($this->fields["plugin_openmedis_medicaldevicecategories_id"]) {
         $row->addCell(
            $row->getHeaderByName('medicaldevice_category'),
            Dropdown::getDropdownName("glpi_plugin_openmedis_medicaldevicecategories",
            $this->fields["plugin_openmedis_medicaldevicecategories_id"]),
            $father
         );
      }

      if ($this->fields["part_number"]) {
         $row->addCell(
            $row->getHeaderByName('part_number'),
            $this->fields['part_number'],
            $father
         );
      }

   }


   function getImportCriteria() {

      return [
         'designation'           => 'equal',
         'plugin_openmedis_medicalaccessorytypes_id' => 'equal',
         'manufacturers_id'      => 'equal',
         'plugin_openmedis_medicaldevicecategories_id' => 'equal',
         'voltage'               => 'delta:10'
      ];
   }
   public static function rawSearchOptionsToAdd($itemtype,$main_joinparams) {
      $tab = [];

      $tab[] = [
         'id'                 => '8610',
         'table'              => 'glpi_plugin_openmedis_devicemedicalaccessories',
         'field'              => 'designation',
         'name'               => static::getTypeName(1),
         'forcegroupby'       => true,
         'massiveaction'      => false,
         'datatype'           => 'string',
         'joinparams'         => [
            'beforejoin'         => [
               'table'              => 'glpi_plugin_openmedis_items_devicemedicalaccessories',
               'joinparams'         => $main_joinparams
            ]
         ]
      ];

      return $tab;
   }

   static function getIcon() {
      return "fas fa-screwdriver";
   }


}
