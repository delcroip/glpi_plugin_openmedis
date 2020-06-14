# openMedis GLPI plugin

This module enable you to create and manage your Medical device
> * Medical device details (in addition to std gélpi device) : install date, Nomencalure.


## Traduction

This plugin will be managed on transiflex or lokalise


## development

I created this module because I wanted to phase out a medical Item asset management system (openMEDIS) to built the new version on the power of glpi

This is my first GLPI module, I found it diffictult to start because the example is not straight forward.

I will try to add comment to clarify the code, here my current understanding:

To build the assset (Medical device) I duplciated the peripherical code and db (Shown as "ASSET > Device" in glpi)

To build the new asset Item (ASSET > ASSET X > Component in GLPI) I duplicate the devicebattery code and db

## Dropdown

It seem that to create a simple dropdown one need to create 3 files
- 1 in the "inc" folder to define the class a subclass of  **CommonDropdown** (e.g peripheralmodel.class.php)
- 2 in the "front" folder: to instanciate the dropdown (e.g. peripheralmodel.php) and to define the edit form (e.g. peripheralmodel.form.php)

and a table:

``` sql
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
```

 **CommonTreeDropdown** class enable and structured tree, (e.g. softwarecategory.class.php)


``` sql
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
```

## asset

It seems that to create an assest 3 files are required:
- 1 class file in the "inc" folder a subclass of  **CommonDBTM**
- 2 files in the "front" folder:to instanciate the serach list (e.g. peripheral.php) and to define the edit form (e.g. peripheral.form.php)

plus a table:
``` sql
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
```


## item

It seems that to create an assest 4 files are required:
- 2 class file in the "inc" folder: on to define the asset-itme relationship , a subclass of **Item_Devices** an other to define the class itself ta subclass of  **CommonDevice**
- 2 files in the "front" folder:to instanciate the dropdown (e.g. peripheral.php) and to define the edit form (e.g. peripheral.form.php)

plus a table:
``` sql
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
```


## profile

It seems that to manage new right, one need to create class file in the inc folder,

a good example can be found here https://github.com/InfotelGLPI/racks/blob/master/inc/profile.class.php

for more information check this page https://glpi-developer-documentation.readthedocs.io/en/master/devapi/acl.html


## config

This files is required in the inc folder if there is some configuuration needed for the whole plugin

for the rack module the unit (metric of not) can be defined for the wholé plugin https://github.com/InfotelGLPI/racks/blob/master/inc/config.class.php


## hooks

Evnt based actions

for more infornation https://glpi-developer-documentation.readthedocs.io/en/master/plugins/requirements.html#hook-php

## setup

for more information https://glpi-developer-documentation.readthedocs.io/en/master/plugins/requirements.html#setup-php

## tips and trics 

https://glpi-developer-documentation.readthedocs.io/en/master/plugins/tips.html