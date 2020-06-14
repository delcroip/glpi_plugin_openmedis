CREATE TABLE IF NOT EXISTS `accesories` (
  `AccessoryID` int(10) NOT NULL AUTO_INCREMENT,
  `Name` varchar(255) NOT NULL,
  `AccessoryType` varchar(255) NOT NULL,
  `ManufacturerID` int(10) NOT NULL,
  `SupplierID` int(10) NOT NULL,
  `PartNumber` varchar(255) NOT NULL,
  `Price` varchar(10) NOT NULL,
  PRIMARY KEY (`AccessoryID`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

--
-- Table structure for table `assetcategory`
-- to be transofrom in type / dropdown

--- CREATE TABLE IF NOT EXISTS `assetcategory` (
---  `AssetCategoryID` int(10) NOT NULL AUTO_INCREMENT,
---  `AssetCategoryNr` int(10) DEFAULT NULL,
---  `AssetCategoryName` varchar(255) COLLATE utf8_bin DEFAULT NULL,
---  PRIMARY KEY (`AssetCategoryID`)
---) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Type of asset - first UMNDS digit' AUTO_INCREMENT=17 ;
--
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


-- dropdown
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

--
-- Table structure for table `consumables`
--



--
-- Table structure for table `failurcateg`
-- drop down

--CREATE TABLE IF NOT EXISTS `failurcateg` (
--  `FailurCategID` int(11) NOT NULL AUTO_INCREMENT,
--  `FailurCategCode` text NOT NULL,
--  PRIMARY KEY (`FailurCategID`)
--) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=7 ;

--
-- Table structure for table `failurecause`
--
-- 
--CREATE TABLE IF NOT EXISTS `failurecause` (
--  `FailureCauseID` int(10) NOT NULL,
--  `FailureCauseCode` varchar(50) CHARACTER SET latin1 DEFAULT NULL,
--  `FailureDesc` varchar(50) CHARACTER SET latin1 DEFAULT NULL,
--  `FailureCauseNo` varchar(50) CHARACTER SET latin1 DEFAULT NULL,
--  PRIMARY KEY (`FailureCauseID`),
--  UNIQUE KEY `Failure Cause Code` (`FailureCauseCode`)
--) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

CREATE TABLE IF NOT EXISTS `assets` (
  `AssetID` char(13) CHARACTER SET latin1 NOT NULL,
  `GenericAssetID` int(10) DEFAULT NULL COMMENT 'Generic Device Name of Asset',
  `UMDNS` int(10) DEFAULT NULL COMMENT 'UMNDS Code',
  `AssetFullName` varchar(255) COLLATE utf8_bin DEFAULT NULL COMMENT 'Name of Asset',
  `ManufacturerID` char(13) COLLATE utf8_bin DEFAULT NULL COMMENT 'Manufacturer of Asset',
  `Model` varchar(255) COLLATE utf8_bin DEFAULT NULL COMMENT 'Device Model Name',
  `SerialNumber` varchar(255) COLLATE utf8_bin DEFAULT NULL COMMENT 'Serial Number of Device',
  `InternalIventoryNumber` varchar(255) COLLATE utf8_bin DEFAULT NULL COMMENT 'Internal Inventory Numner of Asset',
  `LocationID` char(13) COLLATE utf8_bin DEFAULT NULL COMMENT 'Location of Asset',
  `ResponsiblePers` varchar(50) COLLATE utf8_bin DEFAULT NULL,
  `AssetStatusID` int(10) DEFAULT NULL COMMENT 'Condition of Asset',
  `AssetUtilizationID` int(10) DEFAULT NULL COMMENT 'Utilization of Asset',
  `PurchaseDate` date DEFAULT NULL COMMENT 'Aquirey or Pruchase Date of Asset',
  `InstallationDate` date DEFAULT NULL,
  `Lifetime` int(11) DEFAULT NULL,
  `PurchasePrice` double(24,0) DEFAULT NULL COMMENT 'Purchase Price of Asset',
  `CurrentValue` double(24,0) DEFAULT NULL COMMENT 'Current Value of Asset',
  `WarrantyContractID` int(10) DEFAULT NULL COMMENT 'Warranty or Contract',
  `AgentID` char(13) CHARACTER SET utf8 NOT NULL,
  `WarrantyContractExp` date DEFAULT NULL COMMENT 'Date of Contract Expiry',
  `WarrantyContractNotes` text COLLATE utf8_bin COMMENT 'Notes to Warranty or Contract',
  `EmployeeID` char(13) CHARACTER SET utf8 NOT NULL,
  `SupplierID` char(13) CHARACTER SET utf8 NOT NULL COMMENT 'Contractor',
  `DonorID` char(13) CHARACTER SET utf8 NOT NULL,
  `ServiceManual` varchar(20) COLLATE utf8_bin NOT NULL,
  `Notes` text COLLATE utf8_bin COMMENT 'Additional Notes',
  `Picture` varchar(255) COLLATE utf8_bin DEFAULT NULL COMMENT 'link to image of asset',
  `lastmodified` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `by_user` varchar(25) CHARACTER SET utf8 DEFAULT NULL,
  `URL_Manual` varchar(255) COLLATE utf8_bin DEFAULT NULL,
  `MetrologyDocument` varchar(255) COLLATE utf8_bin DEFAULT NULL,
  `MetrologyDate` date DEFAULT NULL,
  `Metrology` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`AssetID`),
  KEY `GenericAssetID` (`GenericAssetID`),
  KEY `AssetUtilizationID` (`AssetUtilizationID`),
  KEY `WarrantyContractID` (`WarrantyContractID`),
  KEY `AssetStatusID` (`AssetStatusID`),
  KEY `LocationID` (`LocationID`),
  KEY `SupplierID` (`SupplierID`),
  KEY `ManufacturerID` (`ManufacturerID`),
  KEY `DonorID` (`DonorID`),
  KEY `EmployeeID` (`EmployeeID`),
  KEY `AgentID` (`AgentID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;


CREATE TABLE IF NOT EXISTS `essential_equipment` (
  `EssentialEquipmentID` int(11) NOT NULL AUTO_INCREMENT,
  `FacilityID` int(11) DEFAULT NULL,
  `GenericAssetID` int(11) DEFAULT NULL,
  `MinimalQuantity` int(11) DEFAULT NULL,
  `Notes` text CHARACTER SET utf8,
  PRIMARY KEY (`EssentialEquipmentID`),
  KEY `FacilityID` (`FacilityID`),
  KEY `GenericAssetID` (`GenericAssetID`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1878 ;