
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

