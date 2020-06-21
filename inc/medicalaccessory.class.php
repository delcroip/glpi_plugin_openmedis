<?php
/**
 * ---------------------------------------------------------------------
 * GLPI - Gestionnaire Libre de Parc Informatique
 * Copyright (C) 2015-2018 Teclib' and contributors.
 *
 * http://glpi-project.org
 *
 * based on GLPI - Gestionnaire Libre de Parc Informatique
 * Copyright (C) 2003-2014 by the INDEPNET Development Team.
 *
 * ---------------------------------------------------------------------
 *
 * LICENSE
 *
 * This file is part of GLPI.
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

class  PluginOpenmedisMedicalAccessory extends CommonDevice {

   static protected $forward_entity_to = ['  PluginOpenmedisItem_MedicalAccessory', 'Infocom'];

   static function getTypeName($nb = 0) {
      return _n('Medical accessory', 'Medical accessories', $nb);
   }


   function getAdditionalFields() {
      return array_merge(
         parent::getAdditionalFields(),
         [
            [
               'name'  => 'medicalaccessorytypes_id',
               'label' => __('Type'),
               'type'  => 'dropdownValue'
            ],
            [
               'name'  => 'medicalaccessorymodels_id',
               'label' => __('Model'),
               'type'  => 'dropdownValue'
            ],
            [
               'name'   => 'part_number',
               'label'  => __('Part Number'),
               'type'   => 'text'
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
         'table'              => 'glpi_plugin_openmedis_medicalaccessorymodels',
         'field'              => 'name',
         'name'               => __('Model'),
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
      $base->addHeader('medicalaccessory_model', __('Model'), $super, $father);
      $base->addHeader('part_number', sprintf('%1$s', __('Part Number')), $super, $father);
   }

   function getHTMLTableCellForItem(HTMLTableRow $row = null, CommonDBTM $item = null,
                                    HTMLTableCell $father = null, array $options = []) {

      $column = parent::getHTMLTableCellForItem($row, $item, $father, $options);

      if ($column == $father) {
         return $father;
      }

      Manufacturer::getHTMLTableCellsForItem($row, $this, null, $options);

      if ($this->fields["medicalaccessorytypes_id"]) {
         $row->addCell(
            $row->getHeaderByName('medicalaccessory_type'),
            Dropdown::getDropdownName("glpi_plugin_openmedis_medicalaccessorytypes",
            $this->fields["medicalaccessorytypes_id"]),
            $father
         );
      }

      if ($this->fields["medicalaccessorymodels_id"]) {
         $row->addCell(
            $row->getHeaderByName('medicalaccessory_model'),
            Dropdown::getDropdownName("glpi_plugin_openmedis_medicalaccessorymodels",
            $this->fields["medicalaccessorymodels_id"]),
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
         'medicalaccessorytypes_id' => 'equal',
         'manufacturers_id'      => 'equal',
         'medicalaccessorymodels_id' => 'equal',
         'voltage'               => 'delta:10'
      ];
   }
}
