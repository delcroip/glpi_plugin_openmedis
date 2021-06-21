# openMedis GLPI plugin

This module enable you to create and manage your Medical device
> * Medical device details (in addition to std gélpi device) : install date, Nomencalure.

## Getting started

#. [Install project-GLPI](https://wiki.glpi-project.org/doku.php?id=en:install)

#. Configure project-GLPI:

	*. Location, __Setup > Dropdowns > Common > Locaiton__
	
	*. Users, __Administration > Users__
	
	*. Groups __Administration > Groups__
	
#. [Deploy the plugin](https://wiki.glpi-project.org/doku.php?id=en:plugins)

#. Configure the HTM Dropdowns in __Setup > Dropdowns__

	The Dropdowns, as the name suggest are all the list from which the user have to select a row
	
	*. __Health technologies management > Medical device categories (e.g. UMDS,GMDN)__ with the classification you want for your medical device
	
	*. __Health technologies management > Medical Devices models__ with the list of medical model you own
	
	*. __Health technologies management > Medical Devices Utilisations__ with the utilisation status you want to have (in use, ....)
	
	*. __Health technologies management > Medical Accessories models__ Not used yet
	
	*. __Health technologies management > Medical Accessories types__ Not used yet
	
#. Configure the Dropdowns in __Administration > Profiles > Select the profile__ 

	The [profile](https://wiki.glpi-project.org/doku.php?id=en:manual:admin:7_administration&s[]=profile#profiles) are use to define a set of rules for user, such as the interface, the access to asset  etc 
	
	*. __Assistance > Association > Associable items to a ticket__ add **Medical Device** and remove the unwanted kind of asset, this enable/disable the possibility to create a ticket on an asset types
	
	*. __Health Technology__ edit the rights you want for that profile 

#. create Entities
	
	Create the entity for the region / district and health facilities

#. Create location 
	
	Create location to document the wards, floors, rooms ... for each HF (not mandatory)
	
#. Start adding your Medical device

#. Medical componement
	
	* Start adding medical component type (if any), 
	* add the medical device model that are compatible with the componment 
	* then you can add the componenent to the medical device compatible.

#. Medical consumable

	* Start adding medical consomable type, 
	* add the medical device model that are compatible with the consomables and 
	* then you can add the consomables (if any) to the medical device compatible.

## Traduction

This plugin will be managed on transiflex or lokalise


## development

  - dedicate reports