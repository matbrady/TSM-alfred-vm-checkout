<?php 

class VMS {

	protected $wf;
	protected $query;
	protected $name;
	protected $vmData;
	protected $pattern;
	protected $url;

	/**
	* Gets the enitre list of VMS from the server
	*
	* @param string $query used to search vm names
	* @return none
	*/
	function __construct( $query ) {

		# Check if the Workflows Class exists: else require it
		if ( !class_exists('Workflows') ) {
			require_once('workflows.php');
		}

		$this->wf = new Workflows();

		# set variables
		$this->query = $query;
		$this->pattern = "/".$this->query."/i";
	}

	/**
	*
	*/
	protected function getName() {
		return file_get_contents( 'name.txt' );
	} 

	/**
	* Gets the enitre list of VMS from the server
	* set the data request url
	*
	* @param string url to request data from
	* @return array of all the VMS
	*/
	protected function getData( $url = "http://vm-checkout.threespot.dev/vm.php" ) {
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
	public function searchVmData( $url ) {

		$this->vmData = $this->getData( $url );

		foreach( $this->vmData as $index=>$vm ) {

			if ( isset($vm->user) &&  $vm->user === "" && ( preg_match( $this->pattern, $vm->vm, $matches) || $this->query === "")  ) {

				$vm->url = $this->url;
				$vm->name = $this->getName();
				$this->wf->result( $index , json_encode($vm) , $vm->vm, "Checkout Virtual Machine ".$vm->vm, 'icon.png', 'yes' );
			}
		}

		$results = $this->wf->results();

		if ( count( $results ) == 0 ) {
			$this->wf->result( 'googlesuggest', $this->query, 'No Suggestions', 'No search suggestions found. Search Google for '.$this->query, 'icon.png' );
		}

		return $this->wf->toxml();
	}

}

?>





