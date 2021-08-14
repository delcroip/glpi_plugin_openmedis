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

$USEDBREPLICATE         = 1;
$DBCONNECTION_REQUIRED  = 0;

include ("../../../../inc/includes.php");

$dbu = new DbUtils();

//TRANS: The name of the report = Applications by locations and versions
$report = new PluginReportsAutoReport(__('medicaldevicesbyutilisation_report_title', 'reports'));

//$softwarecategories = new PluginReportsSoftwareCategoriesCriteria($report, 'softwarecategories',
//                                                                  __('Software category'));
//$softwarecategories->setSqlField("`glpi_softwarecategories`.`id`");

//$software = new PluginReportsSoftwareCriteria($report, 'software', __('Applications', 'reports'));
//$software->setSqlField("`glpi_softwares`.`id`");

$fields = [
     '`glpi_locations`.`name`' => 'location',  
     '`glpi_plugin_openmedis_medicaldevicecategories`.`name`' => 'category', 
     '`state_cpt`.`name`' => 'statemd',
     '`glpi_plugin_openmedis_medicaldevices`.`plugin_openmedis_utilizations_id`' => 'utilization'
];

$utilisation = new PluginReportsDropdownCriteria($report, 'utilization','glpi_plugin_openmedis_utilizations' , PluginOpenmedisUtilization::getTypeName());
$utilisation->setSqlField("`glpi_plugin_openmedis_medicaldevices`.`plugin_openmedis_utilizations_id`");
if (class_exists('PluginOpenmedisToggleCriteria')) { 
     $report->startColumn();
     $report->endColumn();
     $report->startColumn();
     $report->endColumn();
}
$category = new PluginReportsDropdownCriteria($report, 'category', 'PluginOpenmedisMedicalDeviceCategory' , PluginOpenmedisMedicalDeviceCategory::getTypeName());
$category->setSqlField("`glpi_plugin_openmedis_medicaldevices`.`plugin_openmedis_medicaldevicecategories_id`");
if (class_exists('PluginOpenmedisToggleCriteria')) {
     $category_group = new PluginOpenmedisToggleCriteria($report, 'category_group', 'Group categories');
     $category_group->setSqlField("");
}
$statemd = new PluginReportsStatusCriteria($report, 'statemd', __('Status', 'reports'));
$statemd->setSqlField("`glpi_plugin_openmedis_medicaldevices`.`states_id`");
if (class_exists('PluginOpenmedisToggleCriteria')) {
     $category_group = new PluginOpenmedisToggleCriteria($report, 'statemd_group', 'Group states');
     $category_group->setSqlField("");
}

$location = new PluginReportsLocationCriteria($report, 'location', _n('Location', 'Locations', 2));
$location->setSqlField("`glpi_plugin_openmedis_medicaldevices`.`locations_id`");
if (class_exists('PluginOpenmedisToggleCriteria')) {
     $category_group = new PluginOpenmedisToggleCriteria($report, 'location_group', 'Group location');
     $category_group->setSqlField("");
}

$report->displayCriteriasForm();


if ($report->criteriasValidated()) {

     $report->setSubNameAuto();

     $report->setColumns([new PluginReportsColumnLink('utilization', PluginOpenmedisUtilization::getTypeName(1),
                                   'Utilization', ['sorton' => 'utilization']),
                         new PluginReportsColumnLink('category', PluginOpenmedisMedicalDeviceCategory::getTypeName(1),
                                   'Category', ['sorton' => 'category']),
                         new PluginReportsColumnLink('location', __('Location'),
                                   'Location', ['sorton' => 'glpi_locations.name']),
                        new PluginReportsColumn('md', PluginOpenmedisMedicalDevice::getTypeName(1),1),
                        new PluginReportsColumn('statemd', __('Status'))]);

     $query = "SELECT COUNT(`glpi_plugin_openmedis_medicaldevices`.`id`) AS md, `glpi_plugin_openmedis_utilizations`.`name` as utilization,".
             selectUnfiltered($report, $fields).
            " FROM `glpi_plugin_openmedis_medicaldevices`
             LEFT JOIN `glpi_plugin_openmedis_utilizations`
                   ON (`glpi_plugin_openmedis_medicaldevices`.`plugin_openmedis_utilizations_id` = `glpi_plugin_openmedis_utilizations`.`id`)
             LEFT JOIN `glpi_plugin_openmedis_medicaldevicecategories`
                  ON (`glpi_plugin_openmedis_medicaldevices`.`plugin_openmedis_medicaldevicecategories_id` = `glpi_plugin_openmedis_medicaldevicecategories`.`id`)
             LEFT JOIN `glpi_locations`
                  ON (`glpi_locations`.`id` = `glpi_plugin_openmedis_medicaldevices`.`locations_id`)
             LEFT JOIN `glpi_states` state_cpt
                  ON (`state_cpt`.`id` = `glpi_plugin_openmedis_medicaldevices`.`states_id`) ".
             $dbu->getEntitiesRestrictRequest('WHERE', 'glpi_plugin_openmedis_medicaldevices') .
             $report->addSqlCriteriasRestriction().
             groupUnfiltered($fields);
     $report->setGroupBy('utzationilization');
     $report->setSqlRequest($query);
     $report->execute();
} else {
   Html::footer();
}

function groupUnfiltered($fields) {
     $sql = 'GROUP BY  ';
     $first = true;
     foreach ($fields as $key => $field) {
          if (!(isset($_POST[$field.'_group']) && $_POST[$field.'_group'] == 'on')){
               if ($first) {
                    $first = false;           
               } else {
                    $sql .= ', ';
               }
               $sql .= $key.' ';
          }
     }
     return $sql;
     
}
/** Function that fetch the details only if no specific value selected FIXME ad checkbog "group" next to critertias
 * 
 */

function selectUnfiltered($report, $fields) {
     $sql = '';
     foreach ($fields as $key =>  $field) {
          if ($field != 'utilization' )
          {
		if ((isset($_POST[$field.'_group']) && $_POST[$field.'_group'] == 'on')  ){
               		if($sql != '')$sql .= ',';
               		$sql .= "'' AS ".$field;              
          	}else {
               		if($sql != '')$sql .= ',';
               		$sql .= $key.' AS '.$field;
          	}
	 }
     }
     return $sql;
}
