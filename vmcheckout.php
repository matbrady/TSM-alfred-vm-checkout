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

	// public $results;
	protected $pattern;
	protected $tasks = array(
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

	public function __construct() {
		// print_r( $this->request('http://vm-checkout.threespot.dev/vm.php') );
		parent::__construct();  
	}

	protected function set_match_pattern( $query ) {

		$this->pattern = "/".$query."/i";

	}

	public function tasks( $query ) {

		$this->set_match_pattern( $query );

		foreach( $this->tasks as $index => $func ) {

			if ( preg_match( $this->pattern, $func['task'], $matches) || $query === "" ) {

				$this->result( 'demo', $query, 'Task: '.$func['task'] , $func['subtitle'], 'icon.png', 'yes' );

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