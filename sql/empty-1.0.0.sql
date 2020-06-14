-- CREATE TABLE IF NOT EXISTS `accesories` (
  -- `AccessoryID` int(10) NOT NULL AUTO_INCREMENT,
  -- `Name` varchar(255) NOT NULL,
  -- `AccessoryType` varchar(255) NOT NULL,
  -- `ManufacturerID` int(10) NOT NULL,
  -- `SupplierID` int(10) NOT NULL,
  -- `PartNumber` varchar(255) NOT NULL,
  -- `Price` varchar(10) NOT NULL,
  -- PRIMARY KEY (`AccessoryID`)
-- ) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

DROP TABLE IF EXISTS `glpi_devicebatteries`;
CREATE TABLE `glpi_devicebatteries` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `designation` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `comment` text COLLATE utf8_unicode_ci,
  `manufacturers_id` int(11) NOT NULL DEFAULT '0',
  `voltage` int(11) DEFAULT NULL,
  `capacity` int(11) DEFAULT NULL,
  `devicebatterytypes_id` int(11) NOT NULL DEFAULT '0',
  `entities_id` int(11) NOT NULL DEFAULT '0',
  `is_recursive` tinyint(1) NOT NULL DEFAULT '0',
  `devicebatterymodels_id` int(11) DEFAULT NULL,
  `date_mod` datetime DEFAULT NULL,
  `date_creation` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `designation` (`designation`),
  KEY `manufacturers_id` (`manufacturers_id`),
  KEY `entities_id` (`entities_id`),
  KEY `is_recursive` (`is_recursive`),
  KEY `date_mod` (`date_mod`),
  KEY `date_creation` (`date_creation`),
  KEY `devicebatterymodels_id` (`devicebatterymodels_id`),
  KEY `devicebatterytypes_id` (`devicebatterytypes_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


-- Table structure for table `assetcategory`
-- to be transofrom in type / dropdown

--- CREATE TABLE IF NOT EXISTS `assetcategory` (
---  `AssetCategoryID` int(10) NOT NULL AUTO_INCREMENT,
---  `AssetCategoryNr` int(10) DEFAULT NULL,
---  `AssetCategoryName` varchar(255) COLLATE utf8_bin DEFAULT NULL,
---  PRIMARY KEY (`AssetCategoryID`)
---) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Type of asset - first UMNDS digit' AUTO_INCREMENT=17 ;


CREATE TABLE `glpi_softwarecategories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `comment` text COLLATE utf8_unicode_ci,
  `softwarecategories_id` int(11) NOT NULL DEFAULT '0',
  `completename` text COLLATE utf8_unicode_ci,
  `level` int(11) NOT NULL DEFAULT '0',
  `ancestors_cache` longtext COLLATE utf8_unicode_ci,
  `sons_cache` longtext COLLATE utf8_unicode_ci,
  PRIMARY KEY (`id`),
  KEY `softwarecategories_id` (`softwarecategories_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Table structure for table `assetgenericname`
-- to be transof in modele
--CREATE TABLE IF NOT EXISTS `assetgenericname` (
--  `GenericAssetID` int(10) NOT NULL AUTO_INCREMENT,
--  `GenericAssetCode` varchar(50) CHARACTER SET latin1 DEFAULT NULL,
--  `GenericAssetName` varchar(255) COLLATE utf8_bin DEFAULT NULL,
--  `GenericAssetDesc` text CHARACTER SET utf8 NOT NULL COMMENT 'Description according to GMDN list',
--  `AssetCategoryID` int(10) DEFAULT NULL,
--  `GenericPicture` varchar(255) CHARACTER SET latin1 COLLATE latin1_bin DEFAULT NULL COMMENT 'link to image of generic asset',
--  `flag` tinyint(4) NOT NULL,
--  PRIMARY KEY (`GenericAssetID`),
--  KEY `AssetCategoryID` (`AssetCategoryID`)
--) COMMENT='The medical device nomenclature and the five-digit number associated with each medical device are part of ECRI Institutes Universal Medical Device Nomenclature System (UMDNS), a widely employed international classification system for information indexing and retrieval. The five-digit number is the authorized ECRI Institutes Universal Medical Device Code (UMDC).' 
-- ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=100001 ;

DROP TABLE IF EXISTS `glpi_peripheralmodels`;
CREATE TABLE `glpi_peripheralmodels` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `comment` text COLLATE utf8_unicode_ci,
  `product_number` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `weight` int(11) NOT NULL DEFAULT '0',
  `required_units` int(11) NOT NULL DEFAULT '1',
  `depth` float NOT NULL DEFAULT 1,
  `power_connections` int(11) NOT NULL DEFAULT '0',
  `power_consumption` int(11) NOT NULL DEFAULT '0',
  `is_half_rack` tinyint(1) NOT NULL DEFAULT '0',
  `picture_front` text COLLATE utf8_unicode_ci,
  `picture_rear` text COLLATE utf8_unicode_ci,
  `date_mod` datetime DEFAULT NULL,
  `date_creation` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `name` (`name`),
  KEY `date_mod` (`date_mod`),
  KEY `date_creation` (`date_creation`),
  KEY `product_number` (`product_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;



-- dropdown std
--CREATE TABLE IF NOT EXISTS `assetstatus` (
--  `AssetStatusID` int(10) NOT NULL AUTO_INCREMENT,
--  `AssetStatusDesc` varchar(50) COLLATE utf8_bin DEFAULT NULL,
--  PRIMARY KEY (`AssetStatusID`)
--) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=5 ;

--
-- Table structure for table `assetutilization`
-- drop down

--CREATE TABLE IF NOT EXISTS `assetutilization` (
--  `AssetUtilizationID` int(10) NOT NULL AUTO_INCREMENT,
--  `AssetUtilizationDesc` varchar(50) COLLATE utf8_bin DEFAULT NULL,
--  PRIMARY KEY (`AssetUtilizationID`)
--) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=5 ;

DROP TABLE IF EXISTS `glpi_plugin_racks_rackstates`;
CREATE TABLE `glpi_plugin_racks_rackstates` (
   `id` int(11) NOT NULL auto_increment,
   `entities_id` int(11) NOT NULL default '0',
   `is_recursive` tinyint(1) NOT NULL default '0',
   `name` varchar(255) collate utf8_unicode_ci default NULL,
   `comment` text collate utf8_unicode_ci,
   PRIMARY KEY  (`id`),
   KEY `name` (`name`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Table structure for table `consumables`
--  --> std



-- CREATE TABLE IF NOT EXISTS `assets` (
  -- `AssetID` char(13) CHARACTER SET latin1 NOT NULL,
  -- `GenericAssetID` int(10) DEFAULT NULL COMMENT 'Generic Device Name of Asset',
  -- `UMDNS` int(10) DEFAULT NULL COMMENT 'UMNDS Code',
  -- `AssetFullName` varchar(255) COLLATE utf8_bin DEFAULT NULL COMMENT 'Name of Asset',
  -- `ManufacturerID` char(13) COLLATE utf8_bin DEFAULT NULL COMMENT 'Manufacturer of Asset',
  -- `Model` varchar(255) COLLATE utf8_bin DEFAULT NULL COMMENT 'Device Model Name',
  -- `SerialNumber` varchar(255) COLLATE utf8_bin DEFAULT NULL COMMENT 'Serial Number of Device',
  -- `InternalIventoryNumber` varchar(255) COLLATE utf8_bin DEFAULT NULL COMMENT 'Internal Inventory Numner of Asset',
  -- `LocationID` char(13) COLLATE utf8_bin DEFAULT NULL COMMENT 'Location of Asset',
  -- `ResponsiblePers` varchar(50) COLLATE utf8_bin DEFAULT NULL,
  -- `AssetStatusID` int(10) DEFAULT NULL COMMENT 'Condition of Asset',
  -- `AssetUtilizationID` int(10) DEFAULT NULL COMMENT 'Utilization of Asset',
  -- `PurchaseDate` date DEFAULT NULL COMMENT 'Aquirey or Pruchase Date of Asset',
  -- `InstallationDate` date DEFAULT NULL,
  -- `Lifetime` int(11) DEFAULT NULL,
  -- `PurchasePrice` double(24,0) DEFAULT NULL COMMENT 'Purchase Price of Asset',
  -- `CurrentValue` double(24,0) DEFAULT NULL COMMENT 'Current Value of Asset',
  -- `WarrantyContractID` int(10) DEFAULT NULL COMMENT 'Warranty or Contract',
  -- `AgentID` char(13) CHARACTER SET utf8 NOT NULL,
  -- `WarrantyContractExp` date DEFAULT NULL COMMENT 'Date of Contract Expiry',
  -- `WarrantyContractNotes` text COLLATE utf8_bin COMMENT 'Notes to Warranty or Contract',
  -- `EmployeeID` char(13) CHARACTER SET utf8 NOT NULL,
  -- `SupplierID` char(13) CHARACTER SET utf8 NOT NULL COMMENT 'Contractor',
  -- `DonorID` char(13) CHARACTER SET utf8 NOT NULL,
  -- `ServiceManual` varchar(20) COLLATE utf8_bin NOT NULL,
  -- `Notes` text COLLATE utf8_bin COMMENT 'Additional Notes',
  -- `Picture` varchar(255) COLLATE utf8_bin DEFAULT NULL COMMENT 'link to image of asset',
  -- `lastmodified` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  -- `by_user` varchar(25) CHARACTER SET utf8 DEFAULT NULL,
  -- `URL_Manual` varchar(255) COLLATE utf8_bin DEFAULT NULL,
  -- `MetrologyDocument` varchar(255) COLLATE utf8_bin DEFAULT NULL,
  -- `MetrologyDate` date DEFAULT NULL,
  -- `Metrology` tinyint(1) DEFAULT NULL,
  -- PRIMARY KEY (`AssetID`),
  -- KEY `GenericAssetID` (`GenericAssetID`),
  -- KEY `AssetUtilizationID` (`AssetUtilizationID`),
  -- KEY `WarrantyContractID` (`WarrantyContractID`),
  -- KEY `AssetStatusID` (`AssetStatusID`),
  -- KEY `LocationID` (`LocationID`),
  -- KEY `SupplierID` (`SupplierID`),
  -- KEY `ManufacturerID` (`ManufacturerID`),
  -- KEY `DonorID` (`DonorID`),
  -- KEY `EmployeeID` (`EmployeeID`),
  -- KEY `AgentID` (`AgentID`)
-- ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
DROP TABLE IF EXISTS `glpi_peripherals`;
CREATE TABLE `glpi_peripherals` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `entities_id` int(11) NOT NULL DEFAULT '0',
  `name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `date_mod` datetime DEFAULT NULL,
  `contact` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `contact_num` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `users_id_tech` int(11) NOT NULL DEFAULT '0',
  `groups_id_tech` int(11) NOT NULL DEFAULT '0',
  `comment` text COLLATE utf8_unicode_ci,
  `serial` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `otherserial` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `locations_id` int(11) NOT NULL DEFAULT '0',
  `peripheraltypes_id` int(11) NOT NULL DEFAULT '0',
  `peripheralmodels_id` int(11) NOT NULL DEFAULT '0',
  `brand` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
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
  KEY `peripheralmodels_id` (`peripheralmodels_id`),
  KEY `states_id` (`states_id`),
  KEY `users_id_tech` (`users_id_tech`),
  KEY `peripheraltypes_id` (`peripheraltypes_id`),
  KEY `is_deleted` (`is_deleted`),
  KEY `date_mod` (`date_mod`),
  KEY `groups_id_tech` (`groups_id_tech`),
  KEY `is_dynamic` (`is_dynamic`),
  KEY `serial` (`serial`),
  KEY `otherserial` (`otherserial`),
  KEY `date_creation` (`date_creation`),
  KEY `is_recursive` (`is_recursive`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- CREATE TABLE IF NOT EXISTS `essential_equipment` (
  -- `EssentialEquipmentID` int(11) NOT NULL AUTO_INCREMENT,
  -- `FacilityID` int(11) DEFAULT NULL,
  -- `GenericAssetID` int(11) DEFAULT NULL,
  -- `MinimalQuantity` int(11) DEFAULT NULL,
  -- `Notes` text CHARACTER SET utf8,
  -- PRIMARY KEY (`EssentialEquipmentID`),
  -- KEY `FacilityID` (`FacilityID`),
  -- KEY `GenericAssetID` (`GenericAssetID`)
-- ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1878 ;