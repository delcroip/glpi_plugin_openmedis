
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


### Dump table glpi_plugin_openmedis_medicalconsumable

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

DELIMITER $$

DROP PROCEDURE IF EXISTS replace_key_if_exists $$
CREATE PROCEDURE replace_key_if_exists(in theTable varchar(128), in oldIndexName varchar(128), in newIndexNameDef varchar(128) )
BEGIN
 IF((SELECT COUNT(*) AS index_exists FROM information_schema.statistics WHERE TABLE_SCHEMA = DATABASE() and table_name =
theTable AND INDEX_NAME = oldIndexName) > 0) THEN
   SET @s = CONCAT('ALTER TABLE ',theTable, ' DROP KEY ' , oldIndexName , ', ADD KEY ',newIndexNameDef );
   PREPARE stmt FROM @s;
   EXECUTE stmt;
 END IF;
END $$

DROP PROCEDURE IF EXISTS add_column_if_not_exists $$
CREATE PROCEDURE add_column_if_not_exists(in theTable varchar(128), in ColumnName varchar(128), in ColumnDef varchar(128) )
BEGIN
 IF NOT ((SELECT COUNT(*) AS index_exists FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() and table_name =
theTable AND COLUMN_NAME = ColumnName) > 0) THEN
   SET @s = CONCAT('ALTER TABLE ',theTable, ' ADD ' , ColumnName , ' ',ColumnDef );
   PREPARE stmt FROM @s;
   EXECUTE stmt;
 END IF;
END $$

DROP PROCEDURE IF EXISTS rename_column_if_exists $$
CREATE PROCEDURE rename_column_if_exists(in theTable varchar(128), in oldColumnName varchar(128), in newColumnNameDef varchar(128) )
BEGIN
 IF ((SELECT COUNT(*) AS index_exists FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() and table_name =
theTable AND COLUMN_NAME = oldColumnName) > 0) THEN
   SET @s = CONCAT('ALTER TABLE ',theTable, ' change ' , oldColumnName , ' ',newColumnNameDef );
   PREPARE stmt FROM @s;
   EXECUTE stmt;
 END IF;
END $$

DROP PROCEDURE IF EXISTS remane_table_if_exists $$
CREATE PROCEDURE remane_table_if_exists(in oldtableName varchar(128), in newTableName varchar(128) )
BEGIN
 IF((SELECT COUNT(*) AS index_exists FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() and table_name =
theTable ) > 0) THEN
   SET @s = CONCAT('ALTER TABLE ',oldtableName, ' remane ' , newTableName  );
   PREPARE stmt FROM @s;
   EXECUTE stmt;
 END IF;
END $$

DELIMITER ;
### change key plugin_openmedis_medicaldevice_id -->> plugin_openmedis_medicaldevices_id

CALL  replace_key_if_exists('glpi_plugin_openmedis_medicalaccessories_items','plugin_openmedis_medicaldevice_id', 'plugin_openmedis_medicaldevices_id (items_id)');
CALL  add_column_if_not_exists('glpi_plugin_openmedis_medicaldevices','init_usages_counter','int(11) NOT NULL DEFAULT 0');
CALL  add_column_if_not_exists('glpi_plugin_openmedis_medicaldevices','last_usages_counter','int(11) NOT NULL DEFAULT 0');
CALL  remane_table_if_exists('glpi_plugin_openmedis_item_devicemedicalaccessories', 'glpi_plugin_openmedis_item_medicalaccessories');
CALL  remane_table_if_exists('glpi_plugin_openmedis_medicalaccessories', 'glpi_plugin_openmedis_medicalaccessories');
CALL  rename_column_if_exists('glpi_plugin_openmedis_items_medicalaccessories','plugin_openmedis_devicemedicalaccessories_id','plugin_openmedis_medicalaccessories_id int(11) NOT NULL DEFAULT 0');
CALL  replace_key_if_exists('plugin_openmedis_devicemedicalaccessories_id','plugin_openmedis_devicemedicalaccessories_id' , 'plugin_openmedis_medicalaccessories_id (`plugin_openmedis_medicalaccessories_id`)");

