<?php
/**
 * @version $Id: tooglecriteria.class.php 1 2021-08-08 21:00:00Z delcroip $
 -------------------------------------------------------------------------
  LICENSE

 This file is part of Reports plugin for GLPI.

 Reports is free software: you can redistribute it and/or modify
 it under the terms of the GNU Affero General Public License as published by
 the Free Software Foundation, either version 3 of the License, or
 (at your option) any later version.

 Reports is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 GNU Affero General Public License for more details.

 You should have received a copy of the GNU Affero General Public License
 along with Reports. If not, see <http://www.gnu.org/licenses/>.

 @package   reports
 @authors   Patrick Delcroix
 @copyright Copyright (c) 2009-2021 Reports plugin team
 @license   AGPL License 3.0 or (at your option) any later version
            http://www.gnu.org/licenses/agpl-3.0-standalone.html
 @link      https://forge.glpi-project.org/projects/reports
 @link      http://www.glpi-project.org/
 @since     2009
 --------------------------------------------------------------------------
 */

// ----------------------------------------------------------------------
// Original Author of file:
// Purpose of file:
// ----------------------------------------------------------------------

/**
 * Priority selection criteria
**/
class PluginReportsToggleCriteria extends PluginReportsAutoCriteria {


   private $defaultValue;



   /**
    * @param $report
    * @param $name      (default 'toggle')
    * @param $label     (default '')
    * @param $defaultValue value by default
    * @param $sqlField field to select
   **/
   function __construct($report, $name='toggle', $label = '', $defaultValue = 0, $sql_field = '') {
      parent::__construct($report, $name, $sql_field, ($label ? $label : __('Toggle')));
      $this->defaultValue = $defaultValue;
      
      
   }


   public function setDefaultValues() {
      $this->addParameter($this->getName(), 1);

   }


   public function displayCriteria() {

      $this->getReport()->startColumn();
      $buttons =  null;

      if ($this->getParameterValue() == 0) {
         $buttons ="<input  type='checkbox' name = '".$this->GetName()."'>";
      } else {
         $buttons ="<input  type='checkbox' name = '".$this->GetName()."' checked >";
      }
      
      echo $this->getCriteriaLabel().'&nbsp;';
      $this->getReport()->endColumn();
      $this->getReport()->startColumn();
      echo $buttons.'&nbsp;';
      $this->getReport()->endColumn();
   }


   function getSubName() {

      return " ";
   }


   /**
    * @param $priority
   **/
   function setDefaultPriorityValue($priority) {
      $this->addParameter($this->getName(), $priority);
   }


   /**
    * @see plugins/reports/inc/PluginReportsAutoCriteria::getSqlCriteriasRestriction()
   */
   public function getSqlCriteriasRestriction($link='AND') {

      // show restriction only if sqlField is setup
      if ($this->getSqlField() != '') {
         return $link . " " . $this->getSqlField() . "= '" . $this->getParameterValue() . "'";
      }


   }

      /**
    * Get all parameters associated with the criteria
   **/
  function getParameterValue() {
      $value = parent::getParameterValue();
      if ($value == 'on') {
         return 1;
      } else if ( isset($_POST['find'])){
         return 0;
      } else {
         return $this->defaultValue;
      }
}

}
