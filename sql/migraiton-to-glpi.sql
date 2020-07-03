-- Migration script from openmedis 1.4.1 and GLPI 9.3 + openmedis plugin v1.0.x
-- Make sure the openmedis db and the glpi db are in the same mysql server
-- the scripts use the name openmedis_old for the openmedsi database
-- and glpidb for the GLPI database

-- Locations
ALTER TABLE glpidb.`glpi_locations` ADD COLUMN `old_ID` text DEFAULT NULL;

INSERT INTO glpidb.`glpi_locations` (completename,name, comment, entities_id,level,address,town,state,country,locations_id,old_ID)
SELECT 
c.Country as completename,
c.Country as name,
'' as comment,
'0' AS entities_id,
'1' AS level,
'' AS address,
'' AS town,
'' AS state,
c.Country as country,
'0' as locations_id
c.CountryID as old_ID
FROM openmedis_old.countries c 
INNER JOIN openmedis_old.province p on p.CountryID = c.CountryID
GROUP BY c.Country;


INSERT INTO glpidb.`glpi_locations` (completename,name, comment, entities_id,level,address,town,state,country,locations_id,old_ID)
SELECT 
CONCAT(c.Country,'>',p.ProvinceName)as completename,
p.ProvinceName as name,
'' as comment,
'0' AS entities_id,
'2' AS level,
'' AS address,
'' AS town,
p.ProvinceName  as state,
c.Country as country,
(SELECT id FROM glpidb.`glpi_locations` WHERE level=1 and old_ID=c.CountryID) as locations_id
p.`ProvinceID` as old_ID
FROM openmedis_old.province p
JOIN openmedis_old.countries c on p.CountryID = c.CountryID;


INSERT INTO glpidb.`glpi_locations` (completename,name, comment, entities_id,level,address,town,state,country,locations_id,old_ID)
SELECT 
CONCAT(p.ProvinceName,'>',d.DistrictName)as completename,
d.DistrictName as name,
'' as comment,
'0' AS entities_id,
'3' AS level,
'' AS address,
'' AS town,
p.ProvinceName  as state,
c.Country as country,
(SELECT id FROM glpidb.`glpi_locations` WHERE level=2 and comment=p.ProvinceID) as locations_id
d.`DistrictID` as old_ID
FROM openmedis_old.districts d
JOIN openmedis_old.province p on d.ProvinceID = p.ProvinceID
JOIN openmedis_old.countries c on p.CountryID = c.CountryID;

/*
INSERT INTO glpidb.`glpi_locations` (completename,name, comment, entities_id,level,address,town,state,country,locations_id)
SELECT 
(CASE WHEN LENGTH(A.name)>0 THEN A.completename ELSE Concat(A.completename,'Default') END) as completename,
(CASE WHEN LENGTH(A.name)>0 THEN A.name ELSE 'Default' END) as name,
A.comment,
A.entities_id,
A.level,
A.address, 
(CASE WHEN LENGTH(A.name)>0 THEN A.name ELSE 'Default' END) as town,
A.state,
A.country,
A.location_id
FROM
  (SELECT DISTINCT LOWER(SUBSTR( f.ward FROM ( f.ward REGEXP '\w' )))  as name,
 CONCAT((d.DistrictName),'>',LOWER(SUBSTR( f.ward FROM ( f.ward REGEXP '\w' ))) )as completename,
'0' AS entities_id,
'4' AS level,
'' AS address,
'' AS comment,
'' AS town,
(p.ProvinceName)  as state,
(c.Country) as country,
(SELECT id FROM glpidb.`glpi_locations` WHERE level=3 and comment=(d.DistrictID)) as location_id
FROM openmedis_old.facilities f
JOIN openmedis_old.districts d ON d.DistrictID = f.DistrictID
JOIN openmedis_old.province p on d.ProvinceID = p.ProvinceID
JOIN openmedis_old.countries c on p.CountryID = c.CountryID) AS A
*/

DELEtE FROM glpidb.`glpi_locations` WHERE level=4;
SET @@group_concat_max_len = 65535  ;

INSERT INTO glpidb.`glpi_locations` (completename,name, comment, entities_id,level,address,town,state,country,locations_id,longitude,latitude,old_ID)
SELECT
 MAX(A.completename),MAX(A.name),MAX(A.comment),MAX(A.entities_id),MAX(A.level),MAX(A.address),MAX(A.town),MAX(A.state),MAX(A.country),MAX(A.locations_id),MAX(A.longitude),MAX(A.latitude),group_concat(DISTINCT loc.LocationID) as old_ID 
  FROM
(SELECT DISTINCT f.FacilityID, TRIM(CONCAT(f.FacilityName,' ',f.ward,' ',f.division)) as name,
CONCAT(d.DistrictName,'>',f.FacilityName)as completename,
TRIM(CONCAT('FacilityName:"',f.FacilityName, 
'"\n Type:"',t.FacilityTypeDesc,
'"\n FacilityShortName:"',f.ShortName,
'"\n",Ward:"',SUBSTR( f.ward FROM ( f.ward REGEXP '\w' )),
'"\n division:"',SUBSTR( f.division FROM ( f.division REGEXP '\w' )),
'"\n year:"',f.Year_Established,
'"\n status:"',s.FacilityStatusDesc,
'"\n EstCatchement:"',f.EstCatchmentPop,
'"\n Owner:"',o.OwnerDesc,
'"\n numberBeds:"',f.NumberOfBeds,
'"\n fax:"',f.Fax,
'"\n Mobile:"',f.MobilePhone,
'"\n LandLine:"',f.LandLinePhone,
'"}'
))as comment,
'0' AS entities_id,
'4' AS level,
f.Mailing_Address_Street AS address,
f.`Mailing_Address_Town/City` AS town,
p.ProvinceName  as state,
c.Country as country,
(SELECT id FROM glpidb.`glpi_locations` WHERE level=3 and old_ID=f.DistrictID) as locations_id,
f.GPS_South as latitude,
f.GPS_East as longitude
FROM openmedis_old.facilities f
JOIN openmedis_old.districts d ON d.DistrictID = f.DistrictID
JOIN openmedis_old.facilitystatus s ON s.FacilityStatusID = f.FacilityStatusID
JOIN openmedis_old.facilityowner o ON o.OwnerID = f.OwnerID
JOIN openmedis_old.facilitytype t ON t.FacilityTypeID = f.FacilityTypeID
JOIN openmedis_old.province p on d.ProvinceID = p.ProvinceID
JOIN openmedis_old.countries c on p.CountryID = c.CountryID
JOIN openmedis_old.facilities r on f.ReferralFacilityID = r.FacilityID
) as A
LEFT JOIN openmedis_old.location loc on A.FacilityID = loc.FacilityID
GROUP BY A.locations_id, A.name

-- Manufacturers
ALTER TABLE glpidb.`glpi_manufacturers` ADD COLUMN `old_ID` text DEFAULT NULL;
INSERT INTO glpidb.`glpi_manufacturers` (name,comment,old_ID)
SELECT c.ContactName,
CONCAT('{',
'ContactPersonName:"',c.ContactPersonName,
'"\n Address:"',c.Address,
'"\n City:"',c.City,
'"\n PhoneNumber:"',c.PhoneNumber,
'"\n country:"',co.Country,
'"}'),
m.ManufacturerID  as old_ID
FROM openmedis_old.`manufactures` as m
JOIN openmedis_old.contact as c on m.ContactID = c.ContactID
JOIN openmedis_old.countries co on c.CountryID = co.CountryID





-- Agent

ALTER TABLE glpidb.`glpi_users` ADD COLUMN `old_ID` text DEFAULT NULL;

INSERT INTO glpidb.`glpi_users` (name,comment,phone,phone2,realname,firstname,locations_id,old_ID)
Select name,comment,phone,phone2,realname,firstname,locations_id,old_ID
FROM (SELECT LOWER(l.username) as name,
CONCAT('{',
'"\n Position:"',MAX(e.Position),
'"\n email:"',MAX(e.Email),
'"}') as comment,
MAX(e.HandPhone) as phone,
MAX(e.WorkPhone) as phone2,
MAX(e.LastName) as realname,
MAX(e.FirstName) as firstname,
(CASE WHEN (SELECT id FROM glpidb.`glpi_locations` as ll WHERE  FIND_IN_SET(MAX(e.LocationID),ll.old_ID)>0) is not null then (SELECT id FROM glpidb.`glpi_locations` as lll WHERE  FIND_IN_SET(MAX(e.LocationID),lll.old_ID)>0) else '0' END)as locations_id,
MAX(EmployeeID) as old_ID
FROM  openmedis_old.login as l
LEFT JOIN  openmedis_old.`employees` as e on  e.LoginID  =l.LoginID
GROUP BY LOWER(l.username)) as A


-- email

INSERT INTO glpidb.`glpi_useremails` (email,users_id,is_default)
SELECT e.email,
(SELECT id FROM glpidb.`glpi_users` u WHERE ( e.EmployeeID COLLATE utf8_unicode_ci) = u.old_ID) as users_id,
'1' as is_default
FROM openmedis_old.`employees` as e
WHERE (SELECT id FROM glpidb.`glpi_users` u WHERE ( e.EmployeeID COLLATE utf8_unicode_ci) = u.old_ID) IS NOT NULL

-- add profile
INSERT INTO glpidb.`glpi_profiles_users`( `users_id`, `profiles_id`, `entities_id`) 
SELECT 
(SELECT id FROM glpidb.`glpi_users` u WHERE ( e.EmployeeID COLLATE utf8_unicode_ci) = u.old_ID) as users_id,
 (case l.GroupID 
  WHEN 1 THEN '1' -- self-service
  WHEN 2 THEN '3' -- admin
  WHEN 3 THEN '4' -- super-admin
  END) AS profiles_id,
  '0' as entities_id
FROM  openmedis_old.login as l
LEFT JOIN  openmedis_old.`employees` as e on  e.LoginID  =l.LoginID
WHERE (SELECT id FROM glpidb.`glpi_users` u WHERE ( e.EmployeeID COLLATE utf8_unicode_ci) = u.old_ID) IS NOT NULL

-- Supplier

ALTER TABLE glpidb.`glpi_suppliers` ADD COLUMN `old_ID` text DEFAULT NULL;
INSERT INTO glpidb.`glpi_suppliers`(`entities_id`, `name`,  `address`, `postcode`, `town`, `state`, `country`, `website`,  `phonenumber`, `comment`, `fax`, `email`,old_ID) 
SELECT 
'0' as entities_id,
c.ContactName as name,
c.Address as address,
c.PostalCode as postcode,
c.City as town,
'' as state,
co.Country as country,
c.Website as websire,
c.PhoneNumber as phonemumber,
CONCAT('{',
'ContactPersonName:"',c.ContactPersonName,
'"}') as comment,
c.FaxNumber as fax,
'' as email,
s.SupplierID  as old_ID
FROM openmedis_old.`suppliers` as s
JOIN openmedis_old.contact as c on s.ContactID = c.ContactID
JOIN openmedis_old.countries co on c.CountryID = co.CountryID

-- models
INSERT INTO glpidb.`glpi_plugin_openmedis_medicaldevicemodels` (name)
SELECT A.name
FROM (SELECT  DISTINCT LOWER(Model) as name
FROM openmedis_old.`assets`) as A


-- category


-- AssetStatus (FIXME, new table required)
INSERT INTO glpidb.`glpi_states` (name, completename)
SELECT  AssetStatusDesc,AssetStatusDesc
FROM openmedis_old.`assetstatus` 

-- Utilization's
INSERT INTO glpidb.`glpi_plugin_openmedis_utilizations` (name)
SELECT  AssetUtilizationDesc
FROM openmedis_old.`assetutilization` 

-- Asset
ALTER TABLE glpidb.`glpi_plugin_openmedis_medicaldevices` ADD COLUMN `old_ID` text DEFAULT NULL;

INSERT INTO glpidb.`glpi_plugin_openmedis_medicaldevices`( `old_ID`,`plugin_openmedis_medicaldevicemodels_id`,`plugin_openmedis_medicaldevicecategories_id`,`entities_id`, `name`, `contact`, `contact_num`, `users_id_tech`, `groups_id_tech`, `comment`, `serial`, `otherserial`, `locations_id`,   `plugin_openmedis_utilizations_id`, `brand`, `manufacturers_id`, `users_id`, `groups_id`, `states_id`)

SELECT `old_ID`,
(CASE WHEN `plugin_openmedis_medicaldevicemodels_id` IS  NULL THEN '0' ELSE `plugin_openmedis_medicaldevicemodels_id` END) ,
(CASE WHEN `plugin_openmedis_medicaldevicecategories_id` IS NULL THEN '0' ELSE `plugin_openmedis_medicaldevicecategories_id` END) ,
`entities_id`, 
`name`, 
`contact`, 
`contact_num`, 
(CASE WHEN `users_id_tech` IS NULL THEN '0' ELSE `users_id_tech` END ), 
`groups_id_tech`, 
`comment`, 
`serial`, 
`otherserial`, 
(CASE WHEN `locations_id`IS NULL THEN '0' ELSE `locations_id` END) ,   
(CASE WHEN`plugin_openmedis_utilizations_id` IS NULL THEN '0' ELSE `plugin_openmedis_utilizations_id` END) , 
`brand`, 
(CASE WHEN `manufacturers_id` IS NULL THEN '0' ELSE `manufacturers_id` END ), 
`users_id`, 
`groups_id`, 
(CASE WHEN `states_id` IS NULL THEN '0' ELSE `states_id` END )
FROM (
SELECT a.`AssetID` as old_ID, 
(SELECT id FROM glpidb.glpi_plugin_openmedis_medicaldevicemodels WHERE  name  = LOWER(Model) LIMIT 1)  as `plugin_openmedis_medicaldevicemodels_id`,
(SELECT id FROM glpidb.glpi_plugin_openmedis_medicaldevicecategories WHERE  name  = ga.GenericAssetName  LIMIT 1) as  plugin_openmedis_medicaldevicecategories_id, 
'0' as entities_id,
a.`AssetFullName` as name,
a.ResponsiblePers as contact,
'' as contact_num,
(SELECT id FROM glpidb.glpi_users WHERE  old_locaitonsID  = EmployeeID COLLATE utf8_unicode_ci LIMIT 1 ) as users_id_tech,
'0' as groups_id_tech,
Notes as comment,
a.SerialNumber as serial,
a.InternalIventoryNumber as otherserial,
 (SELECT id FROM glpidb.`glpi_locations` as lll WHERE  FIND_IN_SET(LocationID,lll.old_locaitonsID COLLATE utf8_unicode_ci)>0  LIMIT 1)   as locations_id,
(SELECT id FROM glpidb.glpi_plugin_openmedis_utilizations WHERE  name  = au.AssetUtilizationDesc  LIMIT 1 )as plugin_openmedis_utilizations_id,
'' as brand,
(SELECT id FROM glpidb.glpi_manufacturers WHERE  old_locaitonsID  = ManufacturerID COLLATE utf8_unicode_ci LIMIT 1) as manufacturers_id,
'0' as users_id,
'0' as groups_id,
 (SELECT id FROM glpidb.glpi_states WHERE  name  = s.AssetStatusDesc  LIMIT 1) as states_id
FROM openmedis_old.`assets` as a
JOIN  openmedis_old.assetgenericname as ga ON a.GenericAssetID = ga.GenericAssetID 
JOIN openmedis_old.assetstatus as s ON a.AssetStatusID = s.AssetStatusID
join openmedis_old.assetutilization as au on au.AssetUtilizationID = a.AssetUtilizationID
) A

--  Remaining fields ``, ``, ``, `AgentID`, ``, ``, ``, ``, `PurchaseDate`, `InstallationDate`, `Year_installed`, `Lifetime`, `PurchasePrice`, `CurrentValue`, ``, `WarrantyContractID`, `MaintenanceContract`, `MaintenanceContractNo`, `MaintenanceContractExpiry`, `WarrantyContractExp`, `WarrantyContractNotes`, `SupplierID`, `DonorID`, `ServiceManual`, `OperatorsManual`, ``, `Picture`, `lastmodified`, `by_user`, `deleted`, `URL_Manual`, `MetrologyDocument`, `MetrologyDate`, `Metrology`, `` 


-- tickets

-- consumable

-- cleanup 
ALTER TABLE glpidb.`glpi_users` DROP COLUMN `old_ID`;
ALTER TABLE glpidb.`glpi_manufacturers` DROP COLUMN `old_ID`;
ALTER TABLE glpidb.`glpi_locations` DROP COLUMN `old_ID`;
ALTER TABLE glpidb.`glpi_suppliers` DROP COLUMN `old_ID`;
ALTER TABLE glpidb.`glpi_plugin_openmedis_medicaldevices` DROP COLUMN `old_ID`;