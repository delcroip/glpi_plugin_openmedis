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
 @link      https://github.com/delcroip/glpi_open_medis
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
      return _n('Medical device category (e.g. UMDS,GMDN)', 'Medical device categories (e.g. UMDS,GMDN)', $nb);
   }


   function getAdditionalFields() {

      $tab = [['name'      => 'label',
      'label'     => __('Label'),
      'type'      => 'text',
      'list'      => true],
      ['name'      => 'code',
      'label'     => __('Code'),
      'type'      => 'text',
      'list'      => true],
            ['name'      => 'plugin_openmedis_medicalaccessorycategories_id',
                         'label'     => __('Parent'),
                         'type'      => 'dropdownValue'],
         ['name'      => 'picture',
                         'label'     => __('Picture'),
                         'type'      => 'picture'],
                  ];

      if (!Session::haveRightsOr(PluginOpenmedisMedicalDeviceCategory::$rightname, [CREATE, UPDATE, DELETE])) {

         unset($tab[7]);
      }
      return $tab;

   }
   function rawSearchOptions() {
      $tab                       = parent::rawSearchOptions();

      $tab[] = [
         'id'                 => '80',
         'table'              => $this->getTable(),
         'field'              => 'label',
         'name'               => __('Label'),
         'datatype'           => 'text',
         'right'              => PluginOpenmedisMedicalDeviceCategory::$rightname
      ];
      $tab[] = [
         'id'                 => '50',
         'table'              => $this->getTable(),
         'field'              => 'code',
         'name'               => __('Code'),
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


}
