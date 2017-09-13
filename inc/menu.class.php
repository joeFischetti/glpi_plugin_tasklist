<?php
class PluginTasklistMenu extends CommonGLPI {
	static $rightname = 'entity';

   	static function getMenuName() {
		return __("Tasklist List Management", "tasklist");
	}

   	static function getMenuContent() {

      		if (!Session::haveRight('entity', READ)) {
        	return;
      		}


      		$front_tasklist = "/plugins/tasklist/front";
      		$menu = array();
      		$menu['title'] = self::getMenuName();
      		$menu['page']  = "$front_tasklist/list.php";

	      	$menu['links']['search'] = "/plugins/tasklist/front/list.php";
	      
        	$menu['links']['add'] = "/plugins/tasklist/front/list.form.php";

	     	return $menu;
	}
}
