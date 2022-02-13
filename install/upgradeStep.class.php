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

abstract class PluginOpenmedisUpgradeStep{
  var $migration;
  var $migrationStep;
   /**
    * @param Migration $migration
    */
  public abstract function upgrade(Migration $migration);

  protected function addfieldIfNotExists($table, $field, $fieldOptions, $index = false){
    global $DB;
    if($DB->tableExists($table) && !$DB->fieldExists($table, $field)){
      $sql = "ALTER TABLE ".$table;
      $sql .= " ADD `".$field.'` '.$fieldOptions;
      if($index)$sql .= ", ADD KEY `".$field.'` (`'.$field.'`)';
      if($DB->query($sql)){
        return 0;
      }else{
        $this->migration->displayWarning("Error in in migration $this->migrationStep addfieldIfNotExists:" . $DB->error(), true);
        return 1;
      }
    }else {
      $this->migration->displayWarning("Warning in migration $this->migrationStep addfieldIfNotExists: field ".$field.' exist or table '.$table.'does not exist' , true);
      return 0;
    }
  }

  protected function renameTableIfExists($oldTable, $newTable){
    global $DB;
    if($DB->tableExists($oldTable)){
      $sql = "ALTER TABLE ".$oldTable;
      $sql .= " RENAME ".$newTable;
      if($DB->query($sql)){
        return 0;
      }else{
        $this->migration->displayWarning("Error in migration $this->migrationStep renameTableIfExists:" . $DB->error(), true);
        return 1;
      }
    }else {
      $this->migration->displayWarning("Warning in migration $this->migrationStep renameTableIfExists: table ".$oldTable.'  doesn\'t exist' , true);
      return 0;
    }
  }

  protected function removeTableIfExists($oldTable){
    global $DB;
    if($DB->tableExists($oldTable)){
      $sql = "DROP TABLE ".$oldTable;
      if($DB->query($sql)){
        return 0;
      }else{
        $this->migration->displayWarning("Error in migration $this->migrationStep removeTableIfExists:" . $DB->error(), true);
        return 1;
      }
    }else {
      $this->migration->displayWarning("Warning in migration $this->migrationStep removeTableIfExists: table ".$oldTable.'  don\'t exist' , true);
      return 0;
    }
  }
  protected function renamefieldIfExists($table, $oldfield,$newfield, $fieldOptions, $index = false, $indexName = ''){
    global $DB;
    if($DB->tableExists($table) && $DB->fieldExists($table,$oldfield)){
      $sql = "ALTER TABLE ".$table;
      $sql .= " CHANGE ".$oldfield.' '.$newfield.' '.$fieldOptions ;
      if($index){
        if($indexName == '')$indexName = $newfield;
        $sql .=', DROP KEY '.$oldfield.', ADD KEY `'.$newfield.'` (`'.$indexName.'`)'; 
      }
      if($DB->query($sql)){
        return 0;
      }else{
        $this->migration->displayWarning("Error in migration $this->migrationStep renamefieldIfExists:" . $DB->error(), true);
        return 1;
      }
    }else {
      $this->migration->displayWarning("Warning in migration $this->migrationStep renamefieldIfExists: field ".$oldfield.'  don\'t exist  or table '.$table.'does not exis' , true);
      return 0;
    }
  }

  protected function removefieldIfExists($table, $oldfield){
    global $DB;
    if($DB->tableExists($table) &&  $DB->fieldExists($table,$oldfield)){
      $sql = "ALTER TABLE ".$table;
      $sql .= " DROP COLUMN ".$oldfield ;

      if($DB->query($sql)){
        return 0;
      }else{
        $this->migration->displayWarning("Error in migration $this->migrationStep renamefieldIfExists:" . $DB->error(), true);
        return 1;
      }
    }else {
      $this->migration->displayWarning("Warning in migration $this->migrationStep renamefieldIfExists: field ".$oldfield.'  don\'t exist' , true);
      return 0;
    }
  }
  

  protected function indexExists($table, $index){
    global $DB;
    $sql = "SELECT COUNT(*) AS index_exists FROM information_schema.statistics 
      WHERE TABLE_SCHEMA = DATABASE() and table_name =
      ${table} AND INDEX_NAME = ${index}";
      if ($DB->query($sql) == 1 ) return true;
      else return false;
  }

  protected function  replaceIndexIfExists($table, $oldIndex, $field, $newIndex){
    global $DB;
    if($this->indexExists($table,$oldIndex)){
      $sql = "ALTER TABLE ".$table;
      $sql .=' DROP KEY '.$oldIndex.', ADD KEY `'.$newIndex.'` (`'.$field.'`)'; 
      if($DB->query($sql)){
        return 0;
      }else{
        $this->migration->displayWarning("Error in migration $this->migrationStep replaceIndexIfExists:" . $DB->error(), true);
        return 1;
      }
    }else {
      $this->migration->displayWarning("Warning migration $this->migrationStep replaceIndexIfExists: field ".$oldIndex.'  don\'t exist' , true);
      return 0;
    }
  }

  protected function  replaceUnicityIndexIfExists($table,  $fields){
    global $DB;
    if($this->indexExists($table,'unicity')){
      $sql = "ALTER TABLE ".$table;
      $sql .=' DROP INDEX `unicity` , ADD UNIQUE INDEX `unicity` ('.$fields.')'; 
      if($DB->query($sql)){
        return 0;
      }else{
        $this->migration->displayWarning("Error migration $this->migrationStep in replaceIndexIfExists" . $DB->error(), true);
        return 1;
      }
    }else {
      $this->migration->displayWarning("Warning in migration $this->migrationStep replaceIndexIfExists: field unicity  don\'t exist' ", true);
      return 0;
    }
  }

}
