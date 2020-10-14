# openMedis GLPI plugin

This module enable you to create and manage your Medical device
> * Medical device details (in addition to std gélpi device) : install date, Nomencalure.

## Getting started

1. [Install project-GLPI](https://wiki.glpi-project.org/doku.php?id=en:install)
2. Configure project-GLPI:
	*. Location, __Setup > Dropdowns > Common > Locaiton__
	*. Users, __Administration > Users__
	*. Groups __Administration > Groups__
3. [Deploy the plugin](https://wiki.glpi-project.org/doku.php?id=en:plugins)
4. Configure the HTM Dropdowns in __Setup > Dropdowns__
	The Dropdowns, as the name suggest are all the list from which the user have to select a row
	*. __Health technologies management > Medical device categories (e.g. UMDS,GMDN)__ with the classification you want for your medical device
	*. __Health technologies management > Medical Devices models__ with the list of medical model you own
    *. __Health technologies management > Medical Devices Utilisations__ with the utilisation status you want to have (in use, ....)
	*. __Health technologies management > Medical Accessories models__ Not used yet
	*. __Health technologies management > Medical Accessories types__ Not used yet
5. Configure the Dropdowns in __Administration > Profiles > Select the profile__ 
	The [profile](https://wiki.glpi-project.org/doku.php?id=en:manual:admin:7_administration&s[]=profile#profiles) are use to define a set of rules for user, such as the interface, the access to asset  etc 
	*. __Assistance > Association > Associable items to a ticket__ add **Medical Device** and remove the unwanted kind of asset, this enable/disable the possibility to create a ticket on an asset types
	*. __Health Technology__ edit the rights you want for that profile 
6. Start adding your Medical device

## Traduction

This plugin will be managed on transiflex or lokalise


## development

I created this module because I wanted to phase out a medical Item asset management system (openMEDIS) to built the new version on the power of glpi

This is my first GLPI module, I found it diffictult to start because the example is not straight forward.

I will try to add comment to clarify the code, here my current understanding:

until now the key is the nomenclature, to have the module working it is a must have


All class MUST start with PluginPluginnameObject : don't use a capital letter in the middle of the Pluginname or it will mess with GLPI (it will interpret PluginNameObject as NameObject part of the "Plugin" plugin)

Follow to the letter this page:
https://glpi-developer-documentation.readthedocs.io/en/master/devapi/database/dbmodel.html?highlight=name#naming-conventions


https://glpi-developer-documentation.readthedocs.io/en/master/codingstandards.html#variables-and-constants


To build the assset (Medical device) I duplciated the peripherical code and db (Shown as "ASSET > Device" in glpi)

To build the new asset Item (ASSET > ASSET X > Component in GLPI) I duplicate the medicalaccessories code and db

## Dropdown

It seem that to create a simple dropdown one need to create 3 files
- 1 in the "inc" folder to define the class a subclass of  **CommonDropdown** (e.g medicaldevicemodel.class.php)
- 2 in the "front" folder: to instanciate the dropdown (e.g. medicaldevicemodel.php) and to define the edit form (e.g. medicaldevicemodel.form.php)


and a table:

``` sql
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
```

 **CommonTreeDropdown** class enable and structured tree, (e.g. softwarecategory.class.php)


``` sql
DROP TABLE IF EXISTS `glpi_plugin_openmedis_medicalaccessories_items` ;
CREATE TABLE  `glpi_plugin_openmedis_medicalaccessories_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `items_id` int(11) NOT NULL DEFAULT '0',
  `itemtype` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `plugin_openmedis_medicalaccessories_id` int(11) NOT NULL DEFAULT '0',
  `is_deleted` tinyint(1) NOT NULL DEFAULT '0',
  `is_dynamic` tinyint(1) NOT NULL DEFAULT '0',
  `entities_id` int(11) NOT NULL DEFAULT '0',
  `is_recursive` tinyint(1) NOT NULL DEFAULT '0',
  `serial` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `otherserial` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `locations_id` int(11) NOT NULL DEFAULT '0',
  `states_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `plugin_openmedis_medicaldevice_id` (`items_id`),
  KEY `plugin_openmedis_medicalaccessories_id` (`plugin_openmedis_medicalaccessories_id`),
  KEY `is_deleted` (`is_deleted`),
  KEY `is_dynamic` (`is_dynamic`),
  KEY `entities_id` (`entities_id`),
  KEY `is_recursive` (`is_recursive`),
  KEY `serial` (`serial`),
  KEY `item` (`itemtype`,`items_id`),
  KEY `otherserial` (`otherserial`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
```

## asset

https://glpi-developer-documentation.readthedocs.io/en/master/plugins/objects.html#add-a-front-for-my-object-crud

It seems that to create an assest 3 files are required:
- 1 class file in the "inc" folder a subclass of  **CommonDBTM**
- 2 files in the "front" folder:to instanciate the serach list (e.g. medicaldevice.php) and to define the edit form (e.g. medicaldevice.form.php)

plus a table:
``` sql
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
  `plugin_openmedis_medicaldevicecategories_id` int(11) NOT NULL DEFAULT '0',
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
  KEY `plugin_openmedis_medicaldevicemodels_id` (`plugin_openmedis_medicaldevicemodels_id`),
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
```


## item (not yet working)

It seems that to create an assest 4 files are required:
- 2 class file in the "inc" folder: on to define the asset-itme relationship , a subclass of **Item_Devices** an other to define the class itself ta subclass of  **CommonDevice**
- 2 files in the "front" folder:to instanciate the dropdown (e.g. medicaldevice.php) and to define the edit form (e.g. medicaldevice.form.php)

plus a table:
``` sql
DROP TABLE IF EXISTS `glpi_plugin_openmedis_medicalaccessories_items` ;
CREATE TABLE  `glpi_plugin_openmedis_medicalaccessories_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `items_id` int(11) NOT NULL DEFAULT '0',
  `itemtype` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `plugin_openmedis_medicalaccessories_id` int(11) NOT NULL DEFAULT '0',
  `is_deleted` tinyint(1) NOT NULL DEFAULT '0',
  `is_dynamic` tinyint(1) NOT NULL DEFAULT '0',
  `entities_id` int(11) NOT NULL DEFAULT '0',
  `is_recursive` tinyint(1) NOT NULL DEFAULT '0',
  `serial` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `otherserial` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `locations_id` int(11) NOT NULL DEFAULT '0',
  `states_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `plugin_openmedis_medicaldevice_id` (`items_id`),
  KEY `plugin_openmedis_medicalaccessories_id` (`plugin_openmedis_medicalaccessories_id`),
  KEY `is_deleted` (`is_deleted`),
  KEY `is_dynamic` (`is_dynamic`),
  KEY `entities_id` (`entities_id`),
  KEY `is_recursive` (`is_recursive`),
  KEY `serial` (`serial`),
  KEY `item` (`itemtype`,`items_id`),
  KEY `otherserial` (`otherserial`)
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

the hook page define the hook that can be called from elswhere but mostl of the plugin hook are called from the setup page

for more infornation https://glpi-developer-documentation.readthedocs.io/en/master/plugins/requirements.html#hook-php

## setup


for more information https://glpi-developer-documentation.readthedocs.io/en/master/plugins/requirements.html#setup-php

## tips and trics 

https://glpi-developer-documentation.readthedocs.io/en/master/plugins/tips.html
