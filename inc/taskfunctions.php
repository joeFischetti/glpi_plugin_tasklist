<?php
define('STOCKUSER','175');
define('GLPI3','http://10.10.5.252/glpi/apirest.php/');
define('AUTHTOKEN', 'user_token 2cmkryd5n2bxn45dytpm9w2g4rmbkmf9kiv84vjf');
define('APPTOKEN', 'degdksfw7jjsue3x2ad1ingftfjcf4tmowj2vtjj');
define('AUTHHEADER', 'Authorization:  user_token 2cmkryd5n2bxn45dytpm9w2g4rmbkmf9kiv84vjf');
define('APPHEADER', 'App-Token:  degdksfw7jjsue3x2ad1ingftfjcf4tmowj2vtjj');

//Set debug mode to 1 for on, and 0 for off
define('DEBUG', '1');


PluginAddTaskList::initSession(GLPI3);

$ticketNumber = 1;

if(PluginAddTaskList::getTaskCount(GLPI3,$ticketNumber) > 0){
	print "<pre>";
	print "Ticket already has tasks associated";
	print_r(PluginAddTaskList::getItemFullInfo(GLPI3,"ticket",1));
	print "</pre>";
}

else{
	$newTask = array('tickets_id' => $ticketNumber,
			'content' => "Another task");

	$newTask2 = array('tickets_id' => $ticketNumber,
			'content' => "Do more stuff");

	PluginAddTaskList::addItem(GLPI3, "tickettask", $newTask);
	PluginAddTaskList::addItem(GLPI3, "tickettask", $newTask2);

	print "<pre>";
	print_r(PluginAddTaskList::getTicketTasks(GLPI3,1));
	print "</pre>";
}

PluginAddTaskList::killSession(GLPI3);

class PluginAddTaskList {

	public static $options = array(
                CURLOPT_RETURNTRANSFER  => true,          // return web page
                CURLOPT_HEADER          => false,  // don't return headers
                CURLOPT_FOLLOWLOCATION  => true,          // follow redirects
                CURLOPT_MAXREDIRS       => 10,    // stop after 10 redirects
                CURLOPT_ENCODING        => "",    // handle compressed
                CURLOPT_USERAGENT       => "test", // name of client
                CURLOPT_AUTOREFERER     => true,          // set referrer on redirect
                CURLOPT_CONNECTTIMEOUT  => 120,   // time-out on connect
                CURLOPT_TIMEOUT         => 120,   // time-out on response
                CURLOPT_HTTPHEADER      => array('Content-Type: application/json',
                        AUTHHEADER,
                        APPHEADER,),
        );


	static public function printDebug($input){
		if(DEBUG){
			error_log(print_r($input, true));
		}
		
	}

	static public function getNetworkPortID($url, $portName, $computerID){
		self::$options;


		$url .= "search/networkport?";
		$url .=	  "criteria[0][link]=AND&criteria[0][field]=1&criteria[0][searchtype]=contains&criteria[0][value]=$portName";
		$url .= "&&criteria[1][link]=AND&criteria[1][field]=21&criteria[1][searchtype]=contains&criteria[1][value]=$computerID";
		$url .= "&&uid_cols=1&&forcedisplay[0]=2";

		$ch = curl_init($url);
		curl_setopt_array($ch, self::$options);
		$items = curl_exec($ch);

		$readableItems = json_decode($items,true);

		//PluginAddTaskList::printDebug($readableItems);

		if(strcmp($readableItems['count'],"1") != 0){
			PluginAddTaskList::printDebug("invalid response");
			$locationNumber = -1;
		}
		else{
			$idIndex = "Networkport.NetworkPort.id";
			$locationNumber = $readableItems['data'][0][$idIndex];
		}

		curl_close($ch);
		return $locationNumber;
	}


	static public function getModelID($url, $modelType, $modelNum){

		$url .= "search/$modelType"."model?";
		$url .= "criteria[0][field]=1&criteria[0][searchtype]=contains&criteria[0][value]=$modelNum&&uid_cols=1&&forcedisplay[0]=2";

		PluginAddTaskList::printDebug($url);

		$ch = curl_init($url);
		curl_setopt_array($ch, self::$options);
		$items = curl_exec($ch);

		$readableItems = json_decode($items,true);

		PluginAddTaskList::printDebug($readableItems);

		$count = $readableItems['count'];
	
		PluginAddTaskList::printDebug("Found $count model numbers matching $modelNum");
	
		$modelTypeSecondPart = $modelType;

                //Special case because the API wants to see "NetworkEquipment" in one place,
                //      and "Networkequipment" in another...
                //

                if(strcasecmp($modelType, "networkequipment") == 0){
                        $modelTypeSecondPart = "NetworkEquipment";
                }
                
		$idIndex = ucfirst($modelType)."model.".ucfirst($modelTypeSecondPart)."Model.id";
		$idNameKey = ucfirst($modelType)."model.".ucfirst($modelTypeSecondPart)."Model.name";

		if($count < 1){
			PluginAddTaskList::printDebug("invalid response");
			$locationNumber = -1;
		}

		else if($count > 1){
			PluginAddTaskList::printDebug("Finding matching model number in the array");
		
			foreach($readableItems['data'] as $model){
				if(strcasecmp($model[$idNameKey],$modelNum) == 0){
					$locationNumber = $model[$idIndex];
					PluginAddTaskList::printDebug("Using $locationNumber as Model ID");
					return $locationNumber;	
				}
			}
			
			PluginAddTaskList::printDebug("Couldn't match location.... returning invalid");
			return 0;


		}
		else{
			PluginAddTaskList::printDebug("Trying to find key $idIndex in array");		
			$locationNumber = $readableItems['data'][0][$idIndex];
			print "$locationNumber";
		}

		curl_close($ch);
		return $locationNumber;
	}



	static public function getLocationID($url, $completeName){
		//Set LocationID to an invalid value
		//
		$locationID = -1;

		//Build the search URL
		//
		$url .= "search/Location?";
		
		//Add the search criteria to the search URL
		//	
		$url .= "criteria[0][field]=1&criteria[0][searchtype]=contains&criteria[0][value]=$completeName&&uid_cols=1&&forcedisplay[0]=2";

		$ch = curl_init($url);
		curl_setopt_array($ch, self::$options);
		$items = curl_exec($ch);
				
		PluginAddTaskList::printDebug("Looking up location ID");
		$readableItems = json_decode($items,true);

		PluginAddTaskList::printDebug("Received the following array from location query");
		PluginAddTaskList::printDebug($readableItems);

		$count = $readableItems['count'];

		curl_close($ch);

		if($count){
			PluginAddTaskList::printDebug("Found $count locations matching location $completeName");
		}
		else{
			PluginAddTaskList::printDebug("No valid locations found, returning invalid location id");
			return -1;
		}

		if($count > 1){
			PluginAddTaskList::printDebug("Finding matching location in the array");
		
			foreach($readableItems['data'] as $location){
				$locationFormatted = str_replace(' ', "%20", $location['Location.completename']);
				if(strcasecmp($locationFormatted,$completeName) == 0){
					$locationNumber = $location['Location.id'];
					PluginAddTaskList::printDebug("Using $locationNumber as locationID");
					return $locationNumber;	
				}
			}
			
			PluginAddTaskList::printDebug("Couldn't match location.... returning invalid");
			return -1;
		}
		
		else{
			$idIndex = "Location.id";
			$locationNumber = $readableItems['data'][0][$idIndex];
			return $locationNumber;
		}

	}


	static public function getStateID($url, $stateName){
		self::$options;

		$stateID = -1;

		$url .= "search/State?";
		$url .= "criteria[0][field]=1&criteria[0][searchtype]=contains&criteria[0][value]=$stateName&&uid_cols=1&&forcedisplay[0]=2";

		$ch = curl_init($url);
		curl_setopt_array($ch, self::$options);
		$items = curl_exec($ch);

		$readableItems = json_decode($items,true);

		if(strcmp($readableItems['count'],"1") != 0){
			print "invalid response";
			$stateNumber = -1;
			print "$stateNumber";	
		}
		else{
			$idIndex = "State.id";
			$stateNumber = $readableItems['data'][0][$idIndex];
			print "$stateNumber";
		}

		curl_close($ch);
		return $stateNumber;
	}

	static public function getStateTypes($url){
		self::$options;

		$url .= "State";

		$ch = curl_init($url);
		curl_setopt_array($ch, self::$options);

		$items = curl_exec($ch);

		$readableItems = json_decode($items, true);

		curl_close($ch);

	}

	static public function updateNetworking($connection, $mac, $itemType, $itemNumber){

		//Create the network adapter array
		//		itemType is the type of device that this exists on
		//		instantiation_type stays as NetworkPortEthernet
		//		items_id is the item that this belongs to
		//		mac address is the mac address of the adapter
		//		name is the connection type
		//
		$networkInfo = array(	"itemtype"	=> "$itemType",
			"instantiation_type"		=> "NetworkPortEthernet",
			"items_id"			=> $itemNumber,
			"mac"				=> $mac,
			"name"				=> $connection,
		);

		//Gets the array of existing connections
		//
		$existingConnections = PluginAddTaskList::getItemNetworkInfo(GLPI3, "computer", $itemNumber);
	
		error_log("Found ".$existingConnections);

		//flag for the for loop to set if the mac address already exists
		//
		$exists = 0;

		//Step through each connection and see if the current new connection exists
		//
		foreach($existingConnections as $netConn){
			if(strcasecmp($netConn['name'], $connection) == 0){
				$exists = 1;
				$itemID = PluginAddTaskList::getNetworkPortID(GLPI3, $netConn['name'], $itemNumber);
				PluginAddTaskList::printDebug("Found network port id $itemID, updating if necessary");
				return PluginAddTaskList::updateItemInfo(GLPI3, "networkport", $itemID, $networkInfo);
			}
		}

		//After the loop is done, add the network port if it didn't exist
		//
		if($exists == 0){
		PluginAddTaskList::printDebug("Network port not found, adding to list of ports for $itemNumber");
			return PluginAddTaskList::addItem(GLPI3, "networkport", $networkInfo);
		}

		else
			return -1;
	}


	//Add a networking port to GLPI, and link it to an existing asset
	//
	static public function addNetworking($connection, $mac, $itemType, $itemNumber){

		//Create the network adapter array
		//	itemType is the type of device that this exists on
		//	instantiation_type stays as NetworkPortEthernet
		//	items_id is the item that this belongs to
		//	mac address is the mac address of the adapter
		//	name is the connection type
			//
		$networkInfo = array(	"itemtype"	=> "$itemType",
				"instantiation_type"	=> "NetworkPortEthernet",
				"items_id"		=> $itemNumber,
				"mac"			=> $mac,
				"name"			=> $connection,
			);

		//Gets the array of existing connections for the current computer
		//	
		$existingConnections = PluginAddTaskList::getItemNetworkInfo(GLPI3, "computer", $itemNumber);
	
		//flag for the for loop to set if the connection type already already exists
		//	on this machine
		$exists = 0;	

		//Step through each connection and see if the current new connection exists
		//
		foreach($existingConnections as $netConn){
			if(strcasecmp($netConn['name'], $connection) == 0){
				error_log("Found existing $connection connection");
				return PluginAddTaskList::updateNetworking($connection,$mac, $itemType, $itemNumber);
			}
		}
	
		//After the loop is done, add the network port if it didn't exist
		//
		if($exists == 0){
			error_log("Didn't find connection name");
			return PluginAddTaskList::addNetworkPortItem(GLPI3, "networkport", $networkInfo);
		}

		else{
			error_log("Found connection name");
			return -1;
		}
	}

	//Add new networking port item
	static public function addNetworkPortItem($url, $itemType, $itemInfo){

		$url .= "$itemType";

		$payload = array("input" => $itemInfo);

		$ch = curl_init($url);
		curl_setopt_array($ch, self::$options);
		curl_setopt($ch, CURLOPT_POSTFIELDS,json_encode($payload));

		$response = curl_exec($ch);

		$readableResponse = json_decode($response, true);

		curl_close($ch);

		if($readableResponse['id'] != null){
			return $readableResponse['id'];
		}

		else
			return -1;
		}



	//Add a new item to GLPI, of type itemType
	//
	static public function addItem($url, $itemType, $itemInfo){
			
		$url .= "$itemType";

		$payload = array("input" => $itemInfo);

		PluginAddTaskList::printDebug("Attempting to add a new:  $itemType");


		$ch = curl_init($url);
		curl_setopt_array($ch, self::$options);
		curl_setopt($ch, CURLOPT_POSTFIELDS,json_encode($payload));

		$response = curl_exec($ch);

		$readableResponse = json_decode($response, true);

		curl_close($ch);

		if($readableResponse['id'] != null){
			return $readableResponse['id'];
		}


	}

	//Update the info for an existing item
	//
	static public function updateItemInfo($url, $itemType, $itemNumber, $changes){
		$url .= "$itemType/$itemNumber";

		//get the new comments from the change array	
		$newComments = $changes['comment'];

		//If there are new comments that need to be added, we'll get the existing comments
		// And we'll append the new comments to that value.... then save the whole comment
		// set into the changes array
		if($newComments){
			PluginAddTaskList::printDebug("The new comments to be posted are: $newComments");

			$oldComments = PluginAddTaskList::getItemComments(GLPI3, $itemType, $itemNumber);
			if(strpos($oldComments, $newComments) != false){
				PluginAddTaskList::printDebug("new comment already exists, moving on");
				$changes['comment'] = $oldComments;
			}
			else{			

				$newComments = $oldComments . "\n" . date("m/d/Y"). ": "  . $newComments;
				$changes['comment'] = $newComments;
				PluginAddTaskList::printDebug("The entire comment is now: $newComments");
			}
		}

		$payload = array("input" => $changes);

		$ch = curl_init($url);
		curl_setopt_array($ch, self::$options);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
		curl_setopt($ch, CURLOPT_POSTFIELDS,json_encode($payload));
	
		$items = curl_exec($ch);

		$readableItems = json_decode($items, true);
	
		curl_close($ch);
	
		if(!$readableItems[0][$itemNumber]){
			PluginAddTaskList::printDebug("There was a problem");
			PluginAddTaskList::printDebug($readableItems);
		}
	
		else{
			PluginAddTaskList::printDebug("success modifying: $itemNumber");
			return $itemNumber;
		}
	
	}

	static public function getItemComments($url, $itemType, $itemNumber){
		$url .= "$itemType/$itemNumber";

		$ch = curl_init($url);
		curl_setopt_array($ch, self::$options);
		
		PluginAddTaskList::printDebug("The query URL is:  $url");
		
		$info = curl_exec($ch);

		$readableInfo = json_decode($info, true);

		curl_close($ch);

		PluginAddTaskList::printDebug("Item info: ");
		PluginAddTaskList::printDebug($readableInfo);	

		PluginAddTaskList::printDebug("The old comments are:  ");
		PluginAddTaskList::printDebug($readableInfo['comment']);

		return $readableInfo['comment'];
	}


	static public function getItemNetworkInfo($url, $itemType, $itemNumber){
		self::$options;

		$url .= "$itemType/$itemNumber/NetworkPort/";

		$ch = curl_init($url);
		curl_setopt_array($ch, self::$options);

		$items = curl_exec($ch);

		$readableItems = json_decode($items, true);

		curl_close($ch);
	
		return $readableItems;
	}

	static public function getTaskCount($url, $ticketNumber){
		$url .= "Ticket/$ticketNumber/TicketTask";

                $ch = curl_init($url);
                curl_setopt_array($ch, self::$options);

                $items = curl_exec($ch);

                $readableItems = json_decode($items, true);

                return count($readableItems);

                curl_close($ch);
	}

	static public function getTicketTasks($url, $ticketNumber){
		$url .= "Ticket/$ticketNumber/TicketTask";

		$ch = curl_init($url);
                curl_setopt_array($ch, self::$options);

                $items = curl_exec($ch);

                $readableItems = json_decode($items, true);

                return $readableItems;

                curl_close($ch);
        }


	//This function is only use for diagnostic purposes
	//
	static public function getItemFullInfo($url, $itemType, $itemNumber){
		$url .= "$itemType/$itemNumber";

		$ch = curl_init($url);
		curl_setopt_array($ch, self::$options);

		$items = curl_exec($ch);

		$readableItems = json_decode($items, true);
		
		return $readableItems;
	
		curl_close($ch);
	}

	//Using the serial number as a search item, get the item id
	//	that GLPI uses as a unique identifier
	//
	static public function getItemIDbySerial($url, $itemType, $serial){
		//Initialize itemNumber to an invalid return value
		//
		$itemNumber = -1;

		//Build the url for the API query.  
		//
		$url .= "search/$itemType?";
		
		//append the search information to the url for the API
		//	query
		//	criteria[0][field] = 5
		//	criteria[0][searchtype] = contains
		//	criteria[0][value] = $serial
		//	uid_cols = 1 (returns values with keys instead of indexes)
		//	forces 2 (item id) to be returned
		//
		$url .= "criteria[0][field]=5&criteria[0][searchtype]=contains&criteria[0][value]=$serial&&uid_cols=1&&forcedisplay[0]=2";

		//Init the curl command, using the url we built, along with the
		//	options defined globally
		//
		PluginAddTaskList::printDebug("Searching with serial $serial for item ID under $itemType");
		$ch = curl_init($url);
		curl_setopt_array($ch, self::$options);
		$items = curl_exec($ch);

		//Decode the json string that came back, and put it in an
		//	array so it can be accessed
		//
		$readableItems = json_decode($items,true);
		PluginAddTaskList::printDebug("Received item array from serial search:");
		PluginAddTaskList::printDebug($readableItems);		

		//Check the number of responses array['count']
		$count = $readableItems['count'];
		PluginAddTaskList::printDebug("Array has $count items");

		if($count > 1){
			error_log("More than one item with serial $serial exist");
		}
		else if($count == 1){
			$modelTypeSecondPart = $modelType;

                        //Special case because the API wants to see "NetworkEquipment" in one place,
                        //      and "Networkequipment" in another...
                        //

                        if(strcasecmp($modelType, "networkequipment") == 0){
                                $modelTypeSecondPart = "NetworkEquipment";
                        }

			$idIndex = ucfirst($itemType) . $modelTypeSecondPart . ".id";
			$itemNumber = $readableItems['data'][0][$idIndex];
			PluginAddTaskList::printDebug("Found item $itemNumber with serial $serial");
			error_log("Found item $itemNumber with serial $serial");
		}
		else{
			error_log("No item found with serial $serial");
		}

		curl_close($ch);

		return $itemNumber;
	}

	//Debugging function:  Queries the GLPI API for the available
	//	profile information for authorization token that was used
	//
	static public function printProfiles($url){
		self::$options;

		$url .= "getMyProfiles/";
		$ch = curl_init($url);
		curl_setopt_array($ch, self::$options);

		$profiles = curl_exec($ch);
	
		$readableProfiles = json_decode($profiles, true);

		curl_close($ch);
	}

	//Debugging function:  Queries the GLPI APi for the available
	//	search options for a given item type.  It' prints the output to the
	//	error log
	//
	static public function printSearchOptions($url, $itemType){
		self::$options;

		$url .= "listSearchOptions/$itemType";
		$ch = curl_init($url);
		curl_setopt_array($ch, self::$options);

		$searchOptions = curl_exec($ch);

		$readableOptions = json_decode($searchOptions, true);

		PluginAddTaskList::printDebug($readableOptions);
		
		curl_close($ch);
	}

	//This destroys the existing GLPI session with GLPI, which kills the session
	//	token
	//
	static public function killSession($url){
		$url .= "killSession/";
		$ch = curl_init($url);
		curl_setopt_array($ch, self::$options);
	
		curl_exec($ch);
	
		curl_close($ch);
	}

	//This function initiates a session to the provided URL, and then calls the
	//	function that rebuilds the headers to include the provided session token.
	//  If no session token is returned, the function needs to provide an error
	//
	static public function initSession($url) {
	
		$url .= "initSession/";
		$ch = curl_init($url);
		curl_setopt_array($ch, self::$options);

		PluginAddTaskList::printDebug("Connecing to server for session token");

		$initResponse = curl_exec($ch);
		$responseArray = json_decode($initResponse, true);
		
		PluginAddTaskList::printDebug("Server Response:");
		PluginAddTaskList::printDebug($responseArray);
		
		$sessionToken = $responseArray['session_token'];

		curl_close($ch);
	
		PluginAddTaskList::printDebug("Stored Session token as: $sessionToken");
		PluginAddTaskList::rebuildHeaders($sessionToken);
	
	}

	//This function rebuilds the $options array for the curl command
	// to include the GLPI required sessionToken
	//
	static public function rebuildHeaders($sessionToken){
		
		PluginAddTaskList::printDebug("Updating curl options with sessiontoken");

		self::$options = array(
			CURLOPT_RETURNTRANSFER	=> true,	  // return web page
			CURLOPT_HEADER		=> false,  // don't return headers
			CURLOPT_FOLLOWLOCATION	=> true,	  // follow redirects
			CURLOPT_MAXREDIRS	=> 10,	  // stop after 10 redirects
			CURLOPT_ENCODING	=> "",	  // handle compressed
			CURLOPT_USERAGENT	=> "test", // name of client
			CURLOPT_AUTOREFERER	=> true,	  // set referrer on redirect
			CURLOPT_CONNECTTIMEOUT 	=> 120,	  // time-out on connect
			CURLOPT_TIMEOUT		=> 120,	  // time-out on response
			CURLOPT_HTTPHEADER	=> array('Content-Type: application/json',
				APPHEADER,
				"Session-Token: $sessionToken"),
		); 
	}
}

?>
