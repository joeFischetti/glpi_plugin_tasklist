<?php
include ("../../../inc/includes.php");

// Check if plugin is activated...
$plugin = new Plugin();
if (!$plugin->isInstalled('tasklist') || !$plugin->isActivated('tasklist')) {
   Html::displayNotFoundError();
}

$object = new PluginTasklistList();

if (empty($_GET["id"])) {
   $_GET["id"] = "";
}

if (isset($_POST['add'])) {
   //Check CREATE ACL
   $object->check(-1, CREATE, $_POST);
   //Do object creation
   $newid = $object->add($_POST);
   //Redirect to newly created object form
   Html::redirect("{$CFG_GLPI['root_doc']}/plugins/tasklist/front/list.form.php?id=$newid");
} 

else if (isset($_POST['update'])) {
   //Check UPDATE ACL
   $object->check($_POST['id'], UPDATE);
   //Do object update
   $object->update($_POST);
   //Redirect to object form
   Html::back();
} 

else if (isset($_POST['delete'])) {
   //Check DELETE ACL
   $object->check($_POST['id'], DELETE);
   //Put object in dustbin
   $object->delete($_POST);
   //Redirect to objects list
   $object->redirectToList();
} 

else if (isset($_POST['purge'])) {
   //Check PURGE ACL
   $object->check($_POST['id'], PURGE);
   //Do object purge
   $object->delete($_POST, 1);
   //Redirect to objects list
   Html::redirect("{$CFG_GLPI['root_doc']}/plugins/tasklist/front/list.php");
} 

else {
   //per default, display object
	Html::header(
		__('Custom Task List plugin', 'tasklist'),
		$_SERVER['PHP_SELF'],
		'config',
		'PluginTasklistMenu',
		'list'
	);

	$withtemplate = (isset($_GET['withtemplate']) ? $_GET['withtemplate'] : 0);
	$object->display(
		[
		'id'           => $_GET['id'],
		'withtemplate' => $withtemplate
		]
	);
	
	Html::footer();
}
