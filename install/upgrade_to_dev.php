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
 @link      https://github.com/delcroip/glpi_open_medis
 @link      http://www.glpi-project.org/
 @since     2021
 --------------------------------------------------------------------------
 */



if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access this file directly");
}

class PluginOpenmedisUpgradeToDev {

   /**
    * @param Migration $migration
    */
   public function upgrade(Migration $migration) {
    global $DB;
    $err = 0;
    /*if (!$DB->fieldExists("glpi_states","")) {
        if (!$DB->runFile(__DIR__ ."/mysql/upgrade_to_1_2.sql")){
        $this->migration->displayWarning("Error in migration 1.1 to 1.2 : " . $DB->error(), true);
            $err++;
        }
    }*/
    $err += $this->addfieldIfNotExists('glpi_states',
    'is_visible_pluginopenmedismedicaldevice', "tinyint(1) NOT NULL DEFAULT '1'", true);
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
