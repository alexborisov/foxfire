<?php

/**
 * FOXFIRE TRIE - FLATTEN
 * Flattens a trie structure to matrix
 * 
 * @see http://en.wikipedia.org/wiki/Trie
 * @see http://en.wikipedia.org/wiki/Linked_list
 * 
 * @version 1.0
 * @since 1.0
 * @package FoxFire
 * @subpackage Database
 * @license GPL v2.0
 * @link https://github.com/FoxFire/foxfirewiki/DOCS_FOX_db_top
 *
 * ========================================================================================================
 */

class FOX_trie_flatten {

	
	var $trie;				    // Trie structure to convert	
	var $columns;				    // Key columns array	
	var $max_depth;				    // Maximum depth to iterate to
	
	var $ctrl;				    // Control args
	var $null_token;			    // Null token identifier string	
	var $mode;				    // Operating mode - 'control' or 'data'
	
	var $children;				    // Array of child nodes
	
	var $iterator;				    // Iterator object
	
	var $result;				    // Flattened trie structure
	

	// ============================================================================================================ //


	/**
         * Implodes an array of heirarchical datastore keys into a single query $args array
         *
         * @version 1.0
         * @since 1.0
	 * 
	 * @param array $trie | Trie structure
	 * @param array $columns | Array of column names
	 *
	 * @param array $ctrl | Control args
	 *	=> VAL @param string $null_token | String to use as null token 	 
	 *
         * @return array | Exception on failure. True on success.
         */
	
	function __construct($trie, $columns, $ctrl=null){
		
	    
		$this->trie = $trie;			
		$this->columns = $columns;
		
		$ctrl_default = array(
			'null_token' => '*',
			'mode'=>'control'
		);
		
		$this->ctrl = FOX_sUtil::parseArgs($ctrl, $ctrl_default);	
		
		$this->null_token = $this->ctrl['null_token'];	
		$this->mode = $this->ctrl['mode'];
		
		if( $this->mode == 'data' ){
		    
			$this->data_key = array_pop($this->columns);		
		}
		
		$this->max_depth = count($this->columns) - 1;		
						
		try {		    
			$result = $this->build();
		}
		catch (FOX_exception $child) {
		    
			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Error during build process",
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>$child
			));		    
		}
		
		return $result;
		
	}		
	
	
	/**
         * Optimizes the $args matrix passed during instantiations, and assembles it into an object trie
         *
         * @version 1.0
         * @since 1.0
	 *
         * @return bool | Exception on failure. True on success.
         */
	
	public function build() {
	    

		try {
		    
			$this->iterator = new FOX_trie_flatten_iterator(array(

				'base'	    => $this,
				'parent'    => null,			    
				'value'	    => $this->null_token,   // Since this is the root node, we 
								    // set its key to NULL			    
				'trie'	    => $this->trie,			    			    
				'depth'	    => -1,		    // We set the depth to -1 to flag this as the root
								    // node. -1 is used as the flag value because its 
								    // impossible for a node to have an order less than zero.
			    
								    // @see http://en.wikipedia.org/wiki/Trie
			));	
			
		}
		catch (FOX_Exception $child) {

			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Error creating root node",
				'data'=>array("trie"=>$this->trie, "columns"=>$this->columns, 
					      "max_depth"=>$this->max_depth, "ctrl"=>$this->ctrl),
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>$child
			));
		}	    
	    
		return true;
		
	}
	
	
	public function render() {
		
	    
		try {
			$this->iterator->reduce();
		}
		catch (FOX_Exception $child) {

			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Error during reduce",
				'data'=>array("trie"=>$this->trie, "columns"=>$this->columns, 
					      "max_depth"=>$this->max_depth, "ctrl"=>$this->ctrl),
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>$child
			));
		}		

		
		try {
			$this->iterator->render();
		}
		catch (FOX_Exception $child) {

			throw new FOX_exception( array(
				'numeric'=>3,
				'text'=>"Error during render",
				'data'=>array("args"=>$this->args, "columns"=>$this->columns, 
					    "max_depth"=>$this->max_depth, "ctrl"=>$this->ctrl),
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>$child
			));
		}		    


		return $this->result;				
		
	}
	
	
	/**
         * Recursively dumps the entire node tree
         *
         * @version 1.0
         * @since 1.0
	 *
	 * @param int $depth | Depth of this node in the tree. First node is (int)0
         * @return string | Node tree in string form
         */
	
	public function dump(){	    

		echo "\n\n";	    
		echo $this->iterator->dump(0);
		echo "\n\n";		
	}


	
} // End of class FOX_trie_flatten

?>