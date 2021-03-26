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


/**
 * MedicalConsumableItem Class
 * This class is used to manage the various types of medical consumable.
 * \see  medical consumable
**/
class PluginOpenmedisMedicalConsumableItem extends CommonDBTM {

   // From CommonDBTM
   static protected $forward_entity_to = ['MedicalConsumable', 'Infocom'];
   public $dohistory                   = true;
   protected $usenotepad               = true;

   static $rightname                   = 'plugin_openmedis_medicalconsumableitem';

   static function getTypeName($nb = 0) {
      return _n('Medical consumable model', 'Medical consumable models', $nb);
   }


   /**
    * @see CommonGLPI::getMenuName()
    *
    * @since 0.85
   **/
   static function getMenuName() {
      return PluginOpenmedisMedicalConsumable::getTypeName(Session::getPluralNumber());
   }


   /**
    * @since 0.84
    *
    * @see CommonDBTM::getPostAdditionalInfosForName
   **/
   function getPostAdditionalInfosForName() {

      if (isset($this->fields["ref"]) && !empty($this->fields["ref"])) {
         return $this->fields["ref"];
      }
      return '';
   }


   function cleanDBonPurge() {

      $this->deleteChildrenAndRelationsFromDb(
         [
            PluginOpenmedisMedicalConsumable::class,
            PluginOpenmedisMedicalConsumableItem_MedicalDeviceModel::class,
         ]
      );

      $class = new Alert();
      $class->cleanDBonItemDelete($this->getType(), $this->fields['id']);
   }


   function post_getEmpty() {

      $this->fields["alarm_threshold"] = Entity::getUsedConfig("plugin_openmedis_medicalconsumables_alert_repeat",
                                                               $this->fields["entities_id"],
                                                               "plugin_openmedis_default_medicalconsumables_alarm_threshold",
                                                               10);
   }


   function defineTabs($options = []) {

      $ong = [];
      $this->addDefaultFormTab($ong);
      $this->addImpactTab($ong, $options);
      $this->addStandardTab('PluginOpenmedisMedicalConsumable', $ong, $options);
      $this->addStandardTab('PluginOpenmedisMedicalConsumableItem_MedicalDeviceModel', $ong, $options);
      $this->addStandardTab('Infocom', $ong, $options);
      $this->addStandardTab('Document_Item', $ong, $options);
      $this->addStandardTab('Link', $ong, $options);
      $this->addStandardTab('Notepad', $ong, $options);
      $this->addStandardTab('Log', $ong, $options);

      return $ong;
   }


   ///// SPECIFIC FUNCTIONS

   /**
    * Count medical consumable of the medical consumables type
    *
    * @param integer $id Item id
    *
    * @return number of medical consumable
    *
    * @since 9.2 add $id parameter
    **/
   static function getCount($id) {
      global $DB;

      $result = $DB->request([
         'COUNT'  => 'cpt',
         'FROM'   => 'glpi_plugin_openmedis_medicalconsumables',
         'WHERE'  => ['plugin_openmedis_medicalconsumableitems_id' => $id]
      ])->next();
      return $result['cpt'];
   }


   /**
    * Add a compatible medicaldevice type for a medical consumable type
    *
    * @param $medicalconsumableitems_id  integer: medical consumable type identifier
    * @param medicaldevicemodels_id    integer: medicaldevice type identifier
    *
    * @return boolean : true for success
   **/
   function addCompatibleType($medicalconsumableitems_id, $medicaldevicemodels_id) {
      global $DB;

      if (($medicalconsumableitems_id > 0)
          && ($medicaldevicemodels_id > 0)) {
         $params = [
            'plugin_openmedis_medicalconsumableitems_id' => $medicalconsumableitems_id,
            'plugin_openmedis_medicaldevicemodels_id'  => $medicaldevicemodels_id
         ];
         $result = $DB->insert('glpi_plugin_openmedis_medicaldevicemodels', $params);

         if ($result && ($DB->affectedRows() > 0)) {
            return true;
         }
      }
      return false;
   }


   /**
    * Print the medical consumable type form
    *
    * @param $ID        integer ID of the item
    * @param $options   array os possible options:
    *     - target for the Form
    *     - withtemplate : 1 for newtemplate, 2 for newobject from template
    *
    * @return boolean
   **/
   function showForm($ID, $options = []) {

      $this->initForm($ID, $options);
      $this->showFormHeader($options);

      echo "<tr class='tab_bg_1'>";
      echo "<td>".__('Name')."</td>";
      echo "<td>";
      Html::autocompletionTextField($this, "name");
      echo "</td>";
      echo "<td>"._n('Type', 'Types', 1)."</td>";
      echo "<td>";
      PluginOpenmedisMedicalConsumableItemType::dropdown(['value' => $this->fields["plugin_openmedis_medicalconsumableitemtypes_id"]]);
      echo "</td></tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>".__('Reference')."</td>";
      echo "<td>";
      Html::autocompletionTextField($this, "ref");
      echo "</td>";
      echo "<td>".Manufacturer::getTypeName(1)."</td>";
      echo "<td>";
      Manufacturer::dropdown(['value' => $this->fields["manufacturers_id"]]);
      echo "</td></tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>".__('Technician in charge of the hardware')."</td>";
      echo "<td>";
      User::dropdown(['name'   => 'users_id_tech',
                           'value'  => $this->fields["users_id_tech"],
                           'right'  => 'own_ticket',
                           'entity' => $this->fields["entities_id"]]);
      echo "</td>";
      echo "<td rowspan='4' class='middle'>".__('Comments')."</td>";
      echo "<td class='middle' rowspan='4'>
             <textarea cols='45' rows='9' name='comment'>".$this->fields["comment"]."</textarea>";
      echo "</td></tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>".__('Group in charge of the hardware')."</td>";
      echo "<td>";
      Group::dropdown([
         'name'      => 'groups_id_tech',
         'value'     => $this->fields['groups_id_tech'],
         'entity'    => $this->fields['entities_id'],
         'condition' => ['is_assign' => 1]
      ]);
      echo "</td></tr>\n";

      echo "<tr class='tab_bg_1'>";
      echo "<td>".__('Stock location')."</td>";
      echo "<td>";
      Location::dropdown(['value'  => $this->fields["locations_id"],
                               'entity' => $this->fields["entities_id"]]);
      echo "</td></tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>".__('Alert threshold')."</td>";
      echo "<td>";
      Dropdown::showNumber('alarm_threshold', ['value' => $this->fields["alarm_threshold"],
                                                    'min'   => 0,
                                                    'max'   => 100,
                                                    'step'  => 1,
                                                    'toadd' => ['-1' => __('Never')]]);
      Alert::displayLastAlert('PluginOpenmedisMedicalConsumableItem', $ID);
      echo "</td></tr>";

      $this->showFormButtons($options);

      return true;
   }


   function rawSearchOptions() {
      $tab = parent::rawSearchOptions();

      $tab[] = [
         'id'                 => '2',
         'table'              => $this->getTable(),
         'field'              => 'id',
         'name'               => __('ID'),
         'massiveaction'      => false,
         'datatype'           => 'number'
      ];

      $tab[] = [
         'id'                 => '34',
         'table'              => $this->getTable(),
         'field'              => 'ref',
         'name'               => __('Reference'),
         'datatype'           => 'string',
         'autocomplete'       => true,
      ];

      $tab[] = [
         'id'                 => '4',
         'table'              => 'glpi_plugin_openmedis_medicalconsumableitemtypes',
         'field'              => 'name',
         'name'               => _n('Type', 'Types', 1),
         'datatype'           => 'dropdown'
      ];

      $tab[] = [
         'id'                 => '23',
         'table'              => 'glpi_manufacturers',
         'field'              => 'name',
         'name'               => Manufacturer::getTypeName(1),
         'datatype'           => 'dropdown'
      ];

      $tab[] = [
         'id'                 => '9',
         'table'              => $this->getTable(),
         'field'              => '_virtual',
         'name'               => _n('Medical consumable', 'Medical consumables', Session::getPluralNumber()),
         'datatype'           => 'specific',
         'massiveaction'      => false,
         'nosearch'           => true,
         'nosort'             => true,
         'additionalfields'   => ['alarm_threshold']
      ];

      $tab[] = [
         'id'                 => '17',
         'table'              => 'glpi_plugin_openmedis_medicalconsumable',
         'field'              => 'id',
         'name'               => __('Number of used medical consumables'),
         'datatype'           => 'count',
         'forcegroupby'       => true,
         'usehaving'          => true,
         'massiveaction'      => false,
         'joinparams'         => [
            'jointype'           => 'child',
            'condition'          => 'AND NEWTABLE.`date_use` IS NOT NULL
                                     AND NEWTABLE.`date_out` IS NULL'
         ]
      ];

      $tab[] = [
         'id'                 => '18',
         'table'              => 'glpi_plugin_openmedis_medicalconsumable',
         'field'              => 'id',
         'name'               => __('Number of worn medical consumables'),
         'datatype'           => 'count',
         'forcegroupby'       => true,
         'usehaving'          => true,
         'massiveaction'      => false,
         'joinparams'         => [
            'jointype'           => 'child',
            'condition'          => 'AND NEWTABLE.`date_out` IS NOT NULL'
         ]
      ];

      $tab[] = [
         'id'                 => '19',
         'table'              => 'glpi_plugin_openmedis_medicalconsumable',
         'field'              => 'id',
         'name'               => __('Number of new medical consumables'),
         'datatype'           => 'count',
         'forcegroupby'       => true,
         'usehaving'          => true,
         'massiveaction'      => false,
         'joinparams'         => [
            'jointype'           => 'child',
            'condition'          => 'AND NEWTABLE.`date_use` IS NULL
                                     AND NEWTABLE.`date_out` IS NULL'
         ]
      ];

      $tab = array_merge($tab, Location::rawSearchOptionsToAdd());

      $tab[] = [
         'id'                 => '24',
         'table'              => 'glpi_users',
         'field'              => 'name',
         'linkfield'          => 'users_id_tech',
         'name'               => __('Technician in charge of the hardware'),
         'datatype'           => 'dropdown',
         'right'              => 'own_ticket'
      ];

      $tab[] = [
         'id'                 => '49',
         'table'              => 'glpi_groups',
         'field'              => 'completename',
         'linkfield'          => 'groups_id_tech',
         'name'               => __('Group in charge of the hardware'),
         'condition'          => ['is_assign' => 1],
         'datatype'           => 'dropdown'
      ];

      $tab[] = [
         'id'                 => '8',
         'table'              => $this->getTable(),
         'field'              => 'alarm_threshold',
         'name'               => __('Alert threshold'),
         'datatype'           => 'number',
         'toadd'              => [
            '-1'                 => 'Never'
         ]
      ];

      $tab[] = [
         'id'                 => '16',
         'table'              => $this->getTable(),
         'field'              => 'comment',
         'name'               => __('Comments'),
         'datatype'           => 'text'
      ];

      $tab[] = [
         'id'                 => '80',
         'table'              => 'glpi_entities',
         'field'              => 'completename',
         'name'               => Entity::getTypeName(1),
         'massiveaction'      => false,
         'datatype'           => 'dropdown'
      ];

      $tab[] = [
         'id'                 => '40',
         'table'              => 'glpi_plugin_openmedis_medicaldevicemodels',
         'field'              => 'name',
         'datatype'           => 'dropdown',
         'name'               => _n('Medical Device model', 'Medical Device models', Session::getPluralNumber()),
         'forcegroupby'       => true,
         'massiveaction'      => false,
         'joinparams'         => [
            'beforejoin'         => [
               'table'              => 'glpi_plugin_openmedis_medicalconsumableitems_medicaldevicemodels',
               'joinparams'         => [
                  'jointype'           => 'child'
               ]
            ]
         ]
      ];

      // add objectlock search options
      $tab = array_merge($tab, ObjectLock::rawSearchOptionsToAdd(get_class($this)));

      $tab = array_merge($tab, Notepad::rawSearchOptionsToAdd());

      return $tab;
   }


   static function cronInfo() {
      return ['description' => __('Send alarms on medical consumables')];
   }


   /**
    * Cron action on medical consumable : alert if a stock is behind the threshold
    *
    * @param CronTask $task CronTask for log, display information if NULL? (default NULL)
    *
    * @return void
   **/
   static function cronMedicalConsumable($task = null) {
      global $DB, $CFG_GLPI;

      $cron_status = 1;
      if ($CFG_GLPI["use_notifications"]) {
         $message = [];
         $alert   = new Alert();

         foreach (Entity::getEntitiesToNotify('plugin_openmedis_medicalconsumables_alert_repeat') as $entity => $repeat) {
            // if you change this query, please don't forget to also change in showDebug()
            $result = $DB->request(
               [
                  'SELECT'    => [
                     'glpi_plugin_openmedis_medicalconsumableitems.id AS mcID',
                     'glpi_plugin_openmedis_medicalconsumableitems.entities_id AS entity',
                     'glpi_plugin_openmedis_medicalconsumableitems.ref AS ref',
                     'glpi_plugin_openmedis_medicalconsumableitems.name AS name',
                     'glpi_plugin_openmedis_medicalconsumableitems.alarm_threshold AS threshold',
                     'glpi_alerts.id AS alertID',
                     'glpi_alerts.date',
                  ],
                  'FROM'      => self::getTable(),
                  'LEFT JOIN' => [
                     'glpi_alerts' => [
                        'FKEY' => [
                           'glpi_alerts'         => 'items_id',
                           'glpi_plugin_openmedis_medicalconsumableitems' => 'id',
                           [
                              'AND' => ['glpi_alerts.itemtype' => 'PluginOpenmedisMedicalConsumableItem'],
                           ],
                        ]
                     ]
                  ],
                  'WHERE'     => [
                     'glpi_plugin_openmedis_medicalconsumableitems.is_deleted'      => 0,
                     'glpi_plugin_openmedis_medicalconsumableitems.alarm_threshold' => ['>=', 0],
                     'glpi_plugin_openmedis_medicalconsumableitems.entities_id'     => $entity,
                     'OR'                                  => [
                        ['glpi_alerts.date' => null],
                        ['glpi_alerts.date' => ['<', new QueryExpression('CURRENT_TIMESTAMP() - INTERVAL ' . $repeat . ' second')]],
                     ],
                  ],
               ]
            );

            $message = "";
            $items   = [];

            foreach ($result as $medicalconsumable) {
               if (($unused=PluginOpenmedisMedicalConsumable::getUnusedNumber($medicalconsumable["mcID"]))<=$medicalconsumable["threshold"]) {
                  //TRANS: %1$s is the medical consumable name, %2$s its reference, %3$d the remaining number
                  $message .= sprintf(__('Threshold of alarm reached for the type of medical consumable: %1$s - Reference %2$s - Remaining %3$d'),
                                      $medicalconsumable["name"], $medicalconsumable["ref"], $unused);
                  $message .='<br>';

                  $items[$medicalconsumable["mcID"]] = $medicalconsumable;

                  // if alert exists -> delete
                  if (!empty($medicalconsumable["alertID"])) {
                     $alert->delete(["id" => $medicalconsumable["alertID"]]);
                  }
               }
            }

            if (!empty($items)) {
               $options = [
                  'entities_id' => $entity,
                  'items'       => $items,
               ];

               $entityname = Dropdown::getDropdownName("glpi_entities", $entity);
               if (NotificationEvent::raiseEvent('alert', new PluginOpenmedisMedicalConsumableItem(), $options)) {
                  if ($task) {
                     $task->log(sprintf(__('%1$s: %2$s')."\n", $entityname, $message));
                     $task->addVolume(1);
                  } else {
                     Session::addMessageAfterRedirect(sprintf(__('%1$s: %2$s'),
                                                               $entityname, $message));
                  }

                  $input = [
                     'type'     => Alert::THRESHOLD,
                     'itemtype' => 'PluginOpenmedisMedicalConsumableItem',
                  ];

                  // add alerts
                  foreach (array_keys($items) as $ID) {
                     $input["items_id"] = $ID;
                     $alert->add($input);
                     unset($alert->fields['id']);
                  }

               } else {
                  //TRANS: %s is entity name
                  $msg = sprintf(__('%s: send medical consumable alert failed'), $entityname);
                  if ($task) {
                     $task->log($msg);
                  } else {
                     //TRANS: %s is the entity
                     Session::addMessageAfterRedirect($msg, false, ERROR);
                  }
               }
            }
         }
      }

      return $cron_status;
   }


   /**
    * Print a select with compatible medical consumable
    *
    * @param $medicaldevice Medical Device object
    *
    * @return string|boolean
   **/
   static function dropdownForMedicalDevice(PluginOpenmedisMedicalDevice $medicaldevice) {
      global $DB;

      $iterator = $DB->request([
         'SELECT'       => [
            'COUNT'  => '* AS cpt',
            'glpi_locations.completename AS location',
            'glpi_plugin_openmedis_medicalconsumableitems.ref AS ref',
            'glpi_plugin_openmedis_medicalconsumableitems.name AS name',
            'glpi_plugin_openmedis_medicalconsumableitems.id AS tID'
         ],
         'FROM'         => self::getTable(),
         'INNER JOIN'   => [
            'glpi_plugin_openmedis_medicalconsumableitems_medicaldevicemodels' => [
               'ON' => [
                  'glpi_plugin_openmedis_medicalconsumableitems_medicaldevicemodels' => 'plugin_openmedis_medicalconsumableitems_id',
                  'glpi_plugin_openmedis_medicalconsumableitems'               => 'id'
               ]
            ],
            'glpi_plugin_openmedis_medicalconsumables'                   => [
               'ON' => [
                  'glpi_plugin_openmedis_medicalconsumableitems'   => 'id',
                  'glpi_plugin_openmedis_medicalconsumables'       => 'plugin_openmedis_medicalconsumableitems_id', [
                     'AND' => [
                        'glpi_plugin_openmedis_medicalconsumables.date_use' => null
                     ]
                  ]
               ]
            ]
         ],
         'LEFT JOIN'    => [
            'glpi_locations'                    => [
               'ON' => [
                  'glpi_plugin_openmedis_medicalconsumableitems'   => 'locations_id',
                  'glpi_locations'        => 'id'
               ]
            ]
         ],
         'WHERE'        => [
            'glpi_plugin_openmedis_medicalconsumableitems_medicaldevicemodels.plugin_openmedis_medicaldevicemodels_id'  => $medicaldevice->fields['plugin_openmedis_medicaldevicemodels_id']
         ] + getEntitiesRestrictCriteria('glpi_plugin_openmedis_medicalconsumableitems', '', $medicaldevice->fields['entities_id'], true),
         'GROUPBY'      => 'tID',
         'ORDERBY'      => ['name', 'ref']
      ]);

      $results = [];
      while ($data = $iterator->next()) {
         $text = sprintf(__('%1$s - %2$s'), $data["name"], $data["ref"]);
         $text = sprintf(__('%1$s (%2$s)'), $text, $data["cpt"]);
         $text = sprintf(__('%1$s - %2$s'), $text, $data["location"]);
         $results[$data["tID"]] = $text;
      }
      if (count($results)) {
         return Dropdown::showFromArray('plugin_openmedis_medicalconsumableitems_id', $results);
      }
      return false;
   }


   function getEvents() {
      return ['alert' => __('Send alarms on medical consumable')];
   }


   /**
    * Display debug information for current object
   **/
   function showDebug() {

      // see query_alert in cronMedicalConsumable()
      $item = ['mcID'    => $this->fields['id'],
                    'entity'    => $this->fields['entities_id'],
                    'ref'       => $this->fields['ref'],
                    'name'      => $this->fields['name'],
                    'threshold' => $this->fields['alarm_threshold']];

      $options = [];
      $options['entities_id'] = $this->getEntityID();
      $options['items']       = [$item];
      NotificationEvent::debugEvent($this, $options);
   }


   static function getIcon() {
      return PluginOpenmedisMedicalConsumable::getIcon();
   }

}