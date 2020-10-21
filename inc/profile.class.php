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
                'plugin_openmedis_models' => __('Medical Device Models'),
                'plugin_openmedis_type' => __('Medical Device type'),
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
        ['plugin_openmedis' => 1,
        'plugin_openmedis_models' => 1,
        'plugin_openmedis_type' => 1,
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
            ['rights' => [READ => __('Read'), CREATE => __('Create'), UPDATE => __('Update'), DELETE => __('Delete')],
                'label' => __('Medical Device'),
                'field' => 'plugin_openmedis'],
                ['rights' =>[READ => __('Read'), CREATE => __('Create'), UPDATE => __('Update'), DELETE => __('Delete')],
                'label' => __('Type & category '),
                'field' => 'plugin_openmedis_type'],
                ['rights' => [READ => __('Read'), CREATE => __('Create'), UPDATE => __('Update'), DELETE => __('Delete')],
                'label' => __('Model '),
                'field' => 'plugin_openmedis_model'],
                ['rights' => [ CREATE => __('Create')],
                'label' => __('Create ticket '),
                'field' => 'plugin_openmedis_openticket'],
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
     * Initialize profiles, and migrate it necessary
     */
    static function initProfile()
    {
        
        $pfProfile = new self();
        $profile   = new Profile();
        $a_rights  = $pfProfile->getAllRights();
  
        foreach ($a_rights as $data) {
           if (countElementsInTable("glpi_profilerights", "`name` = '".$data['field']."'") == 0) {
              ProfileRight::addProfileRights(array($data['field']));
              $_SESSION['glpiactiveprofile'][$data['field']] = 0;
           }
        }
  
        // Add all rights to current profile of the user
        if (isset($_SESSION['glpiactiveprofile'])) {
           $dataprofile       = array();
           $dataprofile['id'] = $_SESSION['glpiactiveprofile']['id'];
           $profile->getFromDB($_SESSION['glpiactiveprofile']['id']);
           foreach ($a_rights as $info) {
              if (is_array($info)
                  && ((!empty($info['itemtype'])) || (!empty($info['rights'])))
                    && (!empty($info['label'])) && (!empty($info['field']))) {
  
                 if (isset($info['rights'])) {
                    $rights = $info['rights'];
                 } else {
                    $rights = $profile->getRightsFor($info['itemtype']);
                 }
                 foreach ($rights as $right => $label) {
                    $dataprofile['_'.$info['field']][$right] = 1;
                    $_SESSION['glpiactiveprofile'][$data['field']] = $right;
                 }
              }
           }
           $profile->update($dataprofile);
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

