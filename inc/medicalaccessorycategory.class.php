<?php
/**
 * ---------------------------------------------------------------------
 * GLPI - Gestionnaire Libre de Parc Informatique
 * Copyright (C) 2015-2018 Teclib' and contributors.
 *
 * http://glpi-project.org
 *
 * based on GLPI - Gestionnaire Libre de Parc Informatique
 * Copyright © 2021 by Patrick delcroix <patrick@pmpd.eu>
 * This file is part of openmedis Plugin for GLPI.
 * ---------------------------------------------------------------------
 *
 * LICENSE
 *
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
class PluginOpenmedisMedicalAccessoryCategory extends CommonTreeDropdown {

   public $can_be_translated = true;
  // public $must_be_replace              = true;
   public $dohistory                    = true;

   static $rightname                    = 'plugin_openmedis_medicalaccessorycategory';

   static function getTable($classname = null){
      return 'glpi_plugin_openmedis_medicalaccessorycategories';
   }

   static function getTypeName($nb = 0) {
      return _n('Medical device category (e.g. UMDS,GMDN)', 'Medical device categories (e.g. UMDS,GMDN)', $nb);
   }


   function cleanDBonPurge() {
      Rule::cleanForItemAction($this);
   }

   function getAdditionalFields() {

      $tab = [[
         'name'  => $this->getForeignKeyField(),
         'label' => __('As child of'),
         'type'  => 'parent',
         'list'  => false
      ],[
         'name'      => 'code',
         'label'     => __('Code'),
         'type'      => 'text',
         'list'      => true
      ],[
         'name'      => 'picture',
         'label'     => __('Picture'),
         'type'      => 'picture'
      ]];

      if (!Session::haveRightsOr('plugin_openmedis_medicalaccessorycategory', [CREATE, UPDATE, DELETE])) {

         unset($tab[7]);
      }
      return $tab;

   }

   
   static public function rawSearchOptionsToAdd() {
      $tab = [];

      $tab[] = [
         'id'                 => '3',
         'table'              => $this->getTable(),
         'field'              => 'completename',
         'name'               => __('Completename'),
         'datatype'           => 'dropdown'
      ];
      $tab[] = [
         'id'                 => '93',
         'table'              => $this->getTable(),
         'field'              => 'name',
         'name'               => __('Name'),
         'datatype'           => 'dropdown'
      ];
      $tab[] = [
         'id'                 => '94',
         'table'              => $this->getTable(),
         'field'              => 'code',
         'name'               => __('Code'),
         'datatype'           => 'dropdown'
      ];
      return $tab;
   }  
   function rawSearchOptions() {
      $tab                       = parent::rawSearchOptions();


      $tab[] = [
         'id'                 => '3',
         'table'              => $this->getTable(),
         'field'              => 'completename',
         'name'               => __('Completename'),
         'datatype'           => 'dropdown'
      ];
      $tab[] = [
         'id'                 => '93',
         'table'              => $this->getTable(),
         'field'              => 'name',
         'name'               => __('Name'),
         'datatype'           => 'dropdown'
      ];
      $tab[] = [
         'id'                 => '94',
         'table'              => $this->getTable(),
         'field'              => 'code',
         'name'               => __('Code'),
         'datatype'           => 'dropdown'
      ];

      return $tab;
   }


   static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0) {

      if ($item->getType() == __CLASS__) {
         switch ($tabnum) {
            case 1 :
               $item->showChildren();
               break;
            case 2 :
               $item->showItems();
               break;
         }
      }
      return true;
   }

}
