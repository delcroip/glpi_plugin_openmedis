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

/**
 * PluginOpenmedisState_Item Class
 *
 * This class manages the relationship between states and itemtypes for visibility
 */
class PluginOpenmedisState_Item extends CommonDBRelation
{
   static public $itemtype_1 = 'State';
   static public $items_id_1 = 'states_id';
   static public $itemtype_2 = 'itemtype';
   static public $items_id_2 = 'itemtypes_id';
   static public $checkItem_2_Rights = self::DONT_CHECK_ITEM_RIGHTS;

   static function getTypeName($nb = 0) {
      return _n('State visibility', 'States visibility', $nb, 'openmedis');
   }

   /**
    * Get the table name for this relation
    */
   static function getTable($classname = null) {
      return 'glpi_plugin_openmedis_states_items';
   }

   /**
    * Check if a state is visible for a specific itemtype
    *
    * @param int $states_id State ID
    * @param string $itemtype Item type (e.g., 'PluginOpenmedisMedicalDevice')
    * @return bool True if visible, false otherwise
    */
   static function isStateVisible($states_id, $itemtype) {
      global $DB;

      $iterator = $DB->request([
         'FROM' => self::getTable(),
         'WHERE' => [
            'states_id' => $states_id,
            'itemtype' => $itemtype
         ]
      ]);

      return $iterator->count() > 0;
   }

   /**
    * Get visible states for a specific itemtype
    *
    * @param string $itemtype Item type (e.g., 'PluginOpenmedisMedicalDevice')
    * @return array Array of visible state IDs
    */
   static function getVisibleStates($itemtype) {
      global $DB;

      $states = [];
      $iterator = $DB->request([
         'FROM' => self::getTable(),
         'WHERE' => [
            'itemtype' => $itemtype
         ]
      ]);

      foreach ($iterator as $row) {
         $states[] = $row['states_id'];
      }

      return $states;
   }

   /**
    * Set state visibility for an itemtype
    *
    * @param int $states_id State ID
    * @param string $itemtype Item type
    * @param bool $visible Whether the state should be visible
    * @return bool Success
    */
   static function setStateVisibility($states_id, $itemtype, $visible = true) {
      $relation = new self();

      if ($visible) {
         // Add relationship if it doesn't exist
         if (!$relation->getFromDBByCrit([
            'states_id' => $states_id,
            'itemtype' => $itemtype
         ])) {
            return $relation->add([
               'states_id' => $states_id,
               'itemtype' => $itemtype
            ]);
         }
      } else {
         // Remove relationship if it exists
         return $relation->deleteByCriteria([
            'states_id' => $states_id,
            'itemtype' => $itemtype
         ]);
      }

      return true;
   }
}
