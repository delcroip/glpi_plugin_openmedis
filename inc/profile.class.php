<?php
/*
 * @version $Id: HEADER 15930 2011-10-30 15:47:55Z tsmr $
 -------------------------------------------------------------------------
 open_medis plugin for GLPI
 Copyright (C) 2014-2016 by the open_medis Development Team.

 https://github.com/InfotelGLPI/open_medis
 -------------------------------------------------------------------------

 LICENSE

 This file is part of open_medis.

 open_medis is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 open_medis is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with open_medis. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

class PluginOpenMedisProfile extends Profile {

   static $rightname = "profile";

   function getTabNameForItem(CommonGLPI $item, $withtemplate = 0) {

      if ($item->getType()=='Profile'
         && $item->getField('interface')!='helpdesk') {
         return PluginOpenMedisRack::getTypeName(2);
      }
      return '';
   }


   static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0) {
      global $CFG_GLPI;

      if ($item->getType() == 'Profile') {
         $prof = new self();
         self::addDefaultProfileInfos($item->getField('id'),
                                      ['plugin_open_medis'                 => 0,
                                             'plugin_open_medis_model'          => 0,
                                             'plugin_open_medis_open_ticket'    => 0]);
         $prof->showForm($item->getField('id'));
      }
      return true;
   }


   /**
    * Show profile form
    *
    * @param $items_id integer id of the profile
    * @param $target value url of target
    *
    * @return nothing
    **/
   function showForm($profiles_id = 0, $openform = true, $closeform = true) {

      echo "<div class='firstbloc'>";
      if (($canedit = Session::haveRightsOr(self::$rightname, [CREATE, UPDATE, PURGE]))
          && $openform) {
         $profile = new Profile();
         echo "<form method='post' action='".$profile->getFormURL()."'>";
      }

      $profile = new Profile();
      $profile->getFromDB($profiles_id);

      if ($profile->getField('interface') == 'central') {
         $rights = $this->getAllRights();
         $profile->displayRightsChoiceMatrix($rights, ['canedit'       => $canedit,
                                                         'default_class' => 'tab_bg_2',
                                                         'title'         => __('General')]);
      }
      echo "<table class='tab_cadre_fixehov'>";
      echo "<tr class='tab_bg_1'><th colspan='4'>".__('Helpdesk')."</th></tr>\n";

      $effective_rights = ProfileRight::getProfileRights($profiles_id, ['plugin_open_medis_open_ticket']);
      echo "<tr class='tab_bg_2'>";
      echo "<td width='20%'>".__('Associable items to a ticket')."</td>";
      echo "<td colspan='5'>";
      Html::showCheckbox(['name'    => '_plugin_open_medis_open_ticket',
                               'checked' => $effective_rights['plugin_open_medis_open_ticket']]);
      echo "</td></tr>\n";
      echo "</table>";

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

   static function getAllRights($all = false) {
      $rights = [
          ['itemtype'  => 'PluginOpenMedisMedicalDevice',
                'label'     => _n('Medical Device', 'Medical Devices', 2, 'open_medis'),
                'field'     => 'plugin_open_medis'
          ],
          ['itemtype'  => 'PluginOpenMedisModel',
                'label'     => _n('openMedis Model', 'openMedis models', 2, 'open_medis'),
                'field'     => 'plugin_open_medis_model'
          ],
          ['itemtype'  => 'PluginOpenMedisType',
                'label'     => _n('openMedis Type', 'openMedis Type', 2, 'open_medis'),
                'field'     => 'plugin_open_medis_type'
          ],
      ];

      if ($all) {
         $rights[] = ['itemtype' => 'PluginOpenMedisMedicalDevice',
                           'label'    =>  __('Associable items to a ticket'),
                           'field'    => 'plugin_open_medis_open_ticket'];
      }

      return $rights;
   }


   /**
    * Init profiles
    *
    **/

   static function translateARight($old_right) {
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
   * Migration rights from old system to the new one for one profile
   * @param $profiles_id the profile ID
   */
   static function migrateOneProfile($profiles_id) {
      global $DB;
      //Cannot launch migration if there's nothing to migrate...
      if (!$DB->tableExists('glpi_plugin_open_medis_profiles')) {
         return true;
      }

      foreach ($DB->request('glpi_plugin_open_medis_profiles',
                            "`profiles_id`='$profiles_id'") as $profile_data) {

         $matching = ['open_medis'    => 'plugin_open_medis',
                           'model'   => 'plugin_open_medis_model',
						   'type'   => 'plugin_open_medis_type',
                           'open_ticket' => 'plugin_open_medis_open_ticket'];
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


   /**
   * Initialize profiles, and migrate it necessary
   */
   static function initProfile() {
      global $DB;
      $profile = new self();
      $dbu     = new DbUtils();
      //Add new rights in glpi_profilerights table
      foreach ($profile->getAllRights(true) as $data) {
         if ($dbu->countElementsInTable("glpi_profilerights",
                                  "`name` = '".$data['field']."'") == 0) {
            ProfileRight::addProfileRights([$data['field']]);
         }
      }

      //Migration old rights in new ones
      foreach ($DB->request("SELECT `id` FROM `glpi_profiles`") as $prof) {
         self::migrateOneProfile($prof['id']);
      }
      foreach ($DB->request("SELECT *
                           FROM `glpi_profilerights` 
                           WHERE `profiles_id`='".$_SESSION['glpiactiveprofile']['id']."' 
                              AND `name` LIKE '%plugin_open_medis%'") as $prof) {
         $_SESSION['glpiactiveprofile'][$prof['name']] = $prof['rights'];
      }
   }

   static function createFirstAccess($profiles_id) {
      self::addDefaultProfileInfos($profiles_id,
                                   ['plugin_open_medis'             => ALLSTANDARDRIGHT,
                                         'plugin_open_medis_model'       => ALLSTANDARDRIGHT,
										 'plugin_open_medis_type'       => ALLSTANDARDRIGHT,
                                         'plugin_open_medis_open_ticket' => 1], true);

   }


   static function removeRightsFromSession() {
      foreach (self::getAllRights(true) as $right) {
         if (isset($_SESSION['glpiactiveprofile'][$right['field']])) {
            unset($_SESSION['glpiactiveprofile'][$right['field']]);
         }
      }
   }

   static function removeRightsFromDB() {
      $plugprof = new ProfileRight();
      foreach (self::getAllRights(true) as $right) {
         $plugprof->deleteByCriteria(['name' => $right['field']]);
      }
   }

   /**
    * @param $profile
   **/
   static function addDefaultProfileInfos($profiles_id, $rights, $drop_existing = false) {
      $dbu          = new DbUtils();
      $profileRight = new ProfileRight();
      foreach ($rights as $right => $value) {
         if ($dbu->countElementsInTable('glpi_profilerights',
                                   "`profiles_id`='$profiles_id' AND `name`='$right'") && $drop_existing) {
            $profileRight->deleteByCriteria(['profiles_id' => $profiles_id, 'name' => $right]);
         }
         if (!$dbu->countElementsInTable('glpi_profilerights',
                                   "`profiles_id`='$profiles_id' AND `name`='$right'")) {
            $myright['profiles_id'] = $profiles_id;
            $myright['name']        = $right;
            $myright['rights']      = $value;
            $profileRight->add($myright);

            //Add right to the current session
            $_SESSION['glpiactiveprofile'][$right] = $value;
         }
      }
   }
}
