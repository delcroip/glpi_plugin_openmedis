DELETE FROM  glpi_tj.`glpi_plugin_openmedis_medicaldevices` WHERE 1 =1;
DELETE FROM  glpi_tj.`glpi_plugin_openmedis_utilizations` WHERE 1 =1;
-- cat
DELETE FROM  glpi_tj.`glpi_plugin_openmedis_medicaldevicemodels` WHERE 1 =1;
DELETE FROM  glpi_tj.`glpi_states` WHERE 1 =1;

DELETE FROM  glpi_tj.`glpi_suppliers` WHERE 1 =1;
DELETE FROM  glpi_tj.`glpi_useremails` WHERE 1 =1;
DELETE FROM  glpi_tj.`glpi_users` WHERE name <> 'glpi' ;
DELETE FROM  glpi_tj.`glpi_manufacturers` WHERE 1 =1;
DELETE FROM  glpi_tj.`glpi_locations` WHERE 1 =1;
DELETE FROM  glpi_tj.`glpi_entities` WHERE entities_id !=-1;
-- Locations
ALTER TABLE glpi_tj.`glpi_entities` ADD COLUMN `old_ID` text DEFAULT NULL;


INSERT INTO glpi_tj.`glpi_entities` (id, completename,name, comment, level,address,town,state,country,entities_id,old_ID)
SELECT 
((SELECT MAX(id) FROM glpi_entities) +1) as id,
c.Country as completename,
c.Country as name,
'' as comment,
'2' AS level,
'' AS address,
'' AS town,
'' AS state,
c.Country as country,
'0' as entities_id,
MAX(c.CountryID) as old_ID
FROM medis_old.countries c 
INNER JOIN medis_old.province p on p.CountryID = c.CountryID
GROUP BY c.Country;


INSERT INTO glpi_tj.`glpi_entities` (id, name, comment, level,address,town,state,country,entities_id,old_ID)
SELECT 
((SELECT MAX(id) FROM glpi_entities) + p.`ProvinceID`) as id,
p.ProvinceName as name,
'' as comment,
'3' AS level,
'' AS address,
'' AS town,
p.ProvinceName  as state,
c.Country as country,
COALESCE((SELECT id FROM glpi_tj.`glpi_entities` WHERE level=1 and old_ID=c.CountryID),0) as entities_id,
p.`ProvinceID` as old_ID
FROM medis_old.province p
JOIN medis_old.countries c on p.CountryID = c.CountryID;


INSERT INTO glpi_tj.`glpi_entities` (id, name, comment, level,address,town,state,country,entities_id,old_ID)
SELECT 
((SELECT MAX(id) FROM glpi_entities) +d.`DistrictID`) as id,
d.DistrictName as name,
'' as comment,
'4' AS level,
'' AS address,
'' AS town,
p.ProvinceName  as state,
c.Country as country,
COALESCE((SELECT id FROM glpi_tj.`glpi_entities` WHERE level=2 and old_ID=p.ProvinceID),0) as entities_id,
d.`DistrictID` as old_ID
FROM medis_old.districts d
JOIN medis_old.province p on d.ProvinceID = p.ProvinceID
JOIN medis_old.countries c on p.CountryID = c.CountryID;


SET @@group_concat_max_len = 65535  ;

INSERT INTO glpi_tj.`glpi_entities` ( id, name, comment, level,address,town,state,country,entities_id,longitude,latitude,old_ID)
SELECT 
((SELECT MAX(id) FROM glpi_entities) + f.`FacilityID`) as id, 
f.FacilityName as name,
'' as comment,
'5' as level,
f.FacilityAddress as address,
'' as town,
p.ProvinceName  as state,
c.Country as country,
COALESCE((SELECT id FROM glpi_tj.`glpi_entities` WHERE level=3 and old_ID=d.DistrictID),0) as entities_id,
'' as latitude,
'' as longitude,
f.FacilityID as old_ID
FROM medis_old.facilities f
JOIN medis_old.districts d ON d.DistrictID = f.DistrictID
JOIN medis_old.province p on d.ProvinceID = p.ProvinceID
JOIN medis_old.countries c on p.CountryID = c.CountryID;

ALTER TABLE glpi_tj.`glpi_locations` ADD COLUMN `old_ID` text DEFAULT NULL;

INSERT INTO glpidb.`glpi_locations` (completename,name, comment, entities_id,level,address,state,country,old_ID)
SELECT
CONCAT(f.FacilityName,'>',loc.Building,'>',loc.DeptID,'>',loc.Floor,'>',loc.Roomnb) as completename,
CONCAT(f.FacilityName,'>',loc.Building,'>',loc.DeptID,'>',loc.Floor,'>',loc.Roomnb)as name,
loc.NotetoTech as comment,
COALESCE((SELECT id FROM glpi_tj.`glpi_entities` WHERE level=4 and old_ID=loc.FacilityID),0) as entities_id,
'1' as level,
CONCAT('{',
'"\n DeptID:"',loc.DeptID,
'"\n Room:"',loc.Roomnb,
'"\n Floor:"',loc.Floor,
'"\n Building:"',loc.Building,
'"}') as address,
p.ProvinceName  as state,
c.Country as country,
loc.LocationID as old_ID
FROM medis_old.location loc 
JOIN medis_old.facilities f on f.FacilityID = loc.FacilityID
JOIN medis_old.districts d ON d.DistrictID = f.DistrictID
JOIN medis_old.province p on d.ProvinceID = p.ProvinceID
JOIN medis_old.countries c on p.CountryID = c.CountryID;


-- Manufacturers
ALTER TABLE glpi_tj.`glpi_manufacturers` ADD COLUMN `old_ID` text DEFAULT NULL;
INSERT INTO glpi_tj.`glpi_manufacturers` (name,comment,old_ID)
SELECT c.ContactName,
CONCAT('{',
'ContactPersonName:"',c.ContactPersonName,
'"\n Address:"',c.Address,
'"\n City:"',c.City,
'"\n PhoneNumber:"',c.PhoneNumber,
'"\n country:"',co.Country,
'"}'),
m.ManufacturerID  as old_ID
FROM medis_old.`manufactures` as m
JOIN medis_old.contact as c on m.ContactID = c.ContactID
JOIN medis_old.countries co on c.CountryID = co.CountryID;





-- Agent

ALTER TABLE glpi_tj.`glpi_users` ADD COLUMN `old_ID` text DEFAULT NULL;

INSERT INTO glpi_tj.`glpi_users` (name,password,comment,phone,phone2,realname,firstname, entities_id,old_ID,authtype)
Select name,password,comment,phone,phone2,realname,firstname, entities_id,old_ID , 1 as authtype
FROM (SELECT LOWER(REPLACE(l.username,' ','_')) as name, 
MD5(CONCAT(LOWER(REPLACE(MAX(l.username),' ','_')),'@2020')) as password,
CONCAT('{',
'"\n Position:"',MAX(e.Position),
'"\n email:"',MAX(e.Email),
'"}') as comment,
MAX(e.HandPhone) as phone,
MAX(e.WorkPhone) as phone2,
MAX(e.LastName) as realname,
MAX(e.FirstName) as firstname,
(CASE WHEN (SELECT id FROM glpi_tj.`glpi_entities` as ll WHERE  FIND_IN_SET(MAX(e.LocationID),ll.old_ID)>0) is not null then (SELECT id FROM glpi_tj.`glpi_entities` as lll WHERE  FIND_IN_SET(MAX(e.LocationID),lll.old_ID)>0) else '0' END)as  entities_id,
MAX(EmployeeID) as old_ID
FROM  medis_old.login as l
RIGHT JOIN  medis_old.`employees` as e on  e.LoginID  =l.LoginID
GROUP BY LOWER(REPLACE(l.username,' ','_'))) as A;


-- email

INSERT INTO glpi_tj.`glpi_useremails` (email,users_id,is_default)
SELECT e.email,
(SELECT id FROM glpi_tj.`glpi_users` u WHERE ( e.EmployeeID COLLATE utf8_unicode_ci) = u.old_ID) as users_id,
'1' as is_default
FROM medis_old.`employees` as e
WHERE (SELECT id FROM glpi_tj.`glpi_users` u WHERE ( e.EmployeeID COLLATE utf8_unicode_ci) = u.old_ID) IS NOT NULL;

-- add profile
INSERT INTO glpi_tj.`glpi_profiles_users`( `users_id`, `profiles_id`, `entities_id`) 
SELECT 
(SELECT id FROM glpi_tj.`glpi_users` u WHERE ( e.EmployeeID COLLATE utf8_unicode_ci) = u.old_ID) as users_id,
 (case l.GroupID 
  WHEN 1 THEN '1' -- self-service
  WHEN 2 THEN '3' -- admin
  WHEN 3 THEN '4' -- super-admin
  END) AS profiles_id,
  '0' as entities_id
FROM  medis_old.login as l
LEFT JOIN  medis_old.`employees` as e on  e.LoginID  =l.LoginID
WHERE (SELECT id FROM glpi_tj.`glpi_users` u WHERE ( e.EmployeeID COLLATE utf8_unicode_ci) = u.old_ID) IS NOT NULL;

-- Supplier

ALTER TABLE glpi_tj.`glpi_suppliers` ADD COLUMN `old_ID` text DEFAULT NULL;
INSERT INTO glpi_tj.`glpi_suppliers`(`entities_id`, `name`,  `address`, `postcode`, `town`, `state`, `country`, `website`,  `phonenumber`, `comment`, `fax`, `email`,old_ID) 
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
FROM medis_old.`suppliers` as s
JOIN medis_old.contact as c on s.ContactID = c.ContactID
JOIN medis_old.countries co on c.CountryID = co.CountryID;


-- model
INSERT INTO glpi_tj.`glpi_states` (name, completename)
SELECT  AssetStatusDesc,AssetStatusDesc
FROM medis_old.`assetstatus` ;

-- category

ALTER TABLE glpi_tj.`glpi_plugin_openmedis_medicaldevicecategories` ADD COLUMN `old_ID` text DEFAULT NULL;


INSERT INTO `glpi_plugin_openmedis_medicaldevicecategories` ( `code`, `name`, `comment`, `plugin_openmedis_medicaldevicecategories_id`, `picture`, `level`, `old_ID`) 
SELECT  AssetCategoryNr,AssetCategoryName, '', 0, '', 0, AssetCategoryID
FROM medis_old.`assetcategory` ;

INSERT INTO `glpi_plugin_openmedis_medicaldevicecategories` ( `code`, `name`, `comment`, `plugin_openmedis_medicaldevicecategories_id`, `picture`, `level`, `old_ID`) 
SELECT  GenericAssetCode,GenericAssetName, '', AssetCategoryID, GenericPicture, 1, GenericAssetID
FROM medis_old.`assetgenericname` ;

-- models
INSERT INTO glpi_tj.`glpi_plugin_openmedis_medicaldevicemodels` (name)
SELECT A.name
FROM (SELECT  DISTINCT LOWER(Model) as name
FROM medis_old.`assets`) as A;

-- Utilization's
INSERT INTO glpi_tj.`glpi_plugin_openmedis_utilizations` (name)
SELECT  AssetUtilizationDesc
FROM medis_old.`assetutilization` ;

-- Asset
ALTER TABLE glpi_tj.`glpi_plugin_openmedis_medicaldevices` ADD COLUMN `old_ID` text DEFAULT NULL;

INSERT INTO glpi_tj.`glpi_plugin_openmedis_medicaldevices`( `old_ID`,`plugin_openmedis_medicaldevicemodels_id`,`plugin_openmedis_medicaldevicecategories_id`,`entities_id`,`locations_id`, `name`, `contact`, `contact_num`, `users_id_tech`, `groups_id_tech`, `comment`, `serial`, `otherserial`,   `plugin_openmedis_utilizations_id`, `brand`, `manufacturers_id`, `users_id`, `groups_id`, `states_id`)
SELECT `old_ID`,
(CASE WHEN `plugin_openmedis_medicaldevicemodels_id` IS  NULL THEN '0' ELSE `plugin_openmedis_medicaldevicemodels_id` END) ,
(CASE WHEN `plugin_openmedis_medicaldevicecategories_id` IS NULL THEN '0' ELSE `plugin_openmedis_medicaldevicecategories_id` END) ,
`entities_id`, `locations_id`, 
`name`, 
`contact`, 
`contact_num`, 
(CASE WHEN `users_id_tech` IS NULL THEN '0' ELSE `users_id_tech` END ), 
`groups_id_tech`, 
`comment`, 
`serial`, 
`otherserial`, 
(CASE WHEN`plugin_openmedis_utilizations_id` IS NULL THEN '0' ELSE `plugin_openmedis_utilizations_id` END) , 
`brand`, 
(CASE WHEN `manufacturers_id` IS NULL THEN '0' ELSE `manufacturers_id` END ), 
`users_id`, 
`groups_id`, 
(CASE WHEN `states_id` IS NULL THEN '0' ELSE `states_id` END )
FROM (
SELECT a.`AssetID` as old_ID, 
(SELECT id FROM glpi_tj.glpi_plugin_openmedis_medicaldevicemodels WHERE  name  = LOWER(Model) LIMIT 1)  as `plugin_openmedis_medicaldevicemodels_id`,
(SELECT id FROM glpi_tj.glpi_plugin_openmedis_medicaldevicecategories WHERE  old_ID  = a.GenericAssetID  LIMIT 1) as  plugin_openmedis_medicaldevicecategories_id, 
 COALESCE((SELECT entities_id FROM glpi_tj.`glpi_locations` as lle WHERE  FIND_IN_SET(LocationID,lle.old_ID COLLATE utf8_unicode_ci)>0  LIMIT 1),0)   as entities_id,
  COALESCE((SELECT id FROM glpi_tj.`glpi_locations` as lll WHERE  FIND_IN_SET(LocationID,lll.old_ID COLLATE utf8_unicode_ci)>0  LIMIT 1),0)   as locations_id,
a.`AssetFullName` as name,
a.ResponsiblePers as contact,
'' as contact_num,
(SELECT id FROM glpi_tj.glpi_users WHERE  old_ID  = EmployeeID COLLATE utf8_unicode_ci LIMIT 1 ) as users_id_tech,
'0' as groups_id_tech,
Notes as comment,
a.SerialNumber as serial,
a.InternalIventoryNumber as otherserial,

(SELECT id FROM glpi_tj.glpi_plugin_openmedis_utilizations WHERE  name  = au.AssetUtilizationDesc  LIMIT 1 )as plugin_openmedis_utilizations_id,
'' as brand,
(SELECT id FROM glpi_tj.glpi_manufacturers WHERE  old_ID  = ManufacturerID COLLATE utf8_unicode_ci LIMIT 1) as manufacturers_id,
'0' as users_id,
'0' as groups_id,
 (SELECT id FROM glpi_tj.glpi_states WHERE  name  = s.AssetStatusDesc  LIMIT 1) as states_id
FROM medis_old.`assets` as a
JOIN  medis_old.assetgenericname as ga ON a.GenericAssetID = ga.GenericAssetID 
JOIN medis_old.assetstatus as s ON a.AssetStatusID = s.AssetStatusID
join medis_old.assetutilization as au on au.AssetUtilizationID = a.AssetUtilizationID
) A;

--  Remaining fields ``, ``, ``, `AgentID`, ``, ``, ``, ``, ``, ``, 
-- `Year_installed`, `Lifetime`, ``, ``, ``, `WarrantyContractID`, 
-- `MaintenanceContract`, `MaintenanceContractNo`, `MaintenanceContractExpiry`, `WarrantyContractExp`, 
-- ``, ``, `DonorID`, `ServiceManual`, `OperatorsManual`, ``, `Picture`, 
-- `lastmodified`, `by_user`, `deleted`, `URL_Manual`, `MetrologyDocument`, `MetrologyDate`, `Metrology`, `` 

--- infocom 
INSERT INTO glpi_tj.`glpi_infocoms` (`items_id`,`itemtype`,`entities_id`,`buy_date`,
  `use_date`, `warranty_duration`, `warranty_info`, `suppliers_id` ,  `value`,
  `warranty_value` ,  `comment` ,  `warranty_date` ,  `decommission_date` )
SELECT 
DISTINCT md.id,
'pluginopenmedis_medicaldevices' as itemtype,
COALESCE((SELECT id FROM glpi_tj.`glpi_entities` as lll WHERE  FIND_IN_SET(LocationID,lll.old_ID COLLATE utf8_unicode_ci)>0  LIMIT 1),0)   as entities_id,
a.PurchaseDate,
a.InstallationDate,
0 as warranty_duration, 
a.WarrantyContractNotes as warranty_info ,
s.id as suppliers_id ,
COALESCE(a.PurchasePrice,0),
COALESCE(a.CurrentValue,0),
'' as comment, 
WarrantyContractExp as warranty_date , 
null as decommission_date 
FROM medis_old.`assets` as a 
JOIN glpi_tj.`glpi_plugin_openmedis_medicaldevices` as md ON md.old_ID COLLATE utf8_unicode_ci = a.AssetID
inner JOIN glpi_tj.`glpi_suppliers` as s ON s.old_ID COLLATE utf8_unicode_ci = a.SupplierID;
-- contract



-- tickets

-- consumable

-- cleanup 
ALTER TABLE glpi_tj.`glpi_locations` DROP COLUMN `old_ID`;
ALTER TABLE glpi_tj.`glpi_manufacturers` DROP COLUMN `old_ID`;
ALTER TABLE glpi_tj.`glpi_users` DROP COLUMN `old_ID`;
ALTER TABLE glpi_tj.`glpi_suppliers` DROP COLUMN `old_ID`;

ALTER TABLE glpi_tj.`glpi_plugin_openmedis_medicaldevices` DROP COLUMN `old_ID`;
ALTER TABLE glpi_tj.`glpi_plugin_openmedis_medicaldevicecategories` DROP COLUMN `old_ID`;
