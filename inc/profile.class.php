<?php

if (!defined('GLPI_ROOT')) {
    die("Sorry. You can't access directly to this file");
}

class PluginOpenmedisProfile extends Profile
{

    static $rightname = "profile";

    function getTabNameForItem(CommonGLPI $item, $withtemplate = 0)
    {
        if ($item->getType() == 'Profile') {
            return __('Health technology');
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
                ['plugin_openmedis' => __('Medical Device asset'),
                'plugin_openmedis_medicaldevicemodel' => __('Medical Device Models'),
                'plugin_openmedis_medicaldevicecategory' => __('Medical Device Category'),
                'plugin_openmedis_medicalaccessory' => __('Medical Accessory'),
                'plugin_openmedis_medicalaccessory_type' => __('Medical Accessory Type'),
                'plugin_openmedis_medicalaccessorycategorie' => __('Medical Accessory Model'),
                'plugin_openmedis_openticket' => __('OpenTicket for Medical Device')]);
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
        ['plugin_openmedis' => 7,
        'plugin_openmedis_medicaldevicemodel' => 7,
        'plugin_openmedis_medicaldevicecategory' => 7,
        'plugin_openmedis_medicalaccessory' => 7,
        'plugin_openmedis_medicalaccessory_type' => 7,
        'plugin_openmedis_medicalaccessorycategory' => 7,
        'plugin_openmedis_openticket' => 7], true);
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
                'label' => __('Medical Device'),
                'field' => 'plugin_openmedis'],
                ['itemtype' => 'PluginOpenmedisMedicalDeviceCategory',
                'label' => __('Medical Device Categories '),
                'field' => 'plugin_openmedis_medicaldevicecategory'],
                ['itemtype' => 'PluginOpenmedisMedicalDeviceModel',
                'label' => __('Medical Device Model'),
                'field' => 'plugin_openmedis_medicaldevicemodel'],
                ['itemtype' => 'PluginOpenmedisDeviceMedicalAccessory',
                'label' => __('Medical Accessory '),
                'field' => 'plugin_openmedis_medicalaccessory'],
                ['itemtype' => 'PluginOpenmedisMedicalAccessoryCategory',
                'label' => __('Medical Accessory Category'),
                'field' => 'plugin_openmedis_medicalaccessorycategory'],
                ['itemtype' => 'PluginOpenmedisMedicalAccessoryType',
                'label' => __('Medical Accessory Type '),
                'field' => 'plugin_openmedis_medicalaccessory_type']
        ];
        if ($all) {
            $rights[] = ['itemtype' => 'PluginOpenmedisMedicalDevice',
                         'label'    =>  __('Associable items to a ticket'),
                         'field'    => 'plugin_openmedis_openticket'];
         }
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
                       'openmedis_openticket' => 'plugin_openmedis_openticket',
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

