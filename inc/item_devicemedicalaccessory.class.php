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

/**
 * Relation between item and devices
**/
class PluginOpenmedisItem_DeviceMedicalAccessory extends Item_Devices {

   static public $itemtype_2 = 'PluginOpenmedisDeviceMedicalAccessory';
   static public $items_id_2 = 'plugin_openmedis_devicemedicalaccessories_id';
 /*  private $medicalaccessorytypes_id;
   private $medicalaccessorycategories_id;
   private $part_number;
   private $manufacturing_date;*/
   static protected $notable = false;


   /**
    * @since 0.85
    **/
   static function getSpecificities($specif = '') {

      return [
         'serial'             => parent::getSpecificities('serial'),
         'otherserial'        => parent::getSpecificities('otherserial'),
         'locations_id'       => parent::getSpecificities('locations_id'),
         'states_id'          => parent::getSpecificities('states_id'),
         'manufacturing_date' => [
            'long name' => __('Manufacturing date'),
            'short name' => __('Date'),
            'id'         => 8620,
            'size' => 10,
            'datatype' => 'date'

         ]

      ];
   }
  /* function cloneItem($specif = ''){

   }*/

   
}
