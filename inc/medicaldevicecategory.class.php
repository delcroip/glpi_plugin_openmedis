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
class PluginOpenmedisMedicalDeviceCategory extends CommonTreeDropdown {

   public $can_be_translated = true;
  // public $must_be_replace              = true;
   public $dohistory                    = true;

   static $rightname                    = 'plugin_openmedis_medicaldevicecategory';


   static function getTypeName($nb = 0) {
      return _n('Medical device category (e.g. UMDS,GMDN)', 'Medical device categories (e.g. UMDS,GMDN)', $nb);
   }


   function getAdditionalFields() {

      return [
         [
            'name'  => $this->getForeignKeyField(),
            'label' => __('As child of'),
            'type'  => 'parent',
            'list'  => false
	 ]];
   }



}
