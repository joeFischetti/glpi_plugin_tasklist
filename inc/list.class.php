<?php

class PluginTasklistList extends CommonDBTM {

	static $rightname = 'config';

	static function canCreate() {
		return self::canUpdate();
	}

//	static function titleList() {
//		echo "<center><input type='button' class='submit' value='&nbsp;".
//		            __("Regenerate container files", "tasklist")."&nbsp;'
//		            onclick='location.href=\"regenerate_files.php\"' /></center>";
//	}

	function defineTabs($options=array()){
		$ong = array();
		$this->addDefaultFormTab($ong);
		$this->addStandardTab('PluginsTasklistList', $ong, $options);

		return $ong;
	}

	static function getTypeName($nb = 0) {
		return __("List of Tasks", "tasklist");
	}

	function getSearchOptions() {
	$tab = array();

	$tab[1]['table']         = $this->getTable();
	$tab[1]['field']         = 'name';
	$tab[1]['name']          = __("Name");
	//$tab[1]['datatype']      = 'itemlink';
	//$tab[1]['itemlink_type'] = $this->getType();
	$tab[1]['massiveaction'] = false;

	$tab[2]['table']         = $this->getTable();
	$tab[2]['field']         = 'enabled';
	$tab[2]['name']          = __("Enabled");
	$tab[2]['massiveaction'] = false;
/*
      $tab[3]['table']         = $this->getTable();
      $tab[3]['field']         = 'itemtypes';
      $tab[3]['name']          = __("Associated item type");
      $tab[3]['datatype']       = 'specific';
      $tab[3]['massiveaction'] = false;
      $tab[3]['nosearch']      = true;

      $tab[4]['table']         = $this->getTable();
      $tab[4]['field']         = 'type';
      $tab[4]['name']          = __("Type");
      $tab[4]['searchtype']    = array('equals', 'notequals');
      $tab[4]['massiveaction'] = false;

      $tab[5]['table']         = $this->getTable();
      $tab[5]['field']         = 'is_active';
      $tab[5]['name']          = __("Active");
      $tab[5]['datatype']      = 'bool';
      $tab[5]['searchtype']    = array('equals', 'notequals');

      $tab[6]['table']         = 'glpi_entities';
      $tab[6]['field']         = 'completename';
      $tab[6]['name']          = __("Entity");
      $tab[6]['massiveaction'] = false;
      $tab[6]['datatype']      = 'dropdown';

      $tab[7]['table']         = $this->getTable();
      $tab[7]['field']         = 'is_recursive';
      $tab[7]['name']          = __("Child entities");
      $tab[7]['massiveaction'] = false;
      $tab[7]['datatype']      = 'bool';

      $tab[8]['table']         = $this->getTable();
      $tab[8]['field']         = 'id';
      $tab[8]['name']          = __("ID");
      $tab[8]['datatype']      = 'number';
      $tab[8]['massiveaction'] = false;
*/
	return $tab;
	}


        public function showForm($ID, $options = array()) {
		global $CFG_GLPI;

	        $this->initForm($ID, $options);
		$this->showFormHeader($options);

		if (!isset($options['display'])) {
	        	 //display per default
	         	$options['display'] = true;
		}

		$params = $options;
	      	//do not display called elements per default; they'll be displayed or returned here
	      	$params['display'] = false;

	      	echo "<tr class='tab_bg_1'>";

		echo '<td>' . __("List name:  ", "tasklist") . '</td>';
		echo  '<td>';

      		if (!$ID) {
			echo '<input name=\'name\' value=\'New List Name?\'>';
			echo "<br><br><br>";
			
      		} else {
			echo $this->fields["name"];
			echo '<input type=\'hidden\' name=\'name\' value=\'' . $this->fields["name"] . '\'>';
			echo "<br><br><br>";
      		}
		
		echo '</td></tr>';
		
		echo '<tr><td>';
		echo __("Enable List:  ", "tasklist") . '</td>';

		echo '<td>';

		//Show the checkbox to enable/disable the list
		//
		Html::showCheckbox(array(
					"name" => "enabled", 
					"checked" => $this->fields["enabled"],
					"zero_on_empty" => "1"));

		echo '</td>';

		echo '</tr>';

		echo "<tr class='tab_bg_1'>";

		echo "<td>" . __("Tasks ('++' separated)") . "&nbsp;: </td>";
		echo "<td><textarea cols='50' rows='45' name='list' >" . $this->fields["list"] . "</textarea></td>";

		echo "</tr>";
		
	      	$this->showFormButtons($params);

	}

}
