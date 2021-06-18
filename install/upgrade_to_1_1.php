<?php
/**
 * LICENSE
 *
 * Copyright © 2016-2018 Teclib'
 * Copyright © 2010-2018 by the FusionInventory Development Team.
 * Copyright © 2010-2018 by patrick Delcroix <patrick@pmpd.eu>
 *
 * This file is part ofopenMedis Plugin for GLPI.
 *
 *openMedis Plugin for GLPI is a subproject ofopenMedis.openMedis is a mobile
 * device management software.
 *
 *openMedis Plugin for GLPI is free software: you can redistribute it and/or
 * modify it under the terms of the GNU Affero General Public License as published
 * by the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *openMedis Plugin for GLPI is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 * You should have received a copy of the GNU Affero General Public License
 * along with openMedis Plugin for GLPI. If not, see http://www.gnu.org/licenses/.
 * ------------------------------------------------------------------------------
 * @copyright Copyright © 2018 Teclib
 * @license   https://www.gnu.org/licenses/agpl.txt AGPLv3+
 * @link      https://github.com/delcroip/glpi_open_medis

 * ------------------------------------------------------------------------------
 */



if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access this file directly");
}

class PluginOpenmedisUpgradeTo1_1 {
   var $migration;
   /**
    * @param Migration $migration
    */
   public function upgrade(Migration $migration) {
	   global $DB;
    $this->migration = $migration;
    $err = 0;

    if (!$DB->tableExists("glpi_plugin_openmedis_medicalconsomables")) {
        if (!$DB->runFile(__DIR__ ."/mysql/upgrade_to_1_1.sql")){
            $this->migration->displayWarning("Error in migration 1.0 to 1.1 : " . $DB->error(), true);
            $err++;
        }
    }
    $err += $this->addfieldIfNotExists('glpi_states',
      'is_visible_pluginopenmedismedicaldevice',"tinyint(1) NOT NULL DEFAULT '1'", true);
    $err += $this->renameTableifExists('glpi_plugin_openmedis_item_devicemedicalaccessories', 
      'glpi_plugin_openmedis_item_medicalaccessories');
    $err += $this->renameTableIfExists('glpi_plugin_openmedis_devicemedicalaccessorie', 
      'glpi_plugin_openmedis_devicemedicalaccessories');
    $err += $this->addfieldIfNotExists('glpi_plugin_openmedis_medicaldevices',
      'init_usages_counter','int(11) NOT NULL DEFAULT 0');
    $err += $this->addfieldIfNotExists('glpi_plugin_openmedis_medicaldevices',
      'last_usages_counter','int(11) NOT NULL DEFAULT 0');
    $err += $this->renamefieldIfExists('glpi_plugin_openmedis_items_medicalaccessories',
     'plugin_openmedis_devicemedicalaccessories_id','plugin_openmedis_devicemedicalaccessories_id',
     'int(11) NOT NULL DEFAULT 0');
    $err += $this->replaceIndexIfExists('glpi_plugin_openmedis_devicemedicalaccessories_items',
      'plugin_openmedis_medicaldevice_id', 'plugin_openmedis_medicaldevices_id', 'items_id');
    $err += $this->migration->displayWarning("table to be created by the migration already existing : " . $DB->error(), true);
    if ($err > 0){
      return false;
    }
    else{
      return true;
    }
  }

  private function addfieldIfNotExists($table, $field, $fieldOptions, $index = false){
    global $DB;
    if(!$DB->fieldExists($table,$field)){
      $sql = "ALTER TABLE ".$table;
      $sql .= " ADD `".$field.'` '.$fieldOptions;
      if($index)$sql .= ", ADD KEY `".$field.'` (`'.$field.'`)';
      if($DB->query($sql)){
        return 0;
      }else{
        $this->migration->displayWarning("Error in migration 1.0 to 1.1 : addfieldIfNotExists" . $DB->error(), true);
        return 1;
      }
    }else {
      $this->migration->displayWarning("Error in migration 1.0 to 1.1 : field ".$field.' exist' , true);
      return 1;
    }
  }

  private function renameTableIfExists($oldTable, $newTable){
    global $DB;
    if($DB->tableExists($oldTable)){
      $sql = "ALTER TABLE ".$oldTable;
      $sql .= " RENAME ".$newTable;
      if($DB->query($sql)){
        return 0;
      }else{
        $this->migration->displayWarning("Error in migration 1.0 to 1.1 : renameTableIfExists" . $DB->error(), true);
        return 1;
      }
    }else {
      $this->migration->displayWarning("Error in migration 1.0 to 1.1 : table ".$oldTable.'  don\'t exist' , true);
      return 1;
    }
  }

  private function renamefieldIfExists($table, $oldfield,$newfield, $fieldOptions, $index = false, $indexName = ''){
    global $DB;
    if($DB->fieldExists($table,$oldfield)){
      $sql = "ALTER TABLE ".$table;
      $sql .= " change ".$oldfield.' '.$newfield.' '.$fieldOptions ;
      if($index){
        if($indexName == '')$indexName = $newfield;
        $sql .=' DROP KEY '.$oldfield.', ADD KEY `'.$newfield.'` (`'.$indexName.'`)'; 
      }
      if($DB->query($sql)){
        return 0;
      }else{
        $this->migration->displayWarning("Error in migration 1.0 to 1.1 : renamefieldIfExists" . $DB->error(), true);
        return 1;
      }
    }else {
      $this->migration->displayWarning("Error in migration 1.0 to 1.1 : field ".$oldfield.'  don\'t exist' , true);
      return 1;
    }
  }

  private function indexExists($table, $index){
    global $DB;
    $sql = "SELECT COUNT(*) AS index_exists FROM information_schema.statistics 
      WHERE TABLE_SCHEMA = DATABASE() and table_name =
      ${table} AND INDEX_NAME = ${index}";
      if ($DB->query($sql) == 1 ) return true;
      else return false;
  }

  private function  replaceIndexIfExists($table, $oldIndex, $field, $newIndex){
    global $DB;
    if($this->indexExists($table,$oldIndex)){
      $sql = "ALTER TABLE ".$table;
      $sql .=' DROP KEY '.$oldIndex.', ADD KEY `'.$newIndex.'` (`'.$field.'`)'; 
      if($DB->query($sql)){
        return 0;
      }else{
        $this->migration->displayWarning("Error in migration 1.0 to 1.1 : replaceIndexIfExists" . $DB->error(), true);
        return 1;
      }
    }else {
      $this->migration->displayWarning("Error in migration 1.0 to 1.1 : field ".$oldIndex.'  don\'t exist' , true);
      return 1;
    }
  }

}
