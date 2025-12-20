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

use Glpi\Asset\Asset_PeripheralAsset;


/**
 * PluginOpenmedisMedicalDevice Class
**/
class PluginOpenmedisMedicalDevice extends CommonDBTM {
   use Glpi\Features\DCBreadcrumb; 
   //use Glpi\Features\Clonable;
   use Glpi\Features\Inventoriable;
   // From CommonDBTM
   public $dohistory                   = true;
   // used to filter the categories
   private $category                   = '';

   static protected $forward_entity_to = ['Infocom', 'NetworkPort', 'ReservationItem'];

   static $rightname                   = 'plugin_openmedis';
   protected $usenotepad               = true;

   static public $itemtype_1 = 'pluginOpenmedisMedicalDevice';
   static public $items_id_1 = 'pluginopenmedismedicaldevice_id';

   static $types     = ['PluginOpenmedisDeviceMedicalAccessory'];
   /**
    * Name of the type
    *
    * @param $nb : number of item in the type
   **/
   static function getTypeName($nb = 0) {
      return _n('Medical device', 'Medical devices', $nb, 'openmedis');
   }

   static function getFormURL($full = true) {
      global $CFG_GLPI;

      $dir = ($full ? $CFG_GLPI['root_doc'] : '');
      $itemtype = get_called_class();
      $link = "$dir/plugins/openmedis/front/medicaldevice.form.php";

      return $link;
   }
   /**
    * @see CommonDBTM::useDeletedToLockIfDynamic()
    *
    * @since 0.84
   **/
   function useDeletedToLockIfDynamic() {
      return false;
   }
   function redirectToList(): void {
      Html::redirect("{$CFG_GLPI['root_doc']}/plugins/openmedis/front/medicaldevice.php");
   }
    /**
     * Define tabs to display
     *
     * @see CommonGLPI::defineTabs()
    **/
   function defineTabs($options = []) {

      $ong = [];
      $this->addDefaultFormTab($ong);
      $this->addStandardTab('NetworkPort', $ong, $options);
      $this->addStandardTab('Infocom', $ong, $options);
      $this->addStandardTab('Contract_Item', $ong, $options);
      $this->addStandardTab('Document_Item', $ong, $options);
      $this->addStandardTab('KnowbaseItem_Item', $ong, $options);
      $this->addStandardTab('Ticket', $ong, $options);
      $this->addStandardTab('Item_Problem', $ong, $options);
      $this->addStandardTab('Change_Item', $ong, $options);
      $this->addStandardTab('PluginOpenmedisMedicalConsumable', $ong, $options);
      //$this->addStandardTab('Link', $ong, $options);
      $this->addStandardTab('Certificate_Item', $ong, $options);
      //$this->addStandardTab('Lock', $ong, $options);
      $this->addStandardTab('Notepad', $ong, $options);
      $this->addStandardTab('Reservation', $ong, $options);
      $this->addStandardTab('Item_Devices', $ong, $options);
      $this->addStandardTab('Log', $ong, $options);
         //need metrology
      return $ong;
   }


   function prepareInputForAdd($input) {

      if (isset($input["id"]) && ($input["id"] > 0)) {
         $input["_oldID"] = $input["id"];
      }
      unset($input['id']);
      unset($input['withtemplate']);
      return $input;
   }


   function post_addItem() {
      global $DB, $CFG_GLPI;

      // Manage add from template
      if (isset($this->input["_oldID"])) {
         // ADD Devices
         Item_devices::cloneItem($this->getType(), $this->input["_oldID"], $this->fields['id']);
         PluginOpenmedisDeviceMedicalAccessory::cloneItem($this->getType(), $this->input["_oldID"], $this->fields['id']);
         // ADD Infocoms
         Infocom::cloneItem($this->getType(), $this->input["_oldID"], $this->fields['id']);

         // ADD Ports
         NetworkPort::cloneItem($this->getType(), $this->input["_oldID"], $this->fields['id']);

         // ADD Contract
         Contract_Item::cloneItem($this->getType(), $this->input["_oldID"], $this->fields['id']);

         // ADD Documents
         Document_Item::cloneItem($this->getType(), $this->input["_oldID"], $this->fields['id']);

         // ADD Computers
         Asset_PeripheralAsset::cloneItem($this->getType(), $this->input["_oldID"], $this->fields['id']);

         //Add KB links
         KnowbaseItem_Item::cloneItem($this->getType(), $this->input["_oldID"], $this->fields['id']);
      }

   }

   function cleanDBonPurge() {

      $this->deleteChildrenAndRelationsFromDb(
         [
            Certificate_Item::class,
            Item_Problem::class,
            Change_Item::class,
            Item_Project::class,
            PluginOpenmedisDeviceMedicalAccessory::class,
         ]
      );

      Item_Devices::cleanItemDeviceDBOnItemDelete($this->getType(), $this->fields['id'],
                                                  (!empty($this->input['keep_devices'])));
   }


   /**
    * Print the medicaldevice form
    *
    * @param $ID integer ID of the item
    * @param $options array
    *     - target filename : where to go when done.
    *     - withtemplate boolean : template or basic item
    *
    * @return boolean item found
    **/
   
   function showForm($ID, $options = []) {
      global $CFG_GLPI;
      $this->initForm($ID, $options);
      $this->showFormHeader($options);
      $target       = $this->getFormURL();
      $withtemplate = $this->initForm($ID, $options);
      if (!isset($options['display'])) {
         //display per default
         $options['display'] = true;
      }
      $rand = $options['rand'] ?? mt_rand();
      $params_user = [
         'entity' => $_SESSION["glpiactive_entity"],
         'right' => 'all',
         'condition' => ['is_assign' => 1]
      ];
      $ldap_methods = getAllDataFromTable('glpi_authldaps', ['is_active' => 1]);
      if (
          count($ldap_methods)
          && Session::haveRight('user', User::IMPORTEXTAUTHUSERS)
      ) {
          $params_user['ldap_import'] = true;
      }


      $params = $options;
      //do not display called elements per default; they'll be displayed or returned here
      $params['display'] = false;
      echo "<tr class='tab_bg_1'>";
      echo "<td>".PluginOpenmedisMedicalDeviceCategory::getFieldLabel(0, 0)."</br>\n";
      echo PluginOpenmedisMedicalDeviceCategory::getFieldLabel(0, 1)."</td>\n";
      echo "<td>";
      //$this->category =  is_null($_POST['category'] ? $_POST['category'] : '');
      $rand =  mt_rand();
      $parent_name = 'plugin_openmedis_medicaldevicecategories_parent_id';
      $parent_field_id = Html::cleanId("dropdown_".$parent_name.$rand);
      PluginOpenmedisMedicalDeviceCategory::dropdown(['value' => $this->fields["plugin_openmedis_medicaldevicecategories_parent_id"] ,
      'name' => $parent_name,
      'displaywith' => ['code','label'],
      'condition' => [ 'level' => 1],
      'rand' => $rand]);
      echo '<br>';
      PluginOpenmedisMedicalDeviceCategory::dropdown(['value' => $this->fields["plugin_openmedis_medicaldevicecategories_id"],
      'permit_select_parent' => true,
      'displaywith' => ['code','label'],
      'parent_id_field'   =>  $parent_field_id ]);
   

      echo "</td>";
      
      
      echo "<td>".__('Status')."</td>\n";
      echo "<td>";
      $stateItem = new PluginOpenmedisState_Item();
      $visibleStates = $stateItem->getVisibleStates('PluginOpenmedisMedicalDevice');
      State::dropdown([
         'value'     => $this->fields["states_id"],
         'entity'    => $_SESSION["glpiactive_entity"],
         'condition' => count($visibleStates) > 0 ? ['id' => $visibleStates] : []
      ]);
      echo "</td></tr>\n";



      echo "<tr class='tab_bg_1'>";
      $tplmark = $this->getAutofillMark('name', $options);
      
      //TRANS: %1$s is a string, %2$s a second one without spaces between them : to change for RTL
      echo "<td>".sprintf(__('%1$s%2$s'), __('Name', 'openmedis'), $tplmark);
      echo "</td>";
      echo "<td>";
      //$this->fields['withtemplate'] = 2 ;
      $objectName = autoName($this->fields["name"], "name",
                             (isset($options['withtemplate']) && ($options['withtemplate'] == 2)),
                             $this->getType(), $_SESSION["glpiactive_entity"]);
      echo Html::input( "name", ['value' => $objectName]);
      echo "</td>\n";
      echo "<td>".__('Location')."</td>\n";
      echo "<td>";
      Location::dropdown(['value'  => $this->fields["locations_id"],
                               'entity' => $_SESSION["glpiactive_entity"]]);
      echo "</td>\n";



      echo "</tr>\n";
      



      echo "<tr class='tab_bg_1'>";
      echo "<td>".__('Technician in charge of the hardware')."</td>\n";
      echo "<td>";
      

      $params_user['name'] = 'users_id_tech';
      $params_user['value'] = $this->fields["users_id_tech"];
      User::dropdown($params_user);
      echo "</td>";
      echo "<td>".__('Manufacturer')."</td>\n";
      echo "<td>";
      Manufacturer::dropdown(['value' => $this->fields["manufacturers_id"]]);
      echo "</td></tr>\n";

      echo "<tr class='tab_bg_1'>";
      echo "<td>".__('Group in charge of the hardware')."</td>";
      echo "<td>";

      $params_user['name'] = 'groups_id_tech';
      $params_user['value'] = $this->fields["groups_id_tech"];
      Group::dropdown($params_user);
      echo "</td>";
      echo "<td>".__('Model')."</td>\n";
      echo "<td>";
      PluginOpenmedisMedicalDeviceModel::dropdown(['value' => $this->fields["plugin_openmedis_medicaldevicemodels_id"]]);
      echo "</td></tr>\n";

      echo "<tr class='tab_bg_1'>";
      echo "<td>".__('Alternate username number')."</td>\n";
      echo "<td>";
      echo Html::input("contact_num", ['value' => $this->fields["contact_num"]]);
      echo "</td>";
      echo "<td>".__('Serial number')."</td>\n";
      echo "<td>";
      echo Html::input("serial", ['value' => $this->fields["serial"]]);
      echo "</td></tr>\n";

      echo "<tr class='tab_bg_1'>";
      echo "<td>".__('Alternate username')."</td>\n";
      echo "<td>";
      echo Html::input("contact", ['value' => $this->fields["contact"]]);
      echo "</td>\n";

      $tplmark = $this->getAutofillMark('otherserial', $options);
      echo "<td>".sprintf(__('%1$s%2$s'), __('Inventory number'), $tplmark).
           "</td>\n";
      echo "<td>";
      $objectName = autoName($this->fields["otherserial"], "otherserial",
                             (isset($options['withtemplate']) && ($options['withtemplate'] == 2)),
                             $this->getType(), $_SESSION["glpiactive_entity"]);
      echo Html::input("otherserial", ['value' => $objectName]);
      echo "</td></tr>\n";

      echo "<tr class='tab_bg_1'>";
      echo "<td>".__('User')."</td>\n";
      echo "<td>";
      $params_user['name'] = 'users_id';
      $params_user['value'] = $this->fields["users_id"];
      User::dropdown($params_user);
      echo "</td>\n";
      echo "<td>".PluginOpenmedisUtilization::getFieldLabel(1)."</td>\n";
      echo "<td>";

      PluginOpenmedisUtilization::dropdown(['value' => $this->fields["plugin_openmedis_utilizations_id"]]);

      echo "</td></tr>\n";

      $rowspan        = 2;
      echo "<tr class='tab_bg_1'>";
      echo "<td>".__('Group')."</td>\n";
      echo "<td>";
      Group::dropdown([
         'value'     => $this->fields["groups_id"],
         'entity'    => $_SESSION["glpiactive_entity"],
         'condition' => ['is_itemgroup' => 1]
      ]);
      echo "</td>\n";
      echo "<td rowspan='$rowspan'>".__('Comments')."</td>\n";
      echo "<td rowspan='$rowspan'>
            <textarea cols='45' rows='".($rowspan+3)."' name='comment' >".htmlescape($this->fields["comment"]);
      echo "</textarea></td></tr>\n";

      echo "<tr class='tab_bg_1'>";
      echo "<td>".__('Brand')."</td>\n";
      echo "<td>";
      echo Html::input("brand", ['value' => $this->fields["brand"]]);
      echo "</td>\n";
      echo "</tr>\n";

      echo "<tr class='tab_bg_1'>";
      $tplmark = $this->getAutofillMark('barcode', $options);
      echo "<td>".sprintf(__('%1$s%2$s'), __('Barcode','openmedis'), $tplmark).
           "</td>\n";
      echo "<td>";
      $objectName = autoName($this->fields["barcode"], "barcode",
                             (isset($options['withtemplate']) && ($options['withtemplate'] == 2)),
                             $this->getType(), $_SESSION["glpiactive_entity"]);
      echo Html::input("barcode", ['value' => $this->fields["barcode"]]);
      echo "</td></tr>\n";


      // Display auto inventory informations
      if (!empty($ID)
         && $this->fields["is_dynamic"]) {
         echo "<tr class='tab_bg_1'><td colspan='4'>";
         Plugin::doHook("autoinventory_information", $this);
         echo "</td></tr>";
      }

      /* model images*/
      
      if($this->fields["plugin_openmedis_medicaldevicemodels_id"] > 0){
         //error_reporting(E_ALL);
         $models = new PluginOpenmedisMedicalDeviceModel();
         $models->getFromDB($this->fields["plugin_openmedis_medicaldevicemodels_id"]);
         echo "<tr class='tab_bg_1'><td colspan='4'>";
         if (isset($models->fields['picture_front'])){
            echo Html::image(Toolbox::getPictureUrl($models->fields['picture_front']), [
               'alt'   =>"Model front picture",
               'style' => 'width: 45%;',
            ]);
         }
         
         //echo $models->getSpecificValueToDisplay('picture_front');
         //echo "</td><td>";
         
         if (isset($models->fields['picture_rear'])){
            echo Html::image(Toolbox::getPictureUrl($models->fields['picture_rear']), [
               'alt'   =>"Model rear picture",
               'style' => 'width: 45%;',
            ]);
         }
         
         //echo $models->getSpecificValueToDisplay('picture_rear');
         echo "</td></tr>";
      } 

      


      $this->showFormButtons($options);

      return true;
   }



   /**
    * @see CommonDBTM::getSpecificMassiveActions()
   **/
   function getSpecificMassiveActions($checkitem = null) {

      $actions = parent::getSpecificMassiveActions($checkitem);

      if (static::canUpdate()) {
         if (Asset_PeripheralAsset::canCreate() && Session::haveRight(PluginOpenmedisMedicalDevice::$rightname, UPDATE)) {
            $actions[__CLASS__ . MassiveAction::CLASS_ACTION_SEPARATOR . 'add_to_computer'] = __('Add to medical device');
         }

         $kb_item = new KnowbaseItem();
         $kb_item->getEmpty();
         if ($kb_item->canViewItem()) {
            $actions['KnowbaseItem_Item'.MassiveAction::CLASS_ACTION_SEPARATOR.'add'] = _x('button', 'Link knowledgebase article');
         }
      }

      return $actions;
   }

   /**
    * @see CommonDBTM::processMassiveActionsForOneItemtype()
   **/
   static function processMassiveActionsForOneItemtype(MassiveAction $ma, CommonDBTM $item, array $ids) {
      switch ($ma->getAction()) {
         case 'add_to_computer':
            $input = $ma->getInput();
            if (isset($input['computers_id'])) {
               $peripheral = new Asset_PeripheralAsset();
               foreach ($ids as $id) {
                  $input['itemtype'] = $item->getType();
                  $input['items_id'] = $id;
                  if ($peripheral->add($input)) {
                     $ma->itemDone($item->getType(), $id, MassiveAction::ACTION_OK);
                  } else {
                     $ma->itemDone($item->getType(), $id, MassiveAction::ACTION_KO);
                  }
               }
            } else {
               $ma->itemDone($item->getType(), $ids, MassiveAction::ACTION_KO);
            }
            return;
      }
      parent::processMassiveActionsForOneItemtype($ma, $item, $ids);
   }


   function rawSearchOptionsToAdd(){
      $tab = [];

      $tab[] = [
         'id'                 => '4',
         'table'              => 'glpi_plugin_openmedis_medicaldevicecategories',
         'field'              => 'name',
         'name'               => PluginOpenmedisMedicalDeviceCategory::getFieldLabel(1),
         'datatype'           => 'dropdown'
      ];

      $tab[] = [
         'id'                 => '1',
         'table'              => $this->getTable(),
         'field'              => 'name',
         'name'               => __('Name'),
         'datatype'           => 'itemlink',
         'massiveaction'      => false,
         'autocomplete'       => true,
      ];

      $tab[] = [
         'id'                 => '5',
         'table'              => $this->getTable(),
         'field'              => 'barcode',
         'name'               => __('Barcode','openmedis'),
         'datatype'           => 'string',
      ];

      $tab[] = [
         'id'                 => '40',
         'table'              => 'glpi_plugin_openmedis_medicaldevicemodels',
         'field'              => 'name',
         'name'               => __('Model'),
         'datatype'           => 'dropdown'
      ];

      return $tab;

   }

   function rawSearchOptions() {
      $tab = [];
      


      $tab[] = [
         'id'                 => 'common',
         'name'               => __('Characteristics')
      ];



      $tab[] = [
         'id'                 => '2',
         'table'              => $this->getTable(),
         'field'              => 'id',
         'name'               => __('ID'),
         'massiveaction'      => false,
         'datatype'           => 'number'
      ];
      $tab = array_merge($tab, $this->rawSearchOptionsToAdd());
      $tab = array_merge($tab, Location::rawSearchOptionsToAdd());



      $stateItem = new PluginOpenmedisState_Item();
      $visibleStates = $stateItem->getVisibleStates('PluginOpenmedisMedicalDevice');
      $tab[] = [
         'id'                 => '31',
         'table'              => 'glpi_states',
         'field'              => 'completename',
         'name'               => __('Status'),
         'datatype'           => 'dropdown',
         'condition'          => count($visibleStates) > 0 ? ['id' => $visibleStates] : []
      ];

      $tab[] = [
         'id'                 => '6',
         'table'              => $this->getTable(),
         'field'              => 'otherserial',
         'name'               => __('Inventory number'),
         'datatype'           => 'string',
         'autocomplete'       => true,
      ];

      $tab[] = [
         'id'                 => '7',
         'table'              => $this->getTable(),
         'field'              => 'contact',
         'name'               => __('Alternate user name', 'openmedis'),
         'datatype'           => 'string',
         'autocomplete'       => true,
      ];

      $tab[] = [
         'id'                 => '8',
         'table'              => $this->getTable(),
         'field'              => 'contact_num',
         'name'               => __('Alternate user contact number', 'openmedis'),
         'datatype'           => 'string',
         'autocomplete'       => true,
      ];

      $tab[] = [
         'id'                 => '70',
         'table'              => 'glpi_users',
         'field'              => 'name',
         'name'               => __('User'),
         'datatype'           => 'dropdown',
         'right'              => 'all'
      ];

      $tab[] = [
         'id'                 => '71',
         'table'              => 'glpi_groups',
         'field'              => 'completename',
         'name'               => __('Group'),
         'condition'          => ['is_itemgroup' => 1],
         'datatype'           => 'dropdown'
      ];

      $tab[] = [
         'id'                 => '19',
         'table'              => $this->getTable(),
         'field'              => 'date_mod',
         'name'               => __('Last update'),
         'datatype'           => 'datetime',
         'massiveaction'      => false
      ];

      $tab[] = [
         'id'                 => '121',
         'table'              => $this->getTable(),
         'field'              => 'date_creation',
         'name'               => __('Creation date'),
         'datatype'           => 'datetime',
         'massiveaction'      => false
      ];

      $tab[] = [
         'id'                 => '16',
         'table'              => $this->getTable(),
         'field'              => 'comment',
         'name'               => __('Comments'),
         'datatype'           => 'text'
      ];

      $tab[] = [
         'id'                 => '11',
         'table'              => $this->getTable(),
         'field'              => 'brand',
         'name'               => __('Brand'),
         'datatype'           => 'string',
         'autocomplete'       => true,
      ];

      $tab[] = [
         'id'                 => '23',
         'table'              => 'glpi_manufacturers',
         'field'              => 'name',
         'name'               => __('Manufacturer'),
         'datatype'           => 'dropdown'
      ];

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
         'id'                 => '61',
         'table'              => $this->getTable(),
         'field'              => 'template_name',
         'name'               => __('Template name'),
         'datatype'           => 'text',
         'massiveaction'      => false,
         'nosearch'           => true,
         'nodisplay'          => true,
         'autocomplete'       => true,
      ];

      $tab[] = [
         'id'                 => '80',
         'table'              => 'glpi_entities',
         'field'              => 'completename',
         'name'               => __('Entity'),
         'massiveaction'      => false,
         'datatype'           => 'dropdown'
      ];

      $tab[] = [
         'id'                 => '82',
         'table'              => $this->getTable(),
         'field'              => 'is_global',
         'name'               => __('Global management'),
         'datatype'           => 'bool',
         'massiveaction'      => false
      ];

      // add objectlock search options
      $tab = array_merge($tab, ObjectLock::rawSearchOptionsToAdd(get_class($this)));

      $tab = array_merge($tab, Notepad::rawSearchOptionsToAdd());

      //$tab = array_merge($tab, Datacenter::rawSearchOptionsToAdd(get_class($this)));
     // $tab = array_merge($tab, Item_Devices::rawSearchOptionsToAdd(get_class($this)));

      //$tab = array_merge($tab, PluginOpenmedisDeviceMedicalAccessory::rawSearchOptionsToAdd(get_class($this)));

      return $tab;
   }

   static function getTypes($all = false) {

      if ($all) {
         return self::$types;
      }

      // Only allowed types
      $types = self::$types;

      foreach ($types as $key => $type) {
         if (!class_exists($type)) {
            continue;
         }

         $item = new $type();
         if (!$item->canView()) {
            unset($types[$key]);
         }
      }
      return $types;
   }

   /**
    * For other plugins, add a type to the linkable types
    *
    * @since version 1.3.0
    *
    * @param $type string class name
   **/
   static function registerType($type) {
      if (!in_array($type, self::$types)) {
         self::$types[] = $type;
      }
   }


   static function getIcon() {
      return "fas fa-laptop-medical";
   }

   function pre_updateInDB() {

      if ( isset($this->fields['plugin_openmedis_medicaldevicecategories_id'])){
         // set parent
         $cat = new PluginOpenmedisMedicalDeviceCategory();
         $cat->getFromDB($this->fields['plugin_openmedis_medicaldevicecategories_id']);
         while (isset($cat->fields['plugin_openmedis_medicaldevicecategories_id']) 
            && $cat->fields['level']>1) {
            $cat->getFromDB($cat->fields['plugin_openmedis_medicaldevicecategories_id']);
         }
         $this->fields['plugin_openmedis_medicaldevicecategories_parent_id'] =
            $cat->fields['id'];
      }

      parent::pre_updateInDB();
   }
}
