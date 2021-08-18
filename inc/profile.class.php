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
    die("Sorry. You can't access directly to this file");
}

class PluginOpenmedisProfile extends Profile
{

    static $rightname = "profile";

    function getTabNameForItem(CommonGLPI $item, $withtemplate = 0)
    {
        if ($item->getType() == 'Profile') {
            return __('Health technology', 'openmedis');
        }

        return '';
    }

    /**
     * @param CommonGLPI $item
     * @param int $tabnum
     * @param int $withtemplate
     *
     * @return bool
     */
    static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0)
    {

        if ($item->getType() == 'Profile') {
            $ID = $item->getID();
            $prof = new self();

            self::addDefaultProfileInfos($ID,
                [PluginOpenmedisMedicalDevice::$rightname => PluginOpenmedisMedicalDevice::getTypeName(1),
                PluginOpenmedisMedicalDeviceModel::$rightname => PluginOpenmedisMedicalDeviceModel::getTypeName(1),
                PluginOpenmedisMedicalDeviceCategory::$rightname => PluginOpenmedisMedicalDeviceCategory::getTypeName(1),
                PluginOpenmedisDeviceMedicalAccessory::$rightname => PluginOpenmedisDeviceMedicalAccessory::getTypeName(1),
                PluginOpenmedisMedicalConsumableItemType::$rightname => PluginOpenmedisMedicalConsumableItemType::getTypeName(1),
                PluginOpenmedisMedicalAccessoryType::$rightname => PluginOpenmedisMedicalAccessoryType::getTypeName(1),
                PluginOpenmedisMedicalConsumable::$rightname=> PluginOpenmedisMedicalConsumable::getTypeName(1),
                ]);
            $prof->showForm($ID);
        }
        return true;
    }

    /**
     * @param $ID
     */
    static function createFirstAccess($ID)
    {
        //85
        self::addDefaultProfileInfos($ID,
        [PluginOpenmedisMedicalDevice::$rightname => 7,
        PluginOpenmedisMedicalDeviceModel::$rightname => 7,
        PluginOpenmedisMedicalDeviceCategory::$rightname => 7,
        PluginOpenmedisMedicalAccessoryType::$rightname => 7,
        PluginOpenmedisMedicalConsumableItemType::$rightname => 7,
        PluginOpenmedisDeviceMedicalAccessory::$rightname => 7,
        PluginOpenmedisMedicalConsumable::$rightname => 7,
    }

    /**
     * @param      $profiles_id
     * @param      $rights
     * @param bool $drop_existing
     *
     * @internal param $profile
     */
    static function addDefaultProfileInfos($profiles_id, $rights, $drop_existing = false)
    {
        $dbu = new DbUtils();
        $profileRight = new ProfileRight();
        foreach ($rights as $right => $value) {
            if ($dbu->countElementsInTable('glpi_profilerights',
                    ["profiles_id" => $profiles_id, "name" => $right]) && $drop_existing) {
                $profileRight->deleteByCriteria(['profiles_id' => $profiles_id, 'name' => $right]);
            }
            if (!$dbu->countElementsInTable('glpi_profilerights',
                ["profiles_id" => $profiles_id, "name" => $right])) {
                $myright['profiles_id'] = $profiles_id;
                $myright['name'] = $right;
                $myright['rights'] = $value;
                $profileRight->add($myright);

                //Add right to the current session
                $_SESSION['glpiactiveprofile'][$right] = $value;
            }
        }
    }

    /**
     * Show profile form
     *
     * @param int $profiles_id
     * @param bool $openform
     * @param bool $closeform
     *
     * @return nothing
     * @internal param int $items_id id of the profile
     * @internal param value $target url of target
     */
    function showForm($profiles_id = 0, $openform = true, $closeform = true)
    {

        echo "<div class='firstbloc'>";
        if (($canedit = Session::haveRightsOr(self::$rightname, [CREATE, UPDATE, DELETE, PURGE]))
            && $openform) {
            $profile = new Profile();
            echo "<form method='post' action='" . $profile->getFormURL() . "'>";
        }

        $profile = new Profile();
        $profile->getFromDB($profiles_id);
       // if ($profile->getField('interface') == 'central') {
            $rights = $this->getAllRights();
            $profile->displayRightsChoiceMatrix($rights, ['canedit' => $canedit,
                'default_class' => 'tab_bg_2',
                'title' => __('General')]);
        //}

        if ($canedit
            && $closeform) {
            echo "<div class='center'>";
            echo Html::hidden('id', ['value' => $profiles_id]);
            echo Html::submit(_sx('button', 'Save'), ['name' => 'update']);
            echo "</div>\n";
            Html::closeForm();
        }
        echo "</div>";
    }

    static function uninstallProfile()
    {
        $pfProfile = new self();
        $a_rights = $pfProfile->getAllRights();
        foreach ($a_rights as $data) {
            ProfileRight::deleteProfileRights([$data['field']]);
        }
    }

    /**
     * @param bool $all
     *
     * @return array
     */
    static function getAllRights($all = false)
    {
        $rights = [
                ['itemtype' => 'PluginOpenmedisMedicalDevice',
                'label' => PluginOpenmedisMedicalDevice::getTypeName(1),
                'field' => PluginOpenmedisMedicalDevice::$rightname],
                ['itemtype' => 'PluginOpenmedisMedicalDeviceCategory',
                'label' => PluginOpenmedisMedicalDeviceCategory::getTypeName(1),
                'field' => PluginOpenmedisMedicalDeviceCategory::$rightname],
                ['itemtype' => 'PluginOpenmedisMedicalDeviceModel',
                'label' => PluginOpenmedisMedicalDeviceModel::getTypeName(1),
                'field' => PluginOpenmedisMedicalDeviceModel::$rightname],
                ['itemtype' => 'PluginOpenmedisDeviceMedicalAccessory',
                'label' => PluginOpenmedisDeviceMedicalAccessory::getTypeName(1),
                'field' => PluginOpenmedisDeviceMedicalAccessory::$rightname],
                ['itemtype' => 'PluginOpenmedisMedicalConsumableItemType',
                'label' => PluginOpenmedisMedicalConsumableItemType::getTypeName(1),
                'field' => PluginOpenmedisMedicalConsumableItemType::$rightname],
                ['itemtype' => 'PluginOpenmedisMedicalAccessoryType',
                'label' => PluginOpenmedisMedicalAccessoryType::getTypeName(1),
                'field' => PluginOpenmedisMedicalAccessoryType::$rightname],
                ['itemtype' => 'PluginOpenmedisMedicalConsumable',
                'label' => PluginOpenmedisMedicalConsumable::getTypeName(1),
                'field' => PluginOpenmedisMedicalConsumable::$rightname]
        ];

         
        return $rights;
    }

    /**
     * Init profiles
     *
     * @param $old_right
     *
     * @return int
     */

    static function translateARight($old_right)
    {
        switch ($old_right) {
            case '':
                return 0;
            case 'r' :
                return READ;
            case 'w':
                return ALLSTANDARDRIGHT + READNOTE + UPDATENOTE;
            case '0':
            case '1':
                return $old_right;

            default :
                return 0;
        }
    }
   /**
    * @since 0.85
    *
    * Migration rights from old system to the new one for one profile
    * @param $profiles_id the profile ID
    *
    * @return bool
   **/
  static function migrateOneProfile($profiles_id) {
    global $DB;

    //Cannot launch migration if there's nothing to migrate...
    if ($DB->tableExists('glpi_plugin_openmedis_profiles')) {
       foreach ($DB->request('glpi_plugin_openmedis_profiles',
                             ['id' => $profiles_id]) as $profile_data) {

          $matching = ['openmedis'  => 'plugin_openmedis',
                       'openmedis_type' => 'plugin_openmedis_category',
                       'openmedis_model' => 'plugin_openmedis_model'
                    ];
          $current_rights = ProfileRight::getProfileRights($profiles_id, array_values($matching));
          foreach ($matching as $old => $new) {
             if (!isset($current_rights[$old])) {
                $query = "UPDATE `glpi_profilerights`
                          SET `rights`='".self::translateARight($profile_data[$old])."'
                          WHERE `name`='$new' AND `profiles_id`='$profiles_id'";
                $DB->query($query);
             }
          }
       }
    }
 }


   /**
    * Initialize profiles, and migrate it necessary
   **/
  static function initProfile() {
    global $DB;

    $profile = new self();
    $dbu     = new DbUtils();

    //Add new rights in glpi_profilerights table
    foreach ($profile->getAllRights(true) as $data) {
       if ($dbu->countElementsInTable("glpi_profilerights",
                                      ['name' => $data['field']]) == 0) {
          ProfileRight::addProfileRights([$data['field']]);
       }
    }

    //Migration old rights in new ones
    /*foreach ($DB->request(['SELECT' => 'id',
                           'FROM'   => 'glpi_profiles']) as $prof) {
       self::migrateOneProfile($prof['id']);
    }*/
    foreach ($DB->request(['FROM'  => 'glpi_profilerights',
                           'WHERE' => ['profiles_id' => $_SESSION['glpiactiveprofile']['id'],
                                       'name'        => ['LIKE', '%plugin_openmedis%']]]) as $prof) {
       $_SESSION['glpiactiveprofile'][$prof['name']] = $prof['rights'];
    }
 }

   

    static function removeRightsFromSession()
    {
        foreach (self::getAllRights(true) as $right) {
            if (isset($_SESSION['glpiactiveprofile'][$right['field']])) {
                unset($_SESSION['glpiactiveprofile'][$right['field']]);
            }
        }
    }
}

