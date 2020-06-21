<?php
/*
 * @version $Id: HEADER 15930 2011-10-30 15:47:55Z tsmr $
 -------------------------------------------------------------------------
 openmedis plugin for GLPI
 Copyright (C) 2014-2016 by the openmedis Development Team.

 https://github.com/InfotelGLPI/openmedis
 -------------------------------------------------------------------------

 LICENSE

 This file is part of openmedis.

 openmedis is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 openmedis is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with openmedis. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

class PluginOpenmedisMenu extends CommonGLPI {

   static $rightname = 'plugin_openmedis';

   static function getMenuName($nb = 1) {
      return _n('Medical Device', 'Medical Devices',
                 $nb, 'openmedis');
   }

   static function getMenuContent() {
      global $CFG_GLPI;

      $menu                     = [];
      //Menu entry in tools
      $menu['title']            = self::getMenuName(2);
      $menu['page']             = PluginOpenmedisMedicalDevice::getSearchURL(false);
      $menu['links']['search']  = PluginOpenmedisMedicalDevice::getSearchURL(false);


   
      if (PluginOpenmedisMedicalDevice::canCreate()) {
         $menu['options']['openmedis']['links']['add'] = '/plugins/openmedis/front/setup.templates.php?add=1';
      }

      if (PluginOpenmedisMedicalDeviceModel::canView()) {
         $menu['options']['openmedis']['links']['template'] = '/plugins/openmedis/front/setup.templates.php?add=0';
      }

      return $menu;
   }

   static function removeRightsFromSession() {
      if (isset($_SESSION['glpimenu']['assets']['types']['PluginOpenmedisMenu'])) {
         unset($_SESSION['glpimenu']['assets']['types']['PluginOpenmedisMenu']);
      }
      if (isset($_SESSION['glpimenu']['assets']['content']['PluginOpenmedisMenu'])) {
         unset($_SESSION['glpimenu']['assets']['content']['PluginOpenmedisMenu']);
      }
   }
}
