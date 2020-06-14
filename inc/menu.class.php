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

class PluginOpenMedisMenu extends CommonGLPI {

   static $rightname = 'plugin_open_medis';

   static function getMenuName($nb = 1) {
      return _n('Medical Device', 'Medical Devices',
                 $nb, 'open_medis');
   }

   static function getMenuContent() {
      global $CFG_GLPI;

      $menu                     = [];
      //Menu entry in tools
      $menu['title']            = self::getMenuName(2);
      $menu['page']             = PluginOpenMedisMedicalDevice::getSearchURL(false);
      $menu['links']['search']  = PluginOpenMedisMedicalDevice::getSearchURL(false);

      $menu['options']['open_medis']['links']['search'] = PluginOpenMedisMedicalDevice::getSearchURL(false);
      $menu['options']['open_medis']['links']['config'] = PluginOpenMedisConfig::getFormURL(false);

      $menu['options']['config']['title'] = __('Setup');
      $menu['options']['config']['page']  = PluginOpenMedisConfig::getFormURL(false);

      $menu['options']['specifications']['title']           = __('Specifications', 'open_medis');
      $menu['options']['specifications']['page']            = PluginOpenMedisItemSpecification::getSearchURL(false);
      $menu['options']['specifications']['links']['search'] = PluginOpenMedisItemSpecification::getSearchURL(false);

      if (PluginOpenMedisMedicalDevice::canCreate()) {
         $menu['options']['open_medis']['links']['add'] = '/plugins/open_medis/front/setup.templates.php?add=1';
      }

      if (PluginOpenMedisMedicalDeviceModel::canView()) {
         $menu['options']['open_medis']['links']['template'] = '/plugins/open_medis/front/setup.templates.php?add=0';
         $menu['options']['open_medis']['links']["<img  src='".
         $CFG_GLPI["root_doc"]."/pics/menu_showall.png' title=\"".__('Equipments models specifications', 'open_medis').
         "\" alt=\"".__('Equipments models specifications', 'open_medis')."\">"] = PluginOpenMedisItemSpecification::getSearchURL(false);
      }

      return $menu;
   }

   static function removeRightsFromSession() {
      if (isset($_SESSION['glpimenu']['assets']['types']['PluginOpenMedisMenu'])) {
         unset($_SESSION['glpimenu']['assets']['types']['PluginOpenMedisMenu']);
      }
      if (isset($_SESSION['glpimenu']['assets']['content']['pluginopen_medismenu'])) {
         unset($_SESSION['glpimenu']['assets']['content']['pluginopen_medismenu']);
      }
   }
}
