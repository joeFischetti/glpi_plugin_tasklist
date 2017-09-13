<?php
include ("../../../inc/includes.php");

// Check if plugin is activated...
$plugin = new Plugin();
if (!$plugin->isInstalled('tasklist') || !$plugin->isActivated('tasklist')) {
   Html::displayNotFoundError();
}

//Add page header
Html::header(
      __('Custom Task List plugin', 'tasklist'),
      $_SERVER['PHP_SELF'],
      'config',
      'PluginTasklistMenu',
      'list'
);

Session::checkRight('entity', READ);

//PluginTasklistList::titleList();
Search::show('PluginTasklistList');

Html::footer();


