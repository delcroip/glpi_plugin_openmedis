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
 * PluginOpenmedisMedicalConsumable class.
 * This class is used to manage medicaldevice medicalconsumables.
 * @see PluginOpenmedisMedicalConsumableItem
 * @author Julien Dombre
 **/
class PluginOpenmedisMedicalConsumable extends CommonDBChild {
   use Glpi\Features\Clonable;
   static $rightname  = 'plugin_openmedis_medicalconsumable';
   // From CommonDBTM
   static protected $forward_entity_to = ['Infocom'];
   public $dohistory                   = true;
   public $no_form_page                = true;

   // From CommonDBChild
   static public $itemtype             = 'PluginOpenmedisMedicalConsumableItem';
   static public $items_id             = 'plugin_openmedis_medicalconsumableitems_id';

   public function getCloneRelations() :array {
      return [
         Infocom::class
      ];
   }

   function getForbiddenStandardMassiveAction() {

      $forbidden   = parent::getForbiddenStandardMassiveAction();
      $forbidden[] = 'update';
      return $forbidden;
   }

   static function getTypeName($n = 0) {
      return _n('Medical consumable', 'Medical consumables', $n, 'openmedis');
   }

   static function showMassiveActionsSubForm(MassiveAction $ma) {

      switch ($ma->getAction()) {
         case 'updateusages' :
            $input = $ma->getInput();
            if (!isset($input['maxusages'])) {
               $input['maxusages'] = '';
            }
            echo "<input type='text' name='usages' value=\"".$input['maxusages']."\" size='6'>";
            echo "<br><br>".Html::submit(_x('button', 'Update'), ['name' => 'massiveaction']);
            return true;
      }
      return parent::showMassiveActionsSubForm($ma);
   }


   static function getNameField() {
      return 'id';
   }


   function prepareInputForAdd($input) {

      $item = static::getItemFromArray(static::$itemtype, static::$items_id, $input);
      if ($item === false) {
         return false;
      }

      return ["plugin_openmedis_medicalconsumableitems_id" => $item->fields["id"],
                   "entities_id"       => $item->getEntityID(),
                   "date_in"           => date("Y-m-d")];
   }


   function post_updateItem($history = 1) {

      if (in_array('usages', $this->updates)) {
         $medicaldevice = new PluginOpenmedisMedicalDevice();
         if ($medicaldevice->getFromDB($this->fields['plugin_openmedis_medicaldevices_id'])
             && (($this->fields['usages'] > $medicaldevice->getField('last_usages_counter'))
                 || ($this->oldvalues['usages'] == $medicaldevice->getField('last_usages_counter')))) {

            $medicaldevice->update(['id'                 => $medicaldevice->getID(),
                                   'last_usages_counter' => $this->fields['usages'] ]);
         }
      }
      parent::post_updateItem($history);
   }


   function getPreAdditionalInfosForName() {

      $ci = new PluginOpenmedisMedicalConsumableItem();
      if ($ci->getFromDB($this->fields['plugin_openmedis_medicalconsumableitems_id'])) {
         return $ci->getName();
      }
      return '';
   }


   static function processMassiveActionsForOneItemtype(MassiveAction $ma, CommonDBTM $item,
                                                       array $ids) {

      switch ($ma->getAction()) {
         case 'uninstall' :
            foreach ($ids as $key) {
               if ($item->can($key, UPDATE)) {
                  if ($item->uninstall($key)) {
                     $ma->itemDone($item->getType(), $key, MassiveAction::ACTION_OK);
                  } else {
                     $ma->itemDone($item->getType(), $key, MassiveAction::ACTION_KO);
                     $ma->addMessage($item->getErrorMessage(ERROR_ON_ACTION));
                  }
               } else {
                  $ma->itemDone($item->getType(), $key, MassiveAction::ACTION_NORIGHT);
                  $ma->addMessage($item->getErrorMessage(ERROR_RIGHT));
               }
            }
            return;

         case 'backtostock' :
            foreach ($ids as $id) {
               if ($item->can($id, UPDATE)) {
                  if ($item->backToStock(["id" => $id])) {
                     $ma->itemDone($item->getType(), $id, MassiveAction::ACTION_OK);
                  } else {
                     $ma->itemDone($item->getType(), $id, MassiveAction::ACTION_KO);
                     $ma->addMessage($item->getErrorMessage(ERROR_ON_ACTION));
                  }
               } else {
                  $ma->itemDone($item->getType(), $id, MassiveAction::ACTION_NORIGHT);
                  $ma->addMessage($item->getErrorMessage(ERROR_RIGHT));
               }
            }
            return;

         case 'updateusages' :
            $input = $ma->getInput();
            if (isset($input['usages'])) {
               foreach ($ids as $key) {
                  if ($item->can($key, UPDATE)) {
                     if ($item->update(['id' => $key,
                                             'usages' => $input['usages']])) {
                        $ma->itemDone($item->getType(), $key, MassiveAction::ACTION_OK);
                     } else {
                        $ma->itemDone($item->getType(), $key, MassiveAction::ACTION_KO);
                        $ma->addMessage($item->getErrorMessage(ERROR_ON_ACTION));
                     }
                  } else {
                     $ma->itemDone($item->getType(), $key, MassiveAction::ACTION_NORIGHT);
                     $ma->addMessage($item->getErrorMessage(ERROR_RIGHT));
                  }
               }
            } else {
               $ma->itemDone($item->getType(), $ids, MassiveAction::ACTION_KO);
            }
            return;
      }
      parent::processMassiveActionsForOneItemtype($ma, $item, $ids);
   }


   /**
    * Send the medicalconsumable back to stock.
    *
    * @since 0.85 (before name was restore)
    * @param array   $input
    * @param integer $history
    * @return bool
    */
   function backToStock(array $input, $history = 1) {
      global $DB;

      $result = $DB->update(
         $this->getTable(), [
            'date_out'     => 'NULL',
            'date_use'     => 'NULL',
            'plugin_openmedis_medicaldevices_id'  => 0
         ], [
            'id' => $input['id']
         ]
      );
      if ($result && ($DB->affectedRows() > 0)) {
         return true;
      }
      return false;
   }


   // SPECIFIC FUNCTIONS

   /**
    * Link a medicalconsumable to a medical device.
    *
    * Link the first unused medicalconsumable of type $Tid to the medical device $pID.
    *
    * @param integer $tID ID of the medicalconsumable
    * @param integer $pID : ID of the medicaldevice
    *
    * @return boolean True if successful
   **/
   function install($pID, $tID) {
      global $DB;

      // Get first unused medicalconsumable
      $iterator = $DB->request([
         'SELECT' => ['id'],
         'FROM'   => $this->getTable(),
         'WHERE'  => [
            'plugin_openmedis_medicalconsumableitems_id'  => $tID,
            'date_use'           => null
         ],
         'LIMIT'  => 1
      ]);

      if (count($iterator)) {
         $result = $iterator->next();
         $cID = $result['id'];
         // Update medicalconsumable taking care of multiple insertion
         $result = $DB->update(
            $this->getTable(), [
               'date_use'     => date('Y-m-d'),
               'plugin_openmedis_medicaldevices_id'  => $pID
            ], [
               'id'        => $cID,
               'date_use'  => null
            ]
         );
         if ($result && ($DB->affectedRows() > 0)) {
            $changes = [
               '0',
               '',
               __('Installing a medical consumable', 'openmedis'),
            ];
            Log::history($pID, 'PluginOpenmedisMedicalDevice', $changes, 0, Log::HISTORY_LOG_SIMPLE_MESSAGE);
            return true;
         }

      } else {
         Session::addMessageAfterRedirect(__('No free medical consumable', 'openmedis'), false, ERROR);
      }
      return false;
   }


   /**
    * Unlink a medicalconsumable from a medicaldevice by medicalconsumable ID.
    *
    * @param integer $ID ID of the medicalconsumable
    *
    * @return boolean
   **/
   function uninstall($ID) {
      global $DB;

      if ($this->getFromDB($ID)) {
         $medicaldevice = new PluginOpenmedisMedicalDevice();
         $toadd   = [];
         if ($medicaldevice->getFromDB($this->getField("plugin_openmedis_medicaldevices_id"))) {
            $toadd['usages'] = $medicaldevice->fields['last_usages_counter'];
         }

         $result = $DB->update(
            $this->getTable(), [
               'date_out'  => date('Y-m-d')
            ] + $toadd, [
               'id'  => $ID
            ]
         );

         if ($result
             && ($DB->affectedRows() > 0)) {
            $changes = [
               '0',
               '',
               __('Uninstalling a medical consumable', 'openmedis'),
            ];
            Log::history($this->getField("plugin_openmedis_medicaldevices_id"), 'PluginOpenmedisMedicalDevice', $changes,
                         0, Log::HISTORY_LOG_SIMPLE_MESSAGE);

            return true;
         }
         return false;
      }
   }


   /**
    * Print the medicalconsumable count HTML array for the medicalconsumable item $tID
    *
    * @param integer         $tID      ID of the medicalconsumable item
    * @param integer         $alarm_threshold Alarm threshold value
    * @param integer|boolean $nohtml          True if the return value should be without HTML tags (default 0/false)
    *
    * @return string String to display
   **/
   static function getCount($tID, $alarm_threshold, $nohtml = 0) {

      // Get total
      $total = self::getTotalNumber($tID);
      $out   = "";
      if ($total != 0) {
         $unused     = self::getUnusedNumber($tID);
         $used       = self::getUsedNumber($tID);
         $old        = self::getOldNumber($tID);
         $highlight  = "";
         if ($unused <= $alarm_threshold) {
            $highlight = "tab_bg_1_2";
         }

         if (!$nohtml) {
            $out .= "<table  class='tab_format $highlight' width='100%'><tr><td>";
            $out .= __('Total')."</td><td>$total";
            $out .= "</td><td class='b'>";
            $out .= _nx('medicalconsumable', 'New', 'New', $unused);
            $out .= "</td><td class='b'>$unused</td></tr>";
            $out .= "<tr><td>";
            $out .= _nx('medicalconsumable', 'Used', 'Used', $used);
            $out .= "</td><td>$used</td><td>";
            $out .= _nx('medicalconsumable', 'Worn', 'Worn', $old);
            $out .= "</td><td>$old</td></tr></table>";

         } else {
            //TRANS : for display medicalconsumables count : %1$d is the total number,
            //        %2$d the new one, %3$d the used one, %4$d worn one
            $out .= sprintf(__('Total: %1$d (%2$d new, %3$d used, %4$d worn)', 'openmedis'),
                            $total, $unused, $used, $old);
         }

      } else {
         if (!$nohtml) {
            $out .= "<div class='tab_bg_1_2'><i>".__('No medical consumable', 'openmedis')."</i></div>";
         } else {
            $out .= __('No medical consumable', 'openmedis');
         }
      }
      return $out;
   }


   /**
    * Print the medicalconsumable count HTML array for the medicaldevice $pID
    *
    * @since 0.85
    *
    * @param integer         $pID    ID of the medicaldevice
    * @param integer|boolean $nohtml True if the return value should be without HTML tags (default 0/false)
    *
    * @return string String to display
   **/
   static function getCountForMedicalDevice($pID, $nohtml = 0) {

      // Get total
      $total = self::getTotalNumberForMedicalDevice($pID);
      $out   = "";
      if ($total != 0) {
         $used       = self::getUsedNumberForMedicalDevice($pID);
         $old        = self::getOldNumberForMedicalDevice($pID);
         $highlight  = "";
         if ($used == 0) {
            $highlight = "tab_bg_1_2";
         }

         if (!$nohtml) {
            $out .= "<table  class='tab_format $highlight' width='100%'><tr><td>";
            $out .= __('Total')."</td><td>$total";
            $out .= "</td><td colspan='2'></td><tr>";
            $out .= "<tr><td>";
            $out .= _nx('medicalconsumable', 'Used', 'Used', $used);
            $out .= "</td><td>$used</span></td><td>";
            $out .= _nx('medicalconsumable', 'Worn', 'Worn', $old);
            $out .= "</td><td>$old</span></td></tr></table>";

         } else {
            //TRANS : for display medicalconsumables count : %1$d is the total number,
            //        %2$d the used one, %3$d the worn one
            $out .= sprintf(__('Total: %1$d (%2$d used, %3$d worn)', 'openmedis'), $total, $used, $old);
         }

      } else {
         if (!$nohtml) {
            $out .= "<div class='tab_bg_1_2'><i>".__('No medical consumable', 'openmedis')."</i></div>";
         } else {
            $out .= __('No medical consumable', 'openmedis');
         }
      }
      return $out;
   }


   /**
    * Count the total number of medicalconsumables for the medicalconsumable item $tID.
    *
    * @param integer $tID ID of medicalconsumable item.
    *
    * @return integer Number of medicalconsumables counted.
   **/
   static function getTotalNumber($tID) {
      global $DB;

      $row = $DB->request([
         'FROM'   => self::getTable(),
         'COUNT'  => 'cpt',
         'WHERE'  => ['plugin_openmedis_medicalconsumableitems_id' => $tID]
      ])->next();
      return $row['cpt'];
   }


   /**
    * Count the number of medicalconsumables used for the medicaldevice $pID
    *
    * @since 0.85
    *
    * @param integer $pID ID of the medicaldevice.
    *
    * @return integer Number of medicalconsumables counted.
   **/
   static function getTotalNumberForMedicalDevice($pID) {
      global $DB;

      $row = $DB->request([
         'FROM'   => self::getTable(),
         'COUNT'  => 'cpt',
         'WHERE'  => ['plugin_openmedis_medicaldevices_id' => $pID]
      ])->next();
      return (int)$row['cpt'];
   }


   /**
    * Count the number of used medicalconsumables for the medicalconsumable item $tID.
    *
    * @param integer $tID ID of the medicalconsumable item.
    *
    * @return integer Number of used medicalconsumables counted.
   **/
   static function getUsedNumber($tID) {
      global $DB;

      $row = $DB->request([
         'SELECT' => ['id'],
         'COUNT'  => 'cpt',
         'FROM'   => 'glpi_plugin_openmedis_medicalconsumables',
         'WHERE'  => [
            'plugin_openmedis_medicalconsumableitems_id'  => $tID,
            'date_out'           => null,
            'NOT'                => [
               'date_use'  => null
            ]
         ]
      ])->next();
      return (int)$row['cpt'];
   }


   /**
    * Count the number of used medicalconsumables used for the medicaldevice $pID.
    *
    * @since 0.85
    *
    * @param integer $pID ID of the medicaldevice.
    *
    * @return integer Number of used medicalconsumable counted.
   **/
   static function getUsedNumberForMedicalDevice($pID) {
      global $DB;

      $result = $DB->request([
         'COUNT'  => 'cpt',
         'FROM'   => self::getTable(),
         'WHERE'  => [
            'plugin_openmedis_medicaldevices_id'  => $pID,
            'date_out'     => null,
            'NOT'          => ['date_use' => null]
         ]
      ])->next();
      return $result['cpt'];
   }


   /**
    * Count the number of old medicalconsumables for the medicalconsumable item $tID.
    *
    * @param integer $tID ID of the medicalconsumable item.
    *
    * @return integer Number of old medicalconsumables counted.
   **/
   static function getOldNumber($tID) {
      global $DB;

      $result = $DB->request([
         'COUNT'  => 'cpt',
         'FROM'   => self::getTable(),
         'WHERE'  => [
            'plugin_openmedis_medicalconsumableitems_id'  => $tID,
            'NOT'                => ['date_out' => null]
         ]
      ])->next();
      return $result['cpt'];
   }


   /**
    * count how many old medical consumable for themedicaldevice $pID
    *
    * @since 0.85
    *
    * @param $pID integer: medicaldevice identifier.
    *
    * @return integer : number of old medicalconsumable counted.
   **/
   static function getOldNumberForMedicalDevice($pID) {
      global $DB;

      $result = $DB->request([
         'COUNT'  => 'cpt',
         'FROM'   => self::getTable(),
         'WHERE'  => [
            'plugin_openmedis_medicaldevices_id'  => $pID,
            'NOT'          => ['date_out' => null]
         ]
      ])->next();
      return $result['cpt'];
   }


   /**
    * count how many medical consumable unused for the medicalconsumable item $tID
    *
    * @param $tID integer: medicalconsumable item identifier.
    *
    * @return integer : number of medicalconsumable unused counted.
   **/
   static function getUnusedNumber($tID) {
      global $DB;

      $result = $DB->request([
         'COUNT'  => 'cpt',
         'FROM'   => self::getTable(),
         'WHERE'  => [
            'plugin_openmedis_medicalconsumableitems_id'  => $tID,
            'date_use'           => null
         ]
      ])->next();
      return $result['cpt'];
   }


   /**
    * Get the translated value for the status of a medicalconsumable based on the use and out date (if any).
    *
    * @param string $date_use  Date of use (May be null or empty)
    * @param string $date_out  Date of delete (May be null or empty)
    *
    * @return string : Translated value for the medicalconsumable status.
   **/
   static function getStatus($date_use, $date_out) {

      if (is_null($date_use) || empty($date_use)) {
         return _nx('medicalconsumable', 'New', 'New', 1);
      }
      if (is_null($date_out) || empty($date_out)) {
         return _nx('medicalconsumable', 'Used', 'Used', 1);
      }
      return _nx('medicalconsumable', 'Worn', 'Worn', 1);
   }


   /**
    * Print out the medicalconsumables of a defined type
    *
    * @param PluginOpenmedisMedicalConsumableItem   $cartitem  The medicalconsumable item
    * @param boolean|integer $show_old  Show old medicalconsumables or not (default 0/false)
    *
    * @return boolean|void
   **/
   static function showForMedicalConsumableItem(PluginOpenmedisMedicalConsumableItem $cartitem, $show_old = 0) {
      global $DB;

      $tID = $cartitem->getField('id');
      if (!$cartitem->can($tID, READ)) {
         return false;
      }
      $canedit = $cartitem->can($tID, UPDATE);

      $where = ['glpi_plugin_openmedis_medicalconsumables.plugin_openmedis_medicalconsumableitems_id' => $tID];
      $order = [
         'glpi_plugin_openmedis_medicalconsumables.date_use ASC',
         'glpi_plugin_openmedis_medicalconsumables.date_out DESC',
         'glpi_plugin_openmedis_medicalconsumables.date_in'
      ];

      if (!$show_old) { // NEW
         $where['glpi_plugin_openmedis_medicalconsumables.date_out'] = null;
         $order = [
            'glpi_plugin_openmedis_medicalconsumables.date_out ASC',
            'glpi_plugin_openmedis_medicalconsumables.date_use ASC',
            'glpi_plugin_openmedis_medicalconsumables.date_in'
         ];
      } else { //OLD
         $where['NOT'] = ['glpi_plugin_openmedis_medicalconsumables.date_out' => null];
      }

      $stock_time       = 0;
      $use_time         = 0;
      $use_done    = 0;
      $nb_use_done = 0;

      $iterator = $DB->request([
         'SELECT' => [
            'glpi_plugin_openmedis_medicalconsumables.*',
            'glpi_plugin_openmedis_medicaldevices.id AS printID',
            'glpi_plugin_openmedis_medicaldevices.name AS printname',
            'glpi_plugin_openmedis_medicaldevices.init_usages_counter'
         ],
         'FROM'   => self::gettable(),
         'LEFT JOIN' => [
            'glpi_plugin_openmedis_medicaldevices'   => [
               'FKEY'   => [
                  self::getTable()  => 'plugin_openmedis_medicaldevices_id',
                  'glpi_plugin_openmedis_medicaldevices'   => 'id'
               ]
            ]
         ],
         'WHERE'     => $where,
         'ORDER'     => $order
      ]);

      $number = count($iterator);

      echo "<div class='spaced'>";
      if ($canedit && $number) {
         $rand = mt_rand();
         Html::openMassiveActionsForm('mass'.__CLASS__.$rand);
         $actions = ['delete' => _x('button', 'Delete permanently'),
                     'Infocom'.MassiveAction::CLASS_ACTION_SEPARATOR.'activate'
                              => __('Enable the financial and administrative information')
                          ];
         if (!$show_old) {
            $actions['PluginOpenmedisMedicalConsumable'.MassiveAction::CLASS_ACTION_SEPARATOR.'backtostock']
                  = __('Back to stock');
         }
         $massiveactionparams = ['num_displayed'    => min($_SESSION['glpilist_limit'], $number),
                                      'specific_actions' => $actions,
                                      'container'        => 'mass'.__CLASS__.$rand,
                                      'rand'             => $rand];
         Html::showMassiveActions($massiveactionparams);
      }
      echo "<table class='tab_cadre_fixehov'>";
      if (!$show_old) {
         echo "<tr class='noHover'><th colspan='".($canedit?'7':'6')."'>".
               self::getCount($tID, -1)."</th>";
         echo "</tr>";
      } else { // Old
         echo "<tr class='noHover'><th colspan='".($canedit?'9':'8')."'>".__('Worn medical consumables', 'openmedis');
         echo "</th></tr>";
      }

      $header_begin  = "<tr>";
      $header_top    = '';
      $header_bottom = '';
      $header_end    = '';

      if ($canedit && $number) {
         $header_begin  .= "<th width='10'>";
         $header_top     = Html::getCheckAllAsCheckbox('mass'.__CLASS__.$rand);
         $header_bottom  = Html::getCheckAllAsCheckbox('mass'.__CLASS__.$rand);
         $header_end    .= "</th>";
      }
      $header_end .= "<th>".__('ID')."</th>";
      $header_end .= "<th>"._x('item', 'State')."</th>";
      $header_end .= "<th>".__('Add date')."</th><th>".__('Use date')."</th>";
      $header_end .= "<th>".__('Used on')."</th>";

      if ($show_old) {
         $header_end .= "<th>".__('End date')."</th>";
         $header_end .= "<th>".__('Medical device counter', 'openmedis')."</th>";
      }

      $header_end .= "<th width='18%'>".__('Financial and administrative information')."</th>";
      $header_end .= "</tr>";
      echo $header_begin.$header_top.$header_end;

      $usages = [];

      if ($number) {
         while ($data = $iterator->next()) {
            $date_in  = Html::convDate($data["date_in"]);
            $date_use = Html::convDate($data["date_use"]);
            $date_out = Html::convDate($data["date_out"]);
            $medicaldevice  = $data["plugin_openmedis_medicaldevices_id"];

            echo "<tr class='tab_bg_1'>";
            if ($canedit) {
               echo "<td width='10'>";
               Html::showMassiveActionCheckBox(__CLASS__, $data["id"]);
               echo "</td>";
            }
            echo "<td>".$data['id'].'</td>';
            echo "<td class='center'>".self::getStatus($data["date_use"], $data["date_out"]);
            echo "</td><td class='center'>".$date_in."</td>";
            echo "<td class='center'>".$date_use."</td>";
            echo "<td class='center'>";
            if (!is_null($date_use)) {
               if ($data["printID"] > 0) {
                  $printname = $data["printname"];
                  if ($_SESSION['glpiis_ids_visible'] || empty($printname)) {
                     $printname = sprintf(__('%1$s (%2$s)', 'openmedis'), $printname, $data["printID"]);
                  }
                  echo "<a href='".PluginOpenmedisMedicalDevice::getFormURLWithID($data["printID"])."'><span class='b'>".$printname."</span></a>";
               } else {
                  echo NOT_AVAILABLE;
               }
               $tmp_dbeg       = explode("-", $data["date_in"]);
               $tmp_dend       = explode("-", $data["date_use"]);
               $stock_time_tmp = mktime(0, 0, 0, $tmp_dend[1], $tmp_dend[2], $tmp_dend[0])
                                 - mktime(0, 0, 0, $tmp_dbeg[1], $tmp_dbeg[2], $tmp_dbeg[0]);
               $stock_time    += $stock_time_tmp;
            }
            if ($show_old) {
               echo "</td><td class='center'>";
               echo $date_out;
               $tmp_dbeg      = explode("-", $data["date_use"]);
               $tmp_dend      = explode("-", $data["date_out"]);
               $use_time_tmp  = mktime(0, 0, 0, $tmp_dend[1], $tmp_dend[2], $tmp_dend[0])
                                 - mktime(0, 0, 0, $tmp_dbeg[1], $tmp_dbeg[2], $tmp_dbeg[0]);
               $use_time     += $use_time_tmp;
            }

            echo "</td>";
            if ($show_old) {
               // Get initial counter page
               if (!isset($usages[$medicaldevice])) {
                  $usages[$medicaldevice] = $data['init_usages_counter'];
               }
               echo "<td class='center'>";
               if ($usages[$medicaldevice] < $data['usages']) {
                  $use_done   += $data['usages']-$usages[$medicaldevice];
                  $nb_use_done++;
                  $pp               = $data['usages']-$usages[$medicaldevice];
                  printf(_n('%d usage', '%d  usages', $pp, 'openmedis'), $pp);
                  $usages[$medicaldevice]  = $data['usages'];
               } else if ($data['usages'] != 0) {
                  echo "<span class='tab_bg_1_2'>".__('Counter error', 'openmedis')."</span>";
               }
               echo "</td>";
            }
            echo "<td class='center'>";
            Infocom::showDisplayLink('PluginOpenmedisMedicalConsumable', $data["id"]);
            echo "</td>";
            echo "</tr>";
         }
         if ($show_old
             && ($number > 0)) {
            if ($nb_use_done == 0) {
                $nb_use_done = 1;
            }
            echo "<tr class='tab_bg_2'><td colspan='".($canedit?'4':'3')."'>&nbsp;</td>";
            echo "<td class='center b'>".__('Average time in stock')."<br>";
            $rounded_stock_time = round($stock_time/$number/60/60/24/30.5, 1);
            echo $rounded_stock_time." "._n('month', 'months', $rounded_stock_time)."</td>";
            echo "<td>&nbsp;</td>";
            echo "<td class='center b'>".__('Average time in use')."<br>";
            $rounded_use_time = round($use_time/$number/60/60/24/30.5, 1);
            echo $rounded_use_time." "._n('month', 'months', $rounded_use_time)."</td>";
            echo "<td class='center b'>".__('Average number of usages', 'openmedis')."<br>";
            echo round($use_done/$nb_use_done)."</td>";
            echo "<td colspan='".($canedit?'3':'1')."'>&nbsp;</td></tr>";
         } else {
            echo $header_begin.$header_bottom.$header_end;
         }
      }

      echo "</table>";
      if ($canedit && $number) {
         $massiveactionparams['ontop'] = false;
         Html::showMassiveActions($massiveactionparams);
         Html::closeForm();
      }
      echo "</div>\n\n";
   }


   /**
    * Print out a link to add directly a new medicalconsumable from a medicalconsumable item.
    *
    * @param $cartitem  PluginOpenmedisMedicalConsumableItem object
    *
    * @return boolean|void
   **/
   static function showAddForm(PluginOpenmedisMedicalConsumableItem $cartitem) {

      $ID = $cartitem->getField('id');
      if (!$cartitem->can($ID, UPDATE)) {
         return false;
      }
      if ($ID > 0) {
         echo "<div class='firstbloc'>";
         echo "<form method='post' action=\"".static::getFormURL()."\">";
         echo "<table class='tab_cadre_fixe'>";
         echo "<tr><td class='center tab_bg_2' width='20%'>";
         echo "<input type='hidden' name='plugin_openmedis_medicalconsumableitems_id' value='$ID'>\n";
         Dropdown::showNumber('to_add', ['value' => 1,
                                              'min'   => 1,
                                              'max'   => 100]);
         echo "</td><td>";
         echo " <input type='submit' name='add' value=\"".__('Add medical consumables', 'openmedis')."\"
                class='submit'>";
         echo "</td></tr>";
         echo "</table>";
         Html::closeForm();
         echo "</div>";
      }
   }


   /**
    * Show installed medicalconsumables
    *
    * @since 0.84 (before showInstalled)
    *
    * @param PluginOpenmedisMedicalDevice         $medicaldevice PluginOpenmedisMedicalDevice object
    * @param boolean|integer $old     Old medicalconsumables or not? (default 0/false)
    *
    * @return boolean|void
   **/
   static function showForMedicalDevice(PluginOpenmedisMedicalDevice $medicaldevice, $old = 0) {
      global $DB, $CFG_GLPI;

      $instID = $medicaldevice->getField('id');
      if (!self::canView()) {
         return false;
      }
      $canedit = Session::haveRight(PluginOpenmedisMedicalConsumable::$rightname, UPDATE);
      $rand    = mt_rand();

      $where = ['glpi_plugin_openmedis_medicalconsumables.plugin_openmedis_medicaldevices_id' => $instID];
      if ($old) {
         $where['NOT'] = ['glpi_plugin_openmedis_medicalconsumables.date_out' => null];
      } else {
         $where['glpi_plugin_openmedis_medicalconsumables.date_out'] = null;
      }
      $iterator = $DB->request([
         'SELECT'    => [
            'glpi_plugin_openmedis_medicalconsumableitems.id AS tID',
            'glpi_plugin_openmedis_medicalconsumableitems.is_deleted',
            'glpi_plugin_openmedis_medicalconsumableitems.ref AS ref',
            'glpi_plugin_openmedis_medicalconsumableitems.name AS type',
            'glpi_plugin_openmedis_medicalconsumables.id',
            'glpi_plugin_openmedis_medicalconsumables.usages AS usages',
            'glpi_plugin_openmedis_medicalconsumables.date_use AS date_use',
            'glpi_plugin_openmedis_medicalconsumables.date_out AS date_out',
            'glpi_plugin_openmedis_medicalconsumables.date_in AS date_in',
            'glpi_plugin_openmedis_medicalconsumableitemtypes.name AS typename'
         ],
         'FROM'      => self::getTable(),
         'LEFT JOIN' => [
            'glpi_plugin_openmedis_medicalconsumableitems'      => [
               'FKEY'   => [
                  self::getTable()        => 'plugin_openmedis_medicalconsumableitems_id',
                  'glpi_plugin_openmedis_medicalconsumableitems'   => 'id'
               ]
            ],
            'glpi_plugin_openmedis_medicalconsumableitemtypes'  => [
               'FKEY'   => [
                  'glpi_plugin_openmedis_medicalconsumableitems'      => 'plugin_openmedis_medicalconsumableitemtypes_id',
                  'glpi_plugin_openmedis_medicalconsumableitemtypes'  => 'id'
               ]
            ]
         ],
         'WHERE'     => $where,
         'ORDER'     => [
            'glpi_plugin_openmedis_medicalconsumables.date_out ASC',
            'glpi_plugin_openmedis_medicalconsumables.date_use DESC',
            'glpi_plugin_openmedis_medicalconsumables.date_in',
         ]
      ]);

      $number = count($iterator);

      if ($canedit && !$old) {
         echo "<div class='firstbloc'>";
         echo "<form method='post' action=\"".static::getFormURL()."\">";
         echo "<table class='tab_cadre_fixe'>";
         echo "<tr><td class='center tab_bg_2' width='50%'>";
         echo "<input type='hidden' name='plugin_openmedis_medicaldevices_id' value='$instID'>\n";
         if (PluginOpenmedisMedicalConsumableItem::dropdownForMedicalDevice($medicaldevice)) {
            //TRANS : multiplier
            echo "</td><td>".__('x', 'openmedis')."&nbsp;";
            Dropdown::showNumber("nbcart", ['value' => 1,
                                                 'min'   => 1,
                                                 'max'   => 5]);
            echo "</td><td><input type='submit' name='install' value=\""._sx('button', 'Install')."\"
                                  class='submit'>";

         } else {
            echo __('No medical consumable available', 'openmedis');
         }

         echo "</td></tr>";
         echo "</table>";
         Html::closeForm();
         echo "</div>";
      }

      echo "<div id='viewmedicalconsumable$rand'></div>";
      // TODO ADD init_usages_counter to Medical device, last_usages_counter, 
      $usages = $medicaldevice->fields['init_usages_counter'];
      echo "<div class='spaced'>";
      if ($canedit && $number) {
         Html::openMassiveActionsForm('mass'.__CLASS__.$rand);
         if (!$old) {
            $actions = [__CLASS__.MassiveAction::CLASS_ACTION_SEPARATOR.'uninstall'
                                       => __('End of life'),
                             __CLASS__.MassiveAction::CLASS_ACTION_SEPARATOR.'backtostock'
                                       => __('Back to stock')
                            ];
         } else {
            $actions = [__CLASS__.MassiveAction::CLASS_ACTION_SEPARATOR.'updateusages'
                                      => __('Update medical device counter', 'openmedis'),
                             'purge' => _x('button', 'Delete permanently')];
         }
         $massiveactionparams = ['num_displayed'    => min($_SESSION['glpilist_limit'], $number),
                           'specific_actions' => $actions,
                           'container'        => 'mass'.__CLASS__.$rand,
                           'rand'             => $rand,
                           'extraparams'      => ['maxusages'
                                                       => $medicaldevice->fields['last_usages_counter']]];
         Html::showMassiveActions($massiveactionparams);
      }
      echo "<table class='tab_cadre_fixehov'>";
      echo "<tr class='noHover'>";
      if ($old == 0) {
         echo "<th colspan='".($canedit?'6':'5')."'>".__('Used medical consumable', 'openmedis')."</th>";
      } else {
         echo "<th colspan='".($canedit?'9':'8')."'>".__('Worn medical consumable', 'openmedis')."</th>";
      }
      echo "</tr>";

      $header_begin  = "<tr>";
      $header_top    = '';
      $header_end    = '';

      if ($canedit) {
         $header_begin  .= "<th width='10'>";
         $header_top    .= Html::getCheckAllAsCheckbox('mass'.__CLASS__.$rand);
         $header_end    .= "</th>";
      }
      $header_end .= "<th>".__('ID')."</th><th>".SELF::getTypeName(1)."</th>";
      $header_end .= "<th>".PluginOpenmedisMedicalConsumableItemType::getTypeName(1)."</th>";
      $header_end .= "<th>".__('Add date')."</th>";
      $header_end .= "<th>".__('Use date')."</th>";
      if ($old != 0) {
         $header_end .= "<th>".__('End date')."</th>";
         $header_end .= "<th>".__('Medical device counter', 'openmedis')."</th>";
         $header_end .= "<th>".__('Medical consumable usages', 'openmedis')."</th>";
      }
      $header_end .= "</tr>";
      echo $header_begin.$header_top.$header_end;

      $stock_time       = 0;
      $use_time         = 0;
      $use_done    = 0;
      $nb_use_done = 0;

      while ($data = $iterator->next()) {
         $cart_id    = $data["id"];
         $typename   = $data["typename"];
         $date_in    = Html::convDate($data["date_in"]);
         $date_use   = Html::convDate($data["date_use"]);
         $date_out   = Html::convDate($data["date_out"]);
         $viewitemjs = ($canedit ? "style='cursor:pointer' onClick=\"viewEditPluginOpenmedisMedicalConsumable".$cart_id.
                        "$rand();\"" : '');
         echo "<tr class='tab_bg_1".($data["is_deleted"]?"_2":"")."'>";
         if ($canedit) {
            echo "<td width='10'>";
            Html::showMassiveActionCheckBox(__CLASS__, $cart_id);
            echo "</td>";
         }
         echo "<td class='center' $viewitemjs>";
         if ($canedit) {
            echo "\n<script type='text/javascript' >\n";
            echo "function viewEditPluginOpenmedisMedicalConsumable". $cart_id."$rand() {\n";
            $params = ['type'        => __CLASS__,
                            'parenttype'  => 'PluginOpenmedisMedicalDevice',
                            'plugin_openmedis_medicaldevices_id' => $medicaldevice->fields["id"],
                            'id'          => $cart_id];
            Ajax::updateItemJsCode("viewmedicalconsumable$rand",
                                   $CFG_GLPI["root_doc"]."/ajax/viewsubitem.php", $params);
            echo "};";
            echo "</script>\n";
         }
         echo $data["id"]."</td>";
         echo "<td class='center' $viewitemjs>";
         echo "<a href=\"".PluginOpenmedisMedicalConsumableItem::getFormURLWithID($data["tID"])."\">";
         printf(__('%1$s - %2$s'), $data["type"], $data["ref"]);
         echo "</a></td>";
         echo "<td class='center' $viewitemjs>".$typename."</td>";
         echo "<td class='center' $viewitemjs>".$date_in."</td>";
         echo "<td class='center' $viewitemjs>".$date_use."</td>";

         $tmp_dbeg       = explode("-", $data["date_in"]);
         $tmp_dend       = explode("-", $data["date_use"]);

         $stock_time_tmp = mktime(0, 0, 0, $tmp_dend[1], $tmp_dend[2], $tmp_dend[0])
                           - mktime(0, 0, 0, $tmp_dbeg[1], $tmp_dbeg[2], $tmp_dbeg[0]);
         $stock_time    += $stock_time_tmp;
         if ($old != 0) {
            echo "<td class='center' $viewitemjs>".$date_out;

            $tmp_dbeg      = explode("-", $data["date_use"]);
            $tmp_dend      = explode("-", $data["date_out"]);

            $use_time_tmp  = mktime(0, 0, 0, $tmp_dend[1], $tmp_dend[2], $tmp_dend[0])
                              - mktime(0, 0, 0, $tmp_dbeg[1], $tmp_dbeg[2], $tmp_dbeg[0]);
            $use_time     += $use_time_tmp;

            echo "</td><td class='numeric' $viewitemjs>".$data['usages']."</td>";
            echo "<td class='numeric' $viewitemjs>";

            if ($usages < $data['usages']) {
               $use_done   += $data['usages']-$usages;
               $nb_use_done++;
               $pp               = $data['usages']-$usages;
               echo $pp;
               $usages            = $data['usages'];
            } else {
               echo "&nbsp;";
            }
            echo "</td>";
         }
         echo "</tr>";
      }

      if ($old) { // use average
         if ($number > 0) {
            if ($nb_use_done == 0) {
               $nb_use_done = 1;
            }
            echo "<tr class='tab_bg_2'><td colspan='".($canedit?"4":'3')."'>&nbsp;</td>";
            echo "<td class='center b'>".__('Average time in stock')."<br>";
            $time_stock = round($stock_time/$number/60/60/24/30.5, 1);
            echo sprintf(_n('%d month', '%d months', $time_stock, 'openmedis'), $time_stock)."</td>";
            echo "<td class='center b'>".__('Average time in use')."<br>";
            $time_use = round($use_time/$number/60/60/24/30.5, 1);
            echo sprintf(_n('%d month', '%d months', $time_use, 'openmedis'), $time_use)."</td>";
            echo "<td class='center b' colspan='2'>".__('Average number of  usages')."<br>";
            echo round($use_done/$nb_use_done)."</td>";
            echo "</tr>";
         }
      }

      echo "</table>";
      if ($canedit && $number) {
         $massiveactionparams['ontop'] = false;
         Html::showMassiveActions($massiveactionparams);
         Html::closeForm();
      }
      echo "</div>\n\n";
   }


   /**
    * Show form for PluginOpenmedisMedicalConsumable
    * @since 0.84
    *
    * @param integer $ID       Id of the medical consumable
    * @param array   $options  Array of possible options:
    *     - parent Object : the medicaldevices where the medical consumable is used
    *
    * @return boolean False if there was a rights issue. Otherwise, returns true.
    */
   function showForm($ID, $options = []) {

      if (isset($options['parent']) && !empty($options['parent'])) {
         $medicaldevice = $options['parent'];
      }
      if (!$this->getFromDB($ID)) {
         return false;
      }
      $medicaldevice = new PluginOpenmedisMedicalDevice;
      $medicaldevice->check($this->getField('plugin_openmedis_medicaldevices_id'), UPDATE);

      $cartitem = new PluginOpenmedisMedicalConsumableItem;
      $cartitem->getFromDB($this->getField('plugin_openmedis_medicalconsumableitems_id'));

      $is_old  = !empty($this->fields['date_out']);
      $is_used = !empty($this->fields['date_use']);

      $options['colspan'] = 2;
      $options['candel']  = false; // Do not permit delete here
      $options['canedit'] = $is_used; // Do not permit edit if cart is not used
      $this->showFormHeader($options);

      echo "<tr class='tab_bg_1'>";
      echo "<td>".PluginOpenmedisMedicalDevice::getTypeName(1)."</td><td>";
      echo $medicaldevice->getLink();
      echo "<input type='hidden' name='plugin_openmedis_medicaldevices_id' value='".$this->getField('plugin_openmedis_medicaldevices_id')."'>\n";
      echo "<input type='hidden' name='plugin_openmedis_medicalconsumableitems_id' value='".
             $this->getField('plugin_openmedis_medicalconsumableitems_id')."'>\n";
      echo "</td>\n";
      echo "<td>".__('Medical consumable model',  'openmedis')."</td>";
      echo "<td>".$cartitem->getLink()."</td></tr>\n";

      echo "<tr class='tab_bg_1'>";
      echo "<td>".__('Add date')."</td>";
      echo "<td>".Html::convDate($this->fields["date_in"])."</td>";

      echo "<td>".__('Use date')."</td><td>";
      if ($is_used && !$is_old) {
         Html::showDateField("date_use", ['value'      => $this->fields["date_use"],
                                               'maybeempty' => false,
                                               'canedit'    => true,
                                               'min'        => $this->fields["date_in"]]);
      } else {
         echo Html::convDate($this->fields["date_use"]);
      }
      echo "</td></tr>\n";

      if ($is_old) {
         echo "<tr class='tab_bg_1'>";
         echo "<td>".__('End date')."</td><td>";
         Html::showDateField("date_out", ['value'      => $this->fields["date_out"],
                                               'maybeempty' => false,
                                               'canedit'    => true,
                                               'min'        => $this->fields["date_use"]]);
         echo "</td>";
         echo "<td>".__('Medical device counter', 'openmedis')."</td><td>";
         echo "<input type='text' name='usages' value=\"".$this->fields['usages']."\">";
         echo "</td></tr>\n";
      }
      $this->showFormButtons($options);

      return true;
   }


   /**
    * Get notification parameters by entity
    *
    * @param integer $entity The entity (default 0)
    * @return array Array of notification parameters
    */
   static function getNotificationParameters($entity = 0) {
      global $DB, $CFG_GLPI;

      //Look for parameters for this entity
      $iterator = $DB->request([
         'SELECT' => ['medicalconsumables_alert_repeat'],
         'FROM'   => 'glpi_entities',
         'WHERE'  => ['id' => $entity]
      ]);

      if (!count($iterator)) {
         //No specific parameters defined, taking global configuration params
         return $CFG_GLPI['medicalconsumables_alert_repeat'];

      } else {
         $data = $iterator->next();
         //This entity uses global parameters -> return global config
         if ($data['medicalconsumables_alert_repeat'] == -1) {
            return $CFG_GLPI['medicalconsumables_alert_repeat'];
         }
         // ELSE Special configuration for this entity
         return $data['medicalconsumables_alert_repeat'];
      }
   }


   function getTabNameForItem(CommonGLPI $item, $withtemplate = 0) {

      if (!$withtemplate && self::canView()) {
         $nb = 0;
         switch ($item->getType()) {
            case 'PluginOpenmedisMedicalDevice' :
               if ($_SESSION['glpishow_count_on_tabs']) {
                  $nb = self::countForMedicalDevice($item);
               }
               return self::createTabEntry(self::getTypeName(Session::getPluralNumber()), $nb);

            case 'PluginOpenmedisMedicalConsumableItem' :
               if ($_SESSION['glpishow_count_on_tabs']) {
                  $nb = self::countForMedicalConsumableItem($item);
               }
               return self::createTabEntry(self::getTypeName(Session::getPluralNumber()), $nb);
         }
      }
      return '';
   }


   /**
    * Count the number of medical consumables associated with the given medical consumable item.
    * @param PluginOpenmedisMedicalConsumableItem $item PluginOpenmedisMedicalConsumableItem object
    * @return integer
    */
   static function countForMedicalConsumableItem(PluginOpenmedisMedicalConsumableItem $item) {

      return countElementsInTable(['glpi_plugin_openmedis_medicalconsumables'], ['glpi_plugin_openmedis_medicalconsumables.plugin_openmedis_medicalconsumableitems_id' => $item->getField('id')]);
   }


   /**
    * Count the number of medical consumables associated with the given medicaldevice.
    * @param PluginOpenmedisMedicalDevice $item PluginOpenmedisMedicalDevice object
    * @return integer
    */
   static function countForMedicalDevice(PluginOpenmedisMedicalDevice $item) {

      return countElementsInTable(['glpi_plugin_openmedis_medicalconsumables'], ['glpi_plugin_openmedis_medicalconsumables.plugin_openmedis_medicaldevices_id' => $item->getField('id')]);
   }


   static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0) {

      switch ($item->getType()) {
         case 'PluginOpenmedisMedicalDevice' :
            self::showForMedicalDevice($item);
            self::showForMedicalDevice($item, 1);
            return true;

         case 'PluginOpenmedisMedicalConsumableItem' :
            self::showAddForm($item);
            self::showForMedicalConsumableItem($item);
            self::showForMedicalConsumableItem($item, 1);
            return true;
      }
   }

   function getRights($interface = 'central') {
      $ci = new PluginOpenmedisMedicalConsumableItem();
      return $ci->getRights($interface);
   }


   static function getIcon() {
      return "fas fa-vial";
   }

}
