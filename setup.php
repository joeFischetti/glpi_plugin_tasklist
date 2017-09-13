<?php

define('TASKLIST_VERSION', '1.0.2');


function plugin_init_tasklist() {
	global $PLUGIN_HOOKS;

	$PLUGIN_HOOKS['csrf_compliant']['tasklist'] = true;
	$PLUGIN_HOOKS['item_add']['tasklist'] = [
			'Ticket'	=> 	'tasklist_addticket_called'];
	$PLUGIN_HOOKS['config_page']['tasklist'] = 'front/list.php';

	$PLUGIN_HOOKS["menu_toadd"]['tasklist'] = array('config'  => 'PluginTasklistMenu');
}

function plugin_version_tasklist() {
	return [
      		'name'           => 'Task list generator',
      		'version'        => TASKLIST_VERSION,
      		'author'         => 'Joe Fischetti',
      		'license'        => 'GPLv2',
      		'homepage'       => 'http://github.com/joefischetti/',
      		'requirements'   => ['glpi'   => ['min' => '9.1']]
   	];
}


function plugin_tasklist_check_prerequisites() {
	return true;
}


function plugin_tasklist_check_config($verbose = false) {
   	if (true) { // Your configuration check
      		return true;
   	}

   	if ($verbose) {
      		echo "Installed, but not configured";
   	}
   
	return false;
}
