<?php
/**
 * ---------------------------------------------------------------------
 * GLPI - Gestionnaire Libre de Parc Informatique
 * Copyright (C) 2015-2018 Teclib' and contributors.
 *
 * http://glpi-project.org
 *
 * based on GLPI - Gestionnaire Libre de Parc Informatique
 * Copyright (C) 2003-2014 by the INDEPNET Development Team.
 *
 * ---------------------------------------------------------------------
 *
 * LICENSE
 *
 * This file is part of GLPI.
 *
 * GLPI is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * GLPI is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with GLPI. If not, see <http://www.gnu.org/licenses/>.
 * ---------------------------------------------------------------------
 */

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access this file directly");
}

/// Class PluginOpenmedisMedicalDeviceCategory
class PluginOpenmedisMedicalDeviceCategory extends CommonTreeDropdown {

   public $can_be_translated = true;
  // public $must_be_replace              = true;
   public $dohistory                    = true;

   static $rightname                    = 'plugin_openmedis_type';


   static function getTypeName($nb = 0) {
      return _n('Medical device category (e.g. UMDS,GMDN)', 'Medical device categories (e.g. UMDS,GMDN)', $nb);
   }


   function cleanDBonPurge() {
      Rule::cleanForItemAction($this);
   }

   function getAdditionalFields() {

      $tab = [['name'      => 'code',
                         'label'     => __('code of the category'),
                         'type'      => 'text',
                         'list'      => true],
                  ];

      if (!Session::haveRightsOr('plugin_openmedis_type', [CREATE, UPDATE, DELETE])) {

         unset($tab[7]);
      }
      return $tab;

   }
   function rawSearchOptions() {
      $tab                       = parent::rawSearchOptions();

      $tab[] = [
         'id'                 => '80',
         'table'              => 'code',
         'field'              => 'name',
         'name'               => __('Code'),
         'datatype'           => 'text',
         'right'              => 'plugin_openmedis'
      ];
   }



}