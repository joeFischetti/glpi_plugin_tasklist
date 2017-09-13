<?php

function plugin_tasklist_install() {
	global $DB;

	if (!TableExists('glpi_plugin_tasklist_lists')) {
		$query = "CREATE TABLE `glpi_plugin_tasklist_lists` (
			`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
			`name` VARCHAR(255) NOT NULL,
			`list` TEXT NOT NULL,
			`enabled` BOOLEAN NOT NULL DEFAULT FALSE
			) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";


		$DB->query($query);
	}
	return true;
}

function plugin_tasklist_uninstall() {
	global $DB;  

 
	$tables = ['lists'];

	foreach ($tables as $table) {
		$tablename = 'glpi_plugin_tasklist_' . $table;

		if (TableExists($tablename)) {
			$DB->query("DROP TABLE `$tablename`");
		}
	}

	return true;
}

function tasklist_addticket_called(Ticket $newTicket){
	
	//Global access to the database
	//
	global $DB;

	//Query the database for the ticket number of the ticket we just created
	//
	$newTicketRow = $DB->request("glpi_tickets", "id = ". $newTicket->getID());	
	

	//If for whatever reason the query we just ran doesn't return 1 (database problem?), exit out
	//	
	if($newTicketRow->numrows() != 1){
		return;
	}

	//Set the newTicketArray to the one and only returned row
        $newTicketArray = $newTicketRow->next();

	//The itilcategories_id column is the category that was selected... if the category matches one of the categories that
	//	We've specified task lists for, then we'll proceed

	//Get the category from the database
	//
	$category = $DB->request("glpi_itilcategories", "id = ". $newTicketArray['itilcategories_id']);

	//If it failed (or no category was selected), exit
	//
	if($category->numrows() != 1){
		file_put_contents("/var/www/html/glpi/files/_log/tasklist", "No Category Selected\n", FILE_APPEND);
		return;
	}
	

	//Set the selectedCategoryArray to the one and only returned row
	$selectedCategoryArray = $category->next();

	//Write something to one of the log files
	file_put_contents("/var/www/html/glpi/files/_log/tasklist", "Selected Category:  " . $selectedCategoryArray['name'] . "\n", FILE_APPEND);

	//Query the database for a matching task list
	$taskList = $DB->request("glpi_plugin_tasklist_lists", "name = '" . $selectedCategoryArray['name']."'");	
	
	//If one row didn't come back (0 or 2+), write to the log and return
	if($taskList->numrows() != 1){
		file_put_contents("/var/www/html/glpi/files/_log/tasklist", "Couldn't match category\n", FILE_APPEND);
		file_put_contents("/var/www/html/glpi/files/_log/tasklist", "Number of rows returned:  " . $taskList->numrows() . "\n", FILE_APPEND);
		return;
	}

	//Set the selected task array to the row that was returned
	$selectedTaskListArray = $taskList->next();
	
	//If the task list isn't enabled, return
	if($selectedTaskListArray['enabled'] != 1){
		file_put_contents("/var/www/html/glpi/files/_log/tasklist", "Task is disabled\n", FILE_APPEND);
		return;
	}
	
	//Set the $runTasks variable to be the name of the category that was chosen
	//
	$runTasksString = "\n" . $selectedTaskListArray['list'];	

	//explode the string at the ++ so we're left with an array of strings
	//
	$runTasksArray = explode("++", $runTasksString);

	//Set the newTicketID to the ID of the ticket that we just created
	//
	$newTicketID = $newTicketArray['id'];

	
	//For each task (string) in the array, generate an SQL query, and insert that row into the ticket
	// tasks table with the right ticketID	
	foreach($runTasksArray as $task){
	
		$query = "INSERT INTO `glpi_tickettasks` " . 
	                "(`tickets_id`, `content`, `date`) " .
        		" VALUE ('" . $newTicketID . "', '" . $task . "', NOW());";

		$DB->query($query);
	}
}
