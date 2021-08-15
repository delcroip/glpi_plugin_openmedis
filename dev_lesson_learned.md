# GPLI plugin Development lesson learned

Glpi rely a lot on the database schema a naming, therefore tables or class name that are compliant will generate bugs or will limits the acces to glpi functions

Table should have this form: glpi_plugin_[pluginname (all lower and without _)]_[elementname (all lower)]
The class should have this form Plugin[Pluginname(only first letter in CAP)][ElementName (singular form, start with CAP, may have CAP in it)]

## Asset

### Schema fields
when creating an asset there is fields that must be created
ticket_tco
is_deleted
name
id
...

if you want to have devices per entity
is_recursive
entity_id


### other schema

<AssetClass>Type is a std class expected by some reports, alias works when the class have a differnet name


## component

add the "component/ itemDevice" types you want while registering the function 

eg. pluginopenmedisitemdevicemedicalaccessory_types

and "$this->addStandardTab('Item_Devices', $ong, $options);" in defineTabs function

## consumable

you may use the std consomable or create your own class then, in both cases you will have to add the left menu

$this->addStandardTab('PluginOpenmedisMedicalConsumable', $ong, $options);



## reservation

use "reservation_types" to activate the reservation 

and "$this->addStandardTab('Reservation', $ong, $options);" in defineTabs function

Do NOT add the  "planning_types", this type is reserved to planning class

## class function

getIcon(): Define the icon used in UI (awsome font) 
getTypeName: return the string to be displayed for the asset type
getTypes($all = false): Get the allowed component type (to be confirmed)
getType(): return the type of the asset
registerType($type): For other plugins, add a type to the linkable types
getLinkedItems: get linked compenent, all components are in "glpi_computers_items" but the itemtype specify to which asset () use get Type
rawSearchOptions(): used for the list search in the asset pages
getSpecificMassiveActions($checkitem = null): specifiy massive actions
showForm($ID, $options = []): template for the UI
prepareInputForAdd() unction called before the creation of an item, can be usefule when cloning
post_addItem() function called after the creation of an item, can be usefule when cloning
defineTabs($options = []): define the tab to be showed (to be used coherently with _types registered)
redirectToList(): link for the asset list page

## financial information

in order to activate "infocom_types" on asset, the fields "ticket_tco" must be part of the database table

then just add "infocom_types" when regestering your class in setup.php

and "$this->addStandardTab('Infocom', $ong, $options);" in defineTabs function