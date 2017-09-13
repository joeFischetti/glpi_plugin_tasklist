<?php

class PluginTasklistList extends CommonDBTM {

	static $rightname = 'config';

	static function canCreate() {
		return self::canUpdate();
	}

	function defineTabs($options=array()){
		$ong = array();
		$this->addDefaultFormTab($ong);
		$this->addStandardTab('PluginsTasklistList', $ong, $options);

		return $ong;
	}

	static function getTypeName($nb = 0) {
		return __("List of Tasks", "tasklist");
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
