<?php 

require_once('workflows.php');

/** 
* Virtual Machine Checkout 
* 
* Classed used for interatctive with the TS
* Virtual Machine Checkout
* 
* @version 	 0.1.0
* @author 	 Mathew Brady <mat.brady@threespot.com> 
* @copyright 2013 
* @license 	 http://www.php.net/license/3_01.txt PHP License 3.01 
*/ 
class VMC extends Workflows { 

	protected $query;
	protected $pattern;
	protected $checkout_name;
	protected $tasks = array(  // comma placement is for easier commenting of code during development
		'claim' => array(
			"task" => "Claim",
			"subtitle" => "Claim a Virtual Machine"
		)
		,'vacate' => array(
			"task" => "Vacate",
			"subtitle" => "Vacate a Claimed Virtual Machine"
		)
		,'reset_name' => array(
			"task" => "Reset Name",
			"subtitle" => "Reset Your Checkout Name"
		)
		,'set_name' => array(
			"task" => "Set Name",
			"subtitle" => "Set Your Checkout Name"
		)
		// ,'clearvm' => array(
		// 	"task" => "Clear",
		// 	"subtitle" => "Clear Your VM Checkout Name"
		// )
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
			if ( $this->is_name_set() !== false && $func['task'] === "Set Name"  ||
				 $this->is_name_set() === false && $func['task'] === "Reset Name" ) 
			{
				continue; // don't make a result
			}

			// IF user matches a VM task name OR is an empty string
			else { 

				if ( preg_match( $this->pattern, $func['task'], $matches) || $query === "" ) {

					// Create a result with the Task name and subtitle
					$this->result( 'demo', $func['task'], $func['task'], $func['subtitle'], 'icon.png', 'yes' );

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
	public function request_vm_search( $query ) {

		$this->query = $query;

		// Name is set
		if ( $this->is_name_set() !== false ) {
			
		}
		else {

			$prompt = array(
				"data" => $this->query,
				"title" => "Set Name: ".$this->query,
				"subtitle" => "Enter a name to be used to checkout a VM",
				"image" => "icon.png",
				);

			return $this->prompt_user( $prompt );
		}
	}

	/**
	* Prompt User for Checkout Name
	*
	* Description: Generate 
	*/
	protected function prompt_user( $prompt ) {

		$this->result( 'demo', $prompt['data'], $prompt['title'], $prompt['subtitle'], $prompt['image'], 'yes' );

		return $this->toxml();

	}

	/**
	*/
	public function run_task() {}

	/**
	* Set the Name used to Claim and Vacate a VM listing
	*/
	protected function set_checkout_name() {

	}

	/**
	* Claim an Available Virtual Machine
	*/	
	protected function claim_vm() {

	}

	/**
	* Vacate a Claimed Virtaul Machine
	*/	
	protected function vacate_vm() {

	}

	/**
	* Update this Workflow to a newer Version
	*/	
	protected function update_workflow() {

	}

}

// $VMC = new VMC();

// echo $VMC->request_vm_search( "{query}" ) ;

?>