<?php 


class VMC {

	private static $name;
	protected static $wf;
	protected static $data;
	protected static $query;
	protected static $pattern;
	protected static $url;
	protected static $allVMData;
	protected static $functions = array(

									'claim' => array(
										"task" => "claim",
										"subtitle" => "Claim a Virtual Machine"
									),
									'vacate' => array(
										"task" => "vacate",
										"subtitle" => "Vacate a Virtual Machine"
									),
									'resetName' => array(
										"task" => "resetName",
										"subtitle" => "Reset Your Checkout Name"
									),
									'clearvm' => array(
										"task" => "clear",
										"subtitle" => "Clear Your VM Checkout Name"
									)
								);

	function __construct( ) {

		# Check if the Workflows Class exists: else require it
		if ( !class_exists('Workflows') ) {
			require_once('workflows.php');
			self::$wf = new Workflows();
		}

	}

	/**
	*	'vm' Step 1: Check if checkout name is set
	*	
	*	yes - Generate results of available vm functions
	*	no  - prompt user to set a check name before continuing 
	*	@param 'string' : checkout name -OR- VMC function name
	*	@return XML : Alfred results
	*/
	public static function vmStepOne( $query ) {

		if ( self::hasName() ) {

			$data = array(
				"task" => "getFunctions",
				"query" => $query
			);

			$results = self::getFunctionResults( json_encode($data) );

			return $results;

		}
		else {

			$data = array(
				"task" => "setName",
				"query" => $query
			);

			$results = self::promptForName( json_encode($data) );

			return $results;
		}
	}

	/**
	*	'vm' Step 2: Determine the action based on Step1's result query
	*
	*	@param 'string' : JSON data {task, query}
	*	@param 'string' : script task this is being fired from
	*	@return 'string' : notification message
	*	-OR-
	*	@return 'string' : VMC task name
	*/
	public static function stepTwo( $json, $output = true ) {

		self::$data = json_decode( $json );

		switch ( self::$data->task ) {

			case "setName":

				self::setName();

				return self::$data->output === $output ? "Checkout name is now '".self::$data->query."'" : '';

			break;

			case "getFunctions":

				return self::$data->output === $output ?  self::$data->query : '';
				// return self::$data->task. " " .self::$data->task2. " " . self::$data->query;

			break;
		}
	}

	/**
	*	'claim' Step 1: Determine if a NAME has been set
	*
	*	yes - Generate results for claiming a VM
	*	no  - prompt user to set a check name before continuing  
	*/
	public static function claimStepOne( $query ) {

		if ( self::hasName() ) {

			// self::$wf->result( 'demo', $json, 'Going to let you cliam stuff', 'subtitle', 'icon.png', 'yes' );

			self::$query = $query;

			$resutls = self::searchAvailableVMs("http://vm-checkout.threespot.dev/vm.php"); 
			// "http://apps.threespot.com/vmcheckout/vm.php"

			return self::$wf->toxml();

		}
		else {

			$data = array(
				"task" => "setName",
				"query" => $query
			);

			$results = self::promptForName( json_encode($data) );

			return $results;
		}
	}

	/**
	* Search VM list against QUERY
	* 
	* loop through all the vmData
	* find a matching VM name
	* pass it into a workflow result
	*
	* @param sting url to fetch vm data from 
	* @return xml of each match
	*/
	protected function searchAvailableVMs( $url ) {

		self::$allVMData = self::fetchAllVMData( $url );

		foreach( self::$allVMData as $index => $vm ) {

			// if ( isset($vm->user) &&  $vm->user === "" && ( preg_match( $this->pattern, $vm->vm, $matches) || self::$query === "")  ) {

			// 	$vm->url = self::$url;
			// 	$vm->name = $this->getName();
			// 	self::$wf->result( $index , json_encode($vm) , $vm->vm, "Checkout Virtual Machine ".$vm->vm, 'icon.png', 'yes' );
			// }

			self::$wf->result( $index , json_encode($vm) , $vm->vm, "Checkout Virtual Machine ".$vm->vm, 'icon.png', 'yes' );

		}

		$results = self::$wf->results();

		// if ( count( $results ) == 0 ) {
		// 	self::$wf->result( 'googlesuggest', self::$query, 'No Suggestions', 'No search suggestions found. Search Google for '.self::$query, 'icon.png' );
		// }

		return self::$wf->toxml();
	}


	/**
	* Gets the enitre list of VMS from the server
	* set the data request url
	*
	* @param string url to request data from
	* @return array of all the VMS
	*/
	protected function fetchAllVMData( $url = "http://vm-checkout.threespot.dev/vm.php" ) {
	 	self::$url = $url;
		$data = file_get_contents( $url );
		return json_decode( $data );
	}


	/**
	*	Returns the username that will be used to claim a VM
	*
	*	@param none
	*	@return 'string' username
	*/
	public static function getName() {
		return self::$data->query;
	}

	public static function getTask() {
		return self::$data->task;
	}

	/**
	*	Returns the user input for the alfred command box
	*
	*	@param none
	*	@return 'string' user input
	*/
	public static function getQuery() {
		return self::$data->query;
	}

	/**
	*	Sets the username that will be used to claim a VM
	*
	*	@param 'string' username
	*	@return none
	*/
	protected static function setName( ) {

		file_put_contents( 'name.txt', self::$data->query );

	}

	/**
	*	Checks if a name is alrady set for claiming VMs
	*
	*	@param none
	*	@return boolean username is set
	*/
	protected static function hasName() {


		if ( file_exists( 'name.txt' ) ) {

			$text = file_get_contents( 'name.txt' );

			if ( strlen( $text ) > 0 ) {
				self::$name = $text;
				return true;
			}
			else {
				unlink('name.txt');
				return true;
			}
		}

		else {
			return false;
		}
	}


	/**
	*	Sets the VM Class data to be used to determine the tasks and query info
	*
	*	@param string json data
	*/
	protected static function setData( $dataString ) {

		self::$data = json_decode( $dataString );

		self::$pattern = "/".self::$data->query."/i";
	}



	/**
	*	Search Available VM Checkout Workflow Tasks
	*
	*	@param string json data
	*	@return xml Alfred results of functions
	*/
	public static function getFunctionResults( $json ) {

		self::setData( $json );

		foreach( self::$functions as $index => $func ) {

			$val = array_values($func);

			if ( preg_match( self::$pattern, $val[0], $matches) || self::$query === "" ) {

				self::$data->output = false;

				self::$data->query = $val[0];

				self::$wf->result( 'demo', json_encode( self::$data ), 'Task: '.$val[0] , $val[1], 'icon.png', 'yes' );

			}

		}

		return self::$wf->toxml();
	}

	/**
	*	Creates a Alfred prompt to set a username for checking out VMs
	*
	*	@param none
	*	@return xml Alfred result for inputing a name
	*/
	protected static function promptForName( $json ) {

		self::$data = json_decode( $json );

		self::$data->output = true;

		self::$wf->result( 'demo', json_encode( self::$data ), 'Set Name: '.self::$data->query, 'Enter a name to be used to checkout a VM', 'icon.png', 'yes' );

		return self::$wf->toxml();
	}
}

class VMs {

	protected static $vmData;
	protected static $url;

	function __construct() {

	}

	/**
	* Gets the enitre list of VMS from the server
	* set the data request url
	*
	* @param string url to request data from
	* @return array of all the VMS
	*/
	protected static function getVMsData( $url = "http://vm-checkout.threespot.dev/vm.php" ) {
		$this->url = $url;
		$data = file_get_contents( $url );
		return json_decode( $data );
	}


	/**
	* Search VM list against QUERY
	* 
	* loop through all the vmData
	* find a matching VM name
	* pass it into a workflow result
	*
	* @param sting url to fetch vm data from 
	* @return xml of each match
	*/
	public static function searchVmData() {

		self::$vmData = $this->getData( $url );

		foreach( self::$vmData as $index=>$vm ) {

			if ( isset($vm->user) &&  $vm->user === "" && ( preg_match( $this->pattern, $vm->vm, $matches) || self::$query === "")  ) {

				$vm->url = $this->url;
				$vm->name = $this->getName();
				self::$wf->result( $index , json_encode($vm) , $vm->vm, "Checkout Virtual Machine ".$vm->vm, 'icon.png', 'yes' );
			}
		}

		$results = self::$wf->results();

		if ( count( $results ) == 0 ) {
			self::$wf->result( 'googlesuggest', self::$query, 'No Suggestions', 'No search suggestions found. Search Google for '.self::$query, 'icon.png' );
		}

		return self::$wf->toxml();
	}
}

// class VM {
// 	function __construct() {

// 	}

// 	public static function claim() {

// 	}
// }

?>