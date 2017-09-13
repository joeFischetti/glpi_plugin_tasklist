<?php

define('TASKLIST_VERSION', '1.0.2');

/**
 * Init the hooks of the plugins - Needed
 *
 * @return void
 */
function plugin_init_tasklist() {
   global $PLUGIN_HOOKS;

   //required!
	$PLUGIN_HOOKS['csrf_compliant']['tasklist'] = true;
	$PLUGIN_HOOKS['item_add']['tasklist'] = [
			'Ticket'	=> 	'tasklist_addticket_called'];
	$PLUGIN_HOOKS['config_page']['tasklist'] = 'front/list.php';

	$PLUGIN_HOOKS["menu_toadd"]['tasklist'] = array('config'  => 'PluginTasklistMenu');
	
   //some code here, like call to Plugin::registerClass(), populating PLUGIN_HOOKS, ...
}

/**
 * Get the name and the version of the plugin - Needed
 *
 * @return array
 */
function plugin_version_tasklist() {
   return [
      'name'           => 'Task list generator',
      'version'        => TASKLIST_VERSION,
      'author'         => 'Joe Fischetti',
      'license'        => 'GPLv2',
      'homepage'       => 'http://github.com/joefischetti/',
      'requirements'   => [
         'glpi'   => [
            'min' => '9.1'
         ]
      ]
   ];
}

/**
 * Optional : check prerequisites before install : may print errors or add to message after redirect
 *
 * @return boolean
 */
function plugin_tasklist_check_prerequisites() {
   //do what the checks you want
   return true;
}

/**
 * Check configuration process for plugin : need to return true if succeeded
 * Can display a message only if failure and $verbose is true
 *
 * @param boolean $verbose Enable verbosity. Default to false
 *
 * @return boolean
 */
function plugin_tasklist_check_config($verbose = false) {
   if (true) { // Your configuration check
      return true;
   }

   if ($verbose) {
      echo "Installed, but not configured";
   }
   return false;
}
