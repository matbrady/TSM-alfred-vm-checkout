<?php 

require_once('workflows.php');

/** 
* Virtual Machine Checkout 
*
* DISCLAIMER!! BE code written by a FED 
* @version   0.1.1
* @author    Mathew Brady <mat.brady@threespot.com> 
* @copyright 2013 
* @license   http://www.php.net/license/3_01.txt PHP License 3.01 
*/ 
class VMC extends Workflows { 

	protected $query;
	protected $pattern;
	protected $url = "http://apps.threespot.com/vmcheckout/vm.php"; // http://vm-checkout.threespot.dev/vm.php -OR- http://apps.threespot.com/vmcheckout/vm.php
	protected $checkout_name;
	protected $tasks = array(  // comma placement is for easier commenting of code during development
		'claim' => array(
			"task" => "claim",
			"title" => "Claim",
			"subtitle" => "Claim a Virtual Machine"
		)
		,'vacate' => array(
			"task" => "vacate",
			"title" => "Vacate",
			"subtitle" => "Vacate a Claimed Virtual Machine"
		)
		,'set_name' => array(
			"task" => "set",
			"title" => "Set Name",
			"subtitle" => "Set Your Checkout Name"
		)
		,'reset_name' => array(
			"task" => "set",
			"title" => "Reset Name",
			"subtitle" => "Reset Your Checkout Name"
		)
	);

	public function __construct() {
		parent::__construct();  
	}

	/**
	* GETTER - Confirm if Checkout Name is Set
	*
	* Descirption: Checks if the name.txt file exists, checks if 
	* the file contains any content (name) and returns the name. 
	* Otherwise it return false.
	* @param NONE
	* @return YES - 'string' : checkout name
	* @return NO  - boolean  : false
	*/
	protected function is_name_set() {

		// Check if the name file exisis
		if ( file_exists( 'name.txt' ) ) {

			$text = file_get_contents( 'name.txt' );

			// Check if anything is written to the file
			if ( strlen( $text ) > 0 ) { 

				$this->checkout_name = $text;
				// return the checkout name
				return $this->checkout_name;

			}

			else {
				// remove the empty file
				unlink('name.txt');
				return false;
			}
		}

		else {
			return false;
		}
	}

	/**
	* SETTER - Sets the Pattern for Searching
	*
	* Description: Accepts a string it concatinates a regular
	* expression pattern to be used for searching
	* @param 'string' : user input or query
	* @return NONE
	*/
	protected function set_match_pattern( $query ) {

		$this->pattern = "/".$query."/i";
	}

	/**
	* Search VMC Workflow Tasks
	*
	* Description: Accepts the user input string and checks
	* it against the tasks ($task) which can be invoked
	* @param 'string' : user input or query
	* @return XML : result data to be display in Alfred prompt
	*/
	public function display_tasks( $query ) {

		$this->set_match_pattern( $query );

		foreach( $this->tasks as $index => $func ) {

			/**
			* IF checkout name IS set and the Task name is "Set Name"
			*   dont create a result.
			* IF checkout name IS NOT set and the Task name is "Reset Name"
			*   dont create a result.
			* In other words: Only display "Set Name" if no name is set. Only
			* display "Reset Name" if a name is already set 
			*/
			if ( $this->is_name_set() !== false && $func['title'] === "Set Name"  ||
				 $this->is_name_set() === false && $func['title'] === "Reset Name" ) 
			{
				continue; // don't make a result
			}

			// IF user matches a VM task name OR is an empty string
			else { 

				if ( preg_match( $this->pattern, $func['title'], $matches) || $query === "" ) {

					// Create a result with the Task name and subtitle
					$this->result( 'demo', $func['task'], $func['title'], $func['subtitle'], 'icon.png', 'yes' );

				}

			}

		}

		return $this->toxml();

	}

	/**
	* Request VM Search
	*
	* Description: Check if a checkout name is set which is required 
	* checkout a VM. If it IS NOT, request a name. Otherwise display
	* all the available VMs
	* @param 'string' : user input or query
	* @return XML : Available VM data from server
	* -OR-
	* @return XML : Prompt for Checkout Name
	*/
	public function request_vm_search( $action, $query ) {

		$this->query = $query;

		// Name HAS been set
		if ( $this->is_name_set() !== false ) {

			// set the search pattern
			$this->set_match_pattern( $query );

			switch ( $action ) {
				case "claim":
					return $this->search_available_vms();
				break;

				case "vacate":	
					return $this->search_claimed_vms();
				break;

			} 
		}

		// Name has NOT been set
		else {

			// Create a Prompt Response for Alfred to display in the results
			$prompt = array(
				"data" => array(
						'query' => $this->query,
						'action' => 'set_name',
						'message' => 'Checkout name set to: '. $this->query
					),
				"title" => "Set Name: ".$this->query,
				"subtitle" => "Enter a name to be used to checkout a VM",
				"image" => "icon.png",
				);

			return $this->prompt_user( $prompt );
		}
	}


	/**
	* Request Checkout Name Change
	*
	* Description: Allow users to override their existing VM Checkout Name
	* Additions: check for any vms that have been checked out using the old name
	* reset them to the new name
	* @param 'string' : user input or query
	* @return 'string' : message to be 
	*/
	public function request_name_change( $query ) {

		$this->query = $query;

		if ( $this->is_name_set() === false ) {

			// Create a Prompt Response for Alfred to display in the results
			$prompt = array(
				"data" => array(
						'query' => $this->query,
						'action' => 'set_name',
						'message' => 'Checkout name set to: '. $this->query
					),
				"title" => "Set Name: ".$this->query,
				"subtitle" => "Enter a name to be used to checkout a VM",
				"image" => "icon.png",
				);

		}
		else {

			// Create a Prompt Response for Alfred to display in the results
			$prompt = array(
				"data" => array(
						'query' => $this->query,
						'action' => 'set_name',
						'message' => 'Checkout name set to: '. $this->query
					),
				"title" => "Reset Name: ".$this->query,
				"subtitle" => "Change your name to be used to checkout a VM",
				"image" => "icon.png",
				);
		}

		return $this->prompt_user( $prompt );
	}

	/**
	* Search Server for Available Virtual Machines
	*
	* @param 'string' : user input or query
	* @return XML : Available VM results
	*/
	protected function search_available_vms() {

		$url = $this->url;

		$all_vm_data = $this->fetch_all_data( $url );

		foreach( $all_vm_data as $index => $vm ) {

			if ( isset($vm->user) &&  $vm->user === "" && ( preg_match( $this->pattern, $vm->vm, $matches) || $this->query === "")  ) {

				$vm->task = "claim";
				$vm->url = $url;
				$vm->action = "claim_vm";
				$vm->name = $this->get_checkout_name();

				$this->result( $index , json_encode($vm) , $vm->vm, "Checkout Virtual Machine ".$vm->vm, 'icon.png', 'yes' );
			}
		}

		$results = $this->results();

		// if ( count( $results ) == 0 ) {
		// 	self::$wf->result( 'googlesuggest', self::$query, 'No Suggestions', 'No search suggestions found. Search Google for '.self::$query, 'icon.png' );
		// }

		return $this->toxml();
	}

	/**
	* Search Server for Claimed Virtual Machines
	*
	* @param 'string' : user input or query
	* @return XML : Claimed VM results
	*/
	protected function search_claimed_vms() {

		$url = $this->url;

		$all_vm_data = $this->fetch_all_data( $url );

		$name = $this->get_checkout_name();

		$claimed_vms = array();

		foreach( $all_vm_data as $index => $vm ) {

			if ( isset($vm->user) && $vm->user === $name ) {

				$vm->task = "vacate";
				$vm->url = $url;
				$vm->action = "vacate_vm";
				$vm->name = $name;

				$this->result( $index , json_encode($vm) , $vm->vm, "Vacate Virtual Machine ".$vm->vm, 'icon.png', 'yes' );

				array_push( $claimed_vms, $vm->id );
			}
		}

		// If Claimed VMS is more than one add a 'Vacate All VM' result
		if ( count($claimed_vms) > 1 ) {
			$vacate_all_data->task = 'clear';
			$vacate_all_data->action = 'vacate_all';
			$vacate_all_data->name = $name;
			$vacate_all_data->claimed_vms = $claimed_vms;

			$this->result( $index++, json_encode($vacate_all_data), "Vacate All VMs", "Vacate all claimed Virtual Machines", 'icon.png', 'yes' );
		}

		$results = $this->results();

		if ( count( $results ) == 0 ) {
			$this->result( 0, 'false', "No VMs owned by '".$name."'", 'Crap, it looks like you might have to visit the actual VM checkout', 'icon.png' );
		}

		return $this->toxml();
	}

	/**
	* Gets the enitre list of VMS from the server
	* set the data request url
	*
	* @param 'string' : url to request data from
	* @return array of all the VMS
	*/
	protected function fetch_all_data( $url = "http://vm-checkout.threespot.dev/vm.php" ) {

		$data = file_get_contents( $url );

		return json_decode( $data );
	}


	/**
	* Prompt User for Information
	*
	* Description: Generate a Result Prompt from passed data to request
	* from the user. This will be used to set information with the Workflow
	* @param ARRAY : [data, title, subtitle, image] info used to create Workflow Result
	* @return XML : prompt result
	*/
	protected function prompt_user( $prompt ) {

		$this->result( 'demo', json_encode($prompt['data']), $prompt['title'], $prompt['subtitle'], $prompt['image'], 'yes' );

		return $this->toxml();
	}


	/**
	* Get Current VM Checkout Name
	*
	* @param NONE
	* @return 'string' : user's VM checkout name
	*/
	protected function get_checkout_name() {

		return file_get_contents( 'name.txt' );
	}


	/**
	* Notify the User
	*
	* Description: get and return the message from result data
	* @param JSON 'string' : data from the alfred results
	* @return 'string' : message to be shown to the user
	*/
	public function notify_user( $passed_data ) {

		$data = json_decode($passed_data);

		switch (  $data->action ) {

			case 'set_name':
				if ( $data->query === "" ) {
					return "Please set a valid checkout name";
				}
				else {
					file_put_contents( 'name.txt', $data->query );
					return $data->message;
				}

			break;

			default: 
				return '';
			break;
		}
	}


	/**
	* Get Output Query
	*
	* Description: get and return the message from result data
	* @param JSON 'string' : data from the alfred results
	* @return 'string' : message to be shown to the user
	*/
	public function get_output_query( $passed_data ) {

		$data = json_decode($passed_data);

		switch (  $data->action ) {

			case 'claim_vm':
				return $data->vm;
			break;

			default: 
				return '';
			break;
		}
	}


	/**
	* Claim an Available Virtual Machine
	*
	* Description: Checks for the claim_vm action, 
	* sends a PUT request to claim a VM from the server,
	* notifies the user of successful claim.
	* @param 'string'/OBJECT : data passed from vm results
	* @return 'string' : response from CURL command
	*/	
	public function claim_vm( $passed_data ) {

		$data = json_decode($passed_data);

		if ( $data->action === 'claim_vm' ) {

			return $this->update_server( 'PUT', $data );
		}

		else return '';
	}


	/**
	* Vacate a Claimed Virtual Machine
	*
	* Description: Checks for the vacate_vm action,
	* sends a DELETE request to vacate a VM from the server,
	* notifies the user of a successful vacation ;)
	* @param 'string'/OBJECT : data passed from vm results
	* @return 'string' : repsonse from CURL command
	*/
	public function vacate_vm( $passed_data ) {

		$data = json_decode($passed_data);

		if ( $data->action === 'vacate_vm' ) {

			return $this->update_server( 'DELETE', $data );
		}
		else if ( $data->action === 'vacate_all' ) {
			 
			return $this->update_server( 'CLEAR', $data );
		}

		else return '';
	}


	/**
	* Create Request to Server
	*
	* Description: Creates a JSON string and sends a PUT 
	* request to ther server for claiming or vacating a VM
	* @param Object : data used to generate curl command
	* @return 'string' : message to user
	*/
	protected function update_server( $type, $data ) {

		date_default_timezone_set('America/New_York');
		$date = date("Y-m-d H:i:s");

		$messages = array(
				'put' => 'You now own '. $data->vm,
				'delete' => $data->vm.' has been Vacated'
			);

		switch ( $type ) {

			case 'PUT':
				$update_json = '{"id":"'.$data->id.'","user":"'.$data->name.'","checkout":"'.$date.'"}';
				$notification = $messages['put'];
				$good_request = $this->send_curl( $update_json );
			break;

			case 'DELETE':
				$update_json = '{"id":"'.$data->id.'","user":"","checkout":""}';
				$notification = $messages['delete'];
				$good_request = $this->send_curl( $update_json );
			break;

			case 'CLEAR':

				$name = $this->get_checkout_name();

				// For each claimed machine sent vacate request
				foreach( $data->claimed_vms as $index => $id ) {

 					$update_json = '{"id":"'.$id.'","user":"","checkout":""}';

					$good_request = $this->send_curl( $update_json );

					$notification = 'All Your VMS have been Vacated';

				}

			break;

			default:
				// Otherwise notify the user something is broken
				return 'Something went horribly wrong.';
			break;
		} 

		if ( $good_request === false ) {
		  	return $curl_error;
		}
		else {
		  return $notification;
		}
	}


	/**
	* Send Request to the Server
	*
	* Description: Accepts a JSON string that is used to 
	* populate a php CURL command
	* @param 'sting'/JSON : update statement used in curl
	* @return boolean : TRUE on successful curl
	* -OR-
	* @return boolean : FALSE on unsuccessful curl
	*/
	protected function send_curl( $update_json ) {

		$chlead = curl_init();

		// set URL and other appropriate options
		$options = array(
			CURLOPT_URL => $this->url,
		  	CURLOPT_HTTPHEADER => array('Content-Type: application/json','Content-Length: ' . strlen( $update_json ) ),
		  	CURLOPT_VERBOSE => 1,
		  	CURLOPT_RETURNTRANSFER => true,
		  	CURLOPT_CUSTOMREQUEST => "PUT",
		  	CURLOPT_POSTFIELDS => $update_json,
		  	CURLOPT_SSL_VERIFYPEER => 0,
		);

		curl_setopt_array($chlead, $options);

		$curl_result = curl_exec($chlead);
		$curl_error = curl_error($chlead);
		curl_close($chlead);

		// If an Error did NOT occur return true, ELSE return false
		return $curl_error === '' ? true : false;
	}

	/**
	* Update this Workflow to a newer Version
	* NOTE: Not sure how to accomplish this yet
	* Overwrite existing files
	*/	
	protected function update_workflow() {
		#still trying to figure out the best way to do this
	}

}

// $VMC = new VMC(); echo $VMC->request_vm_search( "{query}" ); // used for development

?>