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

	protected $pattern;
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
		,'clearvm' => array(
			"task" => "Clear",
			"subtitle" => "Clear Your VM Checkout Name"
		)
	);

	public function __construct() {
		parent::__construct();  
	}

	/**
	* Sets the Pattern for Searching
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

			// IF user matches a VM task name OR is an empty string
			if ( preg_match( $this->pattern, $func['task'], $matches) || $query === "" ) {

				// Create a result with the Task name and subtitle
				$this->result( 'demo', $query, 'task: '.$func['task'] , $func['subtitle'], 'icon.png', 'yes' );

			}

		}

		return $this->toxml();

	}

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

?>