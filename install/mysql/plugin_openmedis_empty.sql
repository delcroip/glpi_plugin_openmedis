DROP TABLE IF EXISTS `glpi_plugin_openmedis_devicemedicalaccessories`;
CREATE TABLE  `glpi_plugin_openmedis_devicemedicalaccessories` (
  `id` int(11) NOT NULL AUTO_INCREMENT, -- AccessoryID
  `designation` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL, -- former designation
  `comment` text COLLATE utf8_unicode_ci, 
  `manufacturers_id` int(11) NOT NULL DEFAULT '0',-- ManufacturerID
  `part_number` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
 -- `capacity` int(11) DEFAULT NULL,
  `plugin_openmedis_medicalaccessorytypes_id` int(11) NOT NULL DEFAULT '0',
  `entities_id` int(11) NOT NULL DEFAULT '0',
  `is_recursive` tinyint(1) NOT NULL DEFAULT '0',
  `plugin_openmedis_medicaldevicecategories_id` int(11) DEFAULT NULL,
  `date_mod` datetime DEFAULT NULL,
  `date_creation` datetime DEFAULT NULL,
  `picture` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `designation` (`designation`),
  KEY `manufacturers_id` (`manufacturers_id`),
  KEY `entities_id` (`entities_id`),
  KEY `is_recursive` (`is_recursive`),
  KEY `date_mod` (`date_mod`),
  KEY `date_creation` (`date_creation`),
  KEY `plugin_openmedis_medicaldevicecategories_id` (`plugin_openmedis_medicaldevicecategories_id`),
  KEY `plugin_openmedis_medicalaccessorytypes_id` (`plugin_openmedis_medicalaccessorytypes_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `glpi_plugin_openmedis_medicaldevicecategories`;
CREATE TABLE `glpi_plugin_openmedis_medicaldevicecategories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(255) DEFAULT NULL,
  `label` varchar(255)  DEFAULT '', 
  `name` text AS (CONCAT(code,' - ',label)), 
  `completename` text COLLATE utf8_unicode_ci,
  `comment` text DEFAULT NULL, 
  `picture` text DEFAULT NULL, 
  `plugin_openmedis_medicaldevicecategories_id` int(11) NOT NULL DEFAULT '0', 
  `level` int(11) NOT NULL DEFAULT '0',
  `ancestors_cache` longtext COLLATE utf8_unicode_ci,
  `sons_cache` longtext COLLATE utf8_unicode_ci,
  `is_recursive` tinyint NOT NULL DEFAULT '0',
  `entities_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE INDEX `unicity` (`plugin_openmedis_medicaldevicecategories_id`, `code`),
   KEY `entities_id` (`entities_id`),
KEY `plugin_openmedis_medicaldevicecategories_id` (`plugin_openmedis_medicaldevicecategories_id`),
KEY `is_recursive` (`is_recursive`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `glpi_plugin_openmedis_medicaldevicemodels`;
CREATE TABLE `glpi_plugin_openmedis_medicaldevicemodels` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `comment` text COLLATE utf8_unicode_ci,
  `product_number` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `name` (`name`),
  KEY `product_number` (`product_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `glpi_plugin_openmedis_utilizations`;
CREATE TABLE `glpi_plugin_openmedis_utilizations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `comment` text COLLATE utf8_unicode_ci,
  PRIMARY KEY (`id`),
  KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `glpi_plugin_openmedis_medicalaccessorytypes` ;
CREATE TABLE `glpi_plugin_openmedis_medicalaccessorytypes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `comment` text COLLATE utf8_unicode_ci,
  PRIMARY KEY (`id`),
  KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `glpi_plugin_openmedis_items_devicemedicalaccessories` ;
CREATE TABLE  `glpi_plugin_openmedis_items_devicemedicalaccessories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `items_id` int(11) NOT NULL DEFAULT '0',
  `itemtype` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `plugin_openmedis_devicemedicalaccessories_id` int(11) NOT NULL DEFAULT '0',
  `manufacturing_date` datetime DEFAULT NULL,
  `is_deleted` tinyint(1) NOT NULL DEFAULT '0',
  `is_dynamic` tinyint(1) NOT NULL DEFAULT '0',
  `entities_id` int(11) NOT NULL DEFAULT '0',
  `is_recursive` tinyint(1) NOT NULL DEFAULT '0',
  `serial` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `otherserial` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `locations_id` int(11) NOT NULL DEFAULT '0',
  `states_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `plugin_openmedis_medicaldevices_id` (`items_id`),
  KEY `plugin_openmedis_devicemedicalaccessories_id` (`plugin_openmedis_devicemedicalaccessories_id`),
  KEY `is_deleted` (`is_deleted`),
  KEY `is_dynamic` (`is_dynamic`),
  KEY `entities_id` (`entities_id`),
  KEY `is_recursive` (`is_recursive`),
  KEY `serial` (`serial`),
  KEY `item` (`itemtype`,`items_id`),
  KEY `otherserial` (`otherserial`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `glpi_plugin_openmedis_medicaldevices` ;
CREATE TABLE  `glpi_plugin_openmedis_medicaldevices` (
  `id` int(11) NOT NULL AUTO_INCREMENT, -- assetid
  `entities_id` int(11) NOT NULL DEFAULT '0',
  `name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL, -- AssetFullName
  `date_mod` datetime DEFAULT NULL,
  `contact` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `contact_num` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `users_id_tech` int(11) NOT NULL DEFAULT '0',
  `groups_id_tech` int(11) NOT NULL DEFAULT '0',
  `comment` text COLLATE utf8_unicode_ci,
  `serial` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `otherserial` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `locations_id` int(11) NOT NULL DEFAULT '0',
  `plugin_openmedis_medicaldevicemodels_id` int(11) NOT NULL DEFAULT '0',
  `plugin_openmedis_medicaldevicecategories_parent_id` int(11) NOT NULL DEFAULT '0',
  `plugin_openmedis_medicaldevicecategories_id` int(11) NOT NULL DEFAULT '0',
  `plugin_openmedis_utilizations_id` int(11) NOT NULL DEFAULT '0',
  `brand` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `init_usages_counter` int(11) NOT NULL DEFAULT '0',
  `last_usages_counter` int(11) NOT NULL DEFAULT '0',
  `manufacturers_id` int(11) NOT NULL DEFAULT '0',
  `is_global` tinyint(1) NOT NULL DEFAULT '0',
  `is_deleted` tinyint(1) NOT NULL DEFAULT '0',
  `is_template` tinyint(1) NOT NULL DEFAULT '0',
  `template_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `users_id` int(11) NOT NULL DEFAULT '0',
  `groups_id` int(11) NOT NULL DEFAULT '0',
  `states_id` int(11) NOT NULL DEFAULT '0',
  `ticket_tco` decimal(20,4) DEFAULT '0.0000',
  `is_dynamic` tinyint(1) NOT NULL DEFAULT '0',
  `date_creation` datetime DEFAULT NULL,
  `is_recursive` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `name` (`name`),
  KEY `is_template` (`is_template`),
  KEY `is_global` (`is_global`),
  KEY `entities_id` (`entities_id`),
  KEY `manufacturers_id` (`manufacturers_id`),
  KEY `groups_id` (`groups_id`),
  KEY `users_id` (`users_id`),
  KEY `locations_id` (`locations_id`),
  KEY `plugin_openmedis_medicaldevicemodels_id` (`plugin_openmedis_medicaldevicemodels_id`),
  KEY `plugin_openmedis_utilization_id` (`plugin_openmedis_utilizations_id`),
  KEY `states_id` (`states_id`),
  KEY `users_id_tech` (`users_id_tech`),
  KEY `plugin_openmedis_medicaldevicecategories_id` (`plugin_openmedis_medicaldevicecategories_id`),
  KEY `is_deleted` (`is_deleted`),
  KEY `date_mod` (`date_mod`),
  KEY `groups_id_tech` (`groups_id_tech`),
  KEY `is_dynamic` (`is_dynamic`),
  KEY `serial` (`serial`),
  KEY `otherserial` (`otherserial`),
  KEY `date_creation` (`date_creation`),
  KEY `is_recursive` (`is_recursive`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

### Dump table glpi_plugin_openmedis_medicalconsumableitems_medicaldevicemodels

DROP TABLE IF EXISTS `glpi_plugin_openmedis_medicalconsumableitems_medicaldevicemodels`;
CREATE TABLE `glpi_plugin_openmedis_medicalconsumableitems_medicaldevicemodels` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `plugin_openmedis_medicalconsumableitems_id` int(11) NOT NULL DEFAULT '0',
  `plugin_openmedis_medicaldevicemodels_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `unicity` (`plugin_openmedis_medicaldevicemodels_id`,`plugin_openmedis_medicalconsumableitems_id`),
  KEY `plugin_openmedis_medicalconsumableitems_id` (`plugin_openmedis_medicalconsumableitems_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


### Dump table glpi_plugin_openmedis_medicalconsumableitemtypes

DROP TABLE IF EXISTS `glpi_plugin_openmedis_medicalconsumableitemtypes`;
CREATE TABLE `glpi_plugin_openmedis_medicalconsumableitemtypes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `comment` text COLLATE utf8_unicode_ci,
  `date_mod` timestamp NULL DEFAULT NULL,
  `date_creation` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `name` (`name`),
  KEY `date_mod` (`date_mod`),
  KEY `date_creation` (`date_creation`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


### Dump table glpi_plugin_openmedis_medicalconsumables

DROP TABLE IF EXISTS `glpi_plugin_openmedis_medicalconsumables`;
CREATE TABLE `glpi_plugin_openmedis_medicalconsumables` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `entities_id` int(11) NOT NULL DEFAULT '0',
  `plugin_openmedis_medicalconsumableitems_id` int(11) NOT NULL DEFAULT '0',
  `plugin_openmedis_medicaldevices_id` int(11) NOT NULL DEFAULT '0',
  `date_in` date DEFAULT NULL,
  `date_use` date DEFAULT NULL,
  `date_out` date DEFAULT NULL,
  `usages` int(11) NOT NULL DEFAULT '0',
  `date_mod` timestamp NULL DEFAULT NULL,
  `date_creation` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `plugin_openmedis_medicalconsumableitems_id` (`plugin_openmedis_medicalconsumableitems_id`),
  KEY `plugin_openmedis_medicaldevices_id` (`plugin_openmedis_medicaldevices_id`),
  KEY `entities_id` (`entities_id`),
  KEY `date_mod` (`date_mod`),
  KEY `date_creation` (`date_creation`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


### Dump table glpi_plugin_openmedis_medicalconsumableitems

DROP TABLE IF EXISTS `glpi_plugin_openmedis_medicalconsumableitems`;
CREATE TABLE `glpi_plugin_openmedis_medicalconsumableitems` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `entities_id` int(11) NOT NULL DEFAULT '0',
  `is_recursive` tinyint(1) NOT NULL DEFAULT '0',
  `name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ref` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `locations_id` int(11) NOT NULL DEFAULT '0',
  `plugin_openmedis_medicalconsumableitemtypes_id` int(11) NOT NULL DEFAULT '0',
  `manufacturers_id` int(11) NOT NULL DEFAULT '0',
  `users_id_tech` int(11) NOT NULL DEFAULT '0',
  `groups_id_tech` int(11) NOT NULL DEFAULT '0',
  `is_deleted` tinyint(1) NOT NULL DEFAULT '0',
  `comment` text COLLATE utf8_unicode_ci,
  `alarm_threshold` int(11) NOT NULL DEFAULT '10',
  `date_mod` timestamp NULL DEFAULT NULL,
  `date_creation` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `name` (`name`),
  KEY `entities_id` (`entities_id`),
  KEY `manufacturers_id` (`manufacturers_id`),
  KEY `locations_id` (`locations_id`),
  KEY `users_id_tech` (`users_id_tech`),
  KEY `plugin_openmedis_medicalconsumableitemtypes_id` (`plugin_openmedis_medicalconsumableitemtypes_id`),
  KEY `is_deleted` (`is_deleted`),
  KEY `alarm_threshold` (`alarm_threshold`),
  KEY `groups_id_tech` (`groups_id_tech`),
  KEY `date_mod` (`date_mod`),
  KEY `date_creation` (`date_creation`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

ALTER TABLE `glpi_states` ADD COLUMN `is_visible_pluginopenmedismedicaldevice` tinyint(1) NOT NULL DEFAULT '1';
