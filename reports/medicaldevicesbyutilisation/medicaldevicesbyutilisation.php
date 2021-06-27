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

$utilisation = new PluginReportsDropdownCriteria($report, 'utilization','glpi_plugin_openmedis_utilizations' , PluginOpenmedisUtilization::getTypeName());
$utilisation->setSqlField("`glpi_plugin_openmedis_medicaldevices`.`plugin_openmedis_utilizations_id`");

$category = new PluginReportsDropdownCriteria($report, 'category', 'PluginOpenmedisMedicalDeviceCategory' , PluginOpenmedisMedicalDeviceCategory::getTypeName());
$category->setSqlField("`glpi_plugin_openmedis_medicaldevices`.`plugin_openmedis_medicaldevicecategories_id`");


$statemd = new PluginReportsStatusCriteria($report, 'statemd', __('status', 'reports'));
$statemd->setSqlField("`glpi_plugin_openmedis_medicaldevices`.`states_id`");

$location = new PluginReportsLocationCriteria($report, 'location', _n('Location', 'Locations', 2));
$location->setSqlField("`glpi_plugin_openmedis_medicaldevices`.`locations_id`");


$report->displayCriteriasForm();

// Form validate and only one software with license
if ($report->criteriasValidated()) {

   $report->setSubNameAuto();

   $report->setColumns([new PluginReportsColumnLink('util', PluginOpenmedisUtilization::getTypeName(1),
                                   'Utilization', ['sorton' => 'util']),
                         new PluginReportsColumnLink('cat', PluginOpenmedisMedicalDeviceCategory::getTypeName(1),
                                   'Category', ['sorton' => 'cat']),
                         new PluginReportsColumnLink('locat', _n('Location', 'Locations', 1),
                                   'Location', ['sorton' => 'glpi_locations.name']),
                        new PluginReportsColumnLink('md', PluginOpenmedisMedicalDevice::getTypeName(1),
                                   'Medical Device', ['sorton' => 'glpi_computers.name']),
                        new PluginReportsColumn('statemd', _n('Status', 'Statuses', 1))]);

   $query = "SELECT `glpi_plugin_openmedis_utilizations`.`name` AS util,
                    `glpi_locations`.`id` AS locat,
                    `glpi_plugin_openmedis_medicaldevices`.`name` AS md,
                    `glpi_plugin_openmedis_medicaldevicecategories`.`completename` AS cat,
                    `state_cpt`.`name` AS statemd,
                    `glpi_locations`.`name` as location

             FROM `glpi_plugin_openmedis_medicaldevices`
             INNER JOIN `glpi_plugin_openmedis_utilizations`
                   ON (`glpi_plugin_openmedis_medicaldevices`.`plugin_openmedis_utilizations_id` = `glpi_plugin_openmedis_utilizations`.`id`)
             LEFT JOIN `glpi_plugin_openmedis_medicaldevicecategories`
                  ON (`glpi_plugin_openmedis_medicaldevices`.`plugin_openmedis_medicaldevicecategories_id` = `glpi_plugin_openmedis_medicaldevicecategories`.`id`)
             LEFT JOIN `glpi_locations`
                  ON (`glpi_locations`.`id` = `glpi_plugin_openmedis_medicaldevices`.`locations_id`)
             LEFT JOIN `glpi_states` state_cpt
                  ON (`state_cpt`.`id` = `glpi_plugin_openmedis_medicaldevices`.`states_id`) ".
             $dbu->getEntitiesRestrictRequest('WHERE', 'glpi_plugin_openmedis_medicaldevices') .
             $report->addSqlCriteriasRestriction().
             "ORDER BY util ASC, locat ASC";

   $report->setSqlRequest($query);
   $report->execute();
} else {
   Html::footer();
}
