# openMedis GLPI plugin

This module enable you to create and manage your Medical device
> * Medical device details (in addition to std gélpi device) : install date, Nomencalure.


## Getting started

1. [Install project-GLPI](https://wiki.glpi-project.org/doku.php?id=en:install)

	before GLPI 10, a patch need to be applied to make the medical accessories working (without this patch GLPI plugins cannot add component type)

	support medical accessories: https://patch-diff.githubusercontent.com/raw/glpi-project/glpi/pull/8280.patch
	ajax to filter medical device categories based on parent: https://patch-diff.githubusercontent.com/raw/glpi-project/glpi/pull/9429.patch

	before 9.5.6 patches

	administrative report: https://patch-diff.githubusercontent.com/raw/glpi-project/glpi/pull/9375.patch
	other administrative report: https://patch-diff.githubusercontent.com/raw/glpi-project/glpi/pull/9377.patch
	contract report: https://patch-diff.githubusercontent.com/raw/glpi-project/glpi/pull/9402.patch
	default report: https://patch-diff.githubusercontent.com/raw/glpi-project/glpi/pull/9403.patch
	




	then copy it on glpi/ folder

	then apply it: 

	```
		patch -p1 < 8280.patch
		patch -p1 < 9375.patch
		patch -p1 < 9377.patch
		patch -p1 < 9402.patch
		patch -p1 < 9403.patch
	```

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
	
	*. __Health technologies management > Medical Accessories types__
	
5. Configure the Dropdowns in __Administration > Profiles > Select the profile__ 

	The [profile](https://wiki.glpi-project.org/doku.php?id=en:manual:admin:7_administration&s[]=profile#profiles) are use to define a set of rules for user, such as the interface, the access to asset  etc 
	
	*. __Assistance > Association > Associable items to a ticket__ add **Medical Device** and remove the unwanted kind of asset, this enable/disable the possibility to create a ticket on an asset types
	
	*. __Health Technology__ edit the rights you want for that profile 
	
6. Start adding your Medical device

7. Start adding medical component (if any)

8. Start adding medical consomable type and consomables (if any)

9. to use the custom report, the report module must be installed and access must be givent to the openMedis specific reports (group options available if the report module have the PluginOpenmedisToggleCriteria class)

## Standard glpi information

  in order to use properly the service support of GLPI there is elements to configure

  ### Ticket template

  Ticket template are created and manage from the ticket page by clicking on the 3 layer icon

  - it will enable to define the mandatory fields.
  - it will enable to prefill fields, e.g write the content Title or description of the ticket to help the user providing the right information
  - task template can be added (task template are configure in the dropdown page)

  ### Incident / request / change / problem category

  - can trigger the use of a template

  ### planification

  once a task is added to a ticket (via template or created on the ticket manualy), an intervention can be planned by editing the task (option not avaiable upon task creation)
## Traduction

https://app.lokalise.com/public/6803907760dcb8c3a08649.77618538/

Once the po files are updated, on Linux system you can execute in the plugin repository
```
../../vendor/bin/robo compile_locales
```



## development

  - documentation for calibration / checklist (task ?) / intervention report