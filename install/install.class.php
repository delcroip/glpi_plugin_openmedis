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
 *
 * @author tbugier (from flyve-mdm module)
 * @author delcroip
 * @since 0.1.0
 *
 */
class PluginOpenmedisInstall {

   protected static $currentVersion = null;

   protected $migration;

      /**
    * array of upgrade steps key => value
    * key   is the version to upgrade from
    * value is the version to upgrade to
    *
    * Exemple: an entry '2.0' => '2.1' tells that versions 2.0
    * are upgradable to 2.1
    *
    * When possible avoid schema upgrade between bugfix releases. The schema
    * version contains major.minor numbers only. If an upgrade of the schema
    * occurs between bugfix releases, then the upgrade will start from the
    * major.minor.0 version up to the end of the the versions list.
    * Exemple: if previous version is 2.6.1 and current code is 2.6.3 then
    * the upgrade will start from 2.6.0 to 2.6.3 and replay schema changes
    * between 2.6.0 and 2.6.1. This means that upgrade must be _repeatable_.
    *
    * @var array
    */
   private $upgradeSteps = [
      '1.0'    => '1.1',
      '1.1'    => '1.2',
      '1.2'    => '1.3',
      '1.3'    => '1.4'
   ];

   /**
    * Autoloader for installation
    * @param string $classname
    * @return bool
    */
   public static function autoload($classname) {
      // useful only for installer GLPI autoloader already handles inc/ folder
      $filename = dirname(__DIR__) . '/inc/' . strtolower(str_replace('PluginOpenmedis', '',
            $classname)) . '.class.php';
      if (is_readable($filename) && is_file($filename)) {
         include_once($filename);
         return true;
      }
   }

   /**
    *
    * Install the plugin
    *
    * @return boolean true (assume success, needs enhancement)
    *
    */
   public function install(Migration $migration) {
      $this->migration = $migration;
      spl_autoload_register([__CLASS__, 'autoload']);
      $this->installSchema();
      $this->createInitialConfig();
      $this->migration->executeMigration();
      $this->installUpgradeCommonTasks();
      Config::setConfigurationValues(
         'openmedis', [
            'schema_version' => PLUGIN_OPENMEDIS_SCHEMA_VERSION,
         ]
      );
      return true;
   }

   protected function installSchema() {
      global $DB;

      $this->migration->displayMessage("create database schema");
        if (!$DB->runFile(__DIR__ ."/mysql/plugin_openmedis_empty.sql")){
            $this->migration->displayWarning("Error creating tables : " . $DB->error(), true);
            return false;
        }else{

            if (!$DB->runFile(__DIR__ ."/mysql/data-1.0.sql")){
                $this->migration->displayWarning("Error loading the data : " . $DB->error(), true);
                return false;
            }else{
               $query = "SELECT id, name FROM ".PluginOpenmedisMedicalDeviceCategory::getTable()." WHERE level=1";
               if ($result=$DB->query($query)) {
                  if ($DB->numrows($result)>0) {
                     $dropdown = new PluginOpenmedisMedicalDeviceCategory();
                     while ($data=$DB->fetchArray($result)) {
               // get the list on the level 1 cat

                        $dropdown->regenerateTreeUnderID($data['id'],$data['name'],false);
                      }
                      //complete name for level 1 FIXME
                      $query = "UPDATE ".PluginOpenmedisMedicalDeviceCategory::getTable()." t SET t.completename = t.name WHERE level=1";
                      if (!$DB->query($query)) {
                        $this->migration->displayWarning("Error setting medical device category level 1 : " . $DB->error(), true);
                      }

                  }
                  
               }else{
                  $this->migration->displayWarning("Error setting medical device category level > 1 : " . $DB->error(), true);
               }
               
               
            }
        }    
      /*
      // openmedis support only glpis>5
      if (version_compare(GLPI_VERSION, '9.3.0') >= 0) {
         $this->migrateToInnodb();
      }
      */
      return true;
   }

   /**
    * Find a profile having the given comment, or create it
    * @param string $name Name of the profile
    * @param string $comment Comment of the profile
    * @return integer profile ID
    */
   protected static function getOrCreateProfile($name, $comment) {
      global $DB;
      $comment = $DB->escape($comment);
      $profile = new Profile();
      if (version_compare(GLPI_VERSION, '9.4') < 0) {
         $condition = "`comment`='$comment'";
      } else {
         $condition = [
            'comment' => $comment,
         ];
      }
      $profiles = $profile->find($condition);
      $row = array_shift($profiles);
      if ($row === null) {
         $profile->fields["name"] = $DB->escape(__($name, "openmedis"));
         $profile->fields["comment"] = $comment;
         $profile->fields["interface"] = "central";
         if ($profile->addToDB() === false) {
            die("Error while creating users profile : $name\n\n" . $DB->error());
         }
         return $profile->getID();
      } else {
         return $row['id'];
      }
   }

  
   /**
    * @return null|string
    */
   public function getSchemaVersion() {
      if ($this->isPluginInstalled()) {
         $config = Config::getConfigurationValues('openmedis');
         if (!isset($config['schema_version'])) {
            return '1.0'; // first schema verison was not saved
         }
         return $config['schema_version'];
      }else{
         return null;
      }

      return null;
   }

   /**
    * is the plugin already installed ?
    *
    * @return boolean
    */
   public function isPluginInstalled() {
      global $DB;

      // Check tables of the plugin between 1.1 and 2.0 releases
      $result = $DB->query("SHOW TABLES LIKE 'glpi_plugin_openmedis\\_%'");
      if ($result) {
         if ($DB->numrows($result) > 0) {
            return true;
         }
      }

      return false;
   }


   /**
    * Give all rights on the plugin to the profile of the current user
    */
   protected function createFirstAccess() {
      $this->migration->displayMessage("Create write access for current user for the openMedis plugin");

      $profileRight = new ProfileRight();

      $newRights = [
        PluginOpenmedisMedicalDevice::$rightname =>  READ | CREATE | UPDATE | DELETE | PURGE | READNOTE | UPDATENOTE,
        PluginOpenmedisMedicalDeviceModel::$rightname =>  READ | CREATE | UPDATE | DELETE | PURGE,
        PluginOpenmedisMedicalDeviceCategory::$rightname => READ | CREATE | UPDATE | DELETE | PURGE,
        PluginOpenmedisDeviceMedicalAccessory::$rightname =>  READ | CREATE | UPDATE | DELETE | PURGE,
        PluginOpenmedisMedicalAccessoryType ::$rightname =>  READ | CREATE | UPDATE | DELETE | PURGE,
//        PluginOpenmedisMedicalAccessoryCategory::$rightname =>  READ | CREATE | UPDATE | DELETE | PURGE,
        PluginOpenmedisMedicalConsumable::$rightname => READ | CREATE | UPDATE | DELETE | PURGE,
        PluginOpenmedisMedicalConsumableItem::$rightname => READ | CREATE | UPDATE | DELETE | PURGE| READNOTE | UPDATENOTE,
        PluginOpenmedisMedicalConsumableItemType::$rightname => READ | CREATE | UPDATE | DELETE | PURGE,
        PluginOpenmedisUtilization::$rightname => READ | CREATE | UPDATE | DELETE | PURGE,
      ];

      $profileRight->updateProfileRights($_SESSION['glpiactiveprofile']['id'], $newRights);

      $_SESSION['glpiactiveprofile'] = $_SESSION['glpiactiveprofile'] + $newRights;
   }

   
   /**
    * Create a profile for guest users
    */
   protected function createGuestProfileAccess() {
      $this->migration->displayMessage("Create guest profile for the openMedis plugin");
      // create profile for guest users
      $profileId = self::getOrCreateProfile(
         __("openMedis guest users", "openmedis"),
         __("guest openMedis users. Created by openMedis - do NOT modify this comment.", "openmedis")
      );
      Config::setConfigurationValues('openmedis', ['guest_profiles_id' => $profileId]);
      $profileRight = new ProfileRight();
      $profileRight->updateProfileRights($profileId, [
        PluginOpenmedisMedicalDevice::$rightname =>  READ ,
        PluginOpenmedisMedicalDeviceModel::$rightname =>  READ ,
        PluginOpenmedisMedicalDeviceCategory::$rightname =>  READ ,
        PluginOpenmedisDeviceMedicalAccessory::$rightname =>  READ ,
        PluginOpenmedisMedicalAccessoryType ::$rightname => READ ,
//        PluginOpenmedisMedicalAccessoryCategory::$rightname => READ ,
        PluginOpenmedisMedicalConsumable::$rightname => READ ,
        PluginOpenmedisMedicalConsumableItem::$rightname => READ ,
        PluginOpenmedisMedicalConsumableItemType::$rightname =>  READ ,
        PluginOpenmedisUtilization::$rightname =>  READ ,
      ]);
   }

   /**
    * Create a profile for agent user accounts
    */
   protected function createAgentProfileAccess() {
      $this->migration->displayMessage("Create technician profile for the openMedis plugin");
      // create profile for guest users
      $profileId = self::getOrCreateProfile(
         __("openMedis technician  users", "openmedis"),
         __(" openMedis technical users. Created by openMedis - do NOT modify this comment.",
            "openmedis")
      );
      Config::setConfigurationValues('openmedis', ['agent_profiles_id' => $profileId]);
      $profileRight = new ProfileRight();
      $profileRight->updateProfileRights($profileId, [
        PluginOpenmedisMedicalDevice::$rightname =>  READ | CREATE | UPDATE | DELETE |  READNOTE | UPDATENOTE, 
        PluginOpenmedisMedicalDeviceModel::$rightname =>  READ ,
        PluginOpenmedisMedicalDeviceCategory::$rightname =>  READ ,
        PluginOpenmedisDeviceMedicalAccessory::$rightname =>  READ | CREATE | UPDATE | DELETE,
        PluginOpenmedisMedicalAccessoryType ::$rightname => READ ,
  //      PluginOpenmedisMedicalAccessoryCategory::$rightname => READ ,
        PluginOpenmedisMedicalConsumable::$rightname => READ | CREATE | UPDATE | DELETE |  READNOTE | UPDATENOTE, 
        PluginOpenmedisMedicalConsumableItem::$rightname => READ ,
        PluginOpenmedisMedicalConsumableItemType::$rightname =>  READ ,
        PluginOpenmedisUtilization::$rightname =>  READ ,
      ]);
   }

 
   /**
    * Upgrade the plugin to the current code version
    *
    * @param string version to upgrade from
    */
   public function upgrade(Migration $migration) {
      spl_autoload_register([__CLASS__, 'autoload']);
      $this->migration = $migration;
      if (isset($_SESSION['plugin_openmedis']['cli']) && $_SESSION['plugin_openmedis']['cli'] == 'force-upgrade') {
         // Might return false
         $fromSchemaVersion = array_search(PLUGIN_OPENMEDIS_SCHEMA_VERSION, $this->upgradeSteps);
      } else {
         $fromSchemaVersion = $this->getSchemaVersion();
      }
     
      // Prevent problem of execution time
      ini_set("max_execution_time", "0");
      ini_set("memory_limit", "-1");

      while ($fromSchemaVersion && isset($this->upgradeSteps[$fromSchemaVersion])) {
         $this->migration->displayMessage("Upgrade DB schema from  ".$fromSchemaVersion." to ".$this->upgradeSteps[$fromSchemaVersion]);         
         $this->upgradeOneStep($this->upgradeSteps[$fromSchemaVersion]);
         $fromSchemaVersion = $this->upgradeSteps[$fromSchemaVersion];
      }

      if (!PLUGIN_OPENMEDIS_IS_OFFICIAL_RELEASE) {
         $this->migration->displayMessage("Applying dev updates");                 
         $this->upgradeOneStep('dev');
      }
      $this->installUpgradeCommonTasks();
      return true;
   }

   private function installUpgradeCommonTasks() {
      $this->createFirstAccess();
      $this->createGuestProfileAccess();
      $this->createAgentProfileAccess();
      $this->createJobs();
      $this->createDisplayPreferences();

      Config::setConfigurationValues(
         'openmedis', [
            'version' => PLUGIN_OPENMEDIS_VERSION,
            'schema_version' => PLUGIN_OPENMEDIS_SCHEMA_VERSION,
         ]
      );


   }

   /**
    * Proceed to upgrade of the plugin to the given version
    *
    * @param string $toVersion
    */
   protected function upgradeOneStep($toVersion) {
      ini_set("max_execution_time", "0");
      ini_set("memory_limit", "-1");

      $suffix = str_replace('.', '_', $toVersion);
      $includeFile = __DIR__ . "/upgrade_to_$suffix.php";
      if (is_readable($includeFile) && is_file($includeFile)) {
         include_once $includeFile;
         $updateClass = "PluginOpenmedisUpgradeTo$suffix";
         $this->migration->addNewMessageArea("Upgrade to $toVersion");
         $upgradeStep = new $updateClass();
         $upgradeStep->upgrade($this->migration);
         $this->migration->executeMigration();
         $this->migration->displayMessage('Done');
      }
   }

   protected function createJobs() {
      CronTask::Register(PluginOpenmedisMedicalConsumableItem::class, 'MedicalConsumable', MINUTE_TIMESTAMP,
         [
            'comment' => PluginOpenmedisMedicalConsumableItem::cronInfo()['description'],
            'mode'    => CronTask::MODE_EXTERNAL,
         ]);
   }

   /**
    * Uninstall the plugin
    * @return boolean true (assume success, needs enhancement)
    */
   public function uninstall() {
      //$this->rrmdir(GLPI_PLUGIN_DOC_DIR . '/openmedis');

      $this->deleteRelations();
      $this->deleteProfileRights();
      $this->deleteProfiles();
      $this->deleteDisplayPreferences();
      $this->deleteTables();
      // Cron jobs deletion handled by GLPI

      $config = new Config();
      $config->deleteByCriteria(['context' => 'openmedis']);

      return true;
   }

   /**
    * Cannot use the method from PluginFlyvemdmToolbox if the plugin is being uninstalled
    * @param string $dir
    */
   protected function rrmdir($dir) {
      if (file_exists($dir) && is_dir($dir)) {
         $objects = scandir($dir);
         foreach ($objects as $object) {
            if ($object != "." && $object != "..") {
               if (filetype($dir . "/" . $object) == "dir") {
                  $this->rrmdir($dir . "/" . $object);
               } else {
                  unlink($dir . "/" . $object);
               }
            }
         }
         reset($objects);
         rmdir($dir);
      }
   }

   /**
    * Generate default configuration for the plugin
    */
   protected function createInitialConfig() {
      $this->migration->displayMessage("Generate default configuration for te openMedis plugin");
      /*
      no config yet for the module 
      example can be found here https://github.com/flyve-mdm/glpi-plugin/blob/develop/install/install.class.php#L646
      */
   }




   /**
    * Generate HTML version of a text
    * Replaces \n by <br>
    * Encloses the text un <p>...</p>
    * Add anchor to URLs
    * @param string $text
    * @return string
    */
   protected static function convertTextToHtml($text) {
      $text = '<p>' . str_replace("\n\n", '</p><p>', $text) . '</p>';
      $text = '<p>' . str_replace("\n", '<br>', $text) . '</p>';
      return $text;
   }


    /**
     *  Delete module tables
     */

    protected function deleteTables() {
      global $DB;

      $tables = $this->getTables();

      foreach ($tables as $table) {
         $DB->query("DROP TABLE IF EXISTS `$table`");
      }      // GARBAGE COLLECTOR
      $result = $DB->query("SHOW TABLES LIKE 'glpi_plugin_openmedis\\_%'");
      if ($result) {
         if ($DB->numrows($result) > 0) {
            //$this->migration->displayWarning(" Some of the module tables were not removed,".
            //" please clean the database: SHOW TABLES LIKE 'glpi_plugin_openmedis\\_%'", true);
         }
      }

      $tables_glpi = ["glpi_displaypreferences",
      "glpi_documents_items",
      "glpi_savedsearches",
      "glpi_logs",
      "glpi_items_tickets",
      "glpi_dropdowntranslations"];

        foreach ($tables_glpi as $table_glpi) {
        //fixme to be checked
        $DB->query("DELETE FROM `$table_glpi` WHERE `itemtype` LIKE '%luginOpenmedis%';");
        $DB->query("ALTER TABLE glpi_states DROP COLUMN is_visible_pluginopenmedismedicaldevice;");
        }
      }

   /**
    *   getTablelist
    **/
protected function getTables(){
   return [
      PluginOpenmedisMedicalDevice::getTable(),
      PluginOpenmedisMedicalDeviceModel::getTable(),
      PluginOpenmedisMedicalDeviceCategory::getTable(),
      PluginOpenmedisDeviceMedicalAccessory::getTable(),
      PluginOpenmedisMedicalAccessoryType::getTable(),
    //  PluginOpenmedisMedicalAccessoryCategory::getTable(),
      PluginOpenmedisMedicalConsumable::getTable(),
      PluginOpenmedisMedicalConsumableItem::getTable(),
      PluginOpenmedisMedicalConsumableItemType::getTable(),
      PluginOpenmedisUtilization::getTable(),
      PluginOpenmedisItem_DeviceMedicalAccessory::getTable(),
      PluginOpenmedisMedicalConsumableItem_MedicalDeviceModel::getTable(),
    ];
     

}


   protected function deleteProfiles() {
      $config = Config::getConfigurationValues('plugin:openmedis');

      foreach ($config as $profileId) {
         $profile = new Profile();
         $profile->getFromDB($profileId);
         if ($profile->deleteFromDB()) {
            $profileUser = new Profile_User();
            $profileUser->deleteByCriteria(['profiles_id' => $profileId], true);
         }
      }
   }

   protected function deleteProfileRights() {
      $rights = [
        PluginOpenmedisMedicalDevice::$rightname,
        PluginOpenmedisMedicalDeviceModel::$rightname,
        PluginOpenmedisMedicalDeviceCategory::$rightname,
        PluginOpenmedisDeviceMedicalAccessory::$rightname,
        PluginOpenmedisMedicalAccessoryType::$rightname,
      //  PluginOpenmedisMedicalAccessoryCategory::$rightname,
        PluginOpenmedisMedicalConsumable::$rightname,
        PluginOpenmedisMedicalConsumableItem::$rightname,
        PluginOpenmedisMedicalConsumableItemType::$rightname,
        PluginOpenmedisUtilization::$rightname,];
      foreach ($rights as $right) {
         ProfileRight::deleteProfileRights([$right]);
         unset($_SESSION["glpiactiveprofile"][$right]);
      }
   }

   protected function deleteRelations() {
      $pluginItemtypes = [
        PluginOpenmedisMedicalDevice::class,
        PluginOpenmedisMedicalDeviceModel::class,
        PluginOpenmedisMedicalDeviceCategory::class,
        PluginOpenmedisDeviceMedicalAccessory::class,
        PluginOpenmedisMedicalAccessoryType::class,
        //PluginOpenmedisMedicalAccessoryCategory::class,
        PluginOpenmedisMedicalConsumable::class,
        PluginOpenmedisMedicalConsumableItem::class,
        PluginOpenmedisMedicalConsumableItemType::class,
        PluginOpenmedisUtilization::class,
        PluginOpenmedisItem_DeviceMedicalAccessory::class,
        PluginOpenmedisMedicalConsumableItem_MedicalDeviceModel::class,
      ];

      // Itemtypes from the core having relations to itemtypes of the plugin
      $itemtypes = [
         Notepad::class,
         DisplayPreference::class,
         DropdownTranslation::class,
         Log::class,
         Bookmark::class,
         SavedSearch::class,
      ];
      foreach ($pluginItemtypes as $pluginItemtype) {
         foreach ($itemtypes as $itemtype) {
            if (class_exists($itemtype)) {
               $item = new $itemtype();
               $item->deleteByCriteria(['itemtype' => $pluginItemtype]);
            }
         }
      }
   }

   protected function createDisplayPreferences() {
      $displayPreference = new DisplayPreference();
      $itemtype = PluginOpenmedisMedicalDevice::class;
      $rank = 1;
      if (version_compare(GLPI_VERSION, '9.4') < 0) {
         $criteria = "`itemtype` = '$itemtype' AND `num` = '1' AND `users_id` = '0'";
      } else {
         $criteria = [
            'itemtype' => $itemtype,
            'num' => '1',
            'users_id' => '0',
         ];
      }
      if (count($displayPreference->find($criteria)) == 0) {
         $displayPreference->add([
            'itemtype'                 => $itemtype,
            'num'                      => '1',
            'rank'                     => $rank,
            User::getForeignKeyField() => '0'
         ]);
      }
      $rank++;
      if (version_compare(GLPI_VERSION, '9.4') < 0) {
         $criteria = "`itemtype` = '$itemtype' AND `num` = '4' AND `users_id` = '0'";
      } else {
         $criteria = [
            'itemtype' => $itemtype,
            'num' => '4',
            'users_id' => '0',
         ];
      }
      if (count($displayPreference->find($criteria)) == 0) {
         $displayPreference->add([
            'itemtype'                 => $itemtype,
            'num'                      => '4',
            'rank'                     => $rank,
            User::getForeignKeyField() => '0'
         ]);
      }

      $itemtype = PluginOpenmedisDeviceMedicalAccessory::class;
      $rank = 1;
      if (version_compare(GLPI_VERSION, '9.4') < 0) {
         $criteria = "`itemtype` = '$itemtype' AND `num` = '3' AND `users_id` = '0'";
      } else {
         $criteria = [
            'itemtype' => $itemtype,
            'num' => '3',
            'users_id' => '0',
         ];
      }
      if (count($displayPreference->find($criteria)) == 0) {
         $displayPreference->add([
            'itemtype'                 => $itemtype,
            'num'                      => '3',
            'rank'                     => $rank,
            User::getForeignKeyField() => '0'
         ]);
      }
      $rank++;
      $criteria = "`itemtype` = '$itemtype' AND `num` = '4' AND `users_id` = '0'";
      if (version_compare(GLPI_VERSION, '9.4') < 0) {
         $criteria = "`itemtype` = '$itemtype' AND `num` = '4' AND `users_id` = '0'";
      } else {
         $criteria = [
            'itemtype' => $itemtype,
            'num' => '4',
            'users_id' => '0',
         ];
      }
      if (count($displayPreference->find($criteria)) == 0) {
         $displayPreference->add([
            'itemtype'                 => $itemtype,
            'num'                      => '4',
            'rank'                     => $rank,
            User::getForeignKeyField() => '0'
         ]);
      }
      $rank++;
      if (version_compare(GLPI_VERSION, '9.4') < 0) {
         $criteria = "`itemtype` = '$itemtype' AND `num` = '5' AND `users_id` = '0'";
      } else {
         $criteria = [
            'itemtype' => $itemtype,
            'num' => '5',
            'users_id' => '0',
         ];
      }
      if (count($displayPreference->find($criteria)) == 0) {
         $displayPreference->add([
            'itemtype'                 => $itemtype,
            'num'                      => '5',
            'rank'                     => $rank,
            User::getForeignKeyField() => '0'
         ]);
      }

      $itemtype = PluginOpenmedisMedicalConsumable::class;
      $rank = 1;
      if (version_compare(GLPI_VERSION, '9.4') < 0) {
         $criteria = "`itemtype` = '$itemtype' AND `num` = '3' AND `users_id` = '0'";
      } else {
         $criteria = [
            'itemtype' => $itemtype,
            'num' => '3',
            'users_id' => '0',
         ];
      }
      if (count($displayPreference->find($criteria)) == 0) {
         $displayPreference->add([
            'itemtype'                 => $itemtype,
            'num'                      => '3',
            'rank'                     => $rank,
            User::getForeignKeyField() => '0'
         ]);
      }
      $rank++;
      if (version_compare(GLPI_VERSION, '9.4') < 0) {
         $criteria = "`itemtype` = '$itemtype' AND `num` = '4' AND `users_id` = '0'";
      } else {
         $criteria = [
            'itemtype' => $itemtype,
            'num' => '4',
            'users_id' => '0',
         ];
      }
      if (count($displayPreference->find($criteria)) == 0) {
         $displayPreference->add([
            'itemtype'                 => $itemtype,
            'num'                      => '4',
            'rank'                     => $rank,
            User::getForeignKeyField() => '0'
         ]);
      }
      $rank++;
      if (version_compare(GLPI_VERSION, '9.4') < 0) {
         $criteria = "`itemtype` = '$itemtype' AND `num` = '5' AND `users_id` = '0'";
      } else {
         $criteria = [
            'itemtype' => $itemtype,
            'num' => '5',
            'users_id' => '0',
         ];
      }
      if (count($displayPreference->find($criteria)) == 0) {
         $displayPreference->add([
            'itemtype'                 => $itemtype,
            'num'                      => '5',
            'rank'                     => $rank,
            User::getForeignKeyField() => '0'
         ]);
      }

    
   }

   protected function deleteDisplayPreferences() {
      global $DB;

      $table = DisplayPreference::getTable();
      $DB->query("DELETE FROM `$table` WHERE `itemtype` LIKE 'PluginOpenmedis%'");
   }

   /**
    * Works only for GLPI 9.3 and upper
    */
   protected function migrateToInnodb() {
      global $DB;

      $result = $DB->listTables('glpi_plugin_openmedis_%', ['engine' => 'MyIsam']);
      if ($result) {
         while ($table = $result->next()) {
            echo "Migrating {$table['TABLE_NAME']}...";
            $DB->queryOrDie("ALTER TABLE {$table['TABLE_NAME']} ENGINE = InnoDB");
            echo " Done.\n";
         }
      }
   }
}
