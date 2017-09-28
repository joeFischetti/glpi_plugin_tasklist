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
	
	//Set the $runTasksString to be the list of tasks
	//
	$runTasksString = $selectedTaskListArray['list'];	

	//explode the string at the ++ so we're left with an array of strings
	//
	$runTasksArray = explode("++", $runTasksString);

	//Set the newTicketID to the ID of the ticket that we just created, and newTicketUser to the user.
	//
	$newTicketID = $newTicketArray['id'];
	$newTicketUser = $newTicketArray['users_id_recipient'];

	file_put_contents("/var/www/html/glpi/files/_log/tasklist", "New ticket created by user number:  $newTicketUser\n", FILE_APPEND);

	
	//For each task (string) in the array, generate an SQL query, and insert that row into the ticket
	// tasks table with the right ticketID	
	foreach($runTasksArray as $task){


		$task = trim($task);

		//We want to be able to handle tasks that are meant for certain groups, so in order to do that
		//	we need to check if the task has a group or user specified
		//
		//preg_match('/(\[.*\]\n)(.*)/', $task, $taskArray, PREG_OFFSET_CAPTURE);

		//$taskGroup = strpos($task, "Operations");
		$taskPos = preg_match('/\[.*\]/', $task, $taskArray);
	
		file_put_contents("/var/www/html/glpi/files/_log/tasklist", print_r($taskArray, true), FILE_APPEND);


		//If taskArray is null, that means there's no group assigned and we'll enter the task generically
		if($taskArray == null){	
	
			file_put_contents("/var/www/html/glpi/files/_log/tasklist", "Adding unassigned task\n", FILE_APPEND);
	
			$queryGroups = null;

			$query = "INSERT INTO `glpi_tickettasks` " .
	                        "(`tickets_id`, `users_id`, `content`, `date`) " .
        	                " VALUE ('" . $newTicketID . "', '" . $newTicketUser . "', '" . $task . "', NOW());";

		}

		//Else, there WAS a group assigned, and we'll match it to a group and create that task
		else{

			


			//If the task includes a tag [groupName], it'll be in $taskArray[1][0]
			$taskGroup = $taskArray[0];
			$taskGroup = substr($taskGroup, 1, strlen($taskGroup) - 2);

			//And the rest of the text for the task will begin at $taskArray[2][1], so we substr it
                        $taskText = substr($task, strlen($taskGroup) + 2);


			file_put_contents("/var/www/html/glpi/files/_log/tasklist", "Adding group task\n", FILE_APPEND);
			file_put_contents("/var/www/html/glpi/files/_log/tasklist", "Group name:  $taskGroup\n", FILE_APPEND);
			file_put_contents("/var/www/html/glpi/files/_log/tasklist", "Text:  $taskText\n", FILE_APPEND);
		

			//Create a query to get the group ID number
			$matchGroup = "SELECT CASE WHEN (" .
                                        "SELECT (SELECT `id` FROM `glpi_groups` " .
                                        "WHERE name = '" . $taskGroup . "')) IS NOT NULL THEN(" .
                                        "SELECT `id` FROM `glpi_groups` " .
                                        "WHERE name = '" . $taskGroup . "') ELSE '0' END AS groupID";

			$groupIDResponse = $DB->request($matchGroup);

			if($groupIDResponse->numrows() == 0){
				//It should never....
				$groupID = 0;
			}

			else{
				$groupID = $groupIDResponse->next();
				$groupID = $groupID['groupID'];
			}

//			$queryCASE = "SELECT CASE WHEN (" .
//					"SELECT (SELECT `id` FROM `glpi_groups` " .
//					"WHERE name = '" . $taskGroup . "')) IS NOT NULL THEN(" .
//					"SELECT `id` FROM `glpi_groups` " . 
//					"WHERE name = '" . $taskGroup . "') ELSE '0' END";

			//Add the task group as a watcher to the ticket, if it wasn't 0
			if($groupID != 0){

        	                $queryGroups = "INSERT INTO `glpi_groups_tickets` " .
	                                        "(`tickets_id`, `groups_id`, `type`) " .
                	                        " VALUE ('" . $newTicketID . "', (" . $groupID . "), '2');";
			}

			$query = "INSERT INTO `glpi_tickettasks` " . 
	        	        "(`tickets_id`, `users_id`, `content`, `date`, `groups_id_tech`) " .
        			" VALUE ('" . $newTicketID . "', '" . $newTicketUser . "', '" . $taskText . "', NOW(), '" . $groupID . "');";
		}
		
		//If a group was specified, run that query
		if($queryGroups != null){
			file_put_contents("/var/www/html/glpi/files/_log/tasklist", "Assigning group to ticket", FILE_APPEND);
			$DB->query($queryGroups);
		}

		//Run the query to add the ticket task
		$DB->query($query);
	}
}
